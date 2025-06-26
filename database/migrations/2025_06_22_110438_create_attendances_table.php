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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // Reference the primary BIGINT id of therapists
            $table->foreignId('therapist_id')->constrained('therapists')->onDelete('cascade');

            $table->date('date');
            $table->timestampTz('check_in')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestampTz('check_out')->nullable();

            $table->unique(['therapist_id', 'date']); // One record per therapist per day
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
