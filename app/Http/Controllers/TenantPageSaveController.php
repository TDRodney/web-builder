<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Rules\ValidatesBlockSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantPageSaveController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');

        // 1. Strict Controller Authorization Check
        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 2. Strict Input Validation
        $validated = $request->validate([
            'page_id' => 'required|integer',
            'draft_config' => ['required', 'array', new ValidatesBlockSchema],
        ]);

        /**
         * Because Page has the Global TenantScope applied, the query compiled will be:
         * SELECT * FROM pages WHERE id = ? AND tenant_id = ?
         * * If a user passes a page_id belonging to another tenant,
         * this will throw a 404 ModelNotFoundException, completely blocking the request.
         */
        $page = Page::findOrFail($validated['page_id']);

        // Overwrite the JSON payload seamlessly
        $page->update([
            'draft_config' => $validated['draft_config'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'Draft saved safely.']);
    }

    public function publish(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');
        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate(['page_id' => 'required|integer']);

        // Auto-scoped via TenantScope
        $page = Page::findOrFail($validated['page_id']);

        DB::transaction(function () use ($page) {
            $page->refresh(); // Lock to latest state snapshot
            $page->published_config = $page->draft_config;
            $page->save();
        });

        return response()->json(['status' => 'success', 'message' => 'Site published successfully!']);
    }
}
