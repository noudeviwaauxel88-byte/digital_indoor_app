<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipments') && !Schema::hasColumn('equipments', 'equipment_type_id')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->foreignId('equipment_type_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('equipment_types')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('equipments', 'equipment_type_id')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropForeign(['equipment_type_id']);
                $table->dropColumn('equipment_type_id');
            });
        }
    }
};