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
        Schema::create('user_weight_tracking_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('weightunit')->default('lbs');
            $table->date('tracking_start_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_weight_tracking_settings');
    }
};
