<?php

namespace App\Livewire;

use App\Events\GameStateUpdated;
use Illuminate\Events\Dispatcher;
use Livewire\Attributes\On;
use Livewire\Component;

class GameUi extends Component
{
    public string $playId;
    private Dispatcher $eventDispatcher;

    public function mount()
    {
        /*$this->name = Auth::user()->name;

        $this->email = Auth::user()->email;*/
    }


    public function boot(Dispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function render()
    {
        return view('livewire.game-ui');
    }

    public function triggerGameAction(string $gameAction)
    {
        $this->eventDispatcher->dispatch(new GameStateUpdated($this->playId));
        //dd($gameAction);
    }

    #[On('echo:game.{playId},GameStateUpdated')]
    public function notifyGameStateUpdated()
    {
        dd("JAAA");
    }
}
