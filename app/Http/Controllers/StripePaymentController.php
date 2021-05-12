<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Order;
use App\Plan;
use App\User;
use App\UserCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class StripePaymentController extends Controller
{
    public function payment($code)
    {
        if(Auth::user()->type != 'admin')
        {
            $planID = \Illuminate\Support\Facades\Crypt::decrypt($code);
            $plan   = Plan::find($planID);
            if($plan)
            {
                return view('plans.payment', compact('plan'));
            }
            else
            {
                return redirect()->back()->with('error', __('Plan is deleted.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function stripeCreate(Request $request, $plan)
    {
        try
        {
            $authuser     = Auth::user();
            $payment_plan = $payment_frequency = $request->payment_frequency;
            $payment_type = $request->payment_type;

            /* Payment details */
            $code = '';

            $price = $plan->{$payment_frequency . '_price'};

            if(isset($request->coupon) && !empty($request->coupon) && $plan->discounted_price)
            {
                $price = $plan->discounted_price;
                $code  = $request->coupon;
            }

            $product = $plan->name;

            /* Final price */
            $stripe_formatted_price = in_array(
                env('CURRENCY_CODE'), [
                                        'MGA',
                                        'BIF',
                                        'CLP',
                                        'PYG',
                                        'DJF',
                                        'RWF',
                                        'GNF',
                                        'UGX',
                                        'JPY',
                                        'VND',
                                        'VUV',
                                        'XAF',
                                        'KMF',
                                        'KRW',
                                        'XOF',
                                        'XPF',
                                    ]
            ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

            $return_url_parameters = function ($return_type) use ($payment_frequency, $payment_type){
                return '&return_type=' . $return_type . '&payment_processor=stripe&payment_frequency=' . $payment_frequency . '&payment_type=' . $payment_type;
            };

            /* Initiate Stripe */
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            switch($payment_type)
            {
                case 'one-time':

                    $stripe_session = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'name' => $product,
                                    'description' => $payment_plan,
                                    'amount' => $stripe_formatted_price,
                                    'currency' => env('CURRENCY_CODE'),
                                    'quantity' => 1,
                                ],
                            ],
                            'metadata' => [
                                'user_id' => $authuser->id,
                                'package_id' => $plan->id,
                                'payment_frequency' => $payment_frequency,
                                'code' => $code,
                            ],
                            'success_url' => route(
                                'stripe.payment.status', [
                                                           'plan_id' => $plan->id,
                                                           $return_url_parameters('success'),
                                                       ]
                            ),
                            'cancel_url' => route(
                                'stripe.payment.status', [
                                                           'plan_id' => $plan->id,
                                                           $return_url_parameters('cancel'),
                                                       ]
                            ),
                        ]
                    );
                    break;

                case 'recurring':

                    /* Try to get the product related to the package */ try
                {
                    $stripe_product = \Stripe\Product::retrieve($plan->id);
                }
                catch(\Exception $e)
                {
                    /* The product probably does not exist */
                    \Log::debug($e->getMessage());
                }

                    if(!isset($stripe_product))
                    {
                        /* Create the product if not already created */
                        $stripe_product = \Stripe\Product::create(
                            [
                                'id' => $plan->id,
                                'name' => $product,
                                'type' => 'service',
                            ]
                        );
                    }

                    /* Generate the plan id with the proper parameters */
                    $stripe_plan_id = $plan->id . '_' . $payment_plan . '_' . $stripe_formatted_price . '_' . env('CURRENCY_CODE');

                    /* Check if we already have a payment plan created and try to get it */
                    try
                    {
                        $stripe_plan = \Stripe\Plan::retrieve($stripe_plan_id);
                    }
                    catch(\Exception $e)
                    {
                        /* The plan probably does not exist */
                        \Log::debug($e->getMessage());
                    }

                    /* Create the plan if it doesnt exist already */
                    if(!isset($stripe_plan))
                    {
                        try
                        {
                            $stripe_plan = \Stripe\Plan::create(
                                [
                                    'amount' => $stripe_formatted_price,
                                    'interval' => $payment_plan == 'monthly' ? 'month' : 'year',
                                    'product' => $stripe_product->id,
                                    'currency' => env('CURRENCY_CODE'),
                                    'id' => $stripe_plan_id,
                                ]
                            );
                        }
                        catch(\Exception $e)
                        {
                            \Log::debug($e->getMessage());
                        }
                    }

                    $stripe_session = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'subscription_data' => [
                                'items' => [
                                    ['plan' => $stripe_plan->id],
                                ],
                                'metadata' => [
                                    'user_id' => $authuser->id,
                                    'package_id' => $plan->id,
                                    'payment_frequency' => $payment_frequency,
                                    'code' => $code,
                                ],
                            ],
                            'metadata' => [
                                'user_id' => $authuser->id,
                                'package_id' => $plan->id,
                                'payment_frequency' => $payment_frequency,
                                'code' => $code,
                            ],
                            'client_reference_id' => $authuser->id . '###' . $plan->id . '###' . $payment_plan . '###' . time(),
                            'success_url' => route(
                                'stripe.payment.status', [
                                                           'plan_id' => $plan->id,
                                                           $return_url_parameters('success'),
                                                       ]
                            ),
                            'cancel_url' => route(
                                'stripe.payment.status', [
                                                           'plan_id' => $plan->id,
                                                           $return_url_parameters('cancel'),
                                                       ]
                            ),
                        ]
                    );

                    break;
            }

            $stripe_session = $stripe_session ?? false;

            return $stripe_session;
        }
        catch(\Exception $e)
        {
            \Log::debug($e->getMessage());
        }
    }

    public function planGetStripePaymentStatus(Request $request)
    {
        Session::forget('stripe_session');
        try
        {
            if($request->return_type == 'success')
            {
                $objUser                    = \Auth::user();
                $objUser->is_plan_purchased = 1;
                if($objUser->is_trial_done == 1)
                {
                    $objUser->is_trial_done = 2;
                }
                $objUser->save();

                $assignPlan = $objUser->assignPlan($request->plan_id, $request->payment_frequency);
                if($assignPlan['is_success'])
                {
                    return redirect()->route('profile')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->route('profile')->with('error', __($assignPlan['error']));
                }
            }
            else
            {
                return redirect()->route('profile')->with('error', __('Your Payment has failed!'));
            }
        }
        catch(\Exception $exception)
        {
            return redirect()->route('profile')->with('error', $exception->getMessage());
        }
    }

    public function webhookStripe(Request $request)
    {
        /* Initiate Stripe */
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload    = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event      = null;

        try
        {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, env('STRIPE_WEBHOOK_SECRET')
            );

            if(!in_array(
                $event->type, [
                                'invoice.paid',
                                'checkout.session.completed',
                            ]
            ))
            {
                die();
            }

            $session = $event->data->object;

            $payment_id   = $session->id;
            $payer_id     = $session->customer;
            $payer_object = \Stripe\Customer::retrieve($payer_id);
            $payer_name   = $payer_object->name;
            $payer_email  = $payer_object->email;

            \Log::debug('event');
            \Log::debug($event);

            if($session->payment_intent)
            {
                try
                {
                    $stripe = new \Stripe\StripeClient(
                        env('STRIPE_SECRET')
                    );
                    $s      = $stripe->paymentIntents->retrieve(
                        $session->payment_intent, []
                    );
                    \Log::debug('payment_intent');
                    \Log::debug($s);
                }
                catch(\Exception $e)
                {
                    \Log::debug($e->getMessage());
                }
            }

            switch($event->type)
            {
                /* Handling recurring payments */ case 'invoice.paid':

                $payment_total = in_array(
                    env('CURRENCY'), [
                                       'MGA',
                                       'BIF',
                                       'CLP',
                                       'PYG',
                                       'DJF',
                                       'RWF',
                                       'GNF',
                                       'UGX',
                                       'JPY',
                                       'VND',
                                       'VUV',
                                       'XAF',
                                       'KMF',
                                       'KRW',
                                       'XOF',
                                       'XPF',
                                   ]
                ) ? $session->amount_paid : $session->amount_paid / 100;

                $payment_currency = strtoupper($session->currency);

                /* Process meta data */
                $metadata = $session->lines->data[0]->metadata;

                $user_id           = (int)$metadata->user_id;
                $package_id        = (int)$metadata->package_id;
                $payment_frequency = $metadata->payment_frequency;
                $code              = isset($metadata->code) ? $metadata->code : '';

                /* Vars */
                $payment_type            = $session->subscription ? 'recurring' : 'one-time';
                $payment_subscription_id = $payment_type == 'recurring' ? 'stripe###' . $session->subscription : '';

                break;

                /* Handling one time payments */ case 'checkout.session.completed':

                /* Exit when the webhook comes for recurring payments as the invoice.payment_succeeded event will handle it */ if($session->subscription)
            {
                die();
            }

                $payment_total    = in_array(
                    env('CURRENCY'), [
                                       'MGA',
                                       'BIF',
                                       'CLP',
                                       'PYG',
                                       'DJF',
                                       'RWF',
                                       'GNF',
                                       'UGX',
                                       'JPY',
                                       'VND',
                                       'VUV',
                                       'XAF',
                                       'KMF',
                                       'KRW',
                                       'XOF',
                                       'XPF',
                                   ]
                ) ? $session->amount_total : $session->amount_total / 100;
                $payment_currency = strtoupper($session->currency);

                /* Process meta data */
                $metadata = $session->metadata;

                $user_id           = (int)$metadata->user_id;
                $package_id        = (int)$metadata->package_id;
                $payment_frequency = $metadata->payment_frequency;
                $code              = isset($metadata->code) ? $metadata->code : '';

                /* Vars */
                $payment_type            = $session->subscription ? 'recurring' : 'one-time';
                $payment_subscription_id = $payment_type == 'recurring' ? 'stripe###' . $session->subscription : '';

                break;
            }

            $plan = Plan::find($package_id);
            if(!$plan)
            {
                http_response_code(400);
                die();
            }

            /* Make sure the account still exists */
            $user = User::find($user_id);

            if(!$user)
            {
                http_response_code(400);
                die();
            }

            /* Unsubscribe from the previous plan if needed */
            if(!empty($user->payment_subscription_id) && $user->payment_subscription_id != $payment_subscription_id)
            {
                try
                {
                    $user->cancel_subscription($user_id);
                }
                catch(\Exception $exception)
                {
                    \Log::debug($exception->getMessage());
                }
            }

            Order::create(
                [
                    'order_id' => $payment_id,
                    'subscription_id' => $session->subscription,
                    'payer_id' => $payer_id,
                    'name' => $payer_name,
                    'card_number' => '',
                    'card_exp_month' => '',
                    'card_exp_year' => '',
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $payment_total,
                    'price_currency' => $payment_currency,
                    'txn_id' => '',
                    'payment_type' => 'STRIPE',
                    'payment_frequency' => $payment_frequency,
                    'payment_status' => '',
                    'receipt' => '',
                    'user_id' => $user->id,
                ]
            );

            if(!empty($code))
            {
                $coupons = Coupon::where('code', strtoupper($code))->where('is_active', '1')->first();

                $userCoupon         = new UserCoupon();
                $userCoupon->user   = $user->id;
                $userCoupon->coupon = $coupons->id;
                $userCoupon->order  = $payment_id;
                $userCoupon->save();
                $usedCoupun = $coupons->used_coupon();
                if($coupons->limit <= $usedCoupun)
                {
                    $coupons->is_active = 0;
                    $coupons->save();
                }
            }

            $user->payment_subscription_id = $payment_subscription_id;
            $user->save();

        }
        catch(\UnexpectedValueException $e)
        {

            \Log::debug($e->getMessage());

            // Invalid payload
            http_response_code(400);
            exit();

        }
        catch(\Stripe\Error\SignatureVerification $e)
        {

            \Log::debug($e->getMessage());
            // Invalid signature
            http_response_code(400);
            exit();

        }
    }
}
