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

# Unified Builder Workflow

The tenant dashboard is a business/site overview, not a design application. Everyday website work happens inside the single `Tenant/Editor` workspace.

1. **One builder** — pages, block sections, navigation, global theme, media access, responsive previews, and publishing must remain inside the editor shell. Do not add separate theme, navbar, media, or publish studios.
2. **Workspace modes** — the editor supports `pages`, `navigation`, and `theme` modes through `?workspace=`. The Media action opens the existing media manager in the editor. Add future SEO or history experiences as editor modes or panels rather than independent applications.
3. **Protect page drafts** — switching from page editing to a global workspace must flush the current page draft through the existing force-save operation. Internal page navigation must continue using save-before-switch.
4. **Global settings** — theme and navigation save through their existing tenant endpoints and immediately update the editor's reactive theme/navigation state. They remain ordinary `Tenant::theme_config` and `Tenant::navigation_config` values.
5. **Dashboard scope** — show truthful website status, published URL, page counts, theme summary, recent activity when available, and quick actions into the unified builder. Do not restore everyday Kits, Theme Studio, or Navbar Studio navigation.
6. **Compatibility routes** — legacy `/dashboard/theme` and `/dashboard/navbars` URLs may redirect into the matching editor workspace, but must not render standalone applications.
7. **Onboarding-only kits** — only server-verified eligible empty tenants may view `/designs`. Established workspaces are redirected to the editor, and the dashboard must not expose kit browsing after setup.
8. **Truthful business health** — only report capabilities supported by authoritative application data. Do not claim ordering, reservations, inventory, payments, or third-party connections merely because a design mentions them.

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
12. **Compose section patterns** — hero patterns must be ordinary `SectionBlock` trees built from registered child blocks. Use stable `patternKey` values and semantic `patternRole` props so **Change layout** can preserve text, links, and media. Do not add pattern-specific renderers or persistence. Block library **Presets** insert those same trees (plus content/commerce section presets); show them as theme-tinted wireframe thumbnails grouped by job (`heroes` / `content` / `commerce`), never as near-duplicate text cards.
13. **Layout surfaces** — `LayoutGrid` / `LayoutColumn` must apply their grid/flex styles to the children wrapper (`RenderNode` drop zone / public children box), not to an outer shell that only wraps one child. Otherwise buttons and columns always stack. Drag handles on canvas blocks (and the Layers tree) move any allowed block between containers; set Columns to 2+ for side-by-side.
14. **Keep footer chrome global** — footer variants and `moduleOrder` belong to `navigation_config.footer`, render through the shared `SiteFooter.vue`, and are edited in the unified Navigation workspace. Footer imagery must use the existing media picker.

New registrations must remain empty and route to the dashboard. Pending empty tenants who directly request the editor are redirected to the design library; do not create starter content during an editor GET request. The `/designs` page is onboarding-only and redirects established workspaces back to the editor. It offers two exit paths for eligible tenants: "Use this design" applies the selected kit via `POST /designs/site-kits/{kit}/apply`, and "Start from scratch" completes setup via `POST /designs/start-from-scratch` without creating content. Both re-check eligibility on the server and mark setup complete.

The initial Restaurant reservation and Hotel contact layouts are enquiry experiences only. Retail may demonstrate fixture-provider catalog, cart, and checkout-handoff behavior through the existing storefront pipeline, but must not claim live inventory, payment, or order placement. Do not describe or implement booking confirmation or live availability unless those capabilities are separately approved and built.

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

The inspector renders controls dynamically from the `inspectorFields` array in `config/blocks.php` through a single shared renderer, `InspectorField.vue` (used for every block, including `SectionBlock`). Each field takes an optional `'group'` key — `'content'` (default) or `'style'` — and `ContentInspector.vue` renders the groups as collapsible **Content** / **Style & Layout** sections plus a fixed **Effects** section (scroll-reveal animation). Content opens by default; if a block has only style fields, Style opens instead. Supported field types:

| `type` | Renders As | Notes |
|---|---|---|---|
| `'text'` | Single-line text input | Use for labels, URLs, titles |
| `'theme-color'` | Collapsed swatch row (swatch + value summary) that expands to `ThemeColorControl` (theme tokens + preset palette + custom hex/native picker) | Stores a `--theme-*` token string, a hex value, or `'transparent'`. Supports `defaultValue`/`customDefault`, `compact` (palette behind **More colors**; on by default inside the row), `clearable` (reset to empty so the block can fall back to a preset; summary shows "Auto"), and `allowTransparent` (adds a **None (transparent)** choice). Block-wrapper `backgroundColor` fields use `defaultValue => 'transparent'` + `allowTransparent`. |
| `'font-size'` | Preset buttons + px slider + advanced responsive input (`FontSizeControl`) | Stores a CSS length or `clamp()` string |
| `'range'` | Slider + numeric input | Requires `min` and `max` |
| `'number'` | Numeric input | Requires `min` and `max` |
| `'select'` | Dropdown | Requires an `options: [{label, value}]` array. Use only for long lists (5+ options); prefer `'segmented'` or `'toggle'` otherwise |
| `'segmented'` | Segmented button group | Requires `options: [{label, value}]`; use for alignment, font family, variant, size, and other short mutually-exclusive choices |
| `'toggle'` | Switch on a single row with the label | Writes a boolean (e.g. `openInNewTab`, `stackOnNarrow`) |
| `'columns'` | Segmented button group (1/2/3/4/6) | Requires an `options: [{label, value}]` array of numbers; writes the numeric column count. Used for `LayoutGrid.columns` and `MenuBlock.columns` |
| `'media'` | Image preview + "Choose Image" button | Opens `MediaPicker` modal; stores the selected image URL. Also supported inside repeater `subFields` (the picker event carries `{fieldKey, index, subKey}`) |
| `'repeater'` | Collapsible list of items | Requires `subFields: InspectorField[]`; items collapse to a summary row (first text values) with move/duplicate/delete actions; one item expands at a time |

The legacy `'color'` type (raw native picker) still renders as a fallback but has no remaining uses — all color fields are `'theme-color'`. Because theme tokens are stored as bare `--theme-*` strings, anything consuming color props directly must resolve them via `resolveThemeColor()` from `resources/js/lib/themeColor.ts` (already wired into `RenderNode`, `RenderPublicNode`, `SectionBlock`, and `DividerBlock`).

Example `inspectorFields` definition in `config/blocks.php`:

```php
'inspectorFields' => [
    ['key' => 'label',           'label' => 'Button Text',    'type' => 'text',   'placeholder' => 'Click me'],
    ['key' => 'openInNewTab',    'label' => 'Open in new tab', 'type' => 'toggle'],
    ['key' => 'padding',         'label' => 'Padding (px)',   'type' => 'range',  'min' => 0, 'max' => 150, 'group' => 'style'],
    ['key' => 'backgroundColor', 'label' => 'Background',     'type' => 'theme-color', 'group' => 'style', 'compact' => true, 'allowTransparent' => true, 'defaultValue' => 'transparent', 'customDefault' => '#f4f4f5'],
    ['key' => 'variant',         'label' => 'Variant',        'type' => 'segmented', 'group' => 'style', 'options' => [
        ['label' => 'Primary',   'value' => 'primary'],
        ['label' => 'Secondary', 'value' => 'secondary'],
        ['label' => 'Outline',   'value' => 'outline'],
    ]],
],
```

### Inspector Auto-Hydration (`ensureDefaultProps`)

`ContentInspector.vue` watches the selected block and auto-initializes any `inspectorField` key missing from `selectedBlock.props`. The fallback priority is:
1. The matching key in `defaultProps` from `config/blocks.php`.
2. A type-appropriate fallback: first `options[].value` for `select`, `false` for `toggle`, `field.min` for `range`/`number`, or `''` for text fields.

Toggle values stored as strings (`'yes'`/`'true'`) are normalized to actual booleans. This prevents blank inspector controls when new fields are added to existing block definitions.

### Block-specific behaviors

- **`MenuBlock`** — an editable restaurant-style menu. Its `items` repeater is a flat list where each item has `category`, `name`, `description`, and `price`; the component groups items by category (order preserved) and lays categories out across `columns` (1–3). Item name/description/price are inline-editable on the canvas; category labels are edited through the inspector repeater. Fully theme-aware and reveal-friendly. Catalog usage lives in `config/designs.php` via the `$menuBlock` helper (Restaurant Menu page).
- **`ButtonBlock`** — renders an `<a>` on the public site when `url` is set. `openInNewTab` (toggle) controls `target="_blank"` + `rel="noopener noreferrer"`; same-tab links (internal `/slug` or external) omit `target`. `variant` (primary/secondary/outline) and `size` are `segmented` presets. Optional `theme-color` overrides — `backgroundColor`, `textColor`, `hoverBackgroundColor`, `hoverTextColor` — use the compact color control (theme tokens + hex; full palette behind **More colors**) and a clear action to fall back to the variant. Optional `borderRadius` select (`''` / `0px` / `8px` / `16px` / `9999px`) overrides the theme corner token on that button only; leave Theme default to inherit `--theme-border-radius`. Empty color values fall back to the variant; any custom hover color switches from the default `brightness(0.9)` filter to explicit hover colors via `--btn-hover-bg` / `--btn-hover-text`. For the outline variant the border follows the resolved text color. Background and hover-background use `allowTransparent`, so a button can have no fill at all; when a filled variant gets a transparent background without a custom text color, the text falls back to `--theme-primary` so it stays visible.
- **`VideoEmbedBlock`** — lite-embed. It shows a thumbnail poster first (YouTube `hqdefault.jpg` derived from the URL, or an optional `posterUrl` media override, or a theme-derived gradient) and only loads the provider `<iframe>` after a click on the public site. The editor canvas always shows the poster and never loads the iframe.
- **`RichTextBlock`** — TipTap toolbar includes per-selection text color (theme tokens `var(--theme-primary|secondary|text)`, hex presets, native picker, and reset). Colors are stored as inline `style="color: …"` on `<span>` marks via `@tiptap/extension-text-style` and survive save/publish without backend changes.

### Responsive sizing in presets and patterns (`cqw`, not `vw`)

The editor canvas (`.canvas-runtime`), the design-library preview, and the public page `<main class="public-site-runtime">` are all `container-type: inline-size` containers. Catalog layouts, section patterns, and block presets must size display type, hero image heights, and large gaps with **container-relative `cqw` units** (e.g. `clamp(2rem, 5cqw, 4rem)`), never `vw` — `vw` measures the whole window, so inserted sections render comically large inside the editor canvas and ignore the device-preview width. Split heroes use **equal** columns (not `wide-left` / `wide-right` at 65/35) so copy never starves beside media. Dual CTAs are a **stacked `LayoutColumn`**, not a 2-column button grid — side-by-side buttons crush and mid-word-wrap inside a half-width copy track. `ButtonBlock` is `max-width: 100%` and wraps at word boundaries only. The same container also makes `@container`-based stack-on-narrow rules work on the published site.

### Theme workspace one-click palettes

The Theme workspace (`DashboardThemeStudio` / `?workspace=theme`) exposes grouped one-click color palettes: Site kits (Restaurant Warmth, Retail Editorial, Hotel Refined matching `config/designs.php`) and Starter bases. Hover previews the live canvas; click applies all four color slots. Users can still refine primary/secondary/background/text individually afterward.

### Scroll Reveal Animation (`reveal` / `revealDelay` props)

Any block may carry two optional presentation props: `reveal` (one of `fade-up`, `fade-in`, `scale-in`, `slide-left`, `slide-right`) and `revealDelay` (milliseconds, capped at 1200). They drive a one-shot entrance animation on the published site and in kit previews:

- `RenderPublicNode.vue` applies the `v-reveal` directive from `resources/js/lib/reveal.ts`, which uses a shared `IntersectionObserver` and the `.reveal*` classes in `resources/css/app.css`.
- The editor canvas (`RenderNode.vue`) intentionally ignores these props so blocks are always immediately visible while editing.
- `prefers-reduced-motion` disables the effect entirely; an unknown or empty `reveal` value is a no-op.
- The controls live in a fixed "Animation" section at the bottom of `ContentInspector.vue` — do not add `reveal` to per-block `inspectorFields` in `config/blocks.php`.
- Catalog layouts in `config/designs.php` set these props via the shared helpers (`$withReveal`, staggered delays inside `$featureGrid`/`$galleryGrid`). They are ordinary block props: cloned on kit apply, saved with drafts, and never require backend changes.

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

Editor-internal navigation from the topbar, header, or canvas links must call the editor's save-before-switch operation. Never navigate directly away from the editor with an internal anchor because that can discard unsaved draft state. Public links still target ordinary page slugs and require the destination page to be published.

</laravel-boost-guidelines>
