<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('stock_movements', 'other_destination')) {
                $table->string('other_destination')->nullable();
            }
            if (!Schema::hasColumn('stock_movements', 'file_path')) {
                $table->string('file_path')->nullable();
            }
        });

        if (!Schema::hasTable('stock_movement_items')) {
            Schema::create('stock_movement_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_movement_id')->constrained()->onDelete('cascade');
                $table->foreignId('equipment_item_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
    }
};