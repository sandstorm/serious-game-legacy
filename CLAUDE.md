# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Serious Game Legacy + Moonshot — an educational serious game for financial literacy, built as a Laravel/Filament application with event-sourced game logic. Developed by Sandstorm Media GmbH.

## Commands

All commands use `mise` as the task runner. Docker containers must be running first.

```bash
mise start              # Build JS and start all Docker containers
mise stop               # Stop containers
mise down               # Stop and remove containers + volumes

# Testing (Pest, runs inside Docker)
mise pest               # Run all tests
mise pest tests/Unit --filter="TestName"  # Run specific test
mise pest --watch       # Watch mode

# Static analysis & linting
mise phpstan            # PHPStan (level 8, strict)
mise phpstan --pro      # PHPStan with web UI
mise pint --test        # Check code style (dry run)
mise pint               # Fix code style

# Frontend
mise build-js           # Build JS/CSS assets (yarn + vite)
mise watch-js           # Start Vite dev server with HMR

# Laravel
mise artisan migrate    # Run migrations
mise artisan tinker     # Interactive PHP shell
mise enter-laravel      # Shell into Laravel container
mise enter-db           # Shell into DB container
```

App runs at http://127.0.0.1:8090, admin at http://127.0.0.1:8090/admin.

## Architecture

### Ports & Adapters (Hexagonal)

Strict separation between framework-agnostic domain code and Laravel adapters:

- **`app/src/`** (namespace `Domain\`) — Core domain. **Must never depend on any Laravel class.**
- **`app/app/`** (namespace `App\`) — Laravel adapters, Filament resources, Livewire components.

### Event Sourcing & CQRS

Game state is event-sourced. One event stream per `GameId`. Projections are **in-memory only** (not persisted), recalculated on demand since event streams are short.

```
app/src/CoreGameLogic/
  DrivingPorts/ForCoreGameLogic.php    # Entry point interface
  Features/[Feature]/
    Command/                            # Commands (write side)
    Event/                              # Events (persisted to app_game_events table)
    State/                              # Projections (read side, in-memory)
    [Feature]CommandHandler.php         # Command → Event logic
```

Key distinction: **Event Sourcing events** (`GameEventInterface`) are persistent state. **Laravel Events** (`GameStateUpdated`) are transient WebSocket broadcasts — do not confuse them.

### Real-time UI Flow

Livewire `GameUi` component → dispatches command to `ForCoreGameLogic::handle()` → events persisted → `GameStateUpdated` broadcast via WebSocket → all connected Livewire clients re-render.

### Testing

Use `CoreGameLogicApp::createInMemoryForTesting()` for purely in-memory game logic without database persistence.

## Code Style Rules

- **No Laravel facades or `env()`/`app()` helpers** — use dependency injection only. Enforced via PHPStan (`disallowed-calls.neon`).
- **Eloquent ORM only in Filament** — nowhere else.
- **ULIDs** for all IDs (not UUIDs).
- **BEM for CSS** with `is...` modifier shorthand. CSS variables define the design system in `resources/css/*.css`. Component CSS lives next to components.
- **Filament namespace**: `App\Filament\Admin\` (not default `App\Filament\`), prepared for multiple panels.
- **Permissions**: Custom lightweight `App\Authorization\AppAuthorizer` — no Spatie or Casbin.

## Tech Stack

- PHP 8.4 / Laravel 11 / Filament 3 / FrankenPHP
- Livewire 3 + Alpine.js for frontend
- MariaDB 10.11 / Redis 7
- Laravel Reverb for WebSockets
- Pest for testing, PHPStan level 8, Laravel Pint
- Vite + Tailwind CSS 3
- Docker Compose for local dev, Kubernetes for deployment
