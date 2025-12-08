<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voting;
use App\Models\Post;
use App\Models\Settings;
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
        return response()->json([
                    'success' => false,
                    'message' =>   'The Voting has ended . Thank you for your vote.',
                ], Response::HTTP_CONFLICT);

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
                'message' => 'not found',
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

        // Get voting rules from settings based on post type and category
        $votingRule = $this->getVotingRule($post);

        // Check if user has exceeded the maximum actions per post
        if ($votingRule['max_actions_per_post'] > 0) {
            $postVoteCount = $post->votes()->count();
            if ($postVoteCount >= $votingRule['max_actions_per_post']) {
                return response()->json([
                    'success' => false,
                    'message' => $votingRule['max_reached_message'] ?? 'This post has reached the maximum allowed votes.',
                ], Response::HTTP_CONFLICT);
            }
        }

        // Check if user has already voted on this post (for logged in users)
        if ($userId) {
            $userPostVoteCount = Voting::where('user_id', $userId)
                ->where('post_id', $validated['post_id'])
                ->count();

            if ($userPostVoteCount >= $votingRule['max_actions_per_user_per_post']) {
                return response()->json([
                    'success' => false,
                    'message' => $votingRule['already_voted_message'] ?? 'You have already voted on this post',
                ], Response::HTTP_CONFLICT);
            }
        } else {
            // Check if IP has already voted on this post (for anonymous users)
            $ipPostVoteCount = Voting::where('ip_address', $ipAddress)
                ->where('post_id', $validated['post_id'])
                ->count();

            if ($ipPostVoteCount >= $votingRule['max_actions_per_user_per_post']) {
                return response()->json([
                    'success' => false,
                    'message' => $votingRule['already_voted_message'] ?? 'Your IP address has already voted on this post',
                ], Response::HTTP_CONFLICT);
            }
        }

        // Check if user has exceeded the maximum actions per category
        if ($votingRule['max_actions_per_category_per_user'] > 0) {
            if ($userId) {
                $userCategoryVoteCount = Voting::where('user_id', $userId)
                    ->where('category_id', $post->category_id)
                    ->count();

                if ($userCategoryVoteCount >= $votingRule['max_actions_per_category_per_user']) {
                    return response()->json([
                        'success' => false,
                        'message' => $votingRule['max_reached_message'] ?? 'You have reached the maximum allowed votes in this category.',
                    ], Response::HTTP_CONFLICT);
                }
            } else {
                $ipCategoryVoteCount = Voting::where('ip_address', $ipAddress)
                    ->where('category_id', $post->category_id)
                    ->count();

                if ($ipCategoryVoteCount >= $votingRule['max_actions_per_category_per_user']) {
                    return response()->json([
                        'success' => false,
                        'message' => $votingRule['max_reached_message'] ?? 'Your IP address has reached the maximum allowed votes in this category.',
                    ], Response::HTTP_CONFLICT);
                }
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
            'message' => $votingRule['success_message'] ?? 'Vote added successfully',
            'data' => $vote->load(['user', 'post', 'category']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get voting rule based on post type and category
     */
    private function getVotingRule($post)
    {
        // Priority order for matching rules (from most specific to least specific):
        // 1. Specific post_type AND specific category_id
        // 2. Specific post_type AND any category (category_id is null/empty)
        // 3. Any post_type AND specific category_id
        // 4. Global rule (no post_type and no category_id)

        $settings = Settings::where('key', 'voting_rules')->get();

        // First, look for a specific rule for this post type and category (highest priority)
        foreach ($settings as $setting) {
            $value = $setting->value;
            if (
                isset($value['post_type']) && $value['post_type'] === $post->post_type &&
                isset($value['category_id']) && $value['category_id'] == $post->category_id
            ) {
                return array_merge($this->getDefaultVotingRule(), $value);
            }
        }

        // Second, look for a rule for this post type and all categories
        foreach ($settings as $setting) {
            $value = $setting->value;
            if (
                isset($value['post_type']) && $value['post_type'] === $post->post_type &&
                (!isset($value['category_id']) || empty($value['category_id']))
            ) {
                return array_merge($this->getDefaultVotingRule(), $value);
            }
        }

        // Third, look for a rule for this category and all post types
        foreach ($settings as $setting) {
            $value = $setting->value;
            if (
                (!isset($value['post_type']) || empty($value['post_type'])) &&
                isset($value['category_id']) && $value['category_id'] == $post->category_id
            ) {
                return array_merge($this->getDefaultVotingRule(), $value);
            }
        }

        // Finally, check for global rule (no post_type and no category_id)
        foreach ($settings as $setting) {
            $value = $setting->value;
            if (
                (!isset($value['post_type']) || empty($value['post_type'])) &&
                (!isset($value['category_id']) || empty($value['category_id']))
            ) {
                return array_merge($this->getDefaultVotingRule(), $value);
            }
        }

        // Use default rules if no matching rule found
        return $this->getDefaultVotingRule();
    }

    /**
     * Get default voting rule values
     */
    private function getDefaultVotingRule()
    {
        // Default rule - no restrictions, standard messages
        return [
            'max_actions_per_post' => 0, // Unlimited
            'max_actions_per_user_per_post' => 1,
            'max_actions_per_category_per_user' => 0, // Unlimited
            'action_expiration_hours' => 0, // No expiration
            'already_voted_message' => 'You have already voted on this post',
            'max_reached_message' => 'You have reached the maximum allowed votes.',
            'success_message' => 'Vote added successfully',
        ];
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
