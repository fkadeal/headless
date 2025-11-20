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
        Schema::table('custom_post_types', function (Blueprint $table) {
            $table->json('standard_fields')->nullable()->after('config_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_post_types', function (Blueprint $table) {
            $table->dropColumn('standard_fields');
        });
    }
};
