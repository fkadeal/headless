<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomPostType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomPostTypeController extends Controller
{
    /**
     * Display a listing of the custom post types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CustomPostType::with('categories');

        // Apply filters if present
        $filters = $request->query('filters', []);
        if (!empty($filters)) {
            $query->filter($filters);
        } else {
            // Maintain existing behavior for backward compatibility
            $query->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('singular_label', 'like', "%{$search}%")
                        ->orWhere('plural_label', 'like', "%{$search}%");
                });
            })
                ->when($request->category, function ($q, $category) {
                    $q->whereHas('categories', function ($sub) use ($category) {
                        $sub->where('categories.id', $category);
                    });
                })
                ->when($request->filled('enabled'), function ($q) use ($request) {
                    $q->where('enabled', $request->boolean('enabled'));
                }, function ($q) {
                    // Default to showing only enabled post types if not specified
                    $q->where('enabled', true);
                });
        }

        // Apply sorting
        $sort = $request->query('sort');
        if ($sort) {
            $this->applySorting($query, $sort);
        } else {
            $query->orderBy('menu_order');
        }

        $postTypes = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $postTypes,
        ]);
    }

    /**
     * Apply sorting to the query.
     */
    protected function applySorting($query, $sort)
    {
        foreach (explode(',', $sort) as $sortItem) {
            $direction = str_starts_with($sortItem, '-') ? 'desc' : 'asc';
            $field = ltrim($sortItem, '-');
            $query->orderBy($field, $direction);
        }
    }

    /**
     * Display the specified custom post type.
     */
    public function show(string $slugOrId): JsonResponse
    {
        $postType = CustomPostType::where(function ($query) use ($slugOrId) {
            $query->where('id', $slugOrId)
                ->orWhere('slug', $slugOrId);
        })
            ->with(['categories'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $postType,
        ]);
    }
}
