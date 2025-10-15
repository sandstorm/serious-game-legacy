<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for handling user login and authentication for players in the game.
 */
class LoginController extends Controller
{
    private AuthManager $auth;

    public function __construct(

        AuthManager $auth,
    )
    {
        $this->auth = $auth;
    }

    public function login(Request $request): View
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        /** @phpstan-ignore staticMethod.dynamicCall */
        $credentials = $request->validate([
            'soscisurveyId' => ['required'],
            'password' => ['required'],
        ]);

        /** @phpstan-ignore staticMethod.dynamicCall */
        if ($this->auth->guard('game')->attempt(['email' => $credentials['soscisurveyId'], 'password' => $credentials['password']], true)) {
            $request->session()->regenerate();

            // TODO redirect to your games
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'soscisurveyId' => 'Anmeldung fehlgeschlagen.',
        ])->onlyInput('soscisurveyId');
    }

}
