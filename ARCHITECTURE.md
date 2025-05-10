# ARCHITECTURE - Serious Game Legacy + Moonshot

Entwickelt als Open Source von Sandstorm Media GmbH.

[README](README.md) [CODE_STYLE](CODE_STYLE.md) [ARCHITECTURE](ARCHITECTURE.md)

<!-- TOC -->
* [ARCHITECTURE - Serious Game Legacy + Moonshot](#architecture---serious-game-legacy--moonshot)
* [File and Package Structure](#file-and-package-structure)
* [Event Sourcing & CQRS](#event-sourcing--cqrs)
* [Laravel UI: Livewire + Laravel Broadcast / Echo](#laravel-ui-livewire--laravel-broadcast--echo)
* [Testing: via commands](#testing-via-commands)
<!-- TOC -->

# File and Package Structure

We use Ports&Adapters (see [CODE_STYLE](CODE_STYLE.md)) as the general architecture, to
not bind ourselves too heavily to Laravel, but instead have a framework-agnostic and type-safe
game core without too much magic.

**File Structure:**

```
app/ - base directory of the application
  app/ - Laravel & Filament specific classes end up here
  ... all other Laravel default folders ...
  
  DistributionPackages/ - standalone PHP or laravel packages developed in conjunction with this project, but usable standalone
  
  src/                 non-laravel-specific Core Domain
    CoreGameLogic/               the core game logic (event sourced)
      DrivingPorts/              (API)
        ForCoreGameLogic.php     (API) ENTRY POINT for the Laravel application (Interface)
      DrivenPorts/
      Features/                  game-feature implementations, by "game area"
        [Feature Name]/
          Command/               Write Side: all commands for this feature; implementing CommandInterface
          Event/                 Persistence: all events for this feature (persisted to Event Store),
                                              implementing GameEventInterface
          State/                 Read side: extracting the current game state from the Events
          [Feature Name]CommandHandler.php
                                 Write Side Implementation (Command -> Event) 
```

# Event Sourcing & CQRS

We use Event Sourcing and CQRS for storing the core game state. If you need an intro, [see this blog post](https://sandstorm.de/blog/posts/event-sourcing-and-cqrs/).

Details in the way we use event sourcing / CQRS in this project:

- **Projections are only done on-demand, in memory, NOT persisted to database.**
  - This is OK because the length of an event stream (=one gameplay) is relatively limited; thus
    we can always calculate the current state on demand.
- We use one Event Stream per `GameId` (=one gameplay). (this part is pretty obvious).
- To trigger some game action, **Commands** are dispatched to `ForCoreGameLogic::handle()`.
  - the corresponding command handler is triggered. It gets the current game state (=all game events so far
    as list of `GameEvents`) to decide whether we can progress.
  - It returns `GameEventsToPersist` which are added to the event store. Note: We enforce that nobody else did
    intermediate events; so the system is **Strongly Consistent** per GameId.
  - These events are persisted to `app_game_events`.
- Do not confuse **Event Sourcing** with the **Laravel Events** System:
  - Event Sourcing (`GameEventInterface`) is storing **persistent state**.
  - Laravel Events (`Illuminate\Events\Dispatcher` etc) are for temporary (transient) notifications etc of currently-
    connected people/players/... They are NOT persisted.


# Laravel UI: Livewire + Laravel Broadcast / Echo

The synchronous UI works as follows:

- Livewire GameUi
  - trigger commands (CoreGameLogic::handle) -> Game State is updated
  - Then, via Laravel Events, `GameStateUpdated` is **Broadcasted** via WebSockets to all connected clients
    currently in this game session.
  - GameUi (the livewire component) also listens to `GameStateUpdated` -> so the Livewire component re-renders with the new state

# Testing: via commands

- we use commands to create desired state.
- `CoreGameLogicApp::createInMemoryForTesting()` creates a purely in-memory game logic, without persistence to database.
  Helpful for testing :)
