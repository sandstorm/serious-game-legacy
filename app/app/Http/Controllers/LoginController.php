<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Controller for handling user login and authentication for players in the game.
 */
class LoginController extends Controller
{
    // failed attempts per SoSciSurvey ID and IP -> protects a single account against password guessing
    private const MAX_FAILED_ATTEMPTS_PER_ID = 5;
    // failed attempts per IP -> generous, because a whole class usually shares one IP (school network)
    private const MAX_FAILED_ATTEMPTS_PER_IP = 100;
    private const DECAY_SECONDS = 60;

    private AuthManager $auth;

    public function __construct(
        AuthManager $auth,
        private readonly RateLimiter $rateLimiter,
    ) {
        $this->auth = $auth;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function login(Request $request): View
    {
        return view('auth.login');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function authenticate(Request $request): RedirectResponse
    {
        /** @phpstan-ignore staticMethod.dynamicCall */
        $credentials = $request->validate([
            'soscisurveyId' => ['required'],
            'password' => ['required'],
        ]);

        $idKey = 'login:id:' . Str::lower((string) $credentials['soscisurveyId']) . '|' . $request->ip();
        $ipKey = 'login:ip:' . $request->ip();

        foreach ([$idKey => self::MAX_FAILED_ATTEMPTS_PER_ID, $ipKey => self::MAX_FAILED_ATTEMPTS_PER_IP] as $key => $maxAttempts) {
            if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
                return back()->withErrors([
                    'soscisurveyId' => 'Zu viele Anmeldeversuche. Bitte versuche es in ' . $this->rateLimiter->availableIn($key) . ' Sekunden erneut.',
                ])->onlyInput('soscisurveyId');
            }
        }

        /** @phpstan-ignore staticMethod.dynamicCall */
        if ($this->auth->guard('game')->attempt(['email' => $credentials['soscisurveyId'], 'password' => $credentials['password']], true)) {
            // a successful login shows that the previous failed attempts for this ID were legitimate typos.
            // The IP counter is NOT cleared, otherwise any student could reset it with their own account.
            $this->rateLimiter->clear($idKey);
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        $this->rateLimiter->hit($idKey, self::DECAY_SECONDS);
        $this->rateLimiter->hit($ipKey, self::DECAY_SECONDS);

        return back()->withErrors([
            'soscisurveyId' => 'Anmeldung fehlgeschlagen.',
        ])->onlyInput('soscisurveyId');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        /** @phpstan-ignore staticMethod.dynamicCall */
        $this->auth->guard('game')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

}
