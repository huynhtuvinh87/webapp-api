<?php

namespace App\Console;

use App\Console\Commands\BackupDatabase;
use App\Console\Commands\BackupFiles;
use App\Console\Commands\SendStripeMetadata;
use App\Jobs\ALCReports;
use App\Jobs\CleanLockData;
use App\Jobs\NotificationStats;
use App\Jobs\PruneLogTables;
use App\Jobs\QueueContractorPastDue;
use App\Jobs\QueueContractorWarning;
use App\Jobs\QueueContractorWeeklyNotification;
use App\Jobs\QueueEmployeePastDue;
use App\Jobs\QueueEmployeeWeeklyNotification;
use App\Jobs\SendDailyRegistrations;
use App\Jobs\SendWSIBReports;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // NOTE: time is in UTC - need to subtract 5 hours to get actual time it will be sent

        // Scheduling Jobs
        // https://laravel.com/docs/5.8/scheduling#scheduling-queued-jobs

        // Backups
        $schedule->command(BackupDatabase::class)
            ->dailyAt('20:00');
        $schedule->command(BackupFiles::class)
            ->dailyAt('20:15');

        // Stripe Information
        $schedule->command(SendStripeMetadata::class)
            ->dailyAt('19:00');

        // Notifications
        /* Enable Mass Emails to work with AWS SES DEV-797 */
        $schedule->job(new QueueContractorPastDue)
            ->dailyAt('09:00');
        $schedule->job(New QueueEmployeePastDue)
            ->dailyAt('09:30');
        $schedule->job(new QueueContractorWarning)
            ->dailyAt('09:00');
        // Sending Weekly notification on Monday (1)
        $schedule->job(new QueueContractorWeeklyNotification)
            ->weeklyOn(1, '07:00');

        // Sending Weekly Employee notification on Thursdays (DEV-618)
        $schedule->job(new QueueEmployeeWeeklyNotification)
            ->weeklyOn(4, '10:00');

        // Sending Invites Reminder
        // TODO: See DEV-611
        // $schedule->job(new SendInviteReminder)
        //     ->dailyAt('11:00');

        // Statistics
        $schedule->job(new NotificationStats)
            ->dailyAt('23:55');

        // NOTE: Should send at 5PM
        // 10PM UTC = 5PM EST
        $schedule->job(new SendDailyRegistrations)
            ->dailyAt('22:00');

        // 14:00 UTC = 09:00 EST
        // Send on the 15th in Feb, May, Aug, and Nov (Every 3 months)
        $schedule->job(new SendWSIBReports)
            ->cron("0 8 13 FEB,MAY,AUG,NOV *");

        //Clean the table locks every 5 min.
        $schedule->job(new CleanLockData)
            ->everyFiveMinutes();

        // Sending ALC Report
        $schedule->job(new ALCReports)
            ->dailyAt('07:00');

        // Sending second ALC Report
        $schedule->job(new ALCReports)
        ->dailyAt('19:00');

        // Pruning Tables
        $schedule->job(new PruneLogTables)
            ->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function scheduleTimezone()
    {
        return 'America/New_York';
    }
}
