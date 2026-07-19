<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== web-builder project rules ===

# Design Catalog and Site Kit Checklist

Site kits and shared page layouts are composition data for the existing page/block system. They must never create a parallel renderer, block schema, template engine, persistence model, or publish path.

When adding or changing a design catalog entry:

1. **`config/designs.php`** — define styles, shared page layouts, and site kits by stable string keys. Initial content is limited to the approved Restaurant, Retail, and Hotel kits.
2. **Reuse the block AST** — every layout's `blocks` value must use the existing `{ id, type, props, children }` structure and only types/nesting allowed by `config/blocks.php`.
3. **Reference shared layouts** — site-kit pages refer to a `page_layouts` key. Do not duplicate a shared block tree inside each kit.
4. **Clone on apply** — applying a layout or kit must deep-clone the block tree, regenerate every block ID, and persist independent copies to ordinary `Page::draft_config` values. Catalog definitions are never live-linked to tenant pages.
5. **Protect existing work** — initial site kits may only be applied to a new or server-verified empty workspace. Never overwrite non-empty tenant pages, theme, or navigation data. Perform multi-page application in a database transaction.
6. **Preserve publishing semantics** — kit application creates drafts. Only the existing publish action may copy `draft_config` to `published_config`.
7. **Validate and test** — run the design-catalog validation tests and add focused tests for new references, block trees, eligibility rules, cloning, rollback, and tenant isolation as those stages are implemented.
8. **Keep documentation synchronized** — update `spec.md`, `AGENTS.md`, and `gaps.md` in the same change whenever the catalog schema or workflow changes.
9. **Use the media pipeline** — starter images must be ordinary `ImageBlock` nodes. An editable placeholder uses an empty `src` plus useful alt/replacement guidance and is replaced through the existing media picker; do not add a separate kit-asset system.
10. **Enforce server eligibility** — initial application requires `site_setup_completed_at === null`, no pages, and null theme/navigation configuration. Re-check this condition inside the application transaction (the `ApplySiteKit` action).
11. **Complete setup on user work** — successful page create/update/delete/save/publish, theme updates, navigation updates, kit application, and "Start from scratch" permanently set `site_setup_completed_at`. Media operations do not. Never reset a completed tenant to pending.

New registrations must remain empty and route to the dashboard. Pending empty tenants who directly request the editor are redirected to the design library; do not create starter content during an editor GET request. The `/designs` page offers two exit paths for eligible tenants: "Use this design" applies the selected kit via `POST /designs/site-kits/{kit}/apply`, and "Start from scratch" completes setup via `POST /designs/start-from-scratch` without creating content. Both re-check eligibility on the server and mark setup complete.

The initial Restaurant reservation and Hotel contact layouts are enquiry experiences only. The initial Retail Shop layout is presentational only. Do not describe or implement booking confirmation, live availability, inventory, cart, checkout, or payments unless those capabilities are separately approved and built.

If a kit identity, page inventory, content choice, or destructive behavior is ambiguous, stop and ask. Do not infer product decisions or workspace safety.

# Block Installation Checklist

When adding a new block type, ALL of these files must be updated. Missing any step will cause the block to fail silently on the public site (editor shows it locally, but saves fail and publish copies stale data).

1. **`resources/js/components/BuilderBlocks/YourBlock.vue`** — Create the Vue component. Must accept `nodeId` and `blockProps` props. Use `blockProps` as the prop name (not `props`) to avoid SFC collision.

2. **`resources/js/lib/blockRegistry.ts`** — Two changes:
   - Add import at the top
   - Add to `blockComponents` map (key = type string)

3. **`config/blocks.php`** — Add the block's keyed entry under `definitions` with `type`, `label`, `category`, `icon`, `defaultProps`, `inspectorFields`, and optionally `allowedChildren` if the new block is a container.
   - If an existing parent should accept the new block, add it to that parent's inline `allowedChildren` list.
   - Add the same parent-child permission to the top-level `nesting` map used by backend validation.
   - **CRITICAL**: Check EVERY applicable parent (`LayoutGrid`, `LayoutColumn`, etc.) in both locations. Missing a parent entry can make the editor reject insertion or make save fail with a 422 response.

4. **Frontend definition resolution** — The frontend dynamically resolves definitions and allowed-children rules from `config/blocks.php` through the Inertia `blocksConfig` prop. Do not create or maintain a separate `blockDefinitions[]` array in TypeScript.

5. **`npm run build`** — Verify the Vite build succeeds

6. **`php artisan test --compact`** — Verify all tests pass

## Block Component Authoring Guide

### Default Props Rules

- **`backgroundColor` must default to `'transparent'`** — hardcoded white/light defaults (e.g. `#ffffff`, `#f8fafc`) will mask the canvas theme background color. `RenderNode.vue` maps the known legacy white defaults to `transparent`, but new block defaults must use `transparent` in `config/blocks.php` from the start.
- **`padding` defaults to `20`** — measured in pixels, applied by `RenderNode.vue` to the wrapper div.
- Leaf blocks with text content should default their color prop to `'--theme-text'` (the CSS variable name string), not a raw hex. `AtomicText.vue` demonstrates this pattern.

### Connecting to the Global Theme (CSS Variables)

The theme is injected as CSS custom properties on the canvas root `.canvas-runtime` (Editor) and the public page root div (PublicPage). All child blocks inherit these automatically via CSS cascade.

**Available CSS tokens**:

| Token | Maps to |
|---|---|
| `--theme-primary` | Primary brand color (use for CTAs, highlights) |
| `--theme-secondary` | Secondary accent color |
| `--theme-bg` | Page background color |
| `--theme-text` | Default text color |
| `--theme-border-radius` | Corner roundness (e.g. `8px`, `9999px`) |
| `--theme-font-heading` | Heading font family (Google Font name) |
| `--theme-font-body` | Body / UI font family (Google Font name) |

**How to use in a block component**:

```vue
<template>
  <div
    :style="{
      fontFamily: 'var(--theme-font-body)',
      color: 'var(--theme-text)',
      borderRadius: 'var(--theme-border-radius)',
      backgroundColor: blockProps.variant === 'primary' ? 'var(--theme-primary)' : 'transparent',
    }"
  >
    {{ blockProps.content }}
  </div>
</template>
```

- **Never hardcode hex colors** like `#4f46e5` or `#0f172a` in block `.vue` files. Always use the CSS variable fallback pattern: `var(--theme-primary, #4f46e5)`.
- For headings use `var(--theme-font-heading)`, for body text and labels use `var(--theme-font-body)`.
- For hover/active states, use CSS `filter: brightness(0.9)` rather than a separate hardcoded darker hex, so the effect works on all theme colors.

### Inspector Fields (Editor Sidebar Controls)

The inspector sidebar renders controls dynamically from the `inspectorFields` array in `config/blocks.php`. Supported field types:

| `type` | Renders As | Notes |
|---|---|---|---|
| `'text'` | Single-line text input | Use for labels, URLs, titles |
| `'color'` | Native `<input type="color">` + hex text input | Returns 6-digit hex string |
| `'range'` | Slider | Requires `min` and `max` |
| `'number'` | Numeric input | Requires `min` and `max` |
| `'select'` | Dropdown | Requires an `options: [{label, value}]` array |
| `'media'` | Image preview + "Choose Image" button | Opens `MediaPicker` modal; stores the selected image URL |
| `'repeater'` | Dynamic list of grouped subfields | Requires `subFields: InspectorField[]`; supports add/delete/reorder |

Example `inspectorFields` definition in `config/blocks.php`:

```php
'inspectorFields' => [
    ['key' => 'padding',         'label' => 'Padding (px)',   'type' => 'range',  'min' => 0, 'max' => 150],
    ['key' => 'backgroundColor', 'label' => 'Background',     'type' => 'color'],
    ['key' => 'label',           'label' => 'Button Text',    'type' => 'text',   'placeholder' => 'Click me'],
    ['key' => 'variant',         'label' => 'Variant',        'type' => 'select', 'options' => [
        ['label' => 'Primary',   'value' => 'primary'],
        ['label' => 'Secondary', 'value' => 'secondary'],
        ['label' => 'Outline',   'value' => 'outline'],
    ]],
],
```

### RenderNode Background Resolution

`RenderNode.vue` wraps every block in a `<div>` that applies `padding` and `backgroundColor` from `node.props`. The resolved background uses `resolvedBgColor` computed:
- If `backgroundColor` is empty, `'transparent'`, `'#ffffff'`, or `'#f8fafc'` → resolves to `transparent` (lets the canvas theme background show through).
- Any other explicit value is applied as-is, enabling per-block custom backgrounds from the inspector.

### isEditable Injection

Blocks can detect if they are running inside the editor or on the public site:

```vue
<script setup>
import { inject } from 'vue';
const isEditable = inject('isEditable', false);
</script>
```

Use `isEditable` to conditionally show placeholder text when `blockProps` fields are empty (e.g., `blockProps.headline || (isEditable ? 'Click to edit' : '')`).

## Critical: Save/Publish Flow

The editor has a two-step save model:
- **Save** writes `draft_config` to the database (debounced 400ms, cancel-safe via `currentSaveVisit`)
- **Publish** copies `draft_config` → `published_config` (inside a DB transaction)
- The public site reads ONLY `published_config`

**If save fails**: the editor's `saveError` ref goes to `true` for 10s (visible as a "Save failed" alert in the sidebar), and the Publish button is disabled until the next successful save. Previously, save errors were silently swallowed (the `onHttpException` handler returned `false`), which meant the user could click Publish and get stale data copied to the public site.

## Undo/Redo

- Undo/redo uses snapshot arrays (`undoStack` / `redoStack`) captured before each blocks mutation
- `isTraveling` flag suppresses re-saving during history travel

## Device Preview

- Three modes: Desktop (full width), Tablet (768px), Mobile (375px)
- Canvas `container-type: inline-size` lets blocks respond with `@container` queries


## Storefront Block Compatibility

Storefront design uses ordinary registered blocks inside `Page::draft_config` and `Page::published_config`. Do not introduce separate commerce templates, editors, renderers, or publish routes. Version 1 `sourceKey`/`dataBinding` values are presentation bindings; manual block props remain the editor fallback.

Commerce data is resolved per request through `CommerceProvider` and returned in a separate envelope keyed by block UUID. Never persist fixture, preview, provider, price, stock, cart, or checkout data into a page block tree. New providers must preserve normalized money/variant/availability shapes, receive the current tenant explicitly, and return an unavailable state instead of inventing authoritative values.

Fixture cart state is server-side session data namespaced by tenant ID and provider key. Storefront clients send only variant IDs and quantities; providers validate availability and return totals. Do not calculate live-provider prices, discounts, stock, or totals in Vue. Preview selection is request-only and must never trigger a page save. The fixture checkout page is a handoff simulator and must never be described as payment or order placement.

Editor-internal navigation from the topbar, header, or canvas links must call the editor's save-before-switch operation (use the `useSafeNavigate` composable which injects `forceSave` and only navigates on success). Never navigate directly away from the editor with an internal anchor because that can discard unsaved draft state. Public links still target ordinary page slugs and require the destination page to be published (`is_published = true` AND `published_config !== null`).

Bulk publish (`POST /editor/publish-all`) promotes all tenant page drafts to published in a single transaction. The per-page visibility toggle (`PATCH /editor/pages/{page}/visibility`) flips `is_published` while preserving `published_config` — a reversible unlist/re-list. The homepage is permanently listed and cannot be unlisted.

</laravel-boost-guidelines>
