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
        Schema::create('commerce_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('key', 64);
            $table->string('label');
            $table->boolean('is_default')->default(false);
            $table->json('draft_config');
            $table->json('published_config')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'type', 'key']);
            $table->index(['tenant_id', 'type', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerce_templates');
    }
};
