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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->ulid('service_id')->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('duration')->default(30);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
