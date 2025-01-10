<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unknown_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('ability')->nullable();
            $table->string('object')->nullable();
            $table->integer('count')->default(1);
            $table->timestamp('last_seen');
            $table->timestamps();

            $table->unique(['ability', 'object']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unknown_permission');
    }
};
