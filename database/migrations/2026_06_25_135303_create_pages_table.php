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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('slug')->default('home');
            
            // Layout payloads stored as native JSON text
            $table->json('draft_config')->nullable();
            $table->json('published_config')->nullable();
            
            $table->timestamps();

            // A tenant cannot have duplicate slugs (e.g., two 'about' pages)
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
