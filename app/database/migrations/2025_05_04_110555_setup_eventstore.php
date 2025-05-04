<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Sandstorm\EventStore\LaravelAdapter\LaravelEventStore;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $eventStore = new LaravelEventStore(DB::connection(), 'app_game_events');
        $eventStore->setup();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('app_game_events');
    }
};
