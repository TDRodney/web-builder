# Nexura Web Builder — Current Product and Codebase Guide

> Last reviewed against the application code: 2026-07-18.
>
> This describes what exists today. Where older roadmap documents disagree, the running code and tests are authoritative.

## 1. What Nexura is today

Nexura is a multi-tenant website builder built with Laravel, Inertia, Vue, and Tailwind. Each customer owns a tenant subdomain, creates and edits a multi-page site in one builder workspace, and publishes pages to a public site on that same subdomain.

The product currently supports:

- central registration, login, account settings, two-factor authentication, and passkeys;
- tenant-specific dashboards and public websites;
- onboarding with a curated site kit or an empty workspace;
- page creation, editing, deletion, homepage selection, drafts, and publishing;
- a nested drag-and-drop block editor with 23 registered block types;
- theme, navigation, media, responsive preview, and publishing inside one editor;
- contact-form submissions;
- a fixture-backed storefront demonstration with products, collections, cart state, and checkout handoff simulation.

It does **not** yet provide real payments, order management, authoritative inventory, confirmed bookings, live hotel availability, or production commerce integrations.

## 2. Main user journey

1. A user registers on the central domain. Registration creates an empty tenant and redirects through the dashboard.
2. A new, untouched tenant can open the design library and either apply a site kit or choose **Start from scratch**.
   The library can preview every included page with the public renderer at desktop, tablet, or mobile width; the preview itself remains read-only.
3. Applying a kit creates independent page drafts and initial theme/navigation settings. Nothing is published automatically.
4. Normal website work happens in the unified editor at `/editor`.
5. Autosave writes the selected page's block tree to `draft_config`.
6. Publish copies that page's draft into `published_config`.
7. Public visitors only see `published_config`; unpublished drafts remain private to the owner.

The tenant dashboard is an overview rather than a second design application. It reports page and publication status and links into the builder. Legacy theme and navigation studio URLs redirect to the matching editor workspace.

## 3. Tenancy and application shape

The central domain contains authentication, registration, account settings, and the central dashboard. Tenant sites use `{tenant}.{central_domain}` and pass through `IdentifyTenant` middleware.

Tenant ownership is checked before private workspace operations. Tenant-owned records include pages, media, and contact submissions. Theme and navigation data are stored directly on the `Tenant` model as JSON configuration.

The frontend is an Inertia Vue application. Laravel owns routing, validation, persistence, tenant resolution, and authoritative commerce calculations; Vue owns the editing experience and public presentation.

Key files:

- `routes/web.php` — central, tenant, editor, commerce, and public routes;
- `app/Http/Middleware/IdentifyTenant.php` — tenant resolution;
- `app/Models/Tenant.php` — tenant relationships, setup eligibility, theme, and navigation;
- `resources/js/pages/Tenant/Editor.vue` — unified builder shell;
- `resources/js/pages/Tenant/PublicPage.vue` — published-site shell.

## 4. Site kits and the design catalog

The design catalog is configuration in `config/designs.php`. It contains three layers:

- **styles** — reusable theme and navigation defaults;
- **page layouts** — shared block trees using the normal block AST;
- **site kits** — named page inventories that reference a style and shared layouts.

There are currently 3 styles, 14 shared page layouts, and 3 initial site kits:

| Kit | Style | Pages created |
|---|---|---|
| Restaurant | `restaurant-warmth` | Home, Menu, About, Reservations |
| Retail | `retail-editorial` | Home, Shop, Product, Cart, About, Contact |
| Hotel | `hotel-refined` | Home, Rooms, Amenities, Contact |

### Kit safety and eligibility

A tenant may apply an initial kit only when all of these are true:

- `site_setup_completed_at` is null;
- the tenant has no pages;
- `theme_config` is null;
- `navigation_config` is null.

Eligibility is checked on the server and checked again when applying the kit. The application runs in a database transaction. Every referenced block tree is deep-cloned, every block ID is regenerated, and the resulting pages are stored as ordinary drafts. Catalog definitions remain unchanged and are never live-linked to a tenant.

Successful page work, page saving or publishing, theme changes, navigation changes, kit application, or **Start from scratch** permanently completes setup. Media operations alone do not complete setup.

The Restaurant reservation and Hotel contact experiences are enquiry forms. They do not confirm reservations or availability. The Retail kit can demonstrate storefront behavior through fixture data, but it is not a live store integration.

All fourteen starter layouts now begin with a composable `SectionBlock` hero. Five hero patterns—centered, split image right, split image left, editorial, and minimal—are assembled from ordinary text, button, image, grid, and column blocks. Selecting a section exposes **Change layout** in the inspector. The switch maps semantic content roles (eyebrow, heading, body, actions, and media) into the new composition, so changing structure does not discard the section's copy, links, or chosen image.

Starter photography remains ordinary editable media. Hero and supporting image nodes use an empty source plus specific replacement guidance, then open the same media picker used everywhere else in the editor.

Relevant code:

- `config/designs.php` — catalog content;
- `app/Actions/Designs/ApplySiteKit.php` — transactional multi-page application;
- `app/Actions/Designs/CloneBlockTree.php` — independent block-tree cloning;
- `app/Actions/Designs/BuildDesignLibrary.php` — library response data;
- `app/Actions/Designs/BuildPageLayouts.php` — reusable layout data;
- `app/Http/Controllers/TenantDesignLibraryController.php` — onboarding endpoints.

## 5. The unified builder

Everyday editing lives in one `Tenant/Editor` workspace. It has three workspace modes selected with `?workspace=`:

- `pages` — edit page content and manage pages;
- `navigation` — configure the site header and a structured global footer;
- `theme` — configure brand colors, typography, and corner style.

Media opens as a manager/picker inside the editor instead of as a standalone studio. Desktop, tablet, and mobile preview controls resize the canvas to full width, 768 px, or 375 px.

The editor includes:

- a block library and presets;
- click-to-add and drag-from-library insertion, plus nested drag-and-drop composition;
- inline plain-text editing for common content blocks and TipTap editing for rich text;
- a layer tree for selecting and restructuring nodes;
- a data-driven content inspector;
- duplicate, copy, paste, delete, move, and wrap actions;
- undo and redo using client-side snapshots;
- debounced autosave;
- visible save failure state and publish protection;
- save-before-switch behavior for page changes and internal editor navigation.

Switching from page editing to a global workspace force-saves the current page first. Theme and navigation save through tenant endpoints and update the editor's reactive state immediately.

## 6. Pages, drafts, and publishing

Each `Page` has a tenant, title, slug, homepage flag, order, `draft_config`, and `published_config`.

- **Save** validates the block tree and writes `draft_config`.
- **Publish** runs transactionally and copies the saved draft to `published_config`.
- **Public rendering** reads only `published_config`.
- A tenant can have one designated homepage.
- The homepage cannot be deleted until another page becomes the homepage.
- Slugs are unique within a tenant.

This separation is fundamental: applying a kit, editing a page, or saving a draft never silently changes the live site.

Relevant code:

- `app/Models/Page.php`;
- `app/Http/Controllers/TenantPageController.php`;
- `app/Http/Controllers/TenantPageSaveController.php`;
- `app/Http/Controllers/TenantPublicSiteController.php`.

## 7. Block system

Blocks use one recursive structure everywhere:

```json
{
  "id": "unique-block-id",
  "type": "HeroBlock",
  "props": {},
  "children": []
}
```

The backend registry in `config/blocks.php` is the source of truth for definitions, default properties, inspector fields, and nesting rules. Those definitions are sent to the frontend through Inertia. Vue component resolution lives in `resources/js/lib/blockRegistry.ts`.

### Registered blocks

| Group | Blocks |
|---|---|
| Layout | Section, Layout Grid, Layout Column, Spacer, Divider |
| Core content | Hero, Feature, Atomic Text, Rich Text, Button, FAQ, Testimonial, Pricing Cards, Contact Form |
| Media | Image, Video Embed |
| Store presentation | Store Announcement, Image with Text, Collection List, Product Grid, Store Values |
| Store interaction | Product Detail, Shopping Cart, Store Newsletter |

There are 24 registered types in total. Store blocks remain ordinary blocks in the same page tree and use the same editor, renderer, persistence, and publish path.

Blocks can also carry optional `reveal` and `revealDelay` properties. Published pages and kit previews support fade-up, fade-in, scale-in, slide-left, and slide-right entrance effects. The editor keeps blocks visible while editing, and reduced-motion preferences disable the animations.

## 8. Theme and navigation

Theme configuration currently controls:

- primary and secondary colors;
- page background and text colors;
- heading and body fonts from the curated font set;
- border-radius style.

These values become inherited CSS custom properties such as `--theme-primary`, `--theme-bg`, and `--theme-font-heading`, so the same blocks adapt in the editor and on the public site.

Navigation configuration supports brand text or imagery, header items, nested children, action links, a CTA, menu groups/featured content, presets, and active-page styling. Its Footer tab offers minimal, link-column, callout, and editorial variants; brand, links, callout, social, and copyright modules can be reordered or hidden. Footer logos use the existing media picker. Internal links resolve to tenant page slugs; external links are validated URLs.

The former `/dashboard/theme` and `/dashboard/navbars` routes remain only for compatibility and redirect into the editor's theme and navigation modes.

## 9. Media

The media manager is tenant-scoped. It supports JPEG, PNG, GIF, WebP, and SVG uploads up to 5 MB. Files are stored under tenant-specific paths, database metadata is created, and `OptimizeMediaJob` handles dimensions and a 150×150 JPEG thumbnail where supported.

Media can be selected through inspector fields used by blocks such as Image and Testimonial. Deletion removes the record, original file, and thumbnail. The current system does not yet provide storage quotas, responsive image variants, WebP/AVIF generation, reusable alt-text metadata, or orphan-reference detection.

## 10. Storefront and commerce fixture

The default commerce driver is `fixture`. The fixture catalog currently contains 8 sample products, product variants, three collections, and featured/all/related source groups.

Commerce content is hydrated separately from the saved block tree. Blocks keep presentation settings and source keys; request-time provider data is returned in an envelope keyed by block UUID. Product price, availability, variants, and cart totals are supplied by the provider and are not persisted into page drafts.

The Product Grid is a Smart Block with Simple and Advanced editing modes. Its inspector separates Content, Layout, Card, Style, Responsive, and Advanced settings. Merchants can select a supported provider source, sort and limit products, choose grid/carousel/list/editorial presentation, apply one of nine card presets, reorder or hide supported card fields, change visual treatment and hover behavior, and set independent desktop/tablet/mobile columns and image ratios. These controls only save presentation settings. They never rewrite provider product records.

The current fixture supports Featured, All, and Related product sources plus provider order, price, and alphabetical sorting. Sources such as newest, sale, best sellers, manual selections, and collection/category queries will become available only when a commerce provider can supply them authoritatively.

The fixture cart is server-side session state namespaced by tenant and provider. Clients send variant IDs and quantities, and the server validates them and computes totals. The checkout endpoint leads to a fixture handoff page that demonstrates the flow only—it does not charge a card or create a real order.

The provider boundary (`CommerceProvider`) is prepared for future integrations. A real provider must preserve the normalized data shapes, receive the current tenant explicitly, and return an unavailable state rather than inventing prices or stock.

Relevant code:

- `config/commerce.php` — fixture catalog and sources;
- `app/Commerce/Contracts/CommerceProvider.php` — provider contract;
- `app/Commerce/FixtureCommerceProvider.php` and `NullCommerceProvider.php`;
- `app/Commerce/CommerceHydrator.php` — per-block request hydration;
- `app/Commerce/CommerceCartManager.php` — session cart orchestration;
- `app/Http/Controllers/TenantCommerceCartController.php`.

## 11. Contact and enquiries

`ContactFormBlock` posts to the tenant contact endpoint. Submissions are stored as tenant-owned `ContactSubmission` records and protected by validation and rate limiting. This powers general contact and enquiry-style kit pages; it is not a booking engine or transactional messaging system.

## 12. Dashboard and account boundary

The tenant dashboard shows truthful website information: page count, published page count, recent edit time when available, page publication status, theme/navigation summary, and quick actions into the unified builder.

Account settings and logout belong to the central domain. From a tenant subdomain they use full browser navigation/native submission to avoid cross-origin Inertia behavior.

## 13. What remains

The strongest current gaps are:

- page-level and site-wide SEO: titles/descriptions, canonical URLs, Open Graph data, sitemap, and robots controls;
- general site identity: site name, tagline, logo, favicon, social links, and analytics settings;
- persistent server-side revisions, restore points, audit history, and publish diffs;
- production media hardening: quotas, derivatives, object storage strategy, and job failure visibility;
- custom-domain verification, DNS workflow, and TLS provisioning;
- a production commerce provider, checkout/payment integration, orders, and authoritative stock;
- production deployment decisions around database, queues, SSR, monitoring, backups, and email delivery;
- cleanup of existing repository-wide frontend lint debt.

See `gaps.md` for the detailed gap analysis and `spec.md` for the broader product specification. Some numeric summaries in older sections of those documents may lag behind the current 23-block registry, so verify implementation counts against `config/blocks.php`.

## 14. Testing and development

The application uses Pest/PHPUnit for backend and feature coverage, ESLint and Prettier for frontend quality, Laravel Pint for PHP formatting, and Vite for the frontend build.

Common commands:

```bash
composer run dev
php artisan test --compact
php artisan test --compact --filter=SiteKit
vendor/bin/pint --dirty --format agent
npm run build
npm run lint
```

Focused tests exist for tenant isolation, design-catalog validation, kit eligibility/application, cloned block IDs, rollback, pages, editor responses, theme/navigation propagation, media, contact submissions, commerce hydration, and storefront behavior.

## 15. Code map

| Area | Primary locations |
|---|---|
| Routes and tenancy | `routes/web.php`, `app/Http/Middleware/IdentifyTenant.php` |
| Models and persistence | `app/Models`, `database/migrations` |
| Design kits | `config/designs.php`, `app/Actions/Designs` |
| Block schema | `config/blocks.php` |
| Block components | `resources/js/components/BuilderBlocks` |
| Editor | `resources/js/pages/Tenant/Editor.vue`, `resources/js/components/editor` |
| Public renderer | `resources/js/pages/Tenant/PublicPage.vue`, `resources/js/components/RenderPublicNode.vue` |
| Theme/navigation | `app/Http/Controllers/TenantThemeController.php`, `TenantNavigationController.php` |
| Media | `TenantMediaController.php`, `app/Jobs/OptimizeMediaJob.php`, `MediaPicker.vue` |
| Commerce | `config/commerce.php`, `app/Commerce`, `app/Commerce/Contracts/CommerceProvider.php` |
| Tests | `tests/Feature`, `tests/Unit` |

## 16. Rules for extending the product

- Keep pages, navigation, theme, media, preview, and publishing inside the unified editor.
- Treat kits and layouts as composition data for the existing block system, never as a parallel template engine.
- Deep-clone catalog block trees and regenerate IDs before tenant persistence.
- Never overwrite an established tenant with an onboarding kit.
- Preserve the draft/publish boundary.
- Register new blocks in the Vue component map and backend definition/nesting rules, then test both editing and public rendering.
- Keep authoritative commerce data outside saved page JSON.
- Describe fixture storefronts, enquiries, and checkout simulations truthfully.
