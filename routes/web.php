<?php

use App\Http\Controllers\Auth\CentralAuthenticatedSessionController;
use App\Http\Controllers\Auth\CentralRegisteredUserController;
use App\Http\Controllers\TenantEditorController;
use App\Http\Controllers\TenantPageSaveController;
use App\Http\Controllers\TenantPublicSiteController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. CENTRAL DOMAIN ROUTES (Dashboard, Landing Page, Billing)
Route::domain(config('app.central_domain', 'domain.localhost'))->group(function () {

    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');

    Route::middleware('guest')->group(function () {
        Route::get('/register', [CentralRegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [CentralRegisteredUserController::class, 'store'])->name('register.store');
        Route::get('/login', [CentralAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [CentralAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::post('/logout', [CentralAuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', function () {
            if (auth()->user()->tenant) {
                $tenant = auth()->user()->tenant;
                $scheme = request()->getScheme();
                $domain = config('app.central_domain', 'domain.localhost');
                $port = request()->getPort();
                $portSuffix = ($port && ! in_array($port, [80, 443])) ? ":{$port}" : '';

                return redirect("{$scheme}://{$tenant->subdomain}.{$domain}{$portSuffix}/dashboard");
            }

            return Inertia::render('CentralDashboard', [
                'tenant' => null,
            ]);
        })->name('central.dashboard');
    });

    require __DIR__.'/settings.php';
});

// 2. TENANT DOMAIN ROUTES (Public Sites & Live Editors)
Route::domain('{tenant}.'.config('app.central_domain', 'domain.localhost'))
    ->middleware([IdentifyTenant::class])
    ->group(function () {

        Route::middleware(['auth'])->group(function () {
            // Tenant Dashboard
            Route::get('/dashboard', function () {
                $tenant = app('currentTenant');

                return Inertia::render('CentralDashboard', [
                    'tenant' => $tenant->only(['id', 'subdomain']),
                ]);
            })->name('dashboard');

            // State 1: Authed Tenant Owner modifying their workspace canvas
            Route::prefix('editor')->group(function () {
                Route::get('/', [TenantEditorController::class, 'edit'])->name('tenant.editor');
                Route::post('/save', [TenantPageSaveController::class, 'store'])->name('tenant.page.save');
                Route::post('/publish', [TenantPageSaveController::class, 'publish'])->name('tenant.page.publish');
            });
        });

        // State 2: Public User visiting the live published site
        Route::get('/{slug?}', [TenantPublicSiteController::class, 'show'])
            ->where('slug', '.*')
            ->name('tenant.page.public');
    });
