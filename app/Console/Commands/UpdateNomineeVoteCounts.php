<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\Voting;
use Illuminate\Support\Str;

class UpdateNomineeVoteCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-nominee-votes {--post_type=nominee : The post type to update vote counts for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates vote counts for nominee posts and stores them in metadata.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $postType = $this->option('post_type');
        $this->info("Starting to update vote counts for posts of type: {$postType}");

        $posts = Post::where('post_type', $postType)->get();

        if ($posts->isEmpty()) {
            $this->info("No posts found of type: {$postType}");
            return Command::SUCCESS;
        }

        $this->withProgressBar($posts, function ($post) {
            $voteCount = Voting::where('post_id', $post->id)->count();

            // Ensure meta_data is an array
            $metaData = $post->meta_data ?? [];
            if (!is_array($metaData)) {
                $metaData = json_decode($metaData, true) ?? [];
            }
            
            $metaData['vote_count'] = $voteCount;
            $post->meta_data = $metaData;
            $post->save();

            $this->comment("Updated post '{$post->title}' (ID: {$post->id}) with {$voteCount} votes.");
        });

        $this->newLine();
        $this->info('Vote count update process completed.');

        return Command::SUCCESS;
    }
}
