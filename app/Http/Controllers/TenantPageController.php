<?php

namespace App\Http\Controllers;

use App\Actions\Designs\CloneBlockTree;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $pages = $tenant->pages()
            ->select(['id', 'slug', 'title', 'is_homepage', 'sort_order'])
            ->orderBy('sort_order')
            ->get();

        return response()->json($pages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages')->where('tenant_id', $tenant->id),
            ],
            'layout_key' => ['sometimes', 'string', Rule::in(array_keys(config('designs.page_layouts')))],
        ]);

        $pageLayouts = config('designs.page_layouts', []);
        $layoutKey = $validated['layout_key'] ?? null;
        $hasValidLayout = $layoutKey !== null && isset($pageLayouts[$layoutKey]);

        $draftConfig = $hasValidLayout
            ? CloneBlockTree::handle($pageLayouts[$layoutKey]['blocks'])
            : [
                [
                    'id' => 'hero-'.time(),
                    'type' => 'HeroBlock',
                    'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Welcome to '.$validated['title'], 'subheadline' => 'Built with our engine.'],
                    'children' => [],
                ],
            ];

        $page = $tenant->pages()->create([
            'title' => $validated['title'],
            'slug' => strtolower($validated['slug']),
            'is_homepage' => false,
            'sort_order' => $tenant->pages()->count(),
            'draft_config' => $draftConfig,
        ]);
        $tenant->markSiteSetupCompleted();

        return response()->json([
            'status' => 'success',
            'message' => 'Page created successfully.',
            'page' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($page->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages')->where('tenant_id', $tenant->id)->ignore($page->id),
            ],
            'is_homepage' => ['sometimes', 'required', 'boolean'],
            'sort_order' => ['sometimes', 'required', 'integer'],
        ]);

        if (isset($validated['is_homepage']) && $validated['is_homepage'] === true) {
            $tenant->pages()->where('is_homepage', true)->update(['is_homepage' => false]);
        }

        $page->update($validated);
        $tenant->markSiteSetupCompleted();

        return response()->json([
            'status' => 'success',
            'message' => 'Page updated successfully.',
            'page' => $page,
        ]);
    }

    /**
     * Toggle a page's public visibility (list / unlist) without deleting it.
     */
    public function updateVisibility(Request $request, Page $page): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($page->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // The homepage is the public entry point and must stay listed.
        if ($page->is_homepage) {
            return response()->json([
                'error' => 'The homepage cannot be unlisted.',
            ], 422);
        }

        $validated = $request->validate([
            'is_published' => ['required', 'boolean'],
        ]);

        $page->update(['is_published' => $validated['is_published']]);
        $tenant->markSiteSetupCompleted();

        return response()->json([
            'status' => 'success',
            'message' => $validated['is_published']
                ? 'Page is now live.'
                : 'Page has been unlisted.',
            'page' => $page,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($page->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($page->is_homepage) {
            return response()->json([
                'error' => 'Cannot delete the homepage.',
            ], 422);
        }

        $page->delete();
        $tenant->markSiteSetupCompleted();

        return response()->json([
            'status' => 'success',
            'message' => 'Page deleted successfully.',
        ]);
    }
}
