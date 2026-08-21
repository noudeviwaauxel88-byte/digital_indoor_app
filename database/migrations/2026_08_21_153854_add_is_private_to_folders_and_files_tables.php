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
        // Ajout de la colonne is_private sur folders
        if (Schema::hasTable('folders') && !Schema::hasColumn('folders', 'is_private')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->boolean('is_private')->default(false)->after('user_id');
            });
        }

        // Ajout de la colonne is_private sur files
        if (Schema::hasTable('files') && !Schema::hasColumn('files', 'is_private')) {
            Schema::table('files', function (Blueprint $table) {
                $table->boolean('is_private')->default(false)->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('folders', 'is_private')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropColumn('is_private');
            });
        }

        if (Schema::hasColumn('files', 'is_private')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropColumn('is_private');
            });
        }
    }
};