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
    Schema::table('settings', function (Blueprint $table) {
        $table->dropUnique(['key']);
    });

    if (Schema::getConnection()->getDriverName() === 'mysql') {
        Schema::table('settings', function (Blueprint $table) {
            $table->unique(
                ['key', 'value->post_type', 'value->category_id'],
                'settings_key_posttype_category_unique'
            );
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    if (Schema::getConnection()->getDriverName() === 'mysql') {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_posttype_category_unique');
        });
    }

    Schema::table('settings', function (Blueprint $table) {
        $table->unique(['key']);
    });
}
};
