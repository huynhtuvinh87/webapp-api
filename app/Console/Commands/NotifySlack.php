<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

class NotifySlack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // TODO: Add in a param to determine log level
    protected $signature = 'notify:slack {message : Message to be sent to Slack.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message to slack';

    protected $message = "Not defined";

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
        $this->message = $this->argument('message');
		Log::channel("slack")->info($this->message);
    }
}
