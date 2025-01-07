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
        Schema::create('saved_properties_settings_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saved_properties_settings_id');
            $table->foreign('saved_properties_settings_id', 'fk_saved_properties')
                ->references('id')
                ->on('saved_properties_settings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_properties_settings_answers');
    }
};
