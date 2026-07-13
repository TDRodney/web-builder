<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantThemeController extends Controller
{
    private const ALLOWED_HEADING_FONTS = [
        'Instrument Sans', 'Inter', 'Outfit', 'Poppins', 'Roboto', 'Plus Jakarta Sans',
        'Lora', 'Playfair Display', 'Merriweather', 'EB Garamond',
    ];

    private const ALLOWED_BODY_FONTS = [
        'Instrument Sans', 'Inter', 'Outfit', 'Poppins', 'Roboto', 'Plus Jakarta Sans',
        'Lora', 'Playfair Display', 'Merriweather', 'EB Garamond',
    ];

    private const ALLOWED_BORDER_RADIUS = ['0px', '4px', '8px', '16px', '9999px'];

    public function update(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'colors' => 'sometimes|array',
            'colors.primary' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.secondary' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.background' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.text' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'typography' => 'sometimes|array',
            'typography.headingFont' => ['sometimes', 'string', Rule::in(self::ALLOWED_HEADING_FONTS)],
            'typography.bodyFont' => ['sometimes', 'string', Rule::in(self::ALLOWED_BODY_FONTS)],
            'borderRadius' => ['sometimes', 'string', Rule::in(self::ALLOWED_BORDER_RADIUS)],
        ]);

        $existing = $tenant->theme_config ?? [];
        $incoming = array_intersect_key($validated, array_flip(['colors', 'typography', 'borderRadius']));

        $theme = array_merge($existing, $incoming);

        $tenant->update(['theme_config' => $theme]);

        return response()->json([
            'status' => 'success',
            'message' => 'Theme settings saved!',
            'theme_config' => $theme,
        ]);
    }
}
