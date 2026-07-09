# Project Context & Architecture Guide (context.md)

This document is the source of truth for the multi-tenant Web Builder project. It defines the architecture, directory structure, data flow, coding conventions, and recent changes to prevent architectural drift across agent sessions.

---

## 1. System Architecture & Tenancy Model

The application is a **multi-tenant drag-and-drop website builder** built on a **single-database, subdomain-based isolation** model.

### 1.1 Routing & Domain Separation
The system divides routing into two distinct domains:
*   **Central Domain (`domain.localhost`)**:
    *   Handles landing page, landing-page authentication (login, registration, 2FA, passkeys), central user dashboard, and profile settings.
    *   Authentication is managed by **Laravel Fortify**.
*   **Tenant Domain (`{subdomain}.domain.localhost`)**:
    *   Runs the authenticated drag-and-drop page editor (`/editor`) and renders unauthenticated public sites.
    *   Subdomain resolution is executed by the [IdentifyTenant](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Middleware/IdentifyTenant.php) middleware, which queries the database for the matching tenant and binds the resolved `Tenant` model to the service container.
    *   Wildcard session sharing is enabled via `SESSION_DOMAIN=.domain.localhost` in the `.env` file, allowing seamless transition from central dashboards to tenant-specific editors.

### 1.2 The Database Schema
Tenancy relationships are structured as:
`User` (1:1) ➔ `Tenant` (1:N) ➔ `Page`

*   **`tenants` table**: Maps subdomains to users. Columns: `id`, `user_id` (foreign key to `users`, unique), `subdomain` (unique).
*   **`pages` table**: Holds individual layout records. Columns: `id`, `tenant_id`, `slug`, `title`, `is_homepage`, `sort_order`, `draft_config` (JSON), `published_config` (JSON).

---

## 2. Editor & Rendering Architecture

A unified schema architecture prevents representation drift between the editor canvas and the public-facing pages.

### 2.1 The Block Registry
All builder blocks must be defined in the central block registry located at [blockRegistry.ts](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/lib/blockRegistry.ts):
*   **`blockComponents`**: Maps block type strings to their actual Vue 3 components.
*   **`blockDefinitions`**: Contains schema metadata for each block:
    *   `type`: Unique block type string (e.g., `HeroBlock`, `LayoutGrid`).
    *   `label`: Human-readable name.
    *   `defaultProps`: Initial fallback properties (e.g., margins, alignments).
    *   `inspectorFields`: Schema array defining inspector controls (e.g., `range`, `color`, `text`).
    *   `allowedChildren` (Optional): Array of block types permitted as children (enforces containment rules during drag-and-drop operations).

### 2.2 Dual-Render Flow
*   **Editor View (`/editor`)**:
    *   Renders as an interactive client-side SPA via Inertia and Vue 3 ([Editor.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/Tenant/Editor.vue)).
    *   The canvas layout is driven by a recursive [RenderNode.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/RenderNode.vue) component wrapped in `vuedraggable` to allow nesting and layout reordering.
    *   A deep watcher in `Editor.vue` tracks block configurations and triggers debounced POST requests to `/editor/save` to persist `draft_config`.
*   **Public Site (`/` and `/{slug}`)**:
    *   Public views are rendered server-side via **Inertia SSR** inside [PublicPage.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/Tenant/PublicPage.vue) (using the recursive [RenderPublicNode.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/RenderPublicNode.vue)).
    *   This eliminates the risk of code drift between editor and public views.

---

## 3. UI Layout Conventions

### 3.1 Editor Sidebar (Inspector) Layout
The sidebar in `Editor.vue` must remain scrollable without pushing control buttons out of the viewport:
*   **Root Container**: Use `flex flex-col h-screen shrink-0` (no padding on the root sidebar element).
*   **Content Container (Pages, Inspector, Block Library)**: Wrap in `flex-1 overflow-y-auto p-6 space-y-6 min-h-0`. The `min-h-0` is critical to allow flexbox-based scrolling.
*   **Action Panel (Publish / Exit buttons)**: Wrap in a `shrink-0 p-6 border-t border-slate-800 bg-slate-900` container. This forces the panel to remain pinned and visible at the bottom of the viewport.

---

## 4. Coding & Formatting Guidelines

*   **TypeScript**: Explicitly type inputs, states, and components using Composition API `<script setup>`.
*   **CSS / Styling**: Standardize on **Tailwind CSS v4** styling. Do not write inline CSS overrides unless working with dynamic user-configured properties (e.g. padding ranges or custom backgrounds).
*   **Automated Formatters**:
    *   **Prettier & ESLint**: Strictly format JavaScript/TypeScript/Vue files before committing (`npm run lint`). Code reviews should not contain trailing whitespace changes, expanded single-line `if` statements, or alphabetically rearranged imports unless intended.
    *   **Laravel Pint**: Always format PHP code before committing by running `vendor/bin/pint --dirty --format agent`.

---

## 5. Verification & Testing

All architectural updates, API endpoints, and page state controls must be covered by Pest test suites:
*   Run tests locally using `php artisan test --compact`.
*   Feature test cases reside in [tests/Feature](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/tests/Feature) (e.g. [TenantEditorTest.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/tests/Feature/TenantEditorTest.php)).
*   Never delete test cases without explicit confirmation.
