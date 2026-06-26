# Web Builder & Multi-Tenant Integration Guide

Building a web page builder within a multi-tenant system is a fantastic way to learn advanced full-stack architecture. It challenges you to design:
1. A **data schema** that holds dynamic layout configurations.
2. An **Inter-Process Communication (IPC)** bridge between the editor sidebar and the live preview page.
3. Strict **tenant isolation** to ensure organizers can only build/view their own sites.

This guide explains how Nexura’s builder is architected and how you can implement a similar system.

---

## 📐 Data Schema: The Config-Driven Layout

Instead of storing web layouts in separate HTML tables or static files, Nexura uses a **JSON-based config-driven model**. Every tenant's site configuration is saved under their `Organizer` record in two JSON columns:
* `draft_config`: Where edits are saved temporarily as the builder edits.
* `published_config`: The live configuration shown to public visitors.

### The JSON Config Structure
The JSON schema defines a top-level theme, global settings, a header, a footer, and a dynamic list of sections. Each section contains a list of nested blocks:

```json
{
  "version": "2.0.0",
  "theme": "modern",
  "global_styles": {
    "colors": {
      "primary": "#3b82f6",
      "text": "#1f2937"
    }
  },
  "sections": [
    {
      "id": "hero-section-uuid",
      "type": "hero-modern",
      "settings": {
        "background_image": "https://example.com/hero.jpg",
        "alignment": "center"
      },
      "blocks": [
        {
          "id": "block-uuid-1",
          "type": "heading",
          "content": "Welcome to Our Event Store",
          "styles": {
            "font_size": "2.5rem",
            "font_weight": "700"
          }
        },
        {
          "id": "block-uuid-2",
          "type": "subtext",
          "content": "Check out our upcoming schedule below.",
          "styles": {
            "font_size": "1.2rem"
          }
        }
      ]
    }
  ]
}
```

---

## 🔄 The Builder Workspace & postMessage IPC

To make the editor fast and responsive, Nexura separates the editor controls (sidebar) from the site display (preview pane).

```
┌────────────────────────────────────────────────────────┐
│                      BUILDER SHELL                     │
├───────────────────────────┬────────────────────────────┤
│                           │                            │
│     VUE.JS SIDEBAR        │       PREVIEW IFRAME       │
│    (Editor Controls)      │    (Laravel Blade View)    │
│                           │                            │
│  [Add Section]            │  ┌──────────────────────┐  │
│  [Drag to Reorder]        │  │  Welcome to Alchemist│  │
│  [Edit Heading]           │  │                      │  │
│                           │  │  [Click to Select]   │  │
│             │             │  └──────────────────────┘  │
│             └─ postMessage ──────────►                 │
│             ◄─ postMessage ──────────┘                 │
│                           │                            │
└───────────────────────────┴────────────────────────────┘
```

### 1. The Split Pane Shell
The editor page loads a Vue shell with two panels:
* **The Controller (Sidebar)**: A Vue application (`BuilderSidebar.vue`) rendering the editing panels, colors, and layout ordering.
* **The Preview (Iframe)**: An iframe loaded at the tenant's preview URL (`http://alchemist.localhost:8000/preview-mode`).

### 2. Communicating via the postMessage API
Because the parent window (the editor sidebar) and the iframe (the preview site) are distinct execution contexts, they communicate asynchronously using the browser's `window.postMessage` API.

#### Parent Vue App sending updates to Iframe:
When the user edits a style or reorders sections:
```javascript
// Inside Vue Sidebar Component
notifyIframe(type, payload) {
    const iframe = document.getElementById('builder-frame');
    if (iframe && iframe.contentWindow) {
        iframe.contentWindow.postMessage({ type, payload }, '*');
    }
}

// Example usage: Highlight section on hover
selectSection(sectionId) {
    this.activeSectionId = sectionId;
    this.notifyIframe('highlight-section', { sectionId });
}
```

#### Iframe listening for updates:
The preview template (`resources/views/themes/dynamic.blade.php`) contains JavaScript that listens for these events:
```javascript
window.addEventListener('message', function(event) {
    const data = event.data;
    if (!data) return;

    if (data.type === 'highlight-section') {
        const sectionId = data.payload.sectionId;
        // Highlight the corresponding DOM element with a blue border
        document.querySelectorAll('.builder-section-wrapper').forEach(el => {
            el.classList.toggle('border-blue-500', el.dataset.sectionId === sectionId);
        });
    }
});
```

---

## 🛡️ Multi-Tenant Integration Rules

When building the editor backend, ensuring that tenants do not edit or preview other tenants' configurations is critical:

### 1. Routing & Tenant Context
All builder routes are defined inside [routes/tenant.php](file:///c:/Users/Z.BOOK/Desktop/things/code/nexura-dashboarrd-1/routes/tenant.php) and are wrapped in the `IdentifyTenant` middleware.
* When navigating to `http://alchemist.localhost:8000/admin/builder`, the middleware loads the `Organizer` record for `alchemist`.
* Any API request to update the draft (`/builder/draft`) is routed through the subdomain, automatically referencing the correct organizer.

### 2. Controller Authorization
In your controller methods, verify that the authenticated user belongs to the active tenant:
```php
public function edit(Request $request)
{
    $organizer = $request->attributes->get('organizer');
    
    // Ensure the logged-in user is allowed to edit this organizer
    if (auth()->user()->organizer_id !== $organizer->id) {
        abort(403, 'Unauthorized access to this site builder.');
    }
    
    return view('builder.edit', [
        'organizer' => $organizer,
        'initialConfig' => $organizer->draft_config ?? []
    ]);
}
```

### 3. Draft vs. Published States
* **Drafting**: When the user makes changes in the sidebar, Vue makes an AJAX request (`POST /builder/draft`) containing the serialized JSON config. The server saves this to `draft_config`. The preview iframe reloads, displaying the new `draft_config`.
* **Publishing**: When the user clicks "Publish", the backend copies `draft_config` to `published_config`. Public users visiting `http://alchemist.localhost:8000` will only see the updated page *after* this step.
