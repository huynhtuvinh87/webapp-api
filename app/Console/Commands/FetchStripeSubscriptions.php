<?php

namespace App\Console\Commands;

use App\Jobs\GetContractorSubscription;
use App\Models\Contractor;
use Illuminate\Console\Command;

class FetchStripeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-subs {--key=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        Contractor::chunk(1, function($results){

            GetContractorSubscription::dispatch($results[0]);

        });
    }
}
