<?php

namespace App\Console\Commands;

use App\Models\Contractor;
use Carbon\Carbon;
use Exception;
use Illuminate\Cache\RateLimiter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;
use Stripe\Customer;
use Stripe\Stripe;

/**
 * SyncStripe syncs the information from stripe and the DB
 *
 * 1. Grabs list of contractors from Stripe
 * 2. For each contractor in stripe:
 *         a. Check DB for matching subscription information.
 *         b. If the subscription information exists:
 *             update data (just to make sure its correct).
 *         c. If no information exists:
 *             End subscription
 */
class SyncStripe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync {--contractor=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets Stripe information and syncs it with contractors';

    private $contractor;
    /** Max iterations of attempting to grab contractors from stripe */
    private $loopLimit = 50;
    /** How many contractors to grab from stripe at a time */
    private $contractorChunkSize = 100;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {

        $this->contractor = Contractor::find((int) $this->option('contractor'));

        Log::info("CLEAR LINE");
        Stripe::setApiKey(config('services.stripe.secret'));
        $stripeContractors = [];
        $siteContractors = [];

        if (isset($this->contractor)) {
            $stripeContractors[] = $this->getStripeContractor($this->contractor);
            $siteContractors[] = $this->contractor;
        } else {
            $this->info("Before Running");
            $this->report($this);

            $stripeContractors = $this->getAllStripeContractors();
            $siteContractors = $this->getAllSiteContractors();
        }

        // Iterating through each site contractor
        Log::info("Iterating through each site contractor");
        foreach ($siteContractors as $siteContractor) {
            try {
                $siteContractor->deleteDuplicateSubscriptions();
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }

        Log::info("Iterating through each stripe contractor");
        foreach ($stripeContractors as $stripeContractor) {
            try {
                $siteContractor = $this->getSiteContractor($stripeContractor);
                $siteContractor->deleteDuplicateSubscriptions();
                $this->loadStripeIntoSite($siteContractor, $stripeContractor);
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }

        $this->info("After Running");
        $this->report($this);
        Log::info(get_class($this) . " complete");
    }

    /**
     * Gets the stripe contractor based on the site contractor
     *
     * @param [type] $siteContractor
     * @return void
     */
    public function getStripeContractor($siteContractor)
    {
        if (!isset($siteContractor)) {
            throw new Exception("Contractor was not defined in DB");
        }
        return Customer::retrieve($siteContractor->stripe_id);
    }

    /**
     * Gets the contractor in the DB based on the customer from stripe
     *
     * @param Customer $stripeContractor
     * @return Contractor
     */
    private function getSiteContractor(Customer $stripeContractor): Contractor
    {
        $stripeCustomerId = $stripeContractor->id;
        $contractor = Contractor::where('stripe_id', $stripeCustomerId)->first();
        if (!isset($contractor)) {
            throw new Exception("$stripeCustomerId contractor could not be found in DB");
        }
        return $contractor;
    }

    /**
     * Gets all the customers (contractors) in stripe
     *
     * @return void
     */
    public function getAllStripeContractors()
    {
        $limit = $this->contractorChunkSize;
        $loopLimit = $this->loopLimit;
        $allContractors = [];
        $lastCustomerId = null;

        Log::info("Retrieving Stripe Contractors (limit: $limit x $loopLimit)");
        $limiter = app(RateLimiter::class);
        $key = 'getStripeRequests';

        do {
            $limiter->hit($key, 60);

            $customers = Customer::all([
                'limit' => $limit,
                'starting_after' => $lastCustomerId,
            ]);

            foreach ($customers as $customer) {
                array_push($allContractors, $customer);
            }

            $lastCustomerId = $customers['data'][sizeof($customers['data']) - 1]['id'];

            $loopLimit--;
            if ($loopLimit == 0) {
                Log::warn("Loop limit reached.");
            }

        } while (
            // Continue if there are still customers
            sizeof($customers['data']) > 0
            // Continue if there are the list of customers is the same size as the pull limit
             && sizeof($customers['data']) == $limit
            // End if the loop limit is reached
             && $loopLimit > 0
        );

        return $allContractors;
    }

    private function getAllSiteContractors()
    {
        return Contractor::get();
    }

    public function loadStripeIntoSite(Contractor $siteContractor, Customer $stripeContractor)
    {
        Log::debug(__METHOD__);
        // Error handling
        // null exceptions
        if (!isset($siteContractor)) {
            throw new Exception("Site Contractor was not defined");
        }
        if (!isset($stripeContractor)) {
            throw new Exception("Stripe Contractor was not defined");
        }

        // Check DB for subscription information
        $currentSubs = $this->parseSiteContractorSubs($siteContractor);
        $stripeSubs = $this->parseStripeSubs($stripeContractor);

        // If they don't have only 1 sub, throw error
        if (sizeof($currentSubs) > 1) {
            Log::debug("$siteContractor->name does not have just 1 sub", [
                'contractor' => $siteContractor->name,
                'customer id' => $siteContractor->stripe_id,
                'currentSubs count' => sizeof($currentSubs),
            ]);
            throw new Exception("Contractor in DB has no subscription information");
        }

        if (sizeof($stripeSubs) > 1) {
            Log::debug("$siteContractor->name has more than 1 sub from stripe", [
                'contractor' => $siteContractor->name,
                'stripeSubs count' => sizeof($stripeSubs),
            ]);
            throw new Exception("Bad Data");
        } else if (sizeof($stripeSubs) == 0) {
            return $this->markContractorAsInactive($siteContractor);
        }

        $sub = $stripeSubs[0];

        if (sizeof($sub->items->data) != 1) {
            Log::debug("$siteContractor->name does not just have 1 sub item", [
                'contractor' => $siteContractor->name,
                'sub item count' => sizeof($sub->items->data),
            ]);
            throw new Exception("Bad Data");
        }

        $siteContractor->updateStripeInfo($sub);

    }

    /**
     * Gets a list of subscriptions for a given contractor on the site
     *
     * @param [type] $siteContractor
     * @return void
     */
    private function parseSiteContractorSubs(Contractor $siteContractor)
    {
        $subscriptions = DB::table('subscriptions')
            ->where('contractor_id', $siteContractor->id)
            ->get();

        return $subscriptions;
    }

    private function parseStripeSubs($stripeContractor)
    {
        if (!isset($stripeContractor)) {
            throw new Exception("Stripe contractor was not defined");
        }
        if (!isset($stripeContractor->subscriptions)) {
            throw new Exception("Contractor had no subscriptions associated to their account");
        }
        return $stripeContractor->subscriptions->data;
    }

    /**
     * Takes in a site contractor
     * Removes all but 1 subscription entry from DB
     *
     * @param [type] $siteContractor
     * @return void
     */
    private function deleteDuplicateSubs(Contractor $siteContractor)
    {
        // If > 1 subscription in DB,
        // remove all but 1
        $siteContractorSubs = $this->parseSiteContractorSubs($siteContractor);
        $siteSubCount = sizeof($siteContractorSubs);

        if ($siteSubCount > 1) {
            Log::warning("Deleting extra subscription entries for Contractor", [
                'contractor' => $siteContractor->name,
                'contractor ID' => $siteContractor->id,
                'Site subscription count' => $siteSubCount,
            ]);
            foreach ($siteContractorSubs as $key => $siteSub) {
                if ($key > 0) {
                    DB::table('subscriptions')
                        ->where('id', $siteSub->id)
                        ->delete();
                }
            }
        }
    }

    /**
     * Removes subscription information from site for a contractor
     * Use when contractor does not have any active subscriptions from stripe
     *
     * @param Contractor $siteContractor
     * @return void
     */
    private function markContractorAsInactive(Contractor $siteContractor)
    {
        Log::info("Marking contractor $siteContractor->id as inactive");

        /**
         * NOTE: Using cashier will result in updating stripe
         * However, we only want to update the local DB
         * Don't use the following line of code to mark contractor as inactive
         */
        // $siteContractor->subscription('default')->cancelNow();
        $hasActiveSub = $siteContractor->subscribed('default');
        if ($hasActiveSub) {

            DB::table('subscriptions')
                ->where('contractor_id', $siteContractor->id)
                ->update(['ends_at' => Carbon::now()]);
        }
    }

    private function report($console = null)
    {
        $subscriptionCountQuery = "SELECT
                COUNT(*) as duplication_count,
                count
            FROM (
                SELECT
                    COUNT(*) as count,
                    subscriptions.contractor_id
                FROM subscriptions
                GROUP BY subscriptions.contractor_id
            ) as counts
            GROUP BY count;
        ";
        // $subscriptionResults = DB::select($subscriptionCountQuery);
        $subscriptionResults = collect(DB::select($subscriptionCountQuery))
            ->map(function ($entry) {
                return [
                    'statistic' => "Contractors with $entry->count subscription entries",
                    'value' => $entry->duplication_count,
                ];
            })
            ->toArray();

        $contactorCountResults = [
            'statistic' => "Contractor Count",
            'value' => sizeof(Contractor::get()),
        ];

        Log::info('results', [
            'result' => $subscriptionResults,
        ]);
        if (isset($console)) {
            $console->info("Contractors with duplicate subscription information");
            $console->table(
                ['statistic', 'value'],
                $subscriptionResults
            );
        }
    }
}
