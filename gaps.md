# Web Builder — Current Gap Analysis and Roadmap

> Last reconciled: 2026-07-14.
>
> Sources checked: the active application code, `spec.md`, `AGENTS.md`, routes, migrations, models, controllers, Vue components, factories, and feature tests. Where documentation and code disagree, the active code is treated as authoritative.

---

## Executive Summary

The project has a working multi-tenant website-builder core:

- subdomain-based tenant isolation;
- authentication, registration, 2FA, and passkeys;
- multi-page CRUD with a configurable homepage;
- a 15-block drag-and-drop editor;
- draft, autosave, undo/redo, and publish flows;
- shared Vue rendering for the editor and public site;
- block presets and in-canvas block actions;
- tenant themes and curated Google Fonts;
- media upload, thumbnails, selection, and deletion;
- navigation configuration;
- contact-form submissions with rate limiting.

The original roadmap is approximately **70% complete**. The largest remaining product areas are SEO, server-side revision history, general site settings, production media hardening, custom domains, and production database/deployment work.

The previously identified navigation propagation regression is resolved: saved navigation is now included in both editor and public Inertia props, with regression coverage for each response.

### Status Overview

| Area | Status | Summary |
|---|---|---|
| Multi-page management | Complete | CRUD, switching, metadata, homepage selection, and deletion guard are implemented |
| Block editor | Mostly complete | 15 block types, presets, toolbar, schema validation, and TipTap are implemented; 6 specialized blocks remain |
| Media pipeline | Core complete | Upload, tenant isolation, thumbnails, picker, and deletion work; production optimization features remain |
| Theming | Core complete | Theme persistence, dashboard controls, CSS variables, and curated Google Fonts work |
| General site settings | Mostly missing | Site identity, social, analytics, SEO defaults, and custom domains are not modeled |
| Navigation | Core complete | API, editor UI, external links, editor propagation, and public propagation are implemented |
| SEO | Mostly missing | Responsive public rendering and `<html lang>` exist; page metadata, sitemap, robots, canonical, and OG data do not |
| History/collaboration | Missing | Client undo/redo exists, but no persistent revisions, audit trail, or publish diff |
| Testing/infrastructure | Good foundation | 93 tests pass; production database and several integration/SEO tests remain |

---

## Correctness Follow-Up

These are defects in features currently described as implemented, rather than new product capabilities.

### P0 — Navigation Configuration Propagation [Resolved]

`TenantEditorController` and `TenantPublicSiteController` now include `navigation_config` in the tenant prop expected by `Editor.vue` and `PublicPage.vue`.

Verified behavior:

- saved navigation loads when reopening the editor;
- public pages receive the saved header/footer configuration;
- API persistence, editor propagation, and public propagation are covered by feature tests.

Required work:

- [x] Include `navigation_config` in the editor tenant prop.
- [x] Include `navigation_config` in the public-page tenant prop.
- [x] Add an editor Inertia-prop test for existing navigation.
- [x] Add a public-page Inertia-prop test for existing navigation.

### P1 — Block Theme Defaults Are Not Fully Normalized

`AGENTS.md` requires transparent backgrounds and `--theme-text` for text defaults. Most registered definitions follow this rule, but some legacy creation paths still use `#ffffff`, `#f8fafc`, or `#0f172a`.

`RenderNode.vue` normalizes known white backgrounds to transparent, and `AtomicText.vue` maps the legacy text value to the theme token, so this is currently masked at render time. The persisted defaults should still be normalized to prevent future drift.

- [ ] Normalize legacy defaults in `config/blocks.php` and `Editor.vue`.
- [ ] Add a registry test asserting theme-safe default props.

---

## Gap 1 — Multi-Page Management

### Status: Complete

Implemented:

- tenant-scoped page listing, creation, update, and deletion endpoints;
- `title`, `is_homepage`, and `sort_order` page metadata;
- page selector and page-management controls in the editor;
- force-save before switching pages;
- configurable homepage resolution on the public site;
- automatic unsetting of the previous homepage;
- backend guard preventing deletion of the homepage;
- tenant-scoped slug uniqueness and wildcard public slug routing.

Remaining quality improvements, not blockers for the original gap:

- [ ] Add drag-and-drop page ordering in the page manager.
- [ ] Decide how redirects should work when a published page slug changes.
- [ ] Consider a soft-delete or archive workflow for published pages.

---

## Gap 2 — Block Library and Editor Experience

### Status: Mostly Complete

### Implemented Block Types: 15

| Block Type | Status | Notes |
|---|---|---|
| `HeroBlock` | Done | Theme-aware heading and subheading |
| `FeatureBlock` | Done | Theme-aware feature content |
| `AtomicText` | Done | Text content with size and color controls |
| `LayoutGrid` | Done | Configurable nested grid container |
| `LayoutColumn` | Done | Nested column container |
| `ButtonBlock` | Done | Primary, secondary, and outline variants |
| `DividerBlock` | Done | Thickness, color, and margin controls |
| `SpacerBlock` | Done | Adjustable vertical spacing |
| `ImageBlock` | Done | Connected to the media picker |
| `RichTextBlock` | Done | TipTap WYSIWYG with headings, lists, bold, and italic |
| `VideoEmbedBlock` | Done | YouTube, Vimeo, Loom, and raw video support |
| `FAQBlock` | Done | Repeater-driven accordion content |
| `TestimonialBlock` | Done | Quote, author, role, and media-picker avatar |
| `PricingTableBlock` | Done | Repeater-driven plans |
| `ContactFormBlock` | Done | Public submission endpoint and rate limiting |

The previous statement that `RichTextBlock` only had a raw HTML textarea is obsolete. TipTap is installed and integrated. The stored block property remains HTML, and the public renderer intentionally renders that HTML.

### Remaining Block Types

| Priority | Block Type | Key capability |
|---|---|---|
| P1 | `IconBlock` | Lucide icon selection, size, and theme-aware color |
| P1 | `ListBlock` | Ordered, unordered, and icon lists |
| P2 | `MapEmbedBlock` | Validated map embed URL or coordinates |
| P3 | `CodeBlock` | Escaped, syntax-highlighted code |
| P3 | `CountdownBlock` | Client-side countdown with expiry behavior |
| P3 | `SocialLinksBlock` | Repeater of platform and URL pairs |

### Editor Infrastructure Already Implemented

- backend block definitions in `config/blocks.php`;
- frontend component mapping in `blockRegistry.ts`;
- definitions and nesting shared through Inertia `blocksConfig`;
- data-driven inspector fields;
- recursive backend schema and nesting validation;
- drag-and-drop nesting enforcement;
- duplicate, delete, move, copy, paste, and wrap actions;
- three presets: Hero with CTA, Features Grid, and FAQ Accordion Row;
- editor and public block-level error boundaries;
- desktop, tablet, and mobile preview modes.

### Remaining Editor Improvements

- [ ] Implement the six specialized blocks above as product demand requires.
- [ ] Sanitize or explicitly trust-and-document stored rich-text HTML before public rendering.
- [ ] Expand TipTap controls only if needed: links, undo/redo inside rich text, blockquote, alignment, and media.
- [ ] Add frontend component tests for complex repeaters and nested drag/drop behavior.

---

## Gap 3 — Media and Asset Pipeline

### Status: Core Complete, Production Hardening Pending

Implemented:

- tenant-scoped `media` table and model;
- upload validation for JPEG, PNG, GIF, WebP, and SVG up to 5 MB;
- tenant-specific storage paths;
- asynchronous `OptimizeMediaJob` dispatch;
- dimension detection;
- 150×150 cover-style JPEG thumbnail generation through GD;
- media listing with URL and thumbnail URL accessors;
- file, thumbnail, and record deletion;
- tenant-isolation tests for listing and deletion;
- media picker with upload and selection workflows;
- `ImageBlock` and testimonial avatar integration.

Remaining:

- [ ] Enforce per-tenant storage quotas.
- [ ] Add responsive image variants, such as 600 px and 1200 px.
- [ ] Generate WebP or AVIF delivery variants.
- [ ] Add a media metadata endpoint for alt text or other reusable metadata.
- [ ] Define and test an S3-compatible production disk strategy.
- [ ] Add retry/failure visibility for queued optimization jobs.
- [ ] Add orphan detection when media records are deleted while block configs still reference their URLs.

Factory status:

- `MediaFactory` exists but does not yet include the proposed `withThumbnail()` state.
- `ContactSubmissionFactory` exists only as an empty scaffold and needs useful defaults.

---

## Gap 4 — Site-Wide Settings and Theming

### Status: Theme Core Complete, General Settings Missing

Implemented:

- nullable `theme_config` JSON on tenants;
- model casting and mass-assignment support;
- ownership-guarded `PATCH /theme` endpoint;
- validation for four colors, curated fonts, and radius presets;
- dashboard theme settings with palette presets and individual controls;
- `useTheme()` CSS-variable generation;
- editor and public theme propagation;
- curated Google Font selection and `<Head>` link injection;
- theme-aware shared block components.

Current theme tokens:

- `--theme-primary`
- `--theme-secondary`
- `--theme-bg`
- `--theme-text`
- `--theme-border-radius`
- `--theme-font-heading`
- `--theme-font-body`

The original Google Fonts gap is therefore complete at the curated-picker level. A searchable Google Fonts API integration would be an optional enhancement, not required for the current theme system.

### Missing Tenant Settings

The tenant model does not currently store:

- `site_name`;
- `tagline`;
- `favicon_path`;
- `logo_path`;
- `custom_domain`;
- `social_links`;
- `seo_defaults`;
- `analytics_config`.

Remaining:

- [ ] Add a general site identity schema and settings UI.
- [ ] Add logo and favicon selection through the media pipeline.
- [ ] Add social-link settings.
- [ ] Add analytics configuration with explicit consent/privacy considerations.
- [ ] Add tenant-level SEO defaults.
- [ ] Add custom-domain configuration only with a complete verification and TLS plan.
- [ ] Optionally expand theme tokens for accent, surface, muted text, spacing, max width, shadows, and type scale.

---

## Gap 5 — Navigation System

### Status: Core Complete; Enhancements Remain

Implemented:

- `navigation_config` JSON storage and model cast;
- ownership-guarded save endpoint;
- header item editing, add/remove/reorder, and CTA controls;
- internal page selection;
- external URL editing and public `<a>` rendering;
- shared `SiteHeader.vue` and `SiteFooter.vue` components;
- single-line footer copyright configuration.
- saved navigation propagated to editor and public Inertia responses;
- integration tests for editor and public navigation props.

External URL support is no longer a gap. It is present in the editor, renderer, and API test fixture.

Remaining and corrective work:

- [x] Fix navigation prop propagation as described in the P0 section.
- [ ] Add active/current-page styling for internal navigation links.
- [ ] Add a responsive mobile navigation menu; the current mobile breakpoint hides the link list.
- [ ] Add a multi-column footer structure.
- [ ] Add validation for the nested navigation payload, including external URLs.
- [ ] Use route-aware or domain-aware URL generation instead of assuming root-relative paths.
- [ ] Support custom-domain navigation when custom domains are implemented.

---

## Gap 6 — SEO and Public-Site Quality

### Status: Responsive Rendering Complete, SEO Mostly Missing

Implemented:

- public pages use the same Vue block components as the editor;
- published configuration is isolated from drafts;
- full-width public layout without the old card wrapper;
- responsive layouts and container-aware blocks;
- `<html lang>` is provided by the root Inertia Blade template;
- selected Google Fonts are emitted through Inertia `<Head>`.

Current limitation:

- the public page does not set a tenant- or page-specific title;
- the root template falls back to the generic application title;
- there is no page description, Open Graph data, canonical URL, page-level indexing control, tenant favicon, sitemap, or tenant robots response.

### Required SEO Work

- [ ] Add `meta_title`, `meta_description`, `og_image_path`, and `is_indexed` to pages.
- [ ] Add SEO controls to page settings in the editor.
- [ ] Render page/tenant title and description through Inertia `<Head>`.
- [ ] Render Open Graph and canonical tags.
- [ ] Render `noindex, nofollow` when indexing is disabled.
- [ ] Add tenant-aware `/sitemap.xml` before the public catch-all route.
- [ ] Add tenant-aware `/robots.txt` before the public catch-all route.
- [ ] Connect favicon and default OG image to general site settings.
- [ ] Add feature tests for titles, metadata, indexing, sitemap, and robots responses.
- [ ] Validate the production SSR deployment path before claiming server-rendered SEO output in production.

---

## Gap 7 — Version History and Collaboration

### Status: Persistent History Missing

Implemented:

- in-memory undo and redo stacks;
- snapshot capture before block mutations;
- save suppression during history travel;
- draft and published configurations stored separately.

The undo/redo history is lost on refresh and is not an audit trail.

Remaining:

- [ ] Add a `page_revisions` table and `PageRevision` model.
- [ ] Record a revision on every publish.
- [ ] Add periodic or manual checkpoints without recording every 400 ms autosave.
- [ ] Cap or prune revisions per page.
- [ ] Add revision timeline, preview, and restore UI.
- [ ] Add a publish diff summary for added, removed, and modified blocks.
- [ ] Record the user and trigger for each revision.
- [ ] Defer real-time collaboration until persistent revision semantics are stable.

Suggested revision shape:

```text
page_revisions
  id
  page_id
  user_id
  config
  label nullable
  trigger: publish | periodic | manual | restore
  created_at
  updated_at
```

---

## Gap 8 — Infrastructure, Testing, and Developer Experience

### Status: Good Development Baseline, Production Work Pending

Current verified baseline on 2026-07-14:

- **93 tests passed**;
- **1 test skipped**;
- **313 assertions**;
- production Vite build completed successfully;
- the build emitted only third-party Rolldown annotation warnings from a nested VueUse dependency.

The previous statement that the suite contained 23 feature tests is obsolete.

Implemented test coverage includes:

- registration and subdomain validation;
- login, password reset, email verification, 2FA, and passkeys;
- tenant editor access and tenant isolation;
- draft save and publish behavior;
- recursive block schema and nesting validation;
- multi-page CRUD and homepage behavior;
- theme settings;
- navigation API authorization and persistence;
- media validation, ownership, isolation, and deletion;
- contact submissions, validation, and rate limiting;
- dashboard and profile/security settings.

### Important Missing Tests

- [x] Navigation config appears in editor and public Inertia props.
- [ ] Public SEO metadata, sitemap, and robots behavior.
- [ ] Active navigation state and mobile navigation behavior.
- [ ] Rich-text public rendering and sanitization policy.
- [ ] Theme-safe defaults across every block definition.
- [ ] Media optimization job output and failure handling.
- [ ] Revision creation, restore, pruning, and tenant isolation once implemented.

### Production Infrastructure Gaps

- [ ] Move the production environment away from SQLite to PostgreSQL or MySQL.
- [ ] Run a dialect-compatibility audit for JSON columns, indexes, and transactions.
- [ ] Document production database environment variables in `.env.example`.
- [ ] Confirm queue workers, failed-job handling, and media optimization monitoring.
- [ ] Define production object storage and backup policies.
- [ ] Validate wildcard session-cookie behavior and authentication boundaries before custom domains.
- [ ] Define SSR build and process management if production SEO depends on SSR.

---

## Updated Implementation Roadmap

### Phase A — Correctness and Integration

- [x] Fix navigation propagation to editor and public pages.
- [x] Add navigation integration tests.
- [ ] Normalize remaining block theme defaults.
- [ ] Complete `ContactSubmissionFactory` and add `MediaFactory::withThumbnail()`.

### Phase B — SEO and Site Identity

- [ ] Add tenant site name, tagline, logo, and favicon.
- [ ] Add per-page SEO metadata and editor controls.
- [ ] Add title, description, Open Graph, canonical, and indexing tags.
- [ ] Add sitemap and robots endpoints with tests.

### Phase C — Reliability and Recovery

- [ ] Add persistent page revisions.
- [ ] Add revision preview and restore.
- [ ] Add publish diff summaries.
- [ ] Add media quota enforcement and optimization failure visibility.

### Phase D — Production Readiness

- [ ] Add responsive media variants and modern delivery formats.
- [ ] Validate S3-compatible storage.
- [ ] Complete PostgreSQL/MySQL compatibility work.
- [ ] Validate production SSR and queue deployment.
- [ ] Implement custom domains, DNS verification, URL generation, and TLS as one cohesive feature.

### Phase E — Optional Product Expansion

- [ ] Add the remaining specialized blocks based on user demand.
- [ ] Add multi-column footer and mobile navigation.
- [ ] Expand theme tokens and font discovery.
- [ ] Consider collaboration only after revision history is reliable.

---

## Architectural Decisions to Keep

| Decision | Reason |
|---|---|
| Shared Vue block components for editor and public rendering | Prevents Blade/Vue component drift |
| Backend-driven block definitions shared through Inertia | Keeps inspector and validation metadata centralized |
| Separate draft and published configurations | Prevents autosave from changing the public site |
| Header/footer outside the page block tree | Correctly models site-wide chrome |
| Tenant scope plus explicit ownership guards | Provides defense in depth for tenant isolation |
| Presets as cloned block trees | Reuses the same schema and renderer as normal blocks |
| Curated fonts before arbitrary font search | Keeps validation and loading predictable |

---

## Known Technical Risks

| Risk | Current mitigation | Remaining action |
|---|---|---|
| Navigation payload accepts malformed nested data | Only top-level arrays are currently validated | Add nested item, CTA, footer, and external-URL validation |
| Large JSON page configurations | Recursive validation | Measure payload size and set practical limits |
| Unsafe or malformed rich-text HTML | TipTap constrains normal editor input | Define sanitization/trust policy before broader HTML features |
| Unbounded tenant media | Per-file 5 MB limit | Add total tenant quotas and usage reporting |
| Queue failures hide missing thumbnails | Original image remains usable | Add failed-job visibility and retry behavior |
| SQLite write contention | Adequate for local development | Use PostgreSQL/MySQL in production |
| Cross-subdomain/custom-domain sessions | Wildcard cookie for central domain | Design a separate custom-domain authentication strategy |
| SEO depends on runtime rendering mode | Inertia `<Head>` metadata is available client-side | Validate and operate production SSR if crawl-time HTML is required |
