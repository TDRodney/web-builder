<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CentralRegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('auth/Register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'unique:tenants,subdomain',
                'regex:/^[a-z0-9-]+$/i',
                function ($attribute, $value, $fail) {
                    $reserved = ['www', 'admin', 'api', 'mail', 'blog', 'domain', 'central', 'app', 'webmaster', 'host', 'system', 'editor'];
                    if (in_array(strtolower($value), $reserved)) {
                        $fail('This subdomain is reserved and cannot be used.');
                    }
                },
            ],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create Tenant record
        $tenant = Tenant::create([
            'user_id' => $user->id,
            'subdomain' => strtolower($request->subdomain),
        ]);

        // Create default home page for the tenant
        $tenant->pages()->create([
            'slug' => 'home',
            'title' => 'Home',
            'is_homepage' => true,
            'draft_config' => [
                [
                    'id' => 'hero-1',
                    'type' => 'HeroBlock',
                    'props' => [
                        'padding' => 40,
                        'backgroundColor' => '#ffffff',
                        'headline' => 'Welcome to your Site',
                        'subheadline' => 'Built with our engine.',
                    ],
                    'children' => [],
                ],
            ],
            'published_config' => [
                [
                    'id' => 'hero-1',
                    'type' => 'HeroBlock',
                    'props' => [
                        'padding' => 40,
                        'backgroundColor' => '#ffffff',
                        'headline' => 'Welcome to your Site',
                        'subheadline' => 'Built with our engine.',
                    ],
                    'children' => [],
                ],
            ],
        ]);

        Auth::login($user);

        // Redirect to the tenant workspace editor (cross-domain)
        $scheme = $request->getScheme();
        $domain = config('app.central_domain', 'domain.localhost');
        $port = $request->getPort();
        $portSuffix = ($port && ! in_array($port, [80, 443])) ? ":{$port}" : '';
        $url = "{$scheme}://{$tenant->subdomain}.{$domain}{$portSuffix}/editor";

        return Inertia::location($url);
    }
}
