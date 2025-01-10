<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\ApplicationUnavailable;
use Illuminate\Console\Command;

/**
 * Render a "application unavailable" page which can be shown by the upstream (ingress) webserver in case of unavailability.
 *
 * This is a DEVELOPMENT COMMAND, i.e. please use "./dev.sh render-application-unavailable" and check in the result.
 */
class RenderApplicationUnavailable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:render-application-unavailable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DEVELOPMENT COMMAND: Render a "application unavailable" page which can be shown by the upstream webserver in case of unavailability.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo (new ApplicationUnavailable())->render();
    }
}
