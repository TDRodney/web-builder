# Web Builder & Multi-Tenant Integration Guide

Building a web page builder within a multi-tenant system is one of the best ways to learn advanced full-stack architecture. It challenges you to design:
1. A **data schema** that holds dynamic layout configurations as a recursively-nested AST.
2. A **reactive coupling** between the editor sidebar and the live canvas — both running in the same Vue tree, no iframe bridge.
3. Strict **tenant isolation** to ensure organizers can only build/view their own sites.

This guide explains how Nexura's builder is architected and how you can implement a similar system.

---

## Data Schema: The Config-Driven Layout

Nexura stores every page's structure as a JSON AST inside the `pages` table under two columns:
* `draft_config`: Where editor changes accumulate.
* `published_config`: The live configuration shown to public visitors.

Both columns follow the same unified shape:

```json
[
  {
    "id": "hero-1",
    "type": "HeroBlock",
    "props": {
      "padding": 40,
      "backgroundColor": "#ffffff",
      "headline": "Welcome to our Store",
      "subheadline": "Built with our engine."
    },
    "children": []
  },
  {
    "id": "grid-1",
    "type": "LayoutGrid",
    "props": { "columns": 3, "gap": "1rem", "padding": 20, "backgroundColor": "transparent" },
    "children": [
      {
        "id": "col-1",
        "type": "LayoutColumn",
        "props": { "span": 1, "padding": 20, "backgroundColor": "transparent" },
        "children": [
          {
            "id": "feat-1",
            "type": "FeatureBlock",
            "props": { "title": "Blazing Fast", "body": "60fps reactive rendering.", "padding": 20, "backgroundColor": "#f8fafc" },
            "children": []
          }
        ]
      }
    ]
  }
]
```

Key principles:
* `props` is the **only** data carrier — there is no separate `styles` or `content` key, so block components have one obvious place to look.
* `children` is the recursive AST edge — the same Node shape repeats at every level.
* Validation rules (see `app/Rules/ValidatesBlockSchema.php`) walk this tree at save time and assert each `type` is registered in `config/blocks.php`, that `id` is a string, that `props` is an array, and that children only appear in parents whose `allowedChildren` whitelist permits them.

---

## The Builder Workspace: Single-Tree Reactive Canvas

The editor is one Vue component (`resources/js/pages/Tenant/Editor.vue`) containing both panels:

```
┌────────────────────────────────────────────────┐
│                  EDITOR.VUE                     │
├─────────────────────┬──────────────────────────┤
│   CANVAS (draggable)│   INSPECTOR (sidebar)    │
│                     │                          │
│   <RenderNode> ─────┼─►  v-model="selectedNode │
│   renders blocks     │    .props.<key> inputs  │
│   from `blocks` ref  │                          │
│                     │   blockRegistry.ts       │
│   @click.stop ───────┼─►  drives inspector form │
│   → canvasSelection  │                          │
│     .selectNode(node)│                          │
└─────────────────────┴──────────────────────────┘
```

Because both the **canvas** and the **inspector** share the same reactive `selectedNode` ref (which points into the `blocks.value` array), there is no Inter-Process Communication to maintain — Vue's Proxy-based reactivity is the bridge.

### 1. Discovering Blocks From the Registry

`resources/js/lib/blockRegistry.ts` is the single source of truth. It exports:
* `blockComponents` — a `{type -> Vue component}` lookup map. Both `Editor.vue` and `PublicPage.vue` provide this.
* `blockDefinitions` — declarative metadata per type. Each entry carries `defaultProps`, an `inspectorFields[]` array (the schema the inspector uses to generate form controls), an `icon`, a `category`, and an `allowedChildren[]` whitelist.

### 2. Recursive Canvas Rendering

```vue
<component
  :is="blockRegistry[node.type]"
  :node-id="node.id"
  :block-props="node.props"
>
  <draggable v-if="node.children" v-model="node.children"
    :group="{ name: 'canvas-tree', put: (to, from, dragEl) => checkAllowedChild(node.type, dragEl.getAttribute('data-type')) }">
    <template #item="{ element }">
      <RenderNode :node="element" />
    </template>
  </draggable>
</component>
```

`RenderNode.vue` adds editor chrome (drag handle, hover border, click-select, error boundary) on top of the block. Click on any wrapper calls `canvasSelection.selectNode(node)` — setting the reactive ref that powers the inspector.

### 3. Live Inspector Updates

`v-model` on inspector inputs binds directly to `selectedNode.props.<key>`. Because `selectedNode` is the same object that lives inside `blocks.value`, mutating a field immediately re-renders the matching block on the canvas. No serialization, no IPC.

### 4. Auto-Save (Race-Condition-Safe)

A deep `watch(blocks)` fires on any mutation. The watcher:
1. Pushes a JSON-clone of the previous state onto the `undoStack`.
2. Clears the `redoStack`.
3. Debounces a `POST /editor/save` for 400ms (skipped while dragging).
4. Uses Inertia's `useHttp` hook with a cancellation tracker: if another save is in-flight, `.cancel()` it before issuing the new request — protecting against out-of-order writes under heavy typing.

### 5. Publish Flow

`POST /editor/publish` first awaits `forceSave()` (flush every pending change), then runs a DB transaction on the server that copies `draft_config -> published_config`. Public visitors see the new layout immediately.

---

## Multi-Tenant Integration Rules

### 1. Routing & Tenant Context

All tenant routes live under `Route::domain('{tenant}.' . central_domain)` in `routes/web.php`, gated by `IdentifyTenant` middleware.

```php
Route::domain('{tenant}.' . config('app.central_domain'))
    ->where(['tenant' => '^[a-z0-9-]+$'])
    ->middleware(['web', 'identify.tenant'])
    ->group(function () {
        Route::middleware('auth')->prefix('editor')->group(function () {
            Route::get('/', [TenantEditorController::class, 'edit'])->name('tenant.editor');
            Route::post('/save', [TenantPageSaveController::class, 'store'])->name('tenant.page.save');
            Route::post('/publish', [TenantPageSaveController::class, 'publish'])->name('tenant.page.publish');
        });
        Route::get('/{slug?}', [TenantPublicSiteController::class, 'show'])->where('slug', '.*');
    });
```

The middleware:
1. Pulls the `{tenant}` route parameter.
2. Looks up `Tenant::where('subdomain', $subdomain)->firstOrFail()` (instant 404 for unknown subdomains).
3. Binds the model into the container as `app('currentTenant')` and shares it globally with Blade.

### 2. Controller Authorization

Every tenant-scoped controller does two checks before mutating data:

```php
$tenant = app('currentTenant');
if (auth()->id() !== $tenant->user_id) {
    abort(403, 'Unauthorized access to this tenant.');
}

// The query below is auto-scoped by TenantScope
$page = Page::findOrFail($request->input('page_id'));
```

`Page::findOrFail` will 404 on a cross-tenant `page_id` because `TenantScope` injects `WHERE tenant_id = currentTenant->id` into every query on the `Page` model.

### 3. Client-Side Nesting Rules

Drag-and-drop enforces `allowedChildren` *before* the request reaches the server. `RenderNode` passes a `put` predicate to `vuedraggable`:

```js
:group="{ name: 'canvas-tree', put: (to, from, dragEl) => checkAllowedChild(node.type, dragEl.getAttribute('data-type')) }"
```

So a `HeroBlock` can never be drag-dropped into a `LayoutGrid` (which only accepts `LayoutColumn` children). The same rule is re-validated server-side via `ValidatesBlockSchema` reading `config('blocks.nesting')`.

### 4. Draft vs Published States

* **Drafting**: `POST /editor/save` accepts the full `draft_config` array. The custom `ValidatesBlockSchema` rule walks the recursive AST first; only then is `pages.draft_config` updated.
* **Publishing**: `POST /editor/publish` runs a transaction: `UPDATE pages SET published_config = draft_config WHERE id = ?` — atomic and tenant-scoped.
* **Public**: `TenantPublicSiteController::show` reads `published_config`, returns it via `Inertia::render('Tenant/PublicPage', ['tenant', 'page'])`. `PublicPage.vue` provides the same `blockRegistry` but with `isEditable: false`, and renders `<RenderPublicNode>` recursively without editor chrome.
