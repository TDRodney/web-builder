<?php

namespace App\Http\Controllers;

use App\Models\CommerceTemplate;
use App\Rules\ValidatesCommerceTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CommerceTemplateController extends Controller
{
    public function edit(Request $request, CommerceTemplate $commerceTemplate): Response
    {
        $this->authorizeTemplate($commerceTemplate);

        return Inertia::render('Tenant/CommerceTemplateEditor', [
            'tenant' => app('currentTenant')->only(['id', 'subdomain', 'theme_config']),
            'template' => $commerceTemplate,
            'sectionDefinitions' => config('commerce_sections.definitions'),
            'previewResource' => $request->string('preview')->toString() ?: null,
        ]);
    }

    public function update(Request $request, CommerceTemplate $commerceTemplate): JsonResponse
    {
        $this->authorizeTemplate($commerceTemplate);
        $validated = $request->validate(['draft_config' => ['required', new ValidatesCommerceTemplate]]);
        $commerceTemplate->update(['draft_config' => $validated['draft_config']]);

        return response()->json(['status' => 'success']);
    }

    public function publish(CommerceTemplate $commerceTemplate): RedirectResponse
    {
        $this->authorizeTemplate($commerceTemplate);
        DB::transaction(fn () => $commerceTemplate->update(['published_config' => $commerceTemplate->draft_config]));

        return back()->with('success', 'Commerce template published.');
    }

    private function authorizeTemplate(CommerceTemplate $commerceTemplate): void
    {
        $tenant = app('currentTenant');
        abort_unless(auth()->id() === $tenant->user_id && $commerceTemplate->tenant_id === $tenant->id, 403);
    }
}
