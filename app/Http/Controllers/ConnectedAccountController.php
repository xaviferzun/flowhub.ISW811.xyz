<?php

namespace App\Http\Controllers;

use App\Models\ConnectedAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class ConnectedAccountController extends Controller
{
    /**
     * Proveedores soportados y su nombre de despliegue.
     * (Coincide con el whereIn de las rutas /connect/{provider}.)
     */
    private const PROVIDERS = [
        'github' => 'GitHub',
        'discord' => 'Discord',
    ];

    /**
     * Lista las conexiones del usuario, junto con los proveedores
     * disponibles que todavía no ha conectado.
     */
    public function index(Request $request): View
    {
        $connectedAccounts = $request->user()->connectedAccounts()->get()->keyBy('provider');

        return view('connections.index', [
            'providers' => self::PROVIDERS,
            'connectedAccounts' => $connectedAccounts,
        ]);
    }

    /**
     * Redirige al usuario al proveedor OAuth para autorizar FlowHub.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $driver = Socialite::driver($provider);

        //FH-36 GitHub necesita el scope repo para poder crear issues en nombre del usuario (github.create_issue)
        if ($provider === 'github') {
            $driver->scopes(['repo']);
        }

        return $driver->redirect();
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

        return redirect()->route('connections.index')
            ->with('status', ucfirst($provider).' conectado correctamente.');
    }

    /**
     * Revoca (elimina) una conexión existente del usuario autenticado.
     */
    public function destroy(Request $request, ConnectedAccount $connectedAccount): RedirectResponse
    {
        abort_unless($connectedAccount->user_id === $request->user()->id, 403);

        $provider = $connectedAccount->provider;
        $connectedAccount->delete();

        return redirect()->route('connections.index')
            ->with('status', ucfirst($provider).' desconectado correctamente.');
    }

    //FH-35 Redirige a Discord pidiendo el scope webhook.incoming (autoriza publicar en un canal especifico, no login)
    public function redirectWebhook(): RedirectResponse
    {
        return Socialite::driver('discord_webhook')
            ->setScopes(['webhook.incoming'])
            ->redirect();
    }

    //FH-35 Recibe el callback con la URL de webhook ya autorizada por Discord para el canal elegido por el usuario
    public function callbackWebhook(Request $request): RedirectResponse
    {
        $socialiteUser = Socialite::driver('discord_webhook')->user();

        $request->user()->connectedAccounts()->updateOrCreate(
            ['provider' => 'discord_webhook'],
            [
                'provider_user_id' => $socialiteUser->getId(),
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'expires_at' => $socialiteUser->expiresIn
                    ? now()->addSeconds($socialiteUser->expiresIn)
                    : null,
                'webhook_url' => data_get($socialiteUser->accessTokenResponseBody, 'webhook.url'),
            ]
        );

        return redirect()->route('connections.index')
            ->with('status', 'Discord (webhook) conectado correctamente.');
    }
}
