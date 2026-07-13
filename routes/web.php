<?php

use App\Http\Controllers\Auth\CentralAuthenticatedSessionController;
use App\Http\Controllers\Auth\CentralRegisteredUserController;
use App\Http\Controllers\TenantContactController;
use App\Http\Controllers\TenantEditorController;
use App\Http\Controllers\TenantMediaController;
use App\Http\Controllers\TenantNavigationController;
use App\Http\Controllers\TenantPageController;
use App\Http\Controllers\TenantPageSaveController;
use App\Http\Controllers\TenantPublicSiteController;
use App\Http\Controllers\TenantThemeController;
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
    ->where(['tenant' => '^[a-z0-9-]+$'])
    ->middleware([IdentifyTenant::class])
    ->group(function () {

        Route::middleware(['auth'])->group(function () {
            // Tenant Dashboard
            Route::get('/dashboard', function () {
                $tenant = app('currentTenant');

                return Inertia::render('CentralDashboard', [
                    'tenant' => $tenant->only(['id', 'subdomain']),
                    'theme_config' => $tenant->theme_config,
                ]);
            })->name('dashboard');

            // Theme Settings
            Route::patch('/theme', [TenantThemeController::class, 'update'])->name('tenant.theme.update');

            // State 1: Authed Tenant Owner modifying their workspace canvas
            Route::prefix('editor')->group(function () {
                Route::get('/', [TenantEditorController::class, 'edit'])->name('tenant.editor');
                Route::post('/save', [TenantPageSaveController::class, 'store'])->name('tenant.page.save');
                Route::post('/publish', [TenantPageSaveController::class, 'publish'])->name('tenant.page.publish');

                Route::get('/pages', [TenantPageController::class, 'index'])->name('tenant.pages.index');
                Route::post('/pages', [TenantPageController::class, 'store'])->name('tenant.pages.store');
                Route::patch('/pages/{page}', [TenantPageController::class, 'update'])->name('tenant.pages.update');
                Route::delete('/pages/{page}', [TenantPageController::class, 'destroy'])->name('tenant.pages.destroy');

                Route::get('/media', [TenantMediaController::class, 'index'])->name('tenant.media.index');
                Route::post('/media', [TenantMediaController::class, 'store'])->name('tenant.media.store');
                Route::delete('/media/{media}', [TenantMediaController::class, 'destroy'])->name('tenant.media.destroy');

                Route::patch('/navigation', [TenantNavigationController::class, 'update'])->name('tenant.navigation.update');
            });
        });

        // Contact Form Submission
        Route::post('/contact', [TenantContactController::class, 'store'])->name('tenant.contact.store');

        // State 2: Public User visiting the live published site
        Route::get('/{slug?}', [TenantPublicSiteController::class, 'show'])
            ->where('slug', '.*')
            ->name('tenant.page.public');
    });
