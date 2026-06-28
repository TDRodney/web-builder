<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class CentralAuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): \Inertia\Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): Response
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = md5('login'.Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            abort(429, trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]));
        }

        // Validate credentials before logging in, checking if 2FA is needed
        $provider = Auth::guard('web')->getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Check if user has Two-Factor Authentication enabled
        if (Features::enabled(Features::twoFactorAuthentication())) {
            $hasTwoFactor = false;

            if ($user->two_factor_secret) {
                if (Fortify::confirmsTwoFactorAuthentication()) {
                    if (! is_null($user->two_factor_confirmed_at)) {
                        $hasTwoFactor = true;
                    }
                } else {
                    $hasTwoFactor = true;
                }
            }

            if ($hasTwoFactor) {
                $request->session()->put([
                    'login.id' => $user->getKey(),
                    'login.remember' => $request->boolean('remember'),
                ]);

                event(new TwoFactorAuthenticationChallenged($user));

                return $request->wantsJson()
                    ? response()->json(['two_factor' => true])
                    : redirect()->route('two-factor.login');
            }
        }

        // Proceed with normal login
        Auth::login($user, $request->boolean('remember'));

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $tenant = $user->tenant;

        if ($tenant) {
            $scheme = $request->getScheme();
            $domain = config('app.central_domain', 'domain.localhost');
            $port = $request->getPort();
            $portSuffix = ($port && ! in_array($port, [80, 443])) ? ":{$port}" : '';
            $url = "{$scheme}://{$tenant->subdomain}.{$domain}{$portSuffix}/editor";

            return Inertia::location($url);
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
