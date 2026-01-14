<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Image;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $posts = Post::whereNotNull('thumbnail')->get();

        foreach ($posts as $post) {
            $post->images()->create([
                'path' => $post->thumbnail,
                'order' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $posts = Post::has('images')->get();

        foreach ($posts as $post) {
            $firstImage = $post->images()->orderBy('order')->first();
            if ($firstImage) {
                $post->update(['thumbnail' => $firstImage->path]);
            }
        }

        DB::table('images')->where('imageable_type', 'App\Models\Post')->delete();
    }
};