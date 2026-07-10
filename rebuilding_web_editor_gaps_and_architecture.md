# Rebuilding Nexura: Web Editor & Multi-Tenancy Blueprint

When building a lightweight web page editor with multi-tenancy from scratch, you want to avoid the common pitfalls of complex editors: slow page refreshes, broken live styling, and messy state syncs.

This guide identifies the key gaps traditional designs fall into, explains how Nexura's actual implementation resolves each gap, and points to the real entry points in the codebase.

---

## Gaps in Common Architectures (and How Nexura Avoids Them)

### 1. The "Full Iframe Reload" Trap
> Changing a font size or text color should never trigger a full iframe reload. Full reloads disrupt the user experience and create a lagging builder.

**Nexura's answer:** The editor does not use an iframe at all. The live preview canvas lives in the **same Vue tree** as the inspector sidebar, rendered by a recursive `<RenderNode>` component. Inspector `v-model` bindings write directly to a `selectedNode` ref (provided by the parent `Editor.vue`), which is the **same reactive object** that sits inside `blocks.value`. Vue's Proxy-based reactivity propagates the change to the canvas immediately — **no IPC, no postMessage, no reload**.

### 2. Static Tailwind Compilation vs Dynamic Styles
> Tailwind utility classes are compiled at build time. When users select custom colors or paddings, you cannot easily map these dynamically unless you use CSS Custom Properties.

**Nexura's answer:** Each block component receives a flat `props` dictionary via a `blockProps` prop and emits `--dynamic-*` CSS custom properties from a `computedStyles` accessor. The wrapper (`RenderNode`) also applies inline `padding` and `backgroundColor` directly from `node.props`. For responsive sizing, the canvas root declares `container-type: inline-size`, so Tailwind v4's `@md:` / `@lg:` container-query variants on block templates respond to the simulated device width rather than the window.

### 3. Missing DOM Anchors (`data-id`)
> If components and blocks lack clean root wrappers with unique IDs, the parent editor cannot target them for live CSS patching or highlight bounds.

**Nexura's answer:** Because the canvas is a single Vue tree, "targeting" just means holding a reactive ref. `selectedNode = ref(null)` is provided via `provide('canvasSelection', { selectedNode, selectNode })`. Click-handler `@click.stop="selectBlock(node)"` on every `RenderNode` sets the ref; the `:class="{ 'is-selected': canvasSelection?.selectedNode?.id === node.id }"` predicate draws the selection ring. No DOM queries, no `data-block-id` fiddling.

### 4. Oversized Update Payloads
> Transferring massive nested JSON on every keypress creates substantial overhead. Updates should send minimal deltas.

**Nexura's answer:** Updates are debounced at 400ms in the browser, so a burst of keystrokes collapses to a single `POST /editor/save`. The request body **is** the full `draft_config` array (the database accepts the canonical JSON), but the request cadence is bounded by the debounce window, and Inertia v3's `useHttp` hook lets the editor `.cancel()` a superseded in-flight request before issuing a new one — protecting against out-of-order writes under heavy typing.

### 5. Missing Tenant Boundary on Editor Mutations
> If tenant context is implicit (e.g. assumed from the URL but never actually enforced on the query), it is easy for a manipulated request to leak into another tenant's data.

**Nexura's answer:** `IdentifyTenant` middleware binds `app('currentTenant')` on every subdomain request. `Page` carries a `TenantScope` global Eloquent scope that injects `WHERE tenant_id = currentTenant->id` automatically. `TenantPageSaveController::store` does `Page::findOrFail($page_id)` — a cross-tenant `page_id` therefore 404s because the scope already limited visibility. An explicit `auth()->id() !== $tenant->user_id` check also short-circuits non-owners with 403 before any mutation.

---

## Logical Architecture Diagram

```mermaid
sequenceDiagram
    participant User as User Interaction
    participant Editor as Editor.vue (Inertia + Vue)
    participant State as Reactive blocks Ref
    participant Registry as blockRegistry.ts
    participant API as Laravel Server + TenantScope
    participant DB as pages.draft_config / published_config

    User->>Editor: Inspects a block (sets selectedNode)
    Note over Editor,Registry: Inspector reads<br/>activeBlockDefinition.inspectorFields
    User->>Editor: Edits a field (v-model on selectedNode.props.key)
    Editor->>State: Mutation via reactive Proxy
    State->>Editor: <RenderNode> re-renders the canvas (no IPC)
    State->>Editor: Deep watch fires after 400ms debounce
    Editor->>API: useHttp.post('/editor/save', { page_id, draft_config })
    Note over Editor: previous in-flight request is .cancel()'d first
    API->>API: ValidatesBlockSchema + TenantScope + auth()->id() check
    API->>DB: UPDATE pages SET draft_config = ?

    User->>Editor: Clicks Publish
    Editor->>API: useHttp.post('/editor/publish', { page_id })
    API->>DB: BEGIN; UPDATE pages SET published_config = draft_config; COMMIT
```

---

## Database Logical Mapping

| Table | Field | Type | Purpose |
| :--- | :--- | :--- | :--- |
| **tenants** | `id` | int | Primary Key |
| | `user_id` | FK, unique | Owner of the workspace (one tenant per user) |
| | `subdomain` | String, indexed, unique | Subdomain URL resolver |
| | `created_at` / `updated_at` | timestamp | Audit |
| **pages** | `id` | int | Primary Key |
| | `tenant_id` | FK cascade | Tenant scope key |
| | `slug` | String (default `home`) | Unique within tenant |
| | `title` | String, nullable | Page title |
| | `is_homepage` | bool | Single-true invariant per tenant |
| | `sort_order` | int | Manual ordering |
| | `draft_config` | JSON, nullable | Active editing configuration |
| | `published_config` | JSON, nullable | Public-facing configuration |
| Unique | `[tenant_id, slug]` | composite | Pages are unique per tenant |

A copy from `draft_config` to `published_config` is an atomic DB transaction inside `TenantPageSaveController::publish`.

---

## How to Start Building Fresh (Step-by-Step)

### Step 1: Initialize the Multi-Tenant Routing Skeleton

* Add `IdentifyTenant` middleware (extracts `{tenant}` subdomain, looks up `Tenant::where('subdomain', ...)->firstOrFail()`, binds the model as `app('currentTenant')`, shares it with Blade views).
* Register two route groups in `routes/web.php`:
  * Central domain (default landing, auth, central dashboard redirecting to subdomain).
  * Tenant domain (`{tenant}.central_domain`) wrapped in `IdentifyTenant`, with an authed `/editor` prefix and a public `/{slug?}` catch-all declared *after* the `/editor` routes.

### Step 2: Establish Data Isolation

* Add `tenant_id` FK to `pages`, unique index on `[tenant_id, slug]`.
* Create `TenantScope` global scope: if `app()->bound('currentTenant')`, append `WHERE tenant_id = currentTenant->id`.
* Attach the scope from `Page` boot.

### Step 3: Build the Editor Canvas

* Create `Editor.vue` seeded from `page.draft_config` into a `blocks` reactive ref.
* Build a recursive `RenderNode.vue` that wraps each node with editor chrome (`@click.stop` selection, `:style` from `node.props`, drag handle) and renders `<component :is="blockRegistry[node.type]" :block-props="node.props">`.
* Wrap root blocks in `vuedraggable` and nest `<draggable v-model="node.children">` per node when `allowedChildren` allows puts.
* Provide a `canvasSelection { selectedNode, selectNode }` reactively accessible from any descendant.
* Render the inspector sidebar **inline** in `Editor.vue` by iterating `blockDefinitions[type].inspectorFields` and binding `v-model` to `selectedNode.props.<key>`.

### Step 4: Add the Auto-Save and Publish Engine

* `useHttp({ page_id, draft_config })` lets you `http.post('/editor/save')`.
* Track `currentSaveVisit`: when a new save fires, cancel the previous visit first.
* `queueSave` debounces 400ms and short-circuits while `isDragging.value`.
* `forceSave` cancels the timeout and awaits the request — used at drag-end and as a prelude to publish.
* `POST /editor/publish` wraps the `draft_config -> published_config` copy in a DB transaction.

### Step 5: Implement Server-Side Block Validation

* `config/blocks.php` defines `types` and the `nesting` matrix (`LayoutGrid -> [LayoutColumn]`, `LayoutColumn -> all block types`).
* `ValidatesBlockSchema` (a `ValidationRule`) recursively asserts `id`, `type` (must be in `config('blocks.types')`), `props` array, and respects parent-child nesting.
* `TenantPageSaveController::store` attaches the rule to the `draft_config` input.

### Step 6: Render the Public Storefront

* `TenantPublicSiteController::show` resolves the tenant, finds the page (slug or `is_homepage`), 404s on missing/empty, and returns `Inertia::render('Tenant/PublicPage', ['tenant', 'page'])`.
* `PublicPage.vue` provides `blockRegistry` + `isEditable: false` and iterates `page.published_config` rendering `<RenderPublicNode>`.
* `RenderPublicNode.vue` is a clean recursive wrapper that omits editor chrome, still uses `<component :block-props="node.props">`, and catches per-block errors via `onErrorCaptured`.

### Step 7: Add Device Preview and Container Queries

* Scope `.canvas-runtime { container-type: inline-size; contain: layout style; }` so children can use Tailwind v4's `@container` / `@md:` variants relative to the canvas width (not the viewport).
* `viewMode` toggle sets an inline `maxWidth` on `.canvas-runtime` — `375px` mobile, `768px` tablet, `none` desktop. As the max-width changes, container-query breakpoints inside nested blocks re-evaluate instantly and the canvas genuinely simulates the chosen device width.
