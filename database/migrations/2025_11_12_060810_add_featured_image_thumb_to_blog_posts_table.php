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
        Schema::connection('central')->table('blog_posts', function (Blueprint $table) {
            $table->string('featured_image_thumb')->nullable()->after('featured_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('featured_image_thumb');
        });
    }
};
