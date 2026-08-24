<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class ConnectedAccountController extends Controller
{
    /**
     * Redirige al usuario al proveedor OAuth para autorizar FlowHub.
     */
    public function redirect(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Recibe el callback del proveedor, intercambia el código de
     * autorización por tokens, y guarda (o actualiza) la conexión.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $socialiteUser = Socialite::driver($provider)->user();

        $request->user()->connectedAccounts()->updateOrCreate(
            ['provider' => $provider],
            [
                'provider_user_id' => $socialiteUser->getId(),
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'expires_at' => $socialiteUser->expiresIn
                    ? now()->addSeconds($socialiteUser->expiresIn)
                    : null,
            ]
        );

        return redirect()->route('dashboard')
            ->with('status', ucfirst($provider).' conectado correctamente.');
    }
}
