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
        Schema::create('custom_post_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Human-readable name (e.g., "Video Post")
            $table->string('slug')->unique(); // URL-friendly slug (e.g., "video")
            $table->string('singular_label'); // Singular label (e.g., "Video")
            $table->string('plural_label'); // Plural label (e.g., "Videos")
            $table->json('config_fields')->nullable(); // Store configuration fields as JSON
            $table->boolean('enabled')->default(true); // Whether this post type is active
            $table->integer('menu_order')->default(0); // For controlling menu order
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_post_types');
    }
};
