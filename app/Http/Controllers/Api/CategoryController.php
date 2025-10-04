<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with(['parent', 'creator']);

        // Apply filters if present
        $filters = $request->query('filters', []);
        if (!empty($filters)) {
            $query->filter($filters);
        } else {
            // Maintain existing behavior for backward compatibility
            $query->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->parent_id, function ($q, $parentId) {
                $q->where('parent_id', $parentId);
            })
            ->when($request->active, function ($q) {
                $q->where('is_active', true);
            });
        }

        // Apply sorting if present in filters or as query param
        $sort = $request->query('sort');
        if ($sort) {
            $this->applySorting($query, $sort);
        } else {
            $query->orderBy('name');
        }

        $categories = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Apply sorting to the query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $sort
     * @return void
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category = Category::create([
            ...$validator->validated(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category->load(['parent', 'creator']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'creator']);
        
        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->load(['parent', 'creator']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has related posts before deleting
        if ($category->posts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated posts',
            ], Response::HTTP_CONFLICT);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
