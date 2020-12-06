<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;

class GenerateStripeToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:test-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a stripe token as the front-end would';

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
        Stripe::setApiKey(config('services.stripe.key'));

        $token = \Stripe\Token::create(array(
            "card" => array(
                "number" => "4242424242424242",
                "exp_month" => 1,
                "exp_year" => 2021,
                "cvc" => "314"
            )
        ));

        $this->info('Token: '. $token['id']);
    }
}
