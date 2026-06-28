# Project Handover Context - June 28, 2026

This document captures the current implementation state, architecture decisions, and code configuration for the multi-tenant web application.

---

## 1. Project Stack & Architecture Overview
* **Framework:** Laravel 11, Inertia.js (v3), Vue 3, Vite, Tailwind CSS, SQLite.
* **Tenant Isolation Strategy:** Single-database multi-tenancy utilizing wildcard subdomains (`{tenant}.domain.localhost`).
* **Route Mapping Layout:**
  * **Central Domain (`domain.localhost`):** Handles general home landing page, registration, login, and the user's central control dashboard.
  * **Tenant Subdomain (`{tenant}.domain.localhost`):** Handles public-facing sites (`/`) and the live editor canvas (`/editor`).

---

## 2. Key Completed Features

### Dynamic Multi-Tenant Auth & Registration
* **Custom Subdomain Choice:** The registration page ([Register.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/auth/Register.vue)) allows users to choose their preferred custom subdomain under `.domain.localhost` with a dedicated input field.
* **Validation & Blocklist:** Back-end validation in [CentralRegisteredUserController.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Controllers/Auth/CentralRegisteredUserController.php) checks for unique subdomains and blocks reserved names (e.g. `www`, `admin`, `api`, `system`, etc.).
* **Deterministic Starter Hydration:** Once registered, the tenant model is initialized along with a default homepage populated with a boilerplate `HeroBlock` draft configuration.

### Cross-Subdomain Redirections & Session Cookies
* **Local Development Port Handling:** Redirect logic automatically captures the request port (e.g., `:8000`) and preserves it on domain handovers (`Inertia::location`), avoiding standard `404` errors when running the built-in server.
* **Shared Sessions:** Configured cookie sharing across subdomains using:
  ```env
  SESSION_DOMAIN=.domain.localhost
  CENTRAL_DOMAIN=domain.localhost
  ```

### Inertia v3 Background Synchronization
* Swapped out raw `fetch` for Inertia v3's stand-alone **`useHttp`** hook inside [Editor.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/Tenant/Editor.vue).
* Configured `onHttpException` to return `false` so background auto-save failures (like 403 or 422 validations) do not trigger full Inertia redirects or rehydrate/re-render the editor view.
* Enforced a local `400ms` debounce watch loop on canvas layout modifications.

---

## 3. Important Project Files
* **Routing Setup:** [web.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/routes/web.php)
* **Auth Controllers:** 
  * [CentralRegisteredUserController.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Controllers/Auth/CentralRegisteredUserController.php)
  * [CentralAuthenticatedSessionController.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Controllers/Auth/CentralAuthenticatedSessionController.php)
* **Editor Controllers:**
  * [TenantEditorController.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Controllers/TenantEditorController.php)
  * [TenantPageSaveController.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Controllers/TenantPageSaveController.php)
* **Middleware Context:** [IdentifyTenant.php](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/app/Http/Middleware/IdentifyTenant.php)
* **Frontend Pages:**
  * [Register.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/auth/Register.vue)
  * [Editor.vue](file:///c:/Users/Z.BOOK/Desktop/things/code/web-builder/resources/js/pages/Tenant/Editor.vue)

---

## 4. Verification and Running Instructions

### Local Server Launch
1. Ensure the SQLite database exists and is migrated:
   ```powershell
   php artisan migrate
   ```
2. Start the Laravel development server listening on the loopback IP:
   ```powershell
   php artisan serve --host=127.0.0.1 --port=8000
   ```
3. Boot the Vite dev server for hot-reloads:
   ```powershell
   npm run dev
   ```
4. Access the app directly at:
   `http://domain.localhost:8000`

### Automated Tests
Run the entire Pest test suite to verify routing, registration validation, and rate limiters:
```powershell
php artisan test
```
**Current Status:** `40 passed, 0 failed` (100% green).
