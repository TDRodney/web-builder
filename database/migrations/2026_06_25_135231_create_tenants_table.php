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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            // Ensure one user can only ever own one tenant template space in this architecture
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            // Index is vital for rapid domain matching inside middleware
            $table->string('subdomain')->unique()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
