<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('title')->nullable()->after('slug');
            $table->boolean('is_homepage')->default(false)->after('published_config');
            $table->integer('sort_order')->default(0)->after('is_homepage');
        });

        DB::table('pages')
            ->where('slug', 'home')
            ->update([
                'is_homepage' => true,
                'title' => 'Home',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['title', 'is_homepage', 'sort_order']);
        });
    }
};
