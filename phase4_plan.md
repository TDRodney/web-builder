# Phase 4 — Advanced Blocks, Presets & Navigation

> **Depends on**: Phase 3 (Media Pipeline) — `ImageBlock`, `TestimonialBlock`, and preset templates with default images require the media system  
> **Covers**: Gap 2 (P1/P2 blocks + presets) + Gap 5 (Navigation System)

---

## Overview

Phase 4 extends the block library from 9 blocks to 17+, introduces pre-built page section templates (presets), and delivers a complete site navigation system (header + footer). Every block must follow the authoring guide in `AGENTS.md`.

---

## Stage 1 — P1 Priority Blocks

### 1.1 `RichTextBlock`

**What it is**: An inline WYSIWYG editor for formatted prose — bold, italic, headings, links, lists.

**Props**:
```typescript
{
  html: string;   // sanitized HTML string
  padding: number;
  backgroundColor: string;
}
```

**Implementation**:
- Integrate [Tiptap](https://tiptap.dev/) as the editor library (`npm install @tiptap/vue-3 @tiptap/starter-kit`)
- In editor mode (`isEditable = true`): render `<EditorContent :editor="editor">` with a toolbar for Bold, Italic, Heading 1/2, Bullet list, Link
- In public mode: render `<div v-html="blockProps.html" class="prose">` (use Tailwind Typography `prose` class for semantic styling)
- **Sanitization**: Strip `<script>`, `on*` event attributes server-side in `ValidatesBlockSchema.php` or via a dedicated sanitizer before saving

**Theme Integration**:
- Wrap rendered HTML in a div with `font-family: var(--theme-font-body); color: var(--theme-text)`
- Heading elements inside the `prose` output inherit `--theme-font-heading` via a CSS rule in the canvas stylesheet

**`config/blocks.php`**:
```php
'RichTextBlock' => [
    'type'     => 'RichTextBlock',
    'label'    => 'Rich Text',
    'category' => 'content',
    'icon'     => 'file-text',
    'defaultProps' => [
        'html'            => '<p>Start typing...</p>',
        'padding'         => 20,
        'backgroundColor' => 'transparent',
    ],
    'inspectorFields' => [
        ['key' => 'padding', 'label' => 'Padding (px)', 'type' => 'range', 'min' => 0, 'max' => 150],
        ['key' => 'backgroundColor', 'label' => 'Background', 'type' => 'color'],
    ],
],
```

---

### 1.2 `VideoEmbedBlock`

**What it is**: Embeds responsive YouTube, Vimeo, or Loom video iframes.

**Props**:
```typescript
{
  url: string;
  provider: 'youtube' | 'vimeo' | 'loom' | 'raw';
  aspectRatio: '16/9' | '4/3' | '1/1';
  padding: number;
  backgroundColor: string;
}
```

**Implementation**:
- A `resolveEmbedUrl(url: string, provider: string): string` utility that transforms:
  - `youtube.com/watch?v=ID` → `youtube.com/embed/ID`
  - `youtu.be/ID` → `youtube.com/embed/ID`
  - `vimeo.com/ID` → `player.vimeo.com/video/ID`
  - `loom.com/share/ID` → `loom.com/embed/ID`
  - `raw` → pass `url` directly
- Render: `<div :style="{aspectRatio}"><iframe :src="embedUrl" allowfullscreen ...></iframe></div>`
- If `url` is empty AND `isEditable`: show a placeholder with YouTube icon and "Paste a video URL in the inspector"

**Theme Integration**: No direct CSS variable usage (iframe is sandboxed), but the wrapper div respects `padding` and `backgroundColor` via `RenderNode`.

---

## Stage 2 — P2 Priority Blocks

### 2.1 `FAQBlock`

**What it is**: A collapsible accordion for frequently asked questions.

**Props**:
```typescript
{
  items: Array<{ question: string; answer: string }>;
  padding: number;
  backgroundColor: string;
}
```

**Implementation**:
- Each item is rendered as a disclosure widget with a Vue `ref<boolean>` tracking open state
- Use CSS `max-height` + `transition: max-height 0.3s ease` for smooth open/close animation
- In `isEditable` mode: inspector should allow adding/removing/reordering `items` array entries. This requires a new inspector field type `'repeater'` (see Inspector Fields extension below)

**Theme Integration**:
- Question text: `font-family: var(--theme-font-heading); color: var(--theme-text)`
- Answer text: `font-family: var(--theme-font-body); color: var(--theme-text)`
- Dividers between items: use `opacity: 0.15` on a thin border to adapt to light/dark themes
- Toggle icon/chevron: `color: var(--theme-primary)`

---

### 2.2 `TestimonialBlock`

**What it is**: A social-proof quote card with author attribution.

**Depends on**: Phase 3 (for `avatarSrc` media picker)

**Props**:
```typescript
{
  quote: string;
  authorName: string;
  authorRole: string;
  avatarSrc: string;   // media URL from MediaPicker
  padding: number;
  backgroundColor: string;
}
```

**Theme Integration**:
- Quote text: `font-family: var(--theme-font-body); color: var(--theme-text)`
- Author name: `font-family: var(--theme-font-heading); color: var(--theme-primary)`
- Avatar image: `border-radius: 50%` (circular, not theme-radius, as avatars are always circles)
- Card wrapper: optionally uses `var(--theme-border-radius)` and a subtle shadow

---

### 2.3 `PricingTableBlock`

**What it is**: A pricing card grid displaying plan comparisons.

**Props**:
```typescript
{
  plans: Array<{
    title: string;
    price: string;       // e.g. "$29"
    period: string;      // e.g. "/month"
    features: string[];  // bullet list
    ctaText: string;
    ctaUrl: string;
    isPopular: boolean;
  }>;
  padding: number;
  backgroundColor: string;
}
```

**Theme Integration**:
- Plans container: `display: grid; grid-template-columns: repeat(plans.length, 1fr)`
- Popular plan card highlighted with `border: 2px solid var(--theme-primary)` and `background: var(--theme-primary)` header
- CTA button: same style as `ButtonBlock` primary variant
- Card border-radius: `var(--theme-border-radius)`

---

### 2.4 `ContactFormBlock`

**What it is**: A simple lead-capture contact form.

**Props**:
```typescript
{
  fields: Array<{
    type: 'text' | 'email' | 'textarea';
    label: string;
    placeholder: string;
    required: boolean;
  }>;
  submitLabel: string;     // e.g. "Send Message"
  successMessage: string;  // shown after submission
  padding: number;
  backgroundColor: string;
}
```

**Implementation**:
- Requires a new backend endpoint `POST /contact` handled by `TenantContactController`
- Stores submissions in a new `contact_submissions` table (tenant_id, page_id, data JSON, ip, created_at)
- Form validation: all `required` fields must be non-empty; email fields must match email format
- On public site: renders a Vue form that POSTs to `/contact` using `useHttp`, shows success/error toast
- In editor: renders a static preview with no actual form submission capability

**Theme Integration**:
- Submit button: same CSS as primary `ButtonBlock`
- Input focus rings: `outline-color: var(--theme-primary)`
- Input border-radius: `var(--theme-border-radius)`

---

## Stage 3 — Inspector Fields Extension

Phase 4 blocks require two new inspector field types that don't yet exist:

### 3.1 `'repeater'` Field Type

Used by `FAQBlock` (items array), `PricingTableBlock` (plans array).

- Inspector sidebar renders a list of collapsible sub-forms, one per item
- Each sub-form has sub-fields defined in a `subFields` array within the parent `inspectorField` definition
- Add/Remove/Reorder buttons per item

### 3.2 `'media'` Field Type (from Phase 3)

Used by `ImageBlock` and `TestimonialBlock` (avatar).

- Inspector sidebar renders a thumbnail preview + "Choose Image" button
- Clicking opens the `MediaPicker.vue` modal
- On select, updates the block prop value to the chosen media URL

---

## Stage 4 — Block Presets / Templates

Block presets are pre-built AST sub-trees users can insert in one click from the editor sidebar.

### Implementation

**Storage**: Presets are static TypeScript objects defined in `resources/js/lib/blockPresets.ts`.

**Format**: Each preset is a full `BlockNode[]` tree (same structure as `draft_config`). All `id` values in presets must be `'__preset__'` placeholders — when inserted, the editor's `addPreset(preset)` function calls the existing `generateNewIds()` utility to replace all IDs with fresh UUIDs.

**Editor Integration**: Add a "Templates" tab to the sidebar block panel. Clicking a template calls `addPreset()` which deep-clones the preset array, generates new IDs, and appends it to the root `blocks` array (triggering auto-save).

### Target Presets

#### Hero with CTA
```typescript
{
  type: 'LayoutGrid',
  props: { columns: 2, gap: '2rem', padding: 60, backgroundColor: 'transparent' },
  children: [
    {
      type: 'LayoutColumn',
      props: { padding: 20 },
      children: [
        { type: 'HeroBlock', props: { headline: 'Your Headline', subheadline: 'Your subheadline.' } },
        { type: 'ButtonBlock', props: { label: 'Get Started', variant: 'primary', size: 'lg' } },
      ]
    },
    {
      type: 'LayoutColumn',
      props: { padding: 20 },
      children: [
        { type: 'ImageBlock', props: { src: '', alt: 'Hero image', height: '400px' } },
      ]
    }
  ]
}
```

#### Features Row
3-column `LayoutGrid` with three pre-configured `FeatureBlock` nodes.

#### Pricing Grid
3-column `LayoutGrid` containing a `PricingTableBlock`.

#### FAQ Section
A `HeroBlock` (headline: "Frequently Asked Questions") followed by a `FAQBlock` with 3 sample items.

---

## Stage 5 — Navigation System (Gap 5)

### 5.1 Data Model

Add `navigation_config` JSON column to the `tenants` table:

```php
$table->json('navigation_config')->nullable()->after('theme_config');
```

**Navigation shape**:
```json
{
  "header": {
    "showLogo": true,
    "items": [
      { "label": "Home",  "slug": "home",  "type": "internal" },
      { "label": "About", "slug": "about", "type": "internal" },
      { "label": "Blog",  "href": "https://blog.example.com", "type": "external" }
    ],
    "ctaButton": { "label": "Contact", "slug": "contact" }
  },
  "footer": {
    "copyright": "© 2026 My Brand"
  }
}
```

### 5.2 Navigation Editor

Add a **"Navigation"** panel to the editor sidebar (alongside the existing "Blocks" and "Pages" panels).

- **Header Items**: Drag-to-reorder list of nav links. Each item: label field, page selector (internal) or URL field (external).
- **CTA Button**: Toggle on/off + label + target page selector.
- **Footer**: Copyright text field.
- **Save**: `PATCH /navigation` → `TenantNavigationController::update`

### 5.3 Navigation Rendering

**Editor**: A static `SiteHeader.vue` component rendered above the canvas that displays the navigation preview using the current `navigation_config`. Links are non-interactive in edit mode.

**Public Site**: `PublicPage.vue` includes `SiteHeader.vue` and `SiteFooter.vue` components above/below the block tree. These fetch `navigation_config` from the `tenant` prop passed by `TenantPublicSiteController`.

**Theme Integration**:
- Header background: `var(--theme-bg)` with a subtle `box-shadow`
- Nav link hover: `color: var(--theme-primary)`
- CTA button: primary `ButtonBlock` styling
- Logo text: `font-family: var(--theme-font-heading); color: var(--theme-text)`

---

## Test Strategy

### New Test Files

- **`TenantBlockAdvancedTest.php`**: Validates save/publish of pages containing `RichTextBlock`, `FAQBlock`, `PricingTableBlock`, `ContactFormBlock`, `VideoEmbedBlock`
- **`TenantContactSubmissionTest.php`**: POST to `/contact` stores submission, validates fields, rate-limits spam
- **`TenantNavigationTest.php`**: Owner can save and retrieve navigation config, non-owner gets 403

---

## File Change Summary

| File | Action |
|---|---|
| `resources/js/components/BuilderBlocks/RichTextBlock.vue` | **NEW** |
| `resources/js/components/BuilderBlocks/VideoEmbedBlock.vue` | **NEW** |
| `resources/js/components/BuilderBlocks/FAQBlock.vue` | **NEW** |
| `resources/js/components/BuilderBlocks/TestimonialBlock.vue` | **NEW** |
| `resources/js/components/BuilderBlocks/PricingTableBlock.vue` | **NEW** |
| `resources/js/components/BuilderBlocks/ContactFormBlock.vue` | **NEW** |
| `resources/js/lib/blockPresets.ts` | **NEW** |
| `resources/js/components/SiteHeader.vue` | **NEW** |
| `resources/js/components/SiteFooter.vue` | **NEW** |
| `resources/js/lib/blockRegistry.ts` | **MODIFY** — add 5 new blocks |
| `config/blocks.php` | **MODIFY** — add 5 new block definitions + nesting |
| `app/Http/Controllers/TenantContactController.php` | **NEW** |
| `app/Http/Controllers/TenantNavigationController.php` | **NEW** |
| `database/migrations/*_add_navigation_config_to_tenants.php` | **NEW** |
| `database/migrations/*_create_contact_submissions_table.php` | **NEW** |
| `routes/web.php` | **MODIFY** — add contact + navigation routes |
| `resources/js/pages/Tenant/Editor.vue` | **MODIFY** — add Templates panel + SiteHeader |
| `resources/js/pages/Tenant/PublicPage.vue` | **MODIFY** — add SiteHeader + SiteFooter |
| `tests/Feature/TenantBlockAdvancedTest.php` | **NEW** |
| `tests/Feature/TenantContactSubmissionTest.php` | **NEW** |
| `tests/Feature/TenantNavigationTest.php` | **NEW** |
