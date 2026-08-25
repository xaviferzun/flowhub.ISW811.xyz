<?php

use App\Http\Controllers\AutomationController;
use App\Http\Controllers\ConnectedAccountController;
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
    Route::patch('/automations/{automation}/toggle', [AutomationController::class, 'toggle'])->name('automations.toggle');
    Route::delete('/automations/{automation}', [AutomationController::class, 'destroy'])->name('automations.destroy');

    Route::get('/connections', [ConnectedAccountController::class, 'index'])->name('connections.index');
    Route::delete('/connections/{connectedAccount}', [ConnectedAccountController::class, 'destroy'])->name('connections.destroy');

    Route::get('/connect/{provider}', [ConnectedAccountController::class, 'redirect'])
        ->whereIn('provider', ['github', 'discord'])
        ->name('connect.redirect');

    Route::get('/connect/{provider}/callback', [ConnectedAccountController::class, 'callback'])
        ->whereIn('provider', ['github', 'discord'])
        ->name('connect.callback');

    //FH-35 Flujo OAuth separado: autoriza webhook.incoming para publicar mensajes en un canal de Discord
    Route::get('/connect/discord-webhook', [ConnectedAccountController::class, 'redirectWebhook'])
        ->name('connect.discord-webhook.redirect');

    Route::get('/connect/discord-webhook/callback', [ConnectedAccountController::class, 'callbackWebhook'])
        ->name('connect.discord-webhook.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';