# System Architecture

This document outlines the current architecture of the multi-tenant web builder platform. The system uses a hybrid approach, leveraging Inertia and Vue for highly interactive editing interfaces and Laravel Blade for lightning-fast public storefronts.

## 1. Multi-Tenancy & Routing

The system operates as a multi-tenant platform sharing a single database. Tenant isolation is managed primarily through subdomain routing.

*   **Middleware (`IdentifyTenant`)**: Intercepts all requests directed to tenant subdomains (`{tenant}.domain.localhost`). It extracts the subdomain parameter, performs a database lookup against the `tenants` table, and binds the resolved `Tenant` model to Laravel's service container as `currentTenant`. It also shares this model globally with all Blade views.
*   **Database Isolation**: Entities belonging to a specific workspace are strictly linked via foreign keys (e.g., `tenant_id` on the `pages` table).
*   **Routing Segregation**: The application defines central routes (authentication, central dashboard) on the primary domain, while tenant-specific operations (the editor and the public storefront) are grouped under the subdomain route definition in `routes/web.php`.

## 2. Database Mapping

The database schema separates tenant identity from page layout data:

| Table | Purpose | Key Columns |
| :--- | :--- | :--- |
| `tenants` | Handles subdomain mapping and ownership. Enforces that a user owns the workspace. | `id`, `user_id`, `subdomain` |
| `pages` | Stores layout configurations for individual pages within a tenant's site. | `id`, `tenant_id`, `slug`, `draft_config` (JSON), `published_config` (JSON) |

The JSON configuration columns (`draft_config` and `published_config`) store the structured, hierarchical layout of the site. This includes the theme, nested sections, blocks, and their associated styles and properties.

## 3. Web Builder Architecture (Inertia + Vue)

The web editor is the core of the platform, built as a highly interactive single-page application using Inertia.js and Vue 3.

```mermaid
graph TD
    User[User Interaction] --> Editor[Editor.vue (Inertia Page)]
    Editor -->|Live Edits| Canvas[Native Vue RenderNode & vuedraggable]
    Editor -->|Deep Watcher| State[Local draft_config State]
    State -->|Debounced Auto-Save| API[POST /editor/save]
    API -->|Update| DB[(pages table: draft_config)]
    
    User -->|Clicks Publish| PublishAPI[POST /editor/publish]
    PublishAPI -->|Copy to published_config| DB
```

### Key Components:
*   **Editor Interface (`Editor.vue`)**: The main interface is a unified Vue component containing both the inspector sidebar and the live preview canvas.
*   **Native Canvas Rendering**: The live preview canvas is rendered natively within the Vue application using a recursive `<RenderNode>` component. This allows the canvas to instantly reflect state changes made in the sidebar.
*   **Drag and Drop**: Layout manipulation (moving sections, reordering blocks) is handled seamlessly on the client side via `vuedraggable` operating directly on the reactive Vue state array.
*   **State Management & Auto-Save**: A deep watcher observes the `blocks` array. Any modification (dragging, text edits, style adjustments) triggers a debounced Inertia POST request to `/editor/save`. This request persists the entire `draft_config` JSON tree back to the `pages` table, ensuring a seamless auto-save experience.

## 4. Public Storefront

While the backend editor is a comprehensive Vue SPA, the public-facing live site is optimized for maximum speed and SEO compatibility.

*   **Controller**: The `TenantPublicSiteController` resolves incoming traffic by fetching the `Page` record that matches the requested slug (defaulting to the `home` slug).
*   **Blade Rendering**: To serve the public site, the controller bypasses Inertia entirely. It retrieves the read-only `published_config` JSON and passes it directly to a standard Laravel Blade view (`tenant-public.blade.php`).
*   **Performance**: The Blade view parses the JSON configuration server-side and renders the final HTML layout. This ensures that visitors receive a fully constructed, lightning-fast HTML document without needing to boot a large JavaScript framework.
