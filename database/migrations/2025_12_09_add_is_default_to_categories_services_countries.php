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
        Schema::table('categories', function (Blueprint $table) {
            $table->tinyInteger('is_default')->default(0)->after('parent_id');
            $table->index('is_default');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->tinyInteger('is_default')->default(0)->after('price');
            $table->index('is_default');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->tinyInteger('is_default')->default(0)->after('name');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_default']);
            $table->dropColumn('is_default');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['is_default']);
            $table->dropColumn('is_default');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['is_default']);
            $table->dropColumn('is_default');
        });
    }
};

