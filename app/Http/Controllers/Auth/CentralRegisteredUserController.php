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
            'site_setup_completed_at' => null,
        ]);

        Auth::login($user);

        $url = route('dashboard', ['tenant' => $tenant->subdomain]);

        return Inertia::location($url);
    }
}
