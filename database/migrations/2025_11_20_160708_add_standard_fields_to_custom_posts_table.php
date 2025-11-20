<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('custom_posts', function (Blueprint $table) {
            // Add fields from the Post model that might be needed
            $table->unsignedBigInteger('category_id')->nullable()->after('slug');
            $table->text('excerpt')->nullable()->after('content');
            $table->string('featured_image')->nullable()->after('content');
            $table->string('thumbnail')->nullable()->after('featured_image');
            $table->json('meta_data')->nullable()->after('thumbnail');

            // Add foreign key constraint for category
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_posts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'excerpt',
                'featured_image',
                'thumbnail',
                'meta_data'
            ]);
        });
    }
};
