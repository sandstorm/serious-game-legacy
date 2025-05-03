<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GamePlayController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $playId)
    {
        return view('game-play', [
            'playId' => $playId,
        ]);
    }
}
