<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendStripeMetaDeta;
use App\Lib\StripeUtils;
use App\Traits\ControllerTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Invoice;
use Stripe\Stripe;
use Exception;
use Stripe\StripeClient;

class ContractorSubscriptionController extends Controller
{

    use ControllerTrait;

    /**
     * TODO subscribe to miniimum plan allowed for customer
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function subscribe(Request $request)
    {

        try {
            $contractor = $request->user()->role->company;

            if ($contractor->subscribed()){

                if ($contractor->subscription()->onGracePeriod()){

                    $contractor->subscription('default')->resume();

                    return response(['subscription' => $contractor->subscription()]);
                }

                return response([
                    "errors" => [
                        "subscription" => [
                            "Company is already subscribed" //TODO translate
                        ]
                    ],
                ], 409);
            }
        } catch(Exception $e){
            Log::error($e);
        }

        $this->validate($request, [
            'stripe_token' => 'required',
            'plan' => 'required|in:small,medium,large'
        ]);

        try {
            if ($request->get('coupon')) {

                if (!StripeUtils::isCouponValid($request->get('coupon'))){
                    return response()->json([
                        "errors" => [
                            "coupon" => [
                                "Coupon Code is Invalid" //TODO translate
                            ]
                        ]
                    ], 418);
                }
                $contractor->newSubscription('default', config('services.stripe.plans')[$request->get('plan')])->withCoupon($request->get('coupon'))->create(
                    $request->get('stripe_token'),
                        [
                            'email ' => $request->user()->email
                        ]
                );

            }

            else {
                $subscription = $contractor->newSubscription('default',config('services.stripe.plans')[$request->get('plan')])->create(
                    $request->get('stripe_token'),
                    [
                            'email' => $request->user()->email
                    ]
                );
            }

            SendStripeMetaDeta::dispatch($contractor);

            return response(['subscription' => $subscription]);

        }
        catch(\Exception $e){
            return response()->json([
                "errors" => [
                    "card" => [
                        "Payment Not Accepted" //TODO translate
                    ]
                ],
                "message" => $e->getMessage()
            ], 418);
        }

    }

    /**
     * Cancel Subscription
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {

        $contractor = $request->user()->role->company;

        $contractor->subscription('default')->cancel();

        return response($contractor->subscription());

    }

    /**
     * Resume subscription
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resume(Request $request)
    {

        $contractor = $request->user()->company();

        $contractor->subscription('default')->resume();

        return response($contractor);

    }

    /**
     * Modify subscription plan
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function modifySubscription(Request $request)
    {

        $this->validate($request, [
            'plan' => 'required|in:small,medium,large'
        ]);

        $contractor = $request->user()->role->company;

        if (!$contractor->subscribed()){
            return response(['message' => 'conflict'], 409);
        }

        if (!$contractor->canAdaptPlan($request->get('plan'))){
            return response([
                "errors" => [
                    "plan" => [
                        "You do not qualify for this plan" //TODO translate
                    ]
                ]
            ], 418);
        }


        if (in_array($contractor->subscription()->stripe_plan, config('services.stripe.legacy'), true)){

            $contractor
                ->subscription('default')
                ->swap(config('services.stripe.legacy')[$request->get('plan')]);
        }

        $contractor
            ->subscription('default')
            ->swap(config('services.stripe.plans')[$request->get('plan')]);

        return response(['subscription' => $contractor->subscription()]);

    }

    /**
     * Modify Credit Card
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function modifyCreditCard(Request $request)
    {

        $stripe = new StripeClient(config('services.stripe.secret'));

        $this->validate($request, [
            // 'stripe_token' => 'required|string',
            'stripe_payment_method' => 'required|string',
        ]);

        try {
            $paymentMethodId = $request->get('stripe_payment_method');
            $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);

            $contractor = $request->user()->role->company;
            $paymentMethod = $request->get('stripe_payment_method');
            $contractor->updateDefaultPaymentMethod($paymentMethod);
            return response([
                'card' => [
                    'last_four' => $contractor->card_last_four,
                    'brand' => $contractor->card_brand,
                ],
            ]);
        } catch (Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                "errors" => [
                    "card" => [
                        "Card Not Valid", //TODO translate
                    ],
                ],
            ], 418);
        } catch (\Exception $e) {
            $logStack = ['daily'];
            if(config('app.env') == 'production'){
                $logStack[] = 'slack';
            }
            Log::stack($logStack)
                ->error($e->getMessage(), [
                    "error" => $e
                ]);
            return response(["message" => "There was an error updating your credit card."], 400);
        }
    }

    /**
     * Test and apply coupon code
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function applyCouponCode(Request $request){

        $this->validate($request, [
            'coupon' => 'required|string'
        ]);

        $coupon = StripeUtils::describeCoupon($request->get('coupon'));

        if (null === $coupon){
            return response([
                'errors' => [
                    'coupon' => [
                        "Coupon Code is Invalid" //TODO translate
                    ]
                ]
            ], 418);
        }

        $request->user()->role->company->applyCoupon($request->get('code'));

        return response([
            'message' => 'success',
            'coupon' => $coupon
        ]);

    }

    /**
     * Test coupon without applying
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function testCouponCode(Request $request){
        $this->validate($request, [
            'coupon' => 'required|string'
        ]);

        $coupon = StripeUtils::describeCoupon($request->get('coupon'));

        if (null === $coupon){
            return response([
                'errors' => [
                    'coupon' => [
                        "Coupon Code is Invalid" //TODO translate
                    ]
                ]
            ], 418);
        }

        return response([
            'coupon' => $coupon
        ]);
    }

    /**
     * Describe whether user is subscribed and what their subscribed plan is (if exists)
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getSubscriptionDetails(Request $request){

        $contractor = $request->user()->role->company;

        $plan = 'unknown';

        if ($contractor->subscribed()){

            $sub = $contractor->subscription()->stripe_plan;

            if ($sub === config('services.stripe.plans.small') || $sub === config('services.stripe.legacy.small')) {
                $plan = 'small';
            }
            else if ($sub === config('services.stripe.plans.medium') || $sub === config('services.stripe.legacy.medium')) {
                $plan = 'medium';
            }
            else if ($sub === config('services.stripe.plans.large') || $sub === config('services.stripe.legacy.large')) {
                $plan = 'large';
            }

        }


        return response([
            'subscribed' => $contractor->subscribed(),
            'subscription' => $plan,
            'grace_period' => $contractor->subscribed() ? $contractor->subscription()->onGracePeriod() : null,
            'card' => [
                'last_four' => $contractor->card_last_four,
                'brand' => $contractor->card_brand
            ],
        ]);
    }

    public function getSubscriptionBillingPeriod(Request $request){
        $end_date = null;
        $currentPeriodEnd = null;

        $this->logRequest($request, __METHOD__);

        try {
			$company = $request->user()->role->company;
			$companyAsCustomer = $company->asStripeCustomer();
            $subscriptions = isset($companyAsCustomer) ? $companyAsCustomer['subscriptions'] : null;
            if (isset($subscriptions) && isset($subscriptions->data) && isset($subscriptions->data[0]) && isset($subscriptions->data[0]['current_period_end'])){
                $currentPeriodEnd = $subscriptions->data[0]['current_period_end'];
            }
            $end_date = isset($currentPeriodEnd) ? Carbon::createFromTimeStamp($currentPeriodEnd)->toFormattedDateString() : null;

            return response([
				'billing_date' => $request->user()->role->company->subscribed() ? $end_date : null,
				'end_date' => $request->user()->role->company->subscribed() ? $request->user()->role->company->subscription()->ends_at : null
			]);
		}
		catch( Stripe\Exception\InvalidRequestException $e){
			return response([
				'message' => 'Your subscription information could not be found. Please contact support for further assistance.'
			], 404);
		}
        catch(\Exception $e){
			Log::error($e);
        }

    }

    public function invoiceList(Request $request){

        if ($request->user()->role->company->stripe_id === null){
            return response(['message' => 'no subscription'], 409);
        }

		Stripe::setApiKey(config('services.stripe.secret'));

		try{
			$invoices = Invoice::all(['customer' => $request->user()->role->company->stripe_id]);

			return response(['invoices' => $invoices]);
		} catch (Exception $e){
			return response([
				'message' => 'Your invoice information could not be found. Please contact support for further assistance.'
			], 404);
		}

    }

}
