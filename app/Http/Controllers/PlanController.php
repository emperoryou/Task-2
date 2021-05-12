<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Order;
use App\Plan;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(\Auth::user()->type == 'admin')
        {
            $plans = Plan::all();

            return view('plans.index', compact('plans'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(\Auth::user()->type == 'admin')
        {
            $plan = new Plan();

            return view('plans.create', compact('plan'));
        }
        else
        {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ], 401
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(\Auth::user()->type == 'admin')
        {
            $validation                  = [];
            $validation['name']          = 'required|unique:plans';
            $validation['monthly_price'] = 'required|numeric|min:0';
            $validation['annual_price']  = 'required|numeric|min:0';
            $validation['max_users']     = 'required|numeric';
            $validation['max_projects']  = 'required|numeric';
            $validation['trial_days']    = 'required|numeric';

            $validator = \Validator::make($request->all(), $validation);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if((env('ENABLE_STRIPE') == 'on' && !empty(env('STRIPE_KEY')) && !empty(env('STRIPE_SECRET'))) || (env('ENABLE_PAYPAL') == 'on' && !empty(env('PAYPAL_CLIENT_ID')) && !empty(env('PAYPAL_SECRET_KEY'))) || ($request->monthly_price <= 0 && $request->annual_price <= 0))
            {
                $post           = $request->all();
                $post['status'] = $request->has('status') ? 1 : 0;

                if(Plan::create($post))
                {
                    return redirect()->back()->with('success', __('Plan created Successfully!'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Something is wrong'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Please set Stripe or PayPal payment details for add new plan'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Plan $plan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Plan $plan)
    {
        if(\Auth::user()->type == 'admin')
        {
            return view('plans.edit', compact('plan'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plan $plan)
    {
        if(\Auth::user()->type == 'admin')
        {
            if($plan)
            {
                $validation                  = [];
                $validation['name']          = 'required|unique:plans,name,' . $plan->id;
                $validation['monthly_price'] = 'required|numeric|min:0';
                $validation['annual_price']  = 'required|numeric|min:0';
                $validation['max_users']     = 'required|numeric';
                $validation['max_projects']  = 'required|numeric';
                $validation['trial_days']    = 'required|numeric';

                $validator = \Validator::make($request->all(), $validation);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if((env('ENABLE_STRIPE') == 'on' && !empty(env('STRIPE_KEY')) && !empty(env('STRIPE_SECRET'))) || (env('ENABLE_PAYPAL') == 'on' && !empty(env('PAYPAL_CLIENT_ID')) && !empty(env('PAYPAL_SECRET_KEY'))) || ($request->monthly_price <= 0 && $request->annual_price <= 0))
                {
                    $post           = $request->all();
                    $post['status'] = $request->has('status') ? 1 : 0;
                    if($plan->update($post))
                    {
                        return redirect()->back()->with('success', __('Plan updated Successfully!'));
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Something is wrong'));
                    }
                }
                else
                {
                    return redirect()->back()->with('error', __('Please set Stripe or PayPal payment details for add new plan'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Plan not found'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plan $plan)
    {
        return redirect()->back()->with('error', __('Something is wrong'));
    }

    public function orderList()
    {
        if(\Auth::user()->type == 'admin')
        {
            $orders = Order::select(
                [
                    'orders.*',
                    'users.name as user_name',
                ]
            )->join('users', 'orders.user_id', '=', 'users.id')->orderBy('orders.created_at', 'DESC')->get();

            return view('plans.orderlist', compact('orders'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function preparePayment(Request $request)
    {
        if((env('ENABLE_STRIPE') == 'on' && !empty(env('STRIPE_KEY')) && !empty(env('STRIPE_SECRET'))) || (env('ENABLE_PAYPAL') == 'on' && !empty(env('PAYPAL_CLIENT_ID')) && !empty(env('PAYPAL_SECRET_KEY'))))
        {
            $plan_id        = \Illuminate\Support\Facades\Crypt::decrypt($request->code);
            $plan           = Plan::find($plan_id);
            $authuser       = Auth::user();
            $stripe_session = '';

            if($plan)
            {
                /* Check for code usage */
                $plan->discounted_price = false;
                $payment_frequency      = $request->payment_frequency;
                $price                  = $plan->{$payment_frequency . '_price'};

                if(isset($request->coupon) && !empty($request->coupon))
                {
                    $request->coupon = trim($request->coupon);
                    $coupons         = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    if(!empty($coupons))
                    {
                        $usedCoupun             = $coupons->used_coupon();
                        $discount_value         = ($price / 100) * $coupons->discount;
                        $plan->discounted_price = $price - $discount_value;

                        if($usedCoupun >= $coupons->limit)
                        {
                            return redirect()->back()->with('error', __('This coupon code has expired.'));
                        }
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                    }
                }

                if($price <= 0)
                {
                    $authuser->plan = $plan->id;
                    $authuser->save();

                    $assignPlan = $authuser->assignPlan($plan->id, $request->payment_frequency);

                    if($assignPlan['is_success'] == true && !empty($plan))
                    {
                        if(!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '')
                        {
                            try
                            {
                                $authuser->cancel_subscription($authuser->id);
                            }
                            catch(\Exception $exception)
                            {
                                \Log::debug($exception->getMessage());
                            }
                        }

                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        Order::create(
                            [
                                'order_id' => $orderID,
                                'name' => null,
                                'email' => null,
                                'card_number' => null,
                                'card_exp_month' => null,
                                'card_exp_year' => null,
                                'plan_name' => $plan->name,
                                'plan_id' => $plan->id,
                                'price' => $price,
                                'price_currency' => !empty(env('CURRENCY_CODE')) ? env('CURRENCY_CODE') : 'usd',
                                'txn_id' => '',
                                'payment_type' => __('Zero Price'),
                                'payment_status' => 'succeeded',
                                'receipt' => null,
                                'user_id' => $authuser->id,
                            ]
                        );

                        return redirect()->route('home')->with('success', __('Plan successfully upgraded.'));
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                    }
                }

                switch($request->payment_processor)
                {
                    case 'paypal':

                        $result = app('App\Http\Controllers\PaypalController')->paypalCreate($request, $plan);
                        break;

                    case 'stripe':

                        $stripe_session = app('App\Http\Controllers\StripePaymentController')->stripeCreate($request, $plan);
                        break;
                }

                return redirect()->route('payment', $request->code)->with(['stripe_session' => $stripe_session]);
            }
            else
            {
                return redirect()->back()->with('error', __('Plan not found'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Please Enter Stripe or PayPal Payment Details.'));
        }
    }

    public function takeAPlanTrial(Request $request, $plan_id)
    {
        $plan = Plan::find($plan_id);
        $user = Auth::user();
        if($plan && $user->is_trial_done == 0)
        {
            $days                   = $plan->trial_days == '-1' ? '36500' : $plan->trial_days;
            $user->is_trial_done    = 1;
            $user->plan             = $plan->id;
            $user->plan_expire_date = Carbon::now()->addDays($days)->isoFormat('YYYY-MM-DD');
            $user->save();

            $users       = User::where('created_by', '=', $user->getCreatedBy());
            $usr_contact = $users->count();

            if($usr_contact > 0)
            {
                $users     = $users->get();
                $userCount = 0;

                foreach($users as $user)
                {
                    $userCount++;
                    $user->is_active = $userCount <= $plan->max_users ? 1 : 0;
                    $user->save();
                }
            }

            $user_project = $user->projects()->pluck('project_id')->toArray();

            if(count($user_project) > 0)
            {
                $projects     = Project::whereIn('id', $user_project)->get();
                $projectCount = 0;

                foreach($projects as $project)
                {
                    $projectCount++;
                    $project->is_active = $projectCount <= $plan->max_projects ? 1 : 0;
                    $project->save();
                }
            }

            return redirect()->route('home')->with('success', __('Your trial has been started'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function changeUserPlan(Request $request, $plan_id)
    {
        $plan = Plan::find($plan_id);
        $user = Auth::user();

        if($plan && $user->type != 'admin')
        {

            $user->is_register_trial  = 0;
            $user->interested_plan_id = 0;
            $user->save();

            return redirect('/check');

        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
