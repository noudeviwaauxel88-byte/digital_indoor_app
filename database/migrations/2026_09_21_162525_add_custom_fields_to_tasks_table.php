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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'structure')) {
                $table->string('structure')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('tasks', 'module')) {
                $table->string('module')->nullable()->after('structure');
            }
            if (!Schema::hasColumn('tasks', 'document_path')) {
                $table->string('document_path')->nullable()->after('module');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['structure', 'module', 'document_path']);
        });
    }
};