<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Laravel Pulse and the logging of unknown permissions have been removed
        Schema::dropIfExists('pulse_values');
        Schema::dropIfExists('pulse_entries');
        Schema::dropIfExists('pulse_aggregates');
        Schema::dropIfExists('unknown_permissions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // the tables only contained monitoring data, so we do not restore them
    }
};
