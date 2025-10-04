<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::with('creator');

        // Apply filters if present
        $filters = $request->query('filters', []);
        if (!empty($filters)) {
            $query->filter($filters);
        } else {
            // Maintain existing behavior for backward compatibility
            $query->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting if present in filters or as query param
        $sort = $request->query('sort');
        if ($sort) {
            $this->applySorting($query, $sort);
        } else {
            $query->orderBy('name');
        }

        $tags = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $tags,
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
            'slug' => 'required|string|unique:tags,slug|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tag = Tag::create([
            ...$validator->validated(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'data' => $tag->load('creator'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): JsonResponse
    {
        $tag->load('creator');
        
        return response()->json([
            'success' => true,
            'data' => $tag,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:tags,slug,' . $tag->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tag->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully',
            'data' => $tag->load('creator'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        // Check if tag has related posts before deleting
        if ($tag->posts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tag with associated posts',
            ], Response::HTTP_CONFLICT);
        }

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully',
        ]);
    }
}
