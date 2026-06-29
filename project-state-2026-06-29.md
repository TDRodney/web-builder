# Project State & Feature Walkthrough - June 29, 2026

This document details the current architectural state, recent infrastructure enhancements, completed bug fixes, and manual verification steps for the **Nexura Web Builder** project in the `feature-editor-infra` branch.

---

## 1. System Architecture

The Nexura Web Builder is built on a robust, multi-tenant framework:
- **Central Domain (`domain.localhost:8000`)**: Handles user authentication, registration, billing, and the main user dashboard.
- **Tenant Subdomains (`{tenant}.domain.localhost:8000`)**: Isolated spaces where authenticated tenant owners can modify their site via the live AST builder, and public users can view the published site.
- **Recursive AST Layout Engine**: Replaces traditional flat page building with a nested Abstract Syntax Tree (AST) layout model. Page structures are stored in a SQLite database under the `pages` table as JSON configurations (`draft_config` and `published_config`).

### Recursive AST Structure Example:
```json
[
  {
    "id": "layoutgrid-1",
    "type": "LayoutGrid",
    "propsData": { "columns": 3, "gap": "1rem" },
    "children": [
      {
        "id": "layoutcolumn-1",
        "type": "LayoutColumn",
        "propsData": { "span": 1 },
        "children": [
          {
            "id": "atomictext-1",
            "type": "AtomicText",
            "propsData": { "content": "Atomic text nested inside a column!" }
          }
        ]
      }
    ]
  }
]
```

---

## 2. Implemented Components

A rich set of AST blocks has been integrated into the builder's block registry:
1. [LayoutGrid.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/LayoutGrid.vue): A CSS-Grid driven container that supports configurable columns, gap size, and padding.
2. [LayoutColumn.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/LayoutColumn.vue): A structural layout column with flexible basis and grid span definitions.
3. [AtomicText.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/AtomicText.vue): A leaf node component allowing text changes, custom font sizes, and colors.
4. [HeroBlock.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/HeroBlock.vue): A text-center section for high-level branding messages.
5. [FeatureBlock.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/FeatureBlock.vue): A structured card listing features and details.
6. [RenderNode.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/RenderNode.vue): The heart of the layout engine; recursively calls dynamic components based on the AST payload.

---

## 3. Completed Changes & Enhancements

### Selection State Management (`canvasSelection`)
- Exposed a reactive selection tracking variable `selectedNode` and its explicit setter method `selectNode(node)` from [Editor.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/Tenant/Editor.vue).
- Exposed this context down to all nested nodes via Vue's `provide` mechanism.
- Updated [RenderNode.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/RenderNode.vue) to inject and utilize this unified setter on selection clicks.

### Self-Healing & Crash Prevention
- Programmed a synchronous validation mechanism inside `selectNode(node)`. If a clicked node is missing its `styles` metadata (common for nested layout columns generated inside containers), the hook **synchronously initializes** default `styles` (`{ padding: 20, backgroundColor: '#ffffff' }`).
- This completely prevents Vue from crashing due to `TypeError` (e.g. attempting to bind range sliders to properties of an `undefined` styles object).

### Localized Scoped Styling & Cascade Bleeding Containment
- Refactored `LayoutGrid` and `LayoutColumn` components to map layout options (`width`, `gap`, `height`, `padding`, `span`) to localized inline style overrides.
- Implemented `'auto'` default bindings on components (e.g., `--grid-width: auto`). Because CSS variables inherit down the DOM tree, this forces nested child layout containers to discard the parent's width/height constraints and respect their immediate bounds.
- Bound padding and background color directly to [RenderNode.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/RenderNode.vue)'s root `div` `:style` attribute. This completely bypasses issues with Tailwind's dynamic classes in recursive trees.

### Mid-Drag Auto-Save Locks
- Integrated the reactive `isDragging` flag. When a user is dragging a block, `queueSave()` immediately returns, preventing incomplete transit structures from firing auto-saves to the backend.
- Enforced strict draggable group definitions (`:group="{ name: 'canvas-tree', pull: true, put: true }"`) and rigid unique key tracking (`item-key="id"`) across all recursive instances.
- Bound `@start` and `@end` hooks to automatically toggle the `isDragging` state and trigger a `forceSave()` once dropping completes.

### Atomic Database Publishing
- Hardened the `publish` method in [TenantPageSaveController.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Controllers/TenantPageSaveController.php) by wrapping the configuration promotion within an isolated database transaction:
  ```php
  DB::transaction(function () use ($page) {
      $page->refresh(); // Lock to latest state snapshot
      $page->published_config = $page->draft_config;
      $page->save();
  });
  ```

### Public Tenant Page Recursive Rendering
- Refactored [tenant-public.blade.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/views/tenant-public.blade.php) and created [partials/block.blade.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/views/partials/block.blade.php) to recursively render the nested AST config layout. Every block type is mapped to its equivalent blade representation with inline styles matching the Vue components.

---

## 4. Manual Verification Plan

### Test Environment setup:
1. Ensure the development servers are active:
   - Command 1: `npm run dev` (compiling assets in the background)
   - Command 2: `php artisan serve --host=127.0.0.1 --port=8000` (serving backend)
2. Verify local database configuration points to the active Sqlite database (`database/database.sqlite`).

---

### Step-by-Step Manual Verification Checklist:

#### 1. Tenant Workspace Login
- Open a web browser and navigate to the Central Login Page: `http://domain.localhost:8000/login`.
- Input the seed tenant credentials:
  - **Email**: `njaurodney@gmail.com`
  - **Password**: `password`
- Click **Log In**. Upon success, verify that the application redirects you to your tenant dashboard at: `http://mark.domain.localhost:8000/dashboard`.

#### 2. Canvas & Block Library Interaction
- Navigate to the workspace editor page: `http://mark.domain.localhost:8000/editor`.
- Verify the main layout canvas loads.
- Click the **+ Grid Layout** button in the sidebar.
  - Verify that a CSS Grid block is added to the canvas.
  - Verify that 3 columns (`LayoutColumn`) are created inside the grid.
- Select one of the nested columns by clicking on it.
  - Verify that the **Content Inspector** sidebar loads column options (like Grid Column Span).
  - Verify that selecting the column does **not** throw any `TypeError` inside the browser's developer console (F12).

#### 3. Styling Containment Verification
- Select the outer **Grid Layout** container.
- Adjust its padding slider in the inspector (e.g. to `80px`) and select a background color.
  - Verify the grid container updates visually.
- Select one of the nested child columns.
  - Verify that the child column's background and padding are **not** affected by the parent's values (they should remain at their initial transparent/default layout boundaries without cascade bleeding).

#### 4. Drag & Drop Mid-Drag Sync
- Grab a block (e.g. a `HeroBlock`) using its **::: Move** handle.
- Drag the block and hold it mid-air.
  - Open the browser developer console's **Network** tab.
  - Verify that **no `/editor/save` POST requests** are sent while dragging is active.
- Drop the block in its new location (or inside a nested layout column).
  - Verify that a save request is immediately sent and returns successfully.

#### 5. Site Publishing & Live Page Checks
- Click **Publish Draft** in the bottom-left action panel.
- Verify that a confirmation alert appears saying: `"Site published successfully!"`.
- Open a new tab and navigate to the live public page: `http://mark.domain.localhost:8000/`.
- Verify that **all blocks** (including CSS layout grids, columns, and text elements) render in their correct nested positions, showing matching background colors and padding.
