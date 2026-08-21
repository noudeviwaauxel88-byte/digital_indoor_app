<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route pour la page d'accueil publique
Route::get('/', function () {
    return view('welcome');
});

// Route temporaire pour forcer le rôle SuperAdmin et vider les caches
Route::get('/fix-superadmin', function () {
    try {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web'
        ]);

        $user = User::where('email', 'noudeviwaauxel88@gmail.com')->first();

        if (!$user) {
            return "Utilisateur introuvable avec cet email.";
        }

        $user->assignRole($role);

        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        return "<h1>SUCCÈS !</h1><p>Le rôle <b>SuperAdmin</b> a été attribué à " . $user->email . ".</p>";
    } catch (\Exception $e) {
        return "Erreur : " . $e->getMessage();
    }
});

// Routes authentifiées
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
    Route::delete('/equipments/stockout/{stockMovement}', [EquipmentController::class, 'destroyStockOut'])->name('equipments.stockout.destroy');
    Route::post('/equipments/stockout/{equipmentOut}/return', [EquipmentController::class, 'returnStockOut'])->name('equipments.stockout.return');
    
    // Calendrier (Events)
    Route::get('/calendrier', [EventController::class, 'index'])->name('calendar.index');
    Route::post('/calendrier', [EventController::class, 'store'])->name('calendar.store');
    Route::put('/calendrier/{event}', [EventController::class, 'update'])->name('calendar.update');
    Route::delete('/calendrier/{event}', [EventController::class, 'destroy'])->name('calendar.destroy');

    // Mon Drive / Fichiers
    Route::get('/fichiers', [FileController::class, 'index'])->name('files.index');
    Route::get('/fichiers/folder/{folder}', [FileController::class, 'show'])->name('folders.show');
    Route::post('/fichiers/upload', [FileController::class, 'storeFile'])->name('files.store');
    Route::post('/fichiers/folder', [FileController::class, 'storeFolder'])->name('folders.store');
    Route::get('/fichiers/download/{file}', [FileController::class, 'download'])->name('files.download');
    Route::delete('/fichiers/delete/{type}/{id}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::patch('/fichiers/rename/{type}/{id}', [FileController::class, 'rename'])->name('files.rename');
    Route::patch('/fichiers/favorite/{type}/{id}', [FileController::class, 'toggleFavorite'])->name('files.favorite');

    // Administration
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');

    // Route temporaire pour exécuter les migrations
Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return "<h1>SUCCÈS !</h1><p>Les migrations ont été exécutées avec succès.</p><pre>" . Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "Erreur lors de la migration : " . $e->getMessage();
    }
});

});

require __DIR__.'/auth.php';