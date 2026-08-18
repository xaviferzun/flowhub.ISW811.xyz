<?php

use App\Http\Controllers\AutomationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/automations', [AutomationController::class, 'index'])->name('automations.index');
    Route::get('/automations/create', [AutomationController::class, 'create'])->name('automations.create');
    Route::post('/automations', [AutomationController::class, 'store'])->name('automations.store');

    // TODO (FH-XX): rutas de conexiones OAuth (conectar / listar / revocar proveedores)
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';