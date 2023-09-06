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
        Schema::create('fitbit_auth', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('code_verifier');
            $table->string('fitbit_user_id')->nullable()->default(null);
            $table->string('authorisation_code')->nullable()->default(null);
            $table->string('access_token')->nullable()->default(null);
            $table->string('refresh_token')->nullable()->default(null);
            $table->json('json')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('fitbit_auth');
    }
};
