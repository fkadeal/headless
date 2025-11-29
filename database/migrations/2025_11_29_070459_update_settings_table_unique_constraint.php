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
        // Drop the existing unique index on the 'key' column
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']); // This removes the unique constraint on 'key'
        });

        // Add a new unique index on key + value->post_type + value->category_id
        // This prevents duplicate configurations for the same key, post_type, and category_id combination
        Schema::table('settings', function (Blueprint $table) {
            $table->unique(['key', 'value->post_type', 'value->category_id'], 'settings_key_posttype_category_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the unique constraint we added
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_posttype_category_unique');
        });

        // Re-add the unique constraint on the 'key' column
        Schema::table('settings', function (Blueprint $table) {
            $table->unique(['key']);
        });
    }
};
