<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate a unique subdomain slug based on name
        $subdomain = Str::slug($request->name);
        if (empty($subdomain)) {
            $subdomain = 'user-'.$user->id;
        }

        $originalSubdomain = $subdomain;
        $counter = 1;
        while (Tenant::where('subdomain', $subdomain)->exists()) {
            $subdomain = $originalSubdomain.'-'.$counter;
            $counter++;
        }

        // Create Tenant record
        $tenant = Tenant::create([
            'user_id' => $user->id,
            'subdomain' => $subdomain,
        ]);

        // Create default home page for the tenant
        $tenant->pages()->create([
            'slug' => 'home',
            'draft_config' => [
                [
                    'id' => 'hero-1',
                    'type' => 'HeroBlock',
                    'styles' => ['padding' => 40, 'backgroundColor' => '#ffffff'],
                    'content' => ['headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                ],
            ],
            'published_config' => [
                [
                    'id' => 'hero-1',
                    'type' => 'HeroBlock',
                    'styles' => ['padding' => 40, 'backgroundColor' => '#ffffff'],
                    'content' => ['headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                ],
            ],
        ]);

        Auth::login($user);

        // Redirect to the tenant workspace editor (cross-domain)
        $scheme = $request->getScheme();
        $domain = config('app.central_domain', 'domain.localhost');
        $url = "{$scheme}://{$tenant->subdomain}.{$domain}/editor";

        return Inertia::location($url);
    }
}
