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
        Schema::create('happy_hour_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('happy_hour_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_service_id')->constrained('branch_service')->onDelete('cascade');
            $table->decimal('promo_price', 10, 2);
            $table->timestamps();

            $table->unique(['happy_hour_id', 'branch_service_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('happy_hour_services');
    }
};
