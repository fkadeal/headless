<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Models\Voting;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class VotingController extends Controller
{
    /**
     * Store a newly created vote in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $validator->validated();
        
        // Check if the post exists
        $post = Post::find($validated['post_id']);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Get user ID if authenticated, otherwise null
        $userId = auth()->check() ? auth()->id() : null;
        
        // Get IP address (prioritizing forwarded/X-Real-IP headers)
        $ipAddress = $request->ip();
        if ($request->header('X-Real-IP')) {
            $ipAddress = $request->header('X-Real-IP');
        } elseif ($request->header('X-Forwarded-For')) {
            $ipAddress = explode(',', $request->header('X-Forwarded-For'))[0];
        }

        // Check if user has already voted (for logged in users)
        if ($userId) {
            $existingVote = Voting::where('user_id', $userId)
                ->where('post_id', $validated['post_id'])
                ->first();
                
            if ($existingVote) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already voted on this post',
                ], Response::HTTP_CONFLICT);
            }
        } else {
            // Check if IP has already voted on this post (for anonymous users)
            $existingVote = Voting::where('ip_address', $ipAddress)
                ->where('post_id', $validated['post_id'])
                ->first();
                
            if ($existingVote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your IP address has already voted on this post',
                ], Response::HTTP_CONFLICT);
            }
        }

        // Create the vote
        $vote = Voting::create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'post_id' => $validated['post_id'],
            'category_id' => $post->category_id, // Associate with post's category
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vote added successfully',
            'data' => $vote->load(['user', 'post', 'category']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display all votes for a specific post.
     */
    public function showByPost($postId): JsonResponse
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $votes = $post->votes()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $votes,
        ]);
    }

    /**
     * Display the vote status for a specific post for the authenticated user/IP.
     */
    public function checkVoteStatus(Request $request, $postId): JsonResponse
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $userId = auth()->check() ? auth()->id() : null;
        
        // Get IP address (prioritizing forwarded/X-Real-IP headers)
        $ipAddress = $request->ip();
        if ($request->header('X-Real-IP')) {
            $ipAddress = $request->header('X-Real-IP');
        } elseif ($request->header('X-Forwarded-For')) {
            $ipAddress = explode(',', $request->header('X-Forwarded-For'))[0];
        }

        $hasVoted = false;
        if ($userId) {
            $hasVoted = $post->votes()->where('user_id', $userId)->exists();
        } else {
            $hasVoted = $post->votes()->where('ip_address', $ipAddress)->exists();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'post_id' => $postId,
                'has_voted' => $hasVoted,
            ],
        ]);
    }

    /**
     * Display total vote count for a specific post.
     */
    public function getVoteCount($postId): JsonResponse
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $voteCount = $post->votes()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'post_id' => $postId,
                'vote_count' => $voteCount,
            ],
        ]);
    }
}