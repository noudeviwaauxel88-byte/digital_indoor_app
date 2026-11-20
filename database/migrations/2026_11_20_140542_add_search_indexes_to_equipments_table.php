<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipments')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->index('title', 'equipments_title_index');
                $table->index('type', 'equipments_type_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('equipments')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropIndex('equipments_title_index');
                $table->dropIndex('equipments_type_index');
            });
        }
    }
};