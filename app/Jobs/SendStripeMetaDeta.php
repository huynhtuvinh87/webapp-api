<?php

namespace App\Jobs;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stripe\Customer;
use Stripe\Stripe;
use App\Notifications\SlackErrorNotification;
use Exception;
use Log;

class SendStripeMetaDeta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contractor;
    private $hiringOrganization;

    /**'
     * IF SIGNUP, pass hiringOrganization parameter
     * SendStripeMetaDeta constructor.
     * @param Contractor $contractor
     * @param HiringOrganization|null $hiringOrganization
     */
    public function __construct(Contractor $contractor, HiringOrganization $hiringOrganization = null)
    {
        $this->contractor = $contractor;
        $this->hiringOrganization = $hiringOrganization;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try{

        $metadata = [
            'Contractor Name' => $this->contractor->name,
            'Contractor ID' => $this->contractor->id,
            'Companies' => implode(',', $this->contractor->hiringOrganizations()->pluck('name')->toArray())
        ];

        if ($this->hiringOrganization){

            $metadata['SignUp Company'] = $this->hiringOrganization->name;
            $metadata['SignUp ID'] = $this->hiringOrganization->id;
        }

        else if ($this->contractor->hiringOrganizations->count()){
            $metadata['SignUp Company'] = $this->contractor->hiringOrganizations()->first()->name;
            $metadata['SignUp ID'] = $this->contractor->hiringOrganizations()->first()->id;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        Customer::update($this->contractor->stripe_id, [
            'metadata' => $metadata
        ]);

        // Getting customer from server and verifying the stripe metadata was updated
        $stripeCustomer = Customer::retrieve($this->contractor->stripe_id);
        if(is_null($stripeCustomer['metadata']) || sizeof($stripeCustomer['metadata']) == 0){
            throw new Exception("Stripe metadata was not properly updated on stripe");
        }

        } catch (Exception $e){
            Log::error($e);
            Log::channel('slack')->error($e->getMessage());
        }

    }
}
