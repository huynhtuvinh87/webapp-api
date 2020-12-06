<?php

namespace App\Console\Commands;

use App\Notifications\SlackErrorNotification;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification;
use Exception;

class WorkHorse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Just a command to do things';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    }
}
