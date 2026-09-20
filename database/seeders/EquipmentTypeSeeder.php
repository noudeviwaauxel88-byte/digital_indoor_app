<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentType;

class EquipmentTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Camera',
            'Ordinateur',
            'Serveur',
            'Switch',
            'Routeur',
            'Point d\'accès',
            'Imprimante',
            'Scanner',
            'Écran',
            'Onduleur',
            'Téléphone',
            'Tablette',
            'Stockage',
            'Périphérique',
            'alimentation',
            'Autre',
        ];

        foreach ($types as $type) {
            EquipmentType::firstOrCreate(
                ['name' => $type],
                ['image' => null]
            );
        }
    }
}