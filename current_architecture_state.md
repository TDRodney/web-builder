# System Architecture

This document outlines the current architecture of the multi-tenant web builder platform. The system uses a hybrid approach, leveraging Inertia v3 + Vue 3 for both the editor and the public storefront, backed by a config-driven JSON layout model with strict tenant isolation enforced at the database layer.

## 1. Multi-Tenancy & Routing

The system operates as a multi-tenant platform sharing a single database. Tenant isolation is managed primarily through subdomain routing.

* **Middleware (`IdentifyTenant`)**: Intercepts all requests directed to tenant subdomains (`{tenant}.domain.localhost`). It extracts the `{tenant}` route parameter, looks the subdomain up against the `tenants` table via `Tenant::where('subdomain', $subdomain)->firstOrFail()` (instant 404 for unknown tenants), binds the resolved `Tenant` model into Laravel's container as `app('currentTenant')`, shares it globally with all Blade views, and forgets the route parameter so it does not pollute controller signatures.
* **Database Isolation**: Entities belonging to a specific workspace are strictly linked via foreign keys (`tenant_id` on the `pages` table) and guarded at the Eloquent layer by a `TenantScope` global scope that automatically applies `WHERE tenant_id = app('currentTenant')->id` to every query on scoped models. A foreign-tenant `page_id` therefore 404s transitively.
* **Routing Segregation**: `routes/web.php` defines two domain groups:
  * **Central domain group** (`app.central_domain`, default `domain.localhost`): Welcome landing, register/login/logout, central `/dashboard` (which redirects to the user's tenant subdomain when a tenant exists), settings routes.
  * **Tenant domain group** (`Route::domain('{tenant}.' . central_domain)` with `where(['tenant' => '^[a-z0-9-]+$'])`), gated by the `IdentifyTenant` middleware:
    * Authed inner group (`/dashboard`, `/editor` prefix containing `GET /editor`, `POST /editor/save`, `POST /editor/publish`, and standard `/editor/pages` CRUD).
    * Public catch-all (`GET /{slug?}` -> `TenantPublicSiteController@show`, `where('slug', '.*')`), declared *after* the `/editor` routes so precedence is preserved.

## 2. Database Mapping

The database schema separates tenant identity from page layout data:

| Table | Purpose | Key Columns |
| :--- | :--- | :--- |
| `tenants` | Handles subdomain mapping and ownership. Enforces a one-tenant-per-user invariant via a unique `user_id` foreign key. | `id`, `user_id` (FK unique), `subdomain` (string unique), timestamps |
| `pages` | Stores layout configurations for individual pages within a tenant's site. | `id`, `tenant_id` (FK cascade), `slug` (default `home`), `title`, `is_homepage` (bool), `sort_order` (int), `draft_config` (JSON nullable), `published_config` (JSON nullable), unique `[tenant_id, slug]` |

The `users` -> `tenants` -> `pages` chain is the full data backbone. Both JSON columns on `pages` are cast to `array` via method-based Eloquent casts.

## 3. Web Builder Architecture (Inertia + Vue)

The web editor is the core of the platform, built as a highly interactive single-page application using Inertia.js v3 and Vue 3. There is **no iframe** and **no `postMessage` bridge** — the live preview canvas is rendered natively in the same Vue tree as the inspector sidebar, so state changes propagate through Vue's reactivity system directly.

```mermaid
graph TD
    User[User Interaction] --> Editor[Editor.vue (Inertia Page)]
    Editor -->|Live Edits| Canvas[Native Vue RenderNode + vuedraggable]
    Editor -->|Deep Watcher| State[Local draft_config State]
    State -->|Debounced + Cancellation-Safe| API[POST /editor/save]
    API -->|Update| DB[(pages.draft_config)]

    User -->|Clicks Publish| PublishAPI[POST /editor/publish]
    PublishAPI -->|Copy draft -> published| DB
```

### Key Components

* **Editor Interface (`resources/js/pages/Tenant/Editor.vue`)**: The main interface is a unified Vue component containing both the inspector sidebar and the live preview canvas. It holds the local `blocks` reactive array (seeded from `page.draft_config` or a default HeroBlock if empty), the `selectedNode` ref (aliased as `selectedBlock` and provided under two keys: `selectedBlock` and `canvasSelection { selectedNode, selectNode }`), the `viewMode` device-preview state, and the undo/redo stacks (`undoStack` / `redoStack`).
* **Centralized Block Registry (`resources/js/lib/blockRegistry.ts`)**: The single source of truth for block metadata. Exports:
  * `blockComponents` — a `{type: Component}` map (HeroBlock, FeatureBlock, LayoutGrid, LayoutColumn, AtomicText). Used by both the editor and the public storefront.
  * `blockDefinitions` — an array of `BlockDefinition` objects, each containing `type`, `label`, `category`, `icon`, `defaultProps`, `inspectorFields[]` (declarative inspector form schema), and an optional `allowedChildren[]` whitelist.
  * `getBlockDefinition(type)` — lookup helper.
* **Inline Inspector**: There is **no dedicated Inspector component directory** — the inspector form is generated inline inside `Editor.vue` by iterating over `activeBlockDefinition.inspectorFields` and rendering the matching control (`range` -> `<input type=range>`, `color` -> color picker, `number`, otherwise text input). The `v-model` on each input binds directly to `selectedNode.props.<key>`. Because `selectedNode` is a local `ref` (not a readonly prop), the bindings flow through Vue's reactive proxy back into the `blocks` tree and the canvas re-renders live.
* **Recursive Canvas Rendering**: `<RenderNode>` wraps each block with editor chrome (drag handle, hover border, click-select) and renders `<component :is="blockRegistry[node.type]" :node-id :block-props="node.props">`. For nested children, a `<draggable v-model="node.children">` is slotted inside the block component, whose `put` predicate calls `checkAllowedChild(parentType, childType)` to honor `allowedChildren` from the registry. The `<RenderNode>` recursively renders itself for each child. An `onErrorCaptured` boundary shows a red error panel for any failing block.
* **Drag and Drop**: Layout manipulation (moving sections, reordering blocks) is handled seamlessly on the client side via `vuedraggable` operating directly on the reactive Vue state array. A root-level `<draggable v-model="blocks">` reorders top-level blocks; nested draggables handle children. Drag start/end toggle `isDragging`, and end triggers `forceSave()`.
* **State Management & Auto-Save**: A deep `watch(blocks)` observer pushes the previous snapshot onto `undoStack`, clears `redoStack`, snapshots again, and queues a save. The `saveCanvasState` function uses Inertia v3's `useHttp` hook with a **race-condition-safe cancellation tracker**: if a `currentSaveVisit` exists when the next save fires, the prior request is `.cancel()`'d before issuing the new one. The `queueSave` function debounces at 400ms and **skips while dragging** (`isDragging` guard). The `forceSave` function cancels any pending timeout and awaits `saveCanvasState()` directly — used at drag-end and as a prelude to publish.
* **Undo / Redo**: `undoStack` and `redoStack` hold deep JSON-clone snapshots of `blocks.value`. Undo/redo set `isTraveling = true` (suppressing the watch handler from pushing intermediate states), pop the target state, restore to `blocks.value`, sync `http.draft_config`, queue a save, and unblock on `nextTick`.
* **Add Block**: `addBlock(type)` looks up the definition via `getBlockDefinition(type)`, clones `defaultProps`, creates `{id, type, props, children: []}`. Special-cases `LayoutGrid` by pre-seeding three `LayoutColumn` children. If a block with `.children` is selected, the new block is nested only when its type satisfies `parentDef.allowedChildren`; otherwise it is appended to root `blocks.value`.

## 4. Public Storefront

Both the backend editor and the public-facing live site are **Inertia v3 + Vue 3** single-page applications — there is no Blade rendering of layout content. Block components are **shared** between the editor and the storefront, gated by an injected `isEditable` flag.

* **Controller (`TenantPublicSiteController::show`)**: Resolves `currentTenant`, looks up the page (empty slug -> `where('is_homepage', true)` with a fallback to `where('slug', 'home')`; otherwise `where('slug', $slug)`), 404s on missing page or empty `published_config`, and returns Inertia with `Inertia::render('Tenant/PublicPage', ['tenant' => $tenant, 'page' => $page])`.
* **`PublicPage.vue`** (`resources/js/pages/Tenant/PublicPage.vue`): Provides `blockRegistry` (= `blockComponents`) and `isEditable: false`, then renders `<RenderPublicNode v-for="block in page.published_config">` for each top-level block.
* **`RenderPublicNode.vue`**: A read-only mirror of `RenderNode.vue` — recursive, no draggable, no `selectBlock`, no drag-handle. Renders `<component :is="blockRegistry[node.type]" :node-id :block-props="node.props">` and recurses into `node.children`. An `onErrorCaptured` boundary protects the public page from blow-ups in a single block.
* **Performance**: The public page ships the same Vue bundle as the editor minus the `RenderNode` chrome. SSR is enabled via the `@inertiajs/vite` plugin, so visitors receive a fully-constructed HTML document on first paint without waiting for hydration.

## 5. Unified Block Schema

Every node in both `draft_config` and `published_config` follows the same flat shape:

```
{ id: string, type: string, props: { ... }, children: Node[] }
```

`props` is a flat dictionary holding all styling, content, and configuration (no separate `styles` or `content` keys). `blockProps` is the prop name block components receive (`<component :block-props="node.props">`), which keeps the Vue prop named `props` free for the SFC's own `defineProps` consumption.

## 6. Block Validation

* **`config/blocks.php`** declares the canonical list of types and the parent->child nesting matrix:
  * `types: [HeroBlock, FeatureBlock, LayoutGrid, LayoutColumn, AtomicText]`
  * `nesting: LayoutGrid -> [LayoutColumn]`, `LayoutColumn -> [HeroBlock, FeatureBlock, LayoutGrid, LayoutColumn, AtomicText]`. (LayoutGrid confines any non-Column block from being a direct child.)
* **`app/Rules/ValidatesBlockSchema.php`**: A recursive `ValidationRule` invoked by `TenantPageSaveController::store` on the incoming `draft_config` array. For each node it asserts: `id` (string), `type` (string, present in `config('blocks.types')`), `props` (array), and if `children` exists, validates nested against `config('blocks.nesting')` rules before recursing.
* **Client-side enforcement**: `RenderNode::checkAllowedChild` consults the same `allowedChildren` whitelist at drag-time, refusing disallowed puts before they reach the server.

## 7. Container Queries & Device Preview

* **Editor canvas**: `.canvas-runtime` in `Editor.vue`'s scoped style applies `container-type: inline-size` plus `contain: layout style`, making the canvas a query container whose size reflects the device preview.
* **Device preview**: A `viewMode` toggle (desktop/tablet/mobile) drives `canvasMaxWidth` (mobile -> `375px`, tablet -> `768px`, desktop -> `none`). As `maxWidth` changes, the query container's inline size changes, so container-query breakpoints in nested blocks re-evaluate live.
* **Per-block queries**: `HeroBlock.vue` uses the Tailwind v4 `@md:text-6xl` variant on the headline, scaling from `text-4xl` to `text-6xl` when the nearest container is at the `md` breakpoint. Other blocks currently lack explicit container queries and rely on CSS custom properties (`--grid-columns`, `--column-grid-span`, etc.) bound from `blockProps`.

## 8. Tests

The Pest suite covers tenancy, editor save/publish flow, schema validation, and CRUD:

| Suite | Count | Coverage |
| :--- | :--- | :--- |
| `BlockRegistryValidationTest.php` | 6 | Valid config saves; rejects missing-id / unknown-type / missing-props / children-not-array / nesting-violation. |
| `TenantEditorTest.php` | 9 | Guest redirect, owner access, cross-tenant 403, save draft, cross-tenant save 403, publish, public site rendering, subpage routing, invalid subdomain 404, recursive AST save. |
| `TenantPageCrudTest.php` | 8 | List, unauthorized 403, create valid, slug validation, duplicate-slug-per-tenant allowed, update, homepage reassignment unsets others, delete non-homepage, cannot delete homepage. |
| `DashboardTest.php` | 2 | Guest redirect, authed visits dashboard. |
| `Auth/*` (Pest) | 7 | Standard Fortify auth flows. |
| `Settings/*` (Pest) | 2 | ProfileUpdate, Security. |

All Editor/storefront fixtures use the unified `{id, type, props, children}` schema and the factory `withHomePage` state seeds a single `HeroBlock` (with padding + backgroundColor + headline + subheadline in `props`) into both `draft_config` and `published_config`.

## 9. Key File Index

| Subsystem | File |
| :--- | :--- |
| Tenant resolution | `app/Http/Middleware/IdentifyTenant.php` |
| Data isolation scope | `app/Models/Scopes/TenantScope.php` |
| Tenant model | `app/Models/Tenant.php` |
| Page model | `app/Models/Page.php` |
| Editor entry controller | `app/Http/Controllers/TenantEditorController.php` |
| Save/publish controller | `app/Http/Controllers/TenantPageSaveController.php` |
| Page CRUD controller | `app/Http/Controllers/TenantPageController.php` |
| Public storefront controller | `app/Http/Controllers/TenantPublicSiteController.php` |
| Block schema validator | `app/Rules/ValidatesBlockSchema.php` |
| Block types & nesting matrix | `config/blocks.php` |
| Editor UI | `resources/js/pages/Tenant/Editor.vue` |
| Recursive canvas wrapper | `resources/js/components/BuilderBlocks/RenderNode.vue` |
| Recursive public wrapper | `resources/js/components/BuilderBlocks/RenderPublicNode.vue` |
| Block registry (metadata) | `resources/js/lib/blockRegistry.ts` |
| Block components | `resources/js/components/BuilderBlocks/{HeroBlock,FeatureBlock,AtomicText,LayoutGrid,LayoutColumn}.vue` |
| Public page entry | `resources/js/pages/Tenant/PublicPage.vue` |
| Routes | `routes/web.php` |
| Migrations | `database/migrations/{2026_06_25_135231_create_tenants_table, 2026_06_25_135303_create_pages_table, 2026_07_05_150211_add_metadata_to_pages_table}.php` |
| Factories | `database/factories/{TenantFactory, PageFactory}.php` |
