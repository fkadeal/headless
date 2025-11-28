<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
 
        $query = Post::with(['category', 'author', 'tags']);
           
        
        // Apply filters if present
        $filters = $request->query('filters', []);
        if (!empty($filters)) {
            $query->filter($filters);
        } else {
            // Maintain existing behavior for backward compatibility
            $query->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->category, function ($q, $category) {
                $q->where('category_id', $category);
            })
            ->when($request->published, function ($q) {
                $q->where('is_published', true);
            });
        }

        // Apply sorting if present in filters or as query param
        $sort = $request->query('sort');
        if ($sort) {
            $this->applySorting($query, $sort);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $posts = $query->paginate($request->per_page ?? 15);

        // Capture the authenticated user ID before transformation 
        $user = auth('sanctum')->user();
        $userId = optional($user)->id;
        
        // Add vote count and has_voted status to each post
        $posts->getCollection()->transform(function ($post) use ($userId) {
            $post->vote_count = $post->votes()->count();

            // Default has_voted to false
            $post->has_voted = false;

            // If user is authenticated, update with actual voting status
            if ($userId) {
                $post->has_voted = $post->hasUserVoted($userId);
            }

            return $post;
        });

        return response()->json([
            'success' => true,
            'data' => $posts,
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
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts,slug|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $post = Post::create([
            ...$validator->validated(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post->load(['category', 'author']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load(['category', 'author', 'tags']);

        // Add vote count to the post data
        $post->vote_count = $post->votes()->count();

        // Default has_voted to false
        $post->has_voted = false;

        // If user is authenticated, update with actual voting status
        if (auth()->check()) {
            $post->has_voted = $post->hasUserVoted(auth()->id());
        }

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:posts,slug,' . $post->id,
            'content' => 'sometimes|required|string',
            'excerpt' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $post->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post->load(['category', 'author']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
