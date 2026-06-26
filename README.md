# 🚀 Web Builder & Multi-Tenant Engine

Welcome to **Nexura Web Builder**, a high-performance, single-database multi-tenant web application designed for organizing, managing, and showcasing custom event pages. Using a state-of-the-art tech stack consisting of Laravel 13, Inertia.js v3, Vue 3, and Tailwind CSS v4, Nexura enables tenants to edit, drag, arrange, and preview layout blocks in real-time.

---

## 🏗️ Core Architecture

Nexura is engineered with modularity, low latency, and strict security in mind. The application divides layout management, subdomain isolation, and interactive canvas editing into clean, decoupled layers:

### 1. Single-Database Multi-Tenancy
- **Subdomain Routing**: Incoming requests are intercepted by subdomain-aware middleware. If a user visits `http://alchemist.localhost:8000`, the middleware identifies `alchemist` as the active tenant/organizer.
- **Data Isolation**: Tenant models (pages, templates) are bound via tenant foreign keys (e.g. `tenant_id` or `organizer_id`) and isolated using Global Query Scopes to prevent data leaks.

### 2. Config-Driven Layout Model
Rather than storing layouts in raw HTML tables or generating complex files, page structure is stored as structured JSON configs:
- `draft_config`: Where live edits are synced instantly during block drag-and-drops or properties updates.
- `published_config`: The live layout shown to public visitors.

### 3. Real-Time Workspace Canvas
- **Dynamic Block Registry**: The Vue-based builder loads blocks dynamically from registered components (`HeroBlock.vue`, `FeatureBlock.vue`).
- **Safe Auto-Save Engine**: To ensure 60fps local editing performance without bottlenecking, we implement a debounced, race-condition-safe auto-saver. If another edit triggers before the server responds to the previous save request, the outstanding request is immediately aborted using an `AbortController`.

```
┌────────────────────────────────────────────────────────┐
│                      NEXURA SHELL                      │
├───────────────────────────┬────────────────────────────┤
│                           │                            │
│     VUE.JS WORKSPACE      │      CONTENT INSPECTOR     │
│   (Interactive Canvas)    │    (Real-time settings)    │
│                           │                            │
│  - Drag to Reorder Blocks │  - Padding Controls        │
│  - Instant Render Mode    │  - Text Customization      │
│  - Live Content Injection │  - Style Color Pickers     │
│                           │                            │
└───────────────────────────┴────────────────────────────┘
```

---

## 🛠️ Technology Stack

- **Backend**:
  - **PHP 8.4** & **Laravel 13**
  - **Inertia.js v3 (Inertia Laravel)** - Seamless backend routing without SPA complexity
  - **Laravel Fortify** - Secure authentication scaffolding
  - **Laravel Wayfinder** - Dynamic TypeScript client-side route generation

- **Frontend**:
  - **Vue 3** (Composition API script setup)
  - **Inertia.js v3 (Inertia Vue)**
  - **Tailwind CSS v4** - Fast and utility-first styling compilation
  - **vuedraggable** - Smooth drag-and-drop block reordering
  - **TypeScript** - Strongly-typed UI configuration

- **Tooling & Formatting**:
  - **Pest PHP 4** - Modern test runner
  - **Laravel Pint** - Automated PHP code styling
  - **ESLint v9 & Prettier v3** - JavaScript/TypeScript code formatters
  - **Larastan/PHPStan v3** - Static analysis for PHP type safety

---

## 📁 Key File Locations

- **Middleware**: [IdentifyTenant.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Middleware/IdentifyTenant.php) parses the subdomain to set the active tenant scope.
- **Editor UI**: [Editor.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/Tenant/Editor.vue) defines the main editing canvas and drag-and-drop mechanics.
- **Canvas Blocks**:
  - [HeroBlock.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/HeroBlock.vue) - Responsive heading block with custom background colors and paddings.
  - [FeatureBlock.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/components/BuilderBlocks/FeatureBlock.vue) - Column layouts detailing specific highlights or features.
- **Routes**:
  - `routes/web.php` - General landing, authentication, and admin routes.
  - `routes/tenant.php` - Custom tenant subdomain routes.

---

## 🚀 Getting Started

### Prerequisites
Make sure you have the following installed locally:
- PHP >= 8.4 (with sqlite/mysql/postgres support)
- Composer
- Node.js & npm

### Setup
1. **Initialize Project Assets and Dependencies**:
   You can easily bootstrap the environment by running the composer setup script:
   ```bash
   composer run setup
   ```
   This command installs composer packages, generates the app key, runs migrations, installs npm packages, and builds the assets.

2. **Run the Development Servers**:
   To boot up the Laravel backend, Vite asset hot-reloader, and the queue listener concurrently:
   ```bash
   composer run dev
   ```

3. **Running Quality Checks & Tests**:
   - Run PHP unit/feature tests: `composer run test`
   - Run JavaScript linters/formatters: `npm run lint` & `npm run format`
   - Run PHP Pint: `composer run lint`
