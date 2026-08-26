<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    //FH-50 Muestra el estado de 2FA. Si no esta activado, genera un secreto pendiente de confirmar y muestra el qr para escanear con google Authenticator.
    public function show(Request $request): View
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        $qrCodeSvg = null;

        if (! $user->google2fa_enabled) {
            if (! $user->google2fa_secret) {
                $user->google2fa_secret = $google2fa->generateSecretKey();
                $user->save();
            }

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $user->google2fa_secret
            );

            $writer = new \BaconQrCode\Writer(
                new \BaconQrCode\Renderer\ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                )
            );

            $qrCodeSvg = base64_encode($writer->writeString($qrCodeUrl));
        }

        return view('two-factor.show', [
            'enabled' => $user->google2fa_enabled,
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->google2fa_enabled ? null : $user->google2fa_secret,
        ]);
    }

    //FH-50 Confirma la activacion - el usuario debe ingresar un codigo valido generado por su app
    //de autenticacion, probando que efectivamente escaneo el QR y tiene el secreto correcto.
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();

        $valid = $user->google2fa_secret
            && $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'El código ingresado no es válido.']);
        }

        $user->update(['google2fa_enabled' => true]);

        return redirect()->route('two-factor.show')->with('status', '2FA activado correctamente.');
    }

    //FH-50 Desactiva 2FA y borra el secreto (para que, si se reactiva despues, se genere uno nuevo)
    public function disable(Request $request): RedirectResponse
    {
        $request->user()->update([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
        ]);

        return redirect()->route('two-factor.show')->with('status', '2FA desactivado correctamente.');
    }
}