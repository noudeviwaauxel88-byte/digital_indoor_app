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
        Schema::table('users', function (Blueprint $table) {
            // Ajoute les nouvelles colonnes
            $table->string('firstname')->after('id');
            $table->string('lastname')->after('firstname');
            $table->string('phone')->nullable()->after('email');
            
            // Supprime l'ancienne colonne 'name'
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rétablit l'ancienne structure si on annule la migration
            $table->dropColumn(['firstname', 'lastname', 'phone']);
            $table->string('name')->after('id');
        });
    }
};
