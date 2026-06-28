<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Extract the route parameter named 'tenant'
        $subdomain = $request->route('tenant');

        if (! $subdomain) {
            abort(404, 'Tenant domain context missing.');
        }

        // Query the indexed column with a rapid firstOrFail check
        // State 2 mitigation: If the subdomain doesn't exist, instantly 404.
        $tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();

        // Bind the resolved tenant model into Laravel's Service Container
        // This makes it globally accessible via app('currentTenant') during this execution cycle
        app()->instance('currentTenant', $tenant);

        // Share the resolved tenant model with all Blade views during this request
        view()->share('tenant', $tenant);

        // Forget the parameter so it doesn't pollute your Controller method arguments
        $request->route()->forgetParameter('tenant');

        return $next($request);
    }
}
