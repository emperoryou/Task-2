<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Invoice;
use App\InvoicePayment;
use App\Order;
use App\Project;
use App\User;
use App\UserCoupon;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PayPal\Api\Agreement;
use PayPal\Api\Amount;
use PayPal\Api\Currency;
use PayPal\Api\FlowConfig;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Plan;
use PayPal\Api\Presentation;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\WebProfile;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Common\PayPalModel;
use PayPal\Rest\ApiContext;

class PaypalController extends Controller
{
    private $_api_context;

    public function setApiContext()
    {
        $paypal_conf = config('paypal');

        if(isset($_REQUEST['from']) && $_REQUEST['from'] == 'invoice')
        {
            $details = Auth::user()->decodeDetails($_REQUEST['invoice_creator']);

            $paypal_conf['settings']['mode'] = $details['paypal_mode'];
            $paypal_conf['client_id']        = $details['paypal_client_id'];
            $paypal_conf['secret_key']       = $details['paypal_secret_key'];
        }
        else
        {
            $paypal_conf['settings']['mode'] = env('PAYPAL_MODE');
        }

        $this->_api_context = new ApiContext(
            new OAuthTokenCredential(
                $paypal_conf['client_id'], $paypal_conf['secret_key']
            )
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }

    public function clientPayWithPaypal(Request $request, $invoice_id)
    {
        $user    = Auth::user();
        $invoice = Invoice::find($invoice_id);
        $details = $user->decodeDetails($invoice->created_by);

        if($details['enable_paypal'] == 'on')
        {
            $get_amount = $request->amount;

            // validate amount it must be at least 1
            $validator = Validator::make(
                $request->all(), ['amount' => 'required|numeric|min:1']
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $project = Project::find($invoice->project_id);

            if($invoice)
            {
                if($get_amount > $invoice->getDue())
                {
                    return redirect()->back()->with('error', __('Invalid amount.'));
                }
                else
                {
                    $this->setApiContext();

                    $name = $user->name . " - " . Utility::invoiceNumberFormat($invoice->invoice_id);

                    $payer = new Payer();
                    $payer->setPaymentMethod('paypal');

                    $item_1 = new Item();
                    $item_1->setName($name)->setCurrency($project->currency_code)->setQuantity(1)->setPrice($get_amount);

                    $item_list = new ItemList();
                    $item_list->setItems([$item_1]);

                    $amount = new Amount();
                    $amount->setCurrency($project->currency_code)->setTotal($get_amount);

                    $transaction = new Transaction();
                    $transaction->setAmount($amount)->setItemList($item_list)->setDescription($request->notes);

                    $redirect_urls = new RedirectUrls();
                    $redirect_urls->setReturnUrl(route('client.get.payment.status', $invoice->id))->setCancelUrl(route('client.get.payment.status', $invoice->id));

                    $payment = new Payment();
                    $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)->setTransactions([$transaction]);

                    try
                    {
                        $payment->create($this->_api_context);
                    }
                    catch(\PayPal\Exception\PayPalConnectionException $ex) //PPConnectionException
                    {
                        if(\Config::get('app.debug'))
                        {
                            return redirect()->route('invoices.show', $invoice_id)->with('error', __('Connection timeout'));
                        }
                        else
                        {
                            return redirect()->route('invoices.show', $invoice_id)->with('error', __('Some error occur, sorry for inconvenient'));
                        }
                    }

                    foreach($payment->getLinks() as $link)
                    {
                        if($link->getRel() == 'approval_url')
                        {
                            $redirect_url = $link->getHref();
                            break;
                        }
                    }

                    Session::put('paypal_payment_id', $payment->getId());

                    if(isset($redirect_url))
                    {
                        return Redirect::away($redirect_url);
                    }

                    return redirect()->route('invoices.show', $invoice_id)->with('error', __('Unknown error occurred'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function clientGetPaymentStatus(Request $request, $invoice_id)
    {
        $user    = Auth::user();
        $invoice = Invoice::find($invoice_id);

        if($invoice)
        {
            $this->setApiContext();

            $payment_id = Session::get('paypal_payment_id');

            Session::forget('paypal_payment_id');

            if(empty($request->PayerID || empty($request->token)))
            {
                return redirect()->route('invoices.show', $invoice_id)->with('error', __('Payment failed'));
            }

            $payment = Payment::get($payment_id, $this->_api_context);

            $execution = new PaymentExecution();
            $execution->setPayerId($request->PayerID);

            try
            {
                $result = $payment->execute($execution, $this->_api_context)->toArray();

                $status = ucwords(str_replace('_', ' ', $result['state']));

                if($result['state'] == 'approved')
                {
                    $invoice_payment                 = new InvoicePayment();
                    $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber();
                    $invoice_payment->invoice_id     = $invoice->id;
                    $invoice_payment->amount         = $result['transactions'][0]['amount']['total'];
                    $invoice_payment->date           = date('Y-m-d');
                    $invoice_payment->payment_id     = 0;
                    $invoice_payment->payment_type   = __('PAYPAL');
                    $invoice_payment->client_id      = $user->id;
                    $invoice_payment->notes          = $result['transactions'][0]['description'];
                    $invoice_payment->save();

                    if(($invoice->getDue() - $invoice_payment->amount) == 0)
                    {
                        Invoice::change_status($invoice->id, 3);
                    }
                    else
                    {
                        Invoice::change_status($invoice->id, 2);
                    }


                    return redirect()->route('invoices.show', $invoice_id)->with('success', __('Payment added Successfully'));
                }
                else
                {
                    return redirect()->route('invoices.show', $invoice_id)->with('error', __('Transaction has been ' . $status));
                }

            }
            catch(\Exception $e)
            {
                return redirect()->route('invoices.show', $invoice_id)->with('error', __('Transaction has been failed!'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function paypalCreate(Request $request, $myPlan)
    {
        try
        {
            $authuser     = Auth::user();
            $payment_plan = $payment_frequency = $request->payment_frequency;
            $payment_type = $request->payment_type;

            /* Payment details */
            $code = '';

            $price = (float)$myPlan->{$payment_frequency . '_price'};

            if(isset($request->coupon) && !empty($request->coupon) && $myPlan->discounted_price)
            {
                $price = (float)$myPlan->discounted_price;
                $code  = $request->coupon;
            }

            $product = $myPlan->name;

            /* Make sure the price is right depending on the currency */
            $price = in_array(
                env('CURRENCY_CODE'), [
                                        'JPY',
                                        'TWD',
                                        'HUF',
                                    ]
            ) ? number_format($price, 0, '.', '') : number_format($price, 2, '.', '');

            $return_url_parameters = function ($return_type) use ($payment_frequency, $payment_type){
                return '&return_type=' . $return_type . '&payment_processor=stripe&payment_frequency=' . $payment_frequency . '&payment_type=' . $payment_type;
            };

            /* Initiate paypal */
            $paypal = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET_KEY')));
            $paypal->setConfig(['mode' => env('PAYPAL_MODE')]);

            switch($payment_type)
            {
                case 'one-time':

                    /* Payment experience */ $flowConfig = new FlowConfig();
                    $flowConfig->setLandingPageType('Billing');
                    $flowConfig->setUserAction('commit');
                    $flowConfig->setReturnUriHttpMethod('GET');

                    $presentation = new Presentation();
                    $presentation->setBrandName(env('APP_NAME'));

                    $inputFields = new InputFields();
                    $inputFields->setAllowNote(true)->setNoShipping(1)->setAddressOverride(0);

                    $webProfile = new WebProfile();
                    $webProfile->setName(env('APP_NAME') . uniqid())->setFlowConfig($flowConfig)->setPresentation($presentation)->setInputFields($inputFields)->setTemporary(true);

                    /* Create the experience profile */
                    try
                    {
                        $createdProfileResponse = $webProfile->create($paypal);
                    }
                    catch(\Exception $exception)
                    {
                        \Log::debug($exception->getMessage());
                    }

                    $payer = new Payer();
                    $payer->setPaymentMethod('paypal');

                    $item = new Item();
                    $item->setName($product)->setCurrency(env('CURRENCY_CODE'))->setQuantity(1)->setPrice($price);

                    $itemList = new ItemList();
                    $itemList->setItems([$item]);

                    $amount = new Amount();
                    $amount->setCurrency(env('CURRENCY_CODE'))->setTotal($price);

                    $transaction = new Transaction();
                    $transaction->setAmount($amount)->setItemList($itemList)->setInvoiceNumber(uniqid());

                    $redirectUrls = new RedirectUrls();

                    $redirectUrls->setReturnUrl(
                        route(
                            'paypal.payment.status', [
                                                       'plan_id' => $myPlan->id,
                                                       $return_url_parameters('success'),
                                                   ]
                        )
                    )->setCancelUrl(
                        route(
                            'paypal.payment.status', [
                                                       'plan_id' => $myPlan->id,
                                                       $return_url_parameters('cancel'),
                                                   ]
                        )
                    );

                    $payment = new Payment();
                    $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions([$transaction])->setExperienceProfileId($createdProfileResponse->getId());

                    try
                    {
                        $payment->create($paypal);
                    }
                    catch(\Exception $exception)
                    {
                        \Log::debug($exception->getMessage());
                    }

                    $payment_url = $payment->getApprovalLink();

                    header('Location: ' . $payment_url);
                    exit;

                    break;

                case 'recurring':

                    $plan = new \PayPal\Api\Plan();
                    $plan->setName($product)->setDescription($product)->setType('fixed');

                    /* Set billing plan definitions */
                    $payment_definition = new PaymentDefinition();
                    $payment_definition->setName('Regular Payments')->setType('REGULAR')->setFrequency($payment_frequency == 'monthly' ? 'Month' : 'Year')->setFrequencyInterval('1')->setCycles($payment_frequency == 'monthly' ? '60' : '5')->setAmount(
                        new Currency(
                            [
                                'value' => $price,
                                'currency' => env('CURRENCY_CODE'),
                            ]
                        )
                    );

                    /* Set merchant preferences */
                    $merchant_preferences = new MerchantPreferences();
                    $merchant_preferences->setReturnUrl(
                        route(
                            'paypal.payment.status', [
                                                       'plan_id' => $myPlan->id,
                                                       $return_url_parameters('success'),
                                                   ]
                        )
                    )->setCancelUrl(
                        route(
                            'paypal.payment.status', [
                                                       'plan_id' => $myPlan->id,
                                                       $return_url_parameters('cancel'),
                                                   ]
                        )
                    )->setAutoBillAmount('yes')->setInitialFailAmountAction('CONTINUE')->setMaxFailAttempts('0')->setSetupFee(
                        new Currency(
                            [
                                'value' => $price,
                                'currency' => env('CURRENCY_CODE'),
                            ]
                        )
                    );

                    $plan->setPaymentDefinitions([$payment_definition]);
                    $plan->setMerchantPreferences($merchant_preferences);

                    /* Create the plan */
                    try
                    {
                        $plan = $plan->create($paypal);
                    }
                    catch(\Exception $exception)
                    {
                        \Log::debug('1');
                        \Log::debug($exception->getMessage());
                    }

                    /* Make sure to activate the plan */
                    try
                    {
                        $patch = new Patch();
                        $value = new PayPalModel('{"state":"ACTIVE"}');
                        $patch->setOp('replace')->setPath('/')->setValue($value);
                        $patchRequest = new PatchRequest();
                        $patchRequest->addPatch($patch);
                        $plan->update($patchRequest, $paypal);
                        $plan = Plan::get($plan->getId(), $paypal);
                    }
                    catch(\Exception $exception)
                    {
                        \Log::debug('2');
                        \Log::debug($exception->getMessage());
                    }

                    /* Start creating the agreement */
                    $agreement = new Agreement();
                    $agreement->setName($product)->setDescription($authuser->id . '###' . $myPlan->id . '###' . $payment_frequency . '###' . $code . '###' . time())->setStartDate((new \DateTime())->modify($payment_frequency == 'monthly' ? '+30 days' : '+1 year')->format(DATE_ISO8601));

                    /* Set the plan id to the agreement */
                    $agreement_plan = new Plan();
                    $agreement_plan->setId($plan->getId());
                    $agreement->setPlan($agreement_plan);

                    /* Add Payer */
                    $payer = new Payer();
                    $payer->setPaymentMethod('paypal');
                    $agreement->setPayer($payer);

                    /* Create the agreement */
                    try
                    {
                        $agreement = $agreement->create($paypal);
                    }
                    catch(\Exception $exception)
                    {
                        \Log::debug('3');
                        \Log::debug($exception->getMessage());
                    }

                    $payment_url = $agreement->getApprovalLink();

                    header('Location: ' . $payment_url);
                    exit;

                    break;
            }
        }
        catch(\Exception $e)
        {
            \Log::debug($e->getMessage());
        }
    }

    public function planGetPaypalPaymentStatus(Request $request)
    {
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

    public function webhookPaypal(Request $request)
    {
        try
        {
            $payload = @file_get_contents('php://input');
            $data    = json_decode($payload);

            if($payload && $data && $data->event_type == 'PAYMENT.SALE.COMPLETED')
            {

                /* Initiate paypal */
                $paypal = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET_KEY')));
                $paypal->setConfig(['mode' => env('PAYPAL_MODE')]);

                /* Get the billing agreement */
                try
                {
                    $agreement = \PayPal\Api\Agreement::get($data->resource->billing_agreement_id, $paypal);
                }
                catch(\Exception $exception)
                {
                    \Log::debug($exception->getMessage());
                    http_response_code(400);
                }

                /* Get the needed details for the processing */
                $payer_info      = $agreement->getPayer()->getPayerInfo();
                $payer_email     = $payer_info->getEmail();
                $payer_name      = $payer_info->getFirstName() . ' ' . $payer_info->getLastName();
                $payer_id        = $payer_info->getPayerId();
                $subscription_id = $agreement->getId();

                $payment_id       = $data->resource->id;
                $payment_total    = $data->resource->amount->total;
                $payment_currency = $data->resource->amount->currency;

                $extra = explode('###', $agreement->getDescription());

                $user_id                 = (int)$extra[0];
                $package_id              = (int)$extra[1];
                $payment_frequency       = $extra[2];
                $code                    = isset($extra[4]) ? $extra[3] : '';
                $payment_type            = 'recurring';
                $payment_subscription_id = 'paypal###' . $subscription_id;

                $plan = DB::table('plans')->where('id', $package_id)->first();
                if(!$plan)
                {
                    http_response_code(400);
                    die();
                }

                // COMMENTED BECAUSE PRICES OF A PLAN MIGHT CHANGE BUT YOU STILL HAVE TO ACCEPT PAYMENTS FROM OLDER PRICES
                /* Make sure the paid amount equals to the current price of the plan */ //            if($package->{$payment_frequency . '_price'} != $payment_total) {
                //                http_response_code(400);
                //                die();
                //            }

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
                        'subscription_id' => $subscription_id,
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
                        'payment_type' => 'PAYPAL',
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

                $user->payment_subscription_id = $subscription_id;
                $user->save();

                http_response_code(200);
            }

            die();

        }
        catch(\Exception $exception)
        {
            \Log::debug($exception->getMessage());
            http_response_code(400);
        }
    }
}
