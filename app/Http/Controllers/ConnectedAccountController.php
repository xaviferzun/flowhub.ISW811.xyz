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
}
