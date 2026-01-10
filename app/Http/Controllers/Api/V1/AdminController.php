<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Trigger the Artisan command to update nominee vote counts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNomineeVoteCounts(Request $request)
    {
        try {
            // You can optionally pass post_type via the request body or query parameters
            // For now, let's assume it's always 'nominee' as per the command's default,
            // or allow overriding it from the request.
            $postType = $request->input('post_type', 'nominee');

            $exitCode = Artisan::call('app:update-nominee-votes', [
                '--post_type' => $postType,
            ]);

            // Get the output of the command
            $output = Artisan::output();

            if ($exitCode === 0) {
                Log::info("Successfully triggered 'app:update-nominee-votes' command. Output: " . $output);
                return response()->json([
                    'message' => 'Nominee vote counts update triggered successfully.',
                    'output' => $output,
                ]);
            } else {
                Log::error("Failed to trigger 'app:update-nominee-votes' command. Exit code: {$exitCode}. Output: " . $output);
                return response()->json([
                    'message' => 'Failed to trigger nominee vote counts update.',
                    'error_output' => $output,
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception when triggering 'app:update-nominee-votes' command: " . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while triggering the nominee vote counts update.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
