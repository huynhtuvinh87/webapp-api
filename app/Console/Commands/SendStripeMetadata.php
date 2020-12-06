<?php

namespace App\Console\Commands;

use App\Jobs\SendStripeMetaDeta;
use App\Models\Contractor;
use Illuminate\Console\Command;
use Queue;
use Stripe\Customer;
use Stripe\Stripe;
use App\Notifications\StripeMetadataNotification;
use Illuminate\Notifications\Notification;
use Log;

/**
 * Terminal command to send stripe metadata
 */
class SendStripeMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:send-metadata {--contractor=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends stripe metadata';

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

        Stripe::setApiKey(config('services.stripe.secret'));
        $loopLimit = 500;
        $lastCustomerId = null;
        $stripeCustomerPullLimit = 50;

        $contractorsToUpdate = [];

        if ($contractor = Contractor::find((int) $this->option('contractor'))) {

            SendStripeMetaDeta::dispatchNow($contractor);
            $this->info('Dispatching to stripe now for contractor ' . $contractor->name);

        } else {
            // Init stripe request
            // Getting ALL customers
            do {
                $this->info("Processing $stripeCustomerPullLimit customers after " . $lastCustomerId);

                $customers = Customer::all([
                    'limit' => $stripeCustomerPullLimit,
                    'starting_after' => $lastCustomerId,
                ]);

                // For each customer, check the metadata tag.
                // If the metadata is not set, call the updater
                foreach ($customers['data'] as $customer) {
                    // Checking metadata info
                    if (sizeof($customer['metadata']) == 0) {
                        // Getting Contractor by stripe customer ID
                        $contractor = Contractor::where('stripe_id', $customer['id'])->first();
                        // $this->info("Stripe Customer: " . $customer['id']);

                        if (!is_null($contractor)) {
                            $this->info("Contractor found with no metadata: $contractor->id - $contractor->name");

                            array_push($contractorsToUpdate, $contractor);
                        }
                    }
                }

                $lastCustomerId = $customers['data'][sizeof($customers['data']) - 1]['id'];

                $loopLimit--;
                if ($loopLimit == 0) {
                    $this->warn("Loop limit reached.");
                }

                if(sizeof($customers['data']) != $stripeCustomerPullLimit){
                    $this->warn("Customers returned was less than the requested amount - end of list.");
                }

            } while (
                // Continue if there are still customers
                sizeof($customers['data']) > 0
                // Continue if there are the list of customers is the same size as the pull limit
                 && sizeof($customers['data']) == $stripeCustomerPullLimit
                // End if the loop limit is reached
                 && $loopLimit > 0
            );

            // If there are contractors to update, prompt and run
            if (sizeof($contractorsToUpdate) > 0) {
                $contractorsToUpdateCount = sizeof($contractorsToUpdate);
                $this->info("The following $contractorsToUpdateCount contractors are missing metadata and will be updated:");

                foreach ($contractorsToUpdate as $contractor) {
                    $this->info("$contractor->name ( $contractor->id - https://dashboard.stripe.com/customers/$contractor->stripe_id )");
                }

                // if ($this->confirm('Do you wish to continue?')) {

                    // Queue updater here
                    foreach ($contractorsToUpdate as $contractor) {
                        $this->info("Processing $contractor->name ( https://dashboard.stripe.com/customers/$contractor->stripe_id )");
                        SendStripeMetaDeta::dispatch($contractor);
                    }
                // } else {
                    // $this->warn("Updating metadata cancelled!");
                // }
            } else {
                $this->info("No contractors to process!");
            }

		}

		Log::channel("slack")->info("Contractor Stripe Metadata Updated", [
            "Environment" => env('APP_NAME'),
			"Count" => sizeof($contractorsToUpdate)
		]);

        return;
    }
}
