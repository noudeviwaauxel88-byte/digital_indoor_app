<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('equipments', function (Blueprint $table) {
            $table->index('title');
            $table->index('brand');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('equipments', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['brand']);
            $table->dropIndex(['type']);
        });
    }
};