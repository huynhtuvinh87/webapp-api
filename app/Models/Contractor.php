<?php

namespace App\Models;

use App\ViewModels\ViewContractorComplianceByHiringOrg;
use App\ViewModels\ViewContractorComplianceByHiringOrgPosition;
use App\ViewModels\ViewContractorOverallCompliance;
use App\ViewModels\ViewContractorRequirements;
use App\ViewModels\ViewContractorResourcePositionRequirements;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Billable;
use Log;
use Stripe\Customer;
use Stripe\Invoice as StripeInvoice;
use Stripe\Stripe;
use Stripe\Subscription;
use App\Traits\CacheTrait;
use App\ViewModels\ViewContractorResourceComplianceByHiringOrg;
use Illuminate\Support\Facades\Cache;

class Contractor extends Model
{

    use Billable;
    use CacheTrait;

    //BILLABLE OVERRIDE FUNCTIONS
    //*****

    /**
     * Invoice the billable entity outside of regular billing cycle.
     * OVERRIDE to include tax percentage
     *
     * @param  array  $options
     * @return \Stripe\Invoice|bool
     */
    public function invoice(array $options = [])
    {
        if ($this->stripe_id) {
            $parameters = array_merge($options, ['customer' => $this->stripe_id, 'tax_percent' => $this->taxPercentage()]);

            try {
                return StripeInvoice::create($parameters, $this->getStripeKey())->pay();
            } catch (Stripe\Exception\InvalidRequestException $e) {
                return false;
            }
        }

        return true;
    }
    //END BILLABLE OVERRIDE FUNTIONS
    //*****

    public const SMALL_PLAN_THRESHOLD = 3;
    public const MEDIUM_PLAN_THRESHOLD = 10;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'wsib_number',
        // NOTE: owner_id should be -> role.id
        // However, parts of the site assumes -> user.id
        // Just be careful when using this
        'owner_id',
        'is_active',
        'external_id',
        'second_external_id',
        'has_subcontractors',
    ];

    protected $hidden = [
        'card_last_four',
        'stripe_id',
        'card_brand',
        'trial_ends_at',
        'registration_deadline'
    ];

    /**
     * Tax currently only applicable for Canafa, by province
     * @return int
     */
    public function taxPercentage(){
        if (strtolower($this->attributes['country']) === "canada" &&
            DB::table('tax')->where('province_name', $this->attributes['state'])->exists()
        ){
            return DB::table('tax')->where('province_name', $this->attributes['state'])->first()->tax_rate  ;
        }
        return 0;
    }

    /**
     * Determine which plans a contractor is qualified for
     * @return array
     */
    public function availablePlans() : array {
        $available = ['large'];

        if ($this->hiringOrganizations()->count() < self::MEDIUM_PLAN_THRESHOLD){
            $available[] = 'medium';
        }

        if ($this->hiringOrganizations()->count() < self::SMALL_PLAN_THRESHOLD){
            $available[] = 'small';
        }

        return $available;
    }

    /**
     * Determine if user can adapt plan
     * @param string $plan
     * @return bool
     */
    public function canAdaptPlan(string $plan) : bool{
        return in_array($plan, $this->availablePlans(), true);
    }

    /**
     * Return true if user can add hiring organization without upgrading subscription
     * @return bool
     */
    public function planCapacity(){

        if (!$this->subscribed()){
            return false;
        }

        $plan = $this->subscription()->stripe_plan;

        $count = $this->hiringOrganizations()->count();

        if (!$this->subscribed()){
            return false;
        }

        if ($plan === config('services.stripe.plans.small') || $plan === config('services.stripe.legacy.small')){
            return (self::SMALL_PLAN_THRESHOLD - $count) > 0;
        }

        if ($plan === config('services.stripe.plans.medium') || $plan === config('services.stripe.legacy.medium')){
            return (self::MEDIUM_PLAN_THRESHOLD - $count) > 0;
        }

        return true;

    }

    public function getLogoAttribute($value){
        if (!$value) {
            return null;
        }

        return Storage::url($value);
    }

    public function roles() : MorphMany
    {
        return $this->morphMany('App\Models\Role', 'company', 'entity_key', 'entity_id');
    }

    public function subcontractorSurvey(): MorphMany
    {
        return $this->morphMany('App\Models\SubcontractorSurvey', 'company', 'entity_key', 'entity_id');
    }

    Public function localSubscription() : HasOne
    {
        return $this->hasOne(\App\Models\Subscription::class);
    }

    public function resources() : hasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function hiringOrganizations() : BelongsToMany
    {
        return $this->belongsToMany(HiringOrganization::class)->wherePivot('accepted', 1);
    }

    public function invites() : BelongsToMany
    {
        return $this->belongsToMany(HiringOrganization::class)->wherePivot('accepted', 0)->withPivot(['invite_code', 'due_date']);
    }

    public function folders() : BelongsToMany
    {
        return $this->belongsToMany(Folder::class);
    }

    public function positions() : BelongsToMany
    {

        $hiring_org_ids = $this->hiringOrganizations()->pluck('hiring_organizations.id');

        return $this->belongsToMany(Position::class)->whereIn('hiring_organization_id', $hiring_org_ids)->where('positions.is_active', 1)->withTimestamps();
    }
    /**
     * NOTE this relationship is meant specifically for hiring organization management, so they can manage positions for contractors that have not accepted an invitation
     * @return BelongsToMany
     */
    public function allPositions() : BelongsToMany
    {
        return $this->belongsToMany(Position::class)->withTimestamps();
    }

    public function facilities() : BelongsToMany
    {
        return $this->belongsToMany(Facility::class);
    }

    public function workTypes() : BelongsToMany
    {
        return $this->belongsToMany(WorkType::class);
    }

    /**
     * Overall compliance for a user
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function overallCompliance() : HasOne
    {
        return $this->hasOne(ViewContractorOverallCompliance::class);
    }

    /**
     * Compliance for a user specific to a hiring organization
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complianceByHiringOrganization() : HasMany
    {
        return $this->hasMany(ViewContractorComplianceByHiringOrg::class);
    }

    /**
     * Compliance for a user specific to a position
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complianceByHiringOrganizationPositions() : HasMany
    {
        return $this->hasMany(ViewContractorComplianceByHiringOrgPosition::class);
    }

    /**
     * Compliance for a user specific to a hiring organization
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resourceComplianceByHiringOrganization() : HasMany
    {
        return $this->hasMany(ViewContractorResourceComplianceByHiringOrg::class);
    }

    /**
     * Requirements assigned to a user through hiring organization positions
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requirements()  : HasMany
    {
        return $this->hasMany(ViewContractorRequirements::class);
    }
    public function resourcePositionRequirements()  : HasMany
    {
        return $this->hasMany(ViewContractorResourcePositionRequirements::class);
    }

    public function ratings() : HasMany
    {
        return $this->hasMany(Rating::class);
    }


    public function moduleVisibility() : MorphMany
    {
        return $this->morphMany('App\Models\ModuleVisibility', 'entity');
    }

    public function isModuleVisible($moduleId)
    {
        $module = Module::where('id', $moduleId)
            ->first();

        // If module is not set, return null by default
        if(!isset($module)){
            return null;
        }

        // Grabbing the specific visibility for the hiring org
        $modVis = $module
            ->moduleVisibility
            ->where('entity_type', 'contractor')
            ->where('entity_id', $this->id)
            ->first();

        // If the specific visibility is set, set the return value to be the specific visibility
        if (isset($modVis)) {
            $visible = $modVis['visible'];
        } else {
            // If specific vis is not set, return generic
            $visible = $module['visible'];
        }

        return $visible;
    }

    /**
     * Returns one owner role
     */
    public function owner() : HasOne{
        // NOTE: Not using contractor->owner_id as its not always correct.
        // Instead, going to get the first owner from the roles table
        // return $this->hasOne(Role::class, 'id', 'owner_id');

        // return Role::where('entity_key', 'contractor')
        //     ->where('role', 'owner')
        //     ->get();

        return $this->hasOne(Role::class, 'entity_id')
            ->where('role', 'owner')
            ->where('entity_key', 'contractor');
    }

    /**
     * Returns the owners role
     *
     * @return HasMany
     */
    public function owners(): HasMany
    {
        return $this->hasMany(Role::class, 'entity_id')
            ->where('role', 'owner')
            ->where('entity_key', 'contractor');
    }

    /**
     * Gets raw information from stripe, and updates the account information in the DB
     *
     * @return void
     */
    public function updateStripeInfo(Subscription $stripeSubscriptionData = null, $force = true)
    {
        Log::debug(__METHOD__);

        try {

            $last_updated = DB::table('subscriptions')
                ->where('contractor_id', $this->id)
                ->value('updated_at');

            Log::debug("Updating Stripe Information for Contractor $this->id", [
                'lastUpdated' => $last_updated,
            ]);

            if ($last_updated > Carbon::now()->subDay()) {
                Log::debug("Stripe is already updated " . $this->id);
                return;
            }

            $hasActiveSubscription = $this->subscribed('default');

            // Get Stripe Information
            Stripe::setApiKey(config('services.stripe.secret'));

            if (!isset($this->stripe_id)) {
                throw new Exception("No stripe ID");
            }

            if (!isset($stripeSubscriptionData)) {

                $customer = Customer::retrieve($this->stripe_id);

                if (!isset($customer)) {
                    throw new Exception("Stripe contractor was not defined");
                }
                if (!isset($customer->subscriptions)) {
                    throw new Exception("Contractor had no subscriptions associated to their account");
                }

                $stripeSubscriptionData = $customer->subscriptions->data[0];
            }

            if (!isset($stripeSubscriptionData)) {
                throw new Exception("Data for customer could not be found");
            }

            if (!isset($stripeSubscriptionData->plan)) {
                Log::debug('Stripe Sub Data', [
                    'data' => $stripeSubscriptionData,
                ]);
                throw new Exception("Plan not found");
            }

            // Update subscription table

            DB::table('subscriptions')
                ->updateOrInsert(
                    [
                        'contractor_id' => $this->id,
                    ],
                    [
                        'name' => 'default',
                        'quantity' => 1,
                        'stripe_plan' => $stripeSubscriptionData->plan->id,
                        'stripe_id' => $stripeSubscriptionData->id,
                        'ends_at' => isset($stripeSubscriptionData->cancel_at) ? Carbon::createFromTimestamp($stripeSubscriptionData->cancel_at) : null,
                        'created_at' => Carbon::createFromTimestamp($stripeSubscriptionData->created),
                        'updated_at' => Carbon::now(),
                    ]
                );

            $this->refresh();

            return;

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function deleteDuplicateSubscriptions()
    {
        $subscriptions = DB::table('subscriptions')
            ->where('contractor_id', $this->id)
            ->get();

        $subCount = sizeof($subscriptions);

        if ($subCount > 1) {
            Log::warning("Deleting extra subscription entries for Contractor", [
                'contractor' => $this->name,
                'contractor ID' => $this->id,
                'Site subscription count' => $subCount,
            ]);
            foreach ($subscriptions as $key => $siteSub) {
                if ($key > 0) {
                    DB::table('subscriptions')
                        ->where('id', $siteSub->id)
                        ->delete();
                }
            }
        }
    }
}
