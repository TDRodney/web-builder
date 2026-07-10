# Editor Features Summary

## Architecture Overview

The editor is a single-page Inertia v3 app at `/editor`. It uses Vue 3 Composition API with `provide`/`inject` for cross-component state — no Pinia.

**Key architectural decisions:**
- Unified AST schema: every block is `{ id, type, props: {}, children: [] }`
- All block data lives in `props` — no separate `styles` or `content` keys
- `isEditable` injection distinguishes editor (draggable/selectable) from public (read-only)
- `blockRegistry` injection provides the component map for dynamic `<component :is="...">` rendering

## Canvas

The canvas renders a recursive tree of `RenderNode.vue` components. Key behaviors:

- **Drag-and-drop**: powered by `vuedraggable` with `handle=".drag-handle"`. Drag groups use `checkAllowedChild()` to enforce nesting rules per `config/blocks.php` at drag time.
- **Selection**: clicking any block selects it, highlighted via `is-selected` class and `border-indigo-500`. Selection state is managed by `canvasSelection` (preferred) or `selectedBlock` ref (fallback).
- **Ghost clone suppression**: `drag-ghost` CSS class with `transition: none !important` prevents residual ghost artifacts from Vue transitions.
- **Error boundary**: `onErrorCaptured` in RenderNode shows a rose-tinted error card with the block type and error message instead of breaking the whole tree.
- **Container queries**: canvas uses `container-type: inline-size` so blocks can respond to the simulated canvas width with `@container` queries.

## Device Preview

Three view modes toggle via buttons in the inspector sidebar. The canvas `<div>` gets `:style="{ maxWidth }"`:

| Mode | Max Width | 
|---|---|
| Desktop | none (full available width) |
| Tablet | 768px |
| Mobile | 375px |

This is purely a CSS width constraint — no user-agent spoofing or viewport meta changes.

## Inspector Panel

The right sidebar shows form controls generated **entirely from `blockDefinitions[]` in `blockRegistry.ts`**. When a block is selected, the editor finds `activeBlockDefinition` by type and iterates `inspectorFields` to render matching inputs. Supported field types:

| Type | Control | Extra Options |
|---|---|---|
| `text` | `<input type="text">` | `placeholder` |
| `number` | `<input type="number">` | `min`, `max` |
| `color` | `<input type="color">` | — |
| `range` | `<input type="range">` | `min`, `max` |

Every key in `defaultProps` should have a corresponding `inspectorField` so the user can edit it.

## Autosave

- Debounced at **400ms** after the last user interaction
- Uses `useHttp` from Inertia v3 to POST `/editor/save` with `{ page_id, draft_config }`
- Cancellation-safe: prior in-flight requests are `.cancel()`-ed before sending a new one
- **Drag-end flush**: `forceSave()` is injected into RenderNode and called immediately when a drag ends (skips debounce)
- **Save errors**: previously swallowed silently; now `saveError` ref is set to `true` on failure (auto-clears after 10s) and the publish button is **disabled** while a save error is active
- The editor shows a visible "Save failed" alert in the action panel and blocks publishing until the next successful save

## Publish

A separate two-step flow:

1. **Save** writes to `draft_config` (JSON column in `pages` table)
2. **Publish** copies `draft_config` → `published_config` inside a DB transaction

The public site reads exclusively from `published_config`. A page with no `published_config` returns 404.

## Page Management

Inline in the inspector sidebar:
- **Create** new pages with title/slug
- **Rename** existing pages
- **Delete** non-homepage pages
- **Set as Homepage** (one page per tenant)
- **Switch pages** without leaving the editor (router.visit preserves editor context)

Pages are listed with a "Home" badge, and each shows its slug in monospace.

## Undo/Redo History

- Stores snapshots of the `blocks` array before each mutation (driven by the deep watch)
- `undoStack` / `redoStack` are simple arrays — no tree diffing
- Undo restores the previous snapshot via a `isTraveling` flag that suppresses re-saving during history travel
- Undo/redo buttons in the header toolbar

## Drag-and-Drop Nesting Rules

Enforced in two places:
1. **Client**: `checkAllowedChild(parentType, childType)` — reads the `allowedChildren` array from `getBlockDefinition()` and returns a boolean to `vuedraggable`'s `put` callback
2. **Server**: `ValidatesBlockSchema` (`app/Rules/ValidatesBlockSchema.php`) recursively validates every node against `config('blocks.nesting')` on every save

## Remembered Block Add

When adding a block (via the "Add Block" button in the sidebar), if a layout-type block is currently selected on the canvas, the new block is added as a child of that block. Otherwise it's added at root.

## Public Site Rendering

A separate route `/{slug}` renders `PublicPage.vue` which uses `RenderPublicNode.vue` — a read-only mirror of RenderNode. It receives `isEditable: false` and `blockRegistry` via provide. There's no drag, no selection, no inspector.

## Centralized Block Registry

All block metadata lives in `resources/js/lib/blockRegistry.ts`:
- `blockComponents` — maps type string → Vue SFC for dynamic rendering
- `blockDefinitions[]` — metadata array (label, category, icon, defaultProps, inspectorFields, allowedChildren)
- `getBlockDefinition(type)` — lookup helper

Block components use `blockProps` as their prop name (not `props`) to avoid collision with the Vue `props` object.