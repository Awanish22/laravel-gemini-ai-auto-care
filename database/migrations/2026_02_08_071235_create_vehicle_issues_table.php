<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_issues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('vehicle_make');
            $table->string('vehicle_model');
            $table->integer('vehicle_year');
            $table->string('odometer_reading')->nullable();
            $table->text('ai_analysis')->nullable();
            $table->json('ai_recommendations')->nullable();
            $table->string('severity_level')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_issues');
    }
};