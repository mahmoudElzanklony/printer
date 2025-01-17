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
        Schema::create('properties_icons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('label');
            $table->string('icon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties_icons');
    }
};
