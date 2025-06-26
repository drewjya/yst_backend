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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->ulid('branch_id')->unique();
            $table->text('address')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->boolean('is_home_service')->default(false);
            $table->boolean('is_active')->default(true);
            $table->time('open_hour')->nullable();
            $table->time('close_hour')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
