<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

//FH-50 Segundo paso del login cuando el usuario tiene 2FA activado. La contraseña ya se valido n AuthenticatedSessionController
class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);

        $google2fa = new Google2FA();

        $valid = $user
            && $user->google2fa_secret
            && $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'El código ingresado no es válido.']);
        }

        $request->session()->forget('2fa_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}