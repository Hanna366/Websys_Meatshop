<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tenant:send-lifecycle-alerts')->dailyAt('08:00');
        
        // Auto-sync GitHub releases every hour
        $schedule->job(new \App\Jobs\SyncGitHubReleases())->hourly();
        
        // Also sync every 15 minutes during business hours for faster updates
        $schedule->job(new \App\Jobs\SyncGitHubReleases())->cron('*/15 8-20 * * *'); // Every 15 mins from 8am to 8pm
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
