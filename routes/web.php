<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FileController; // Contrôleur pour les fichiers
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route pour la page d'accueil publique
Route::get('/', function () {
    return view('welcome');
});

// Routes qui nécessitent que l'utilisateur soit authentifié
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projets
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    
    // Tâches
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Équipements (Stock)
    Route::get('/equipments', [EquipmentController::class, 'index'])->name('equipments.index');
    Route::post('/equipments', [EquipmentController::class, 'store'])->name('equipments.store');
    Route::get('/equipments/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipments.edit');
    Route::patch('/equipments/{equipment}', [EquipmentController::class, 'update'])->name('equipments.update');
    Route::delete('/equipments/{equipment}', [EquipmentController::class, 'destroy'])->name('equipments.destroy');
    Route::get('/equipments/{equipment}/stockout', [EquipmentController::class, 'createStockOut'])->name('equipments.stockout.create');
    Route::post('/equipments/{equipment}/stockout', [EquipmentController::class, 'storeStockOut'])->name('equipments.stockout.store');
    Route::get('/equipments/stockout/history', [EquipmentController::class, 'stockOutHistory'])->name('equipments.stockout.history');
    Route::delete('/equipments/stockout/{stockMovement}', [App\Http\Controllers\EquipmentController::class, 'destroyStockOut'])
    ->name('equipments.stockout.destroy');
    
    // Calendrier (Events)
    Route::get('/calendrier', [EventController::class, 'index'])->name('calendar.index');
    Route::post('/calendrier', [EventController::class, 'store'])->name('calendar.store');
    Route::put('/calendrier/{event}', [EventController::class, 'update'])->name('calendar.update');
    Route::delete('/calendrier/{event}', [EventController::class, 'destroy'])->name('calendar.destroy');

    // ==========================================
    // == GESTION DES FICHIERS (MON DRIVE) ==
    // ==========================================
    
    // Affichage principal et navigation
    Route::get('/fichiers', [FileController::class, 'index'])->name('files.index');
    Route::get('/fichiers/folder/{folder}', [FileController::class, 'show'])->name('folders.show');
    
    // Création (Upload et Dossier)
    Route::post('/fichiers/upload', [FileController::class, 'storeFile'])->name('files.store');
    Route::post('/fichiers/folder', [FileController::class, 'storeFolder'])->name('folders.store');
    
    // Actions sur les fichiers/dossiers
    Route::get('/fichiers/download/{file}', [FileController::class, 'download'])->name('files.download');
    Route::delete('/fichiers/delete/{type}/{id}', [FileController::class, 'destroy'])->name('files.destroy'); // type: 'file' ou 'folder'
    Route::patch('/fichiers/rename/{type}/{id}', [FileController::class, 'rename'])->name('files.rename');
    Route::patch('/fichiers/favorite/{type}/{id}', [FileController::class, 'toggleFavorite'])->name('files.favorite');

    // GESTION DES UTILISATEURS (ADMIN)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // ...

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');

});

// Route temporaire pour vous passer en SUPERADMIN et débloquer le menu
Route::get('/setup-superadmin', function () {
    $user = \App\Models\User::where('email', 'noudeviwaauxel88@gmail.com')->first();

    if (!$user) {
        return "Utilisateur introuvable.";
    }

    // 1. Mise à jour de toutes les colonnes/attributs de rôle possibles dans la table 'users'
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $user->role = 'superadmin'; // ou 'Superadmin', 'SUPERADMIN'
    }
    
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_admin')) {
        $user->is_admin = true;
    }

    $user->save();

    // 2. Attribution via Spatie Permission si le package est configuré
    if (class_exists(\Spatie\Permission\Models\Role::class)) {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $roles = ['superadmin', 'Superadmin', 'SUPERADMIN', 'Super Admin', 'admin'];
        foreach ($roles as $r) {
            $roleObj = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
            $user->assignRole($roleObj);
        }
    }

    // 3. Vidage complet du cache Laravel
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');

    return "
        <h2> Configuration réussie pour {$user->email} !</h2>
        <p><b>Attributs de l'utilisateur :</b> " . json_encode($user->toArray()) . "</p>
        <p>Veuillez vous déconnecter puis vous reconnecter sur l'application.</p>
    ";
});

// Route temporaire pour vous attribuer la casse exacte du rôle SuperAdmin
Route::get('/fix-superadmin', function () {
    // 1. Réinitialiser le cache de Spatie
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // 2. Créer le rôle exact 'SuperAdmin' (avec S et A majuscules, sans espace)
    $role = \Spatie\Permission\Models\Role::firstOrCreate([
        'name' => 'SuperAdmin',
        'guard_name' => 'web'
    ]);

    // 3. Récupérer votre compte
    $user = \App\Models\User::where('email', 'noudeviwaauxel88@gmail.com')->first();

    if (!$user) {
        return "Utilisateur introuvable avec cet email.";
    }

    // 4. Lui attribuer ce rôle
    $user->assignRole($role);

    // 5. Nettoyer le cache système
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');

    return "<h1>SUCCÈS !</h1><p>Le rôle <b>SuperAdmin</b> a été attribué à " . $user->email . ".</p><p>Déconnectez-vous puis reconnectez-vous sur l'application.</p>";
});

require __DIR__.'/auth.php';