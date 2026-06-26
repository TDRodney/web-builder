<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;



// 1. CENTRAL DOMAIN ROUTES (Dashboard, Landing Page, Billing)
Route::domain(config('app.central_domain', 'domain.localhost'))->group(function () {
    
    Route::get('/', function () {
        return view('welcome'); // TODO : Landing page on main domain - nexura.com/editor
    });

    // Central Auth & Management Dashboard (via Breeze)
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('CentralDashboard'); // TODO: anything dashboard view goes here.
        })->name('dashboard'); // make CentralDashboard on vue pages
    });
});

// 2. TENANT DOMAIN ROUTES (Public Sites & Live Editors)
// We capture the dynamic {tenant} subdomain string directly via Laravel routing
Route::domain('{tenant}.' . config('app.central_domain', 'domain.localhost'))
    ->middleware([IdentifyTenant::class])
    ->group(function () {

        // State 2: Public User visiting the live published site
        Route::get('/', [TenantPageController::class, 'show'])->name('tenant.page.public');

        // State 1: Authed Tenant Owner modifying their workspace canvas
        Route::middleware(['auth'])->prefix('editor')->group(function () {
            Route::get('/', [TenantEditorController::class, 'edit'])->name('tenant.editor');
        });
});