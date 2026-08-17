<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Créer (ou récupérer) l'utilisateur Admin
        $admin = User::firstOrCreate(
            ['email' => 'auxel@digitalindoor.com'], // On cherche par email
            [
                'firstname' => 'Auxel',
                'lastname'  => 'SuperAdmin',
                'phone'     => '0700000000', // Numéro fictif
                'password'  => Hash::make('password'), // Mot de passe par défaut
                'job_title' => 'Administrateur Système',
            ]
        );

        // 2. Lui assigner le rôle SuperAdmin
        $admin->assignRole('SuperAdmin');
    }
}