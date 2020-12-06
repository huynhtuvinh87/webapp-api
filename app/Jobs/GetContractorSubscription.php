<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Stripe;

/**
 * Find contractor's subscription by customr ID and create entry in subscriptions table
 *
 * Class GetContractorSubscription
 * @package App\Jobs
 */
class GetContractorSubscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contractor;
    private $key;

    /**
     * GetContractorSubscription constructor.
     * @param $contractor
     * @param null $key
     */
    public function __construct($contractor, $key = null)
    {
        if ($key === null){
            $key = config('services.stripe.secret');
        }

        $this->contractor = $contractor;
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Stripe::setApiKey($this->key);

        $contractor = $this->contractor;

        if ($contractor->stripe_id){
            try {
                $customer = Customer::retrieve($contractor->stripe_id);
                $subscriptions = $customer['subscriptions']['data'];
                $count = count($subscriptions);
                for ($i = 0;  $i < $count; $i++){
                    $subscription = $subscriptions[$i];
                    if ($subscription['status'] === 'active'){

                        DB::table('subscriptions')
                            ->insert([
                                'contractor_id' => $contractor->id,
                                'name' => 'default',
                                'stripe_id' => $subscription['id'],
                                'stripe_plan' => $subscription['plan']['id'],
                                'quantity' => 1,
                                'ends_at' => $subscription['cancel_at_period_end'] ? Carbon::createFromTimestamp($subscription['current_period_end']) :  null,
                                'created_at' => $subscription['start_date'] ? Carbon::createFromTimestamp($subscription['start_date']) : null,
                                'updated_at' => $subscription['start_date'] ? Carbon::createFromTimestamp($subscription['start_date']) : null
                            ]);

                    }

                }
            }
            catch(\Exception $exception){
                Log::info("STRIPE FAILED IMPORT: ID: $contractor->id, NAME: $contractor->name");
            }

        }
    }
}
