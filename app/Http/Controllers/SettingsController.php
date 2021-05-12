<?php

namespace App\Http\Controllers;

use App\Mail\EmailTest;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        if(Auth::user()->type == 'admin')
        {
            return view('users.setting');
        }
        else
        {
            $details = Auth::user()->decodeDetails();

            return view('users.owner_setting', compact('details'));
        }
    }

    public function store(Request $request)
    {
        $usr = Auth::user();
        if($usr->type == 'admin')
        {
            $validate = [];

            if($request->from == 'mail')
            {
                $validate = [
                    'mail_driver' => 'required|string',
                    'mail_host' => 'required|string',
                    'mail_port' => 'required|string',
                    'mail_username' => 'required|string',
                    'mail_password' => 'required|string',
                    'mail_from_address' => 'required|string',
                    'mail_from_name' => 'required|string',
                    'mail_encryption' => 'required|string',
                ];
            }
            elseif($request->from == 'payment')
            {
                $validate = [
                    'currency' => 'required|max:3',
                    'currency_code' => 'required|string|max:5',
                ];

                if(isset($request->enable_stripe) && $request->enable_stripe = 'on')
                {
                    $validate['stripe_key']    = 'required|string';
                    $validate['stripe_secret'] = 'required|string';
                }

                if(isset($request->enable_paypal) && $request->enable_paypal = 'on')
                {
                    $validate['paypal_client_id']  = 'required|string';
                    $validate['paypal_secret_key'] = 'required|string';
                }
            }
            elseif($request->from == 'pusher')
            {
                $validate = [
                    'pusher_app_id' => 'required|string',
                    'pusher_app_key' => 'required|string',
                    'pusher_app_secret' => 'required|string',
                    'pusher_app_cluster' => 'required|string',
                ];
            }

            $validator = Validator::make(
                $request->all(), $validate
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            if($request->favicon)
            {
                $request->validate(['favicon' => 'required|image|mimes:png|max:2048']);
                $faviconName = 'favicon.png';
                $request->favicon->storeAs('logo', $faviconName);
            }
            if($request->full_logo)
            {
                $request->validate(['full_logo' => 'required|image|mimes:png|max:2048']);
                $logoName = 'logo.png';
                $request->full_logo->storeAs('logo', $logoName);
            }

            if($request->from == 'site_setting')
            {
                $post = $request->all();
                unset($post['_token'], $post['full_logo'], $post['favicon'], $post['from']);

                $post['header_text']    = (!isset($request->header_text) && empty($request->header_text)) ? '' : $request->header_text;
                $post['footer_text']    = (!isset($request->footer_text) && empty($request->footer_text)) ? '' : $request->footer_text;
                $post['enable_landing'] = isset($request->enable_landing) ? $request->enable_landing : 'off';
                $created_at             = date('Y-m-d H:i:s');
                $updated_at             = date('Y-m-d H:i:s');

                foreach($post as $key => $data)
                {
                    \DB::insert(
                        'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ', [
                                                                                                                                                                                                                          $data,
                                                                                                                                                                                                                          $key,
                                                                                                                                                                                                                          $usr->id,
                                                                                                                                                                                                                          $created_at,
                                                                                                                                                                                                                          $updated_at,
                                                                                                                                                                                                                      ]
                    );
                }

                return redirect()->back()->with('success', __('Basic Setting updated successfully'));
            }

            if($request->from == 'mail')
            {
                $arrEnv = [
                    'MAIL_DRIVER' => $request->mail_driver,
                    'MAIL_HOST' => $request->mail_host,
                    'MAIL_PORT' => $request->mail_port,
                    'MAIL_USERNAME' => $request->mail_username,
                    'MAIL_PASSWORD' => $request->mail_password,
                    'MAIL_ENCRYPTION' => $request->mail_encryption,
                    'MAIL_FROM_ADDRESS' => $request->mail_from_address,
                    'MAIL_FROM_NAME' => $request->mail_from_name,
                ];

                $env = Utility::setEnvironmentValue($arrEnv);

                if($env)
                {
                    return redirect()->back()->with('success', __('Mailer Setting updated successfully'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Something is wrong'));
                }
            }

            if($request->from == 'pusher')
            {
                $arrEnv = [
                    'PUSHER_APP_ID' => $request->pusher_app_id,
                    'PUSHER_APP_KEY' => $request->pusher_app_key,
                    'PUSHER_APP_SECRET' => $request->pusher_app_secret,
                    'PUSHER_APP_CLUSTER' => $request->pusher_app_cluster,
                ];

                $env = Utility::setEnvironmentValue($arrEnv);

                if($env)
                {
                    return redirect()->back()->with('success', __('Pusher Setting updated successfully'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Something is wrong'));
                }
            }

            if($request->from == 'payment')
            {
                $arrEnv = [
                    'CURRENCY' => $request->currency,
                    'CURRENCY_CODE' => $request->currency_code,
                ];

                if(isset($request->enable_stripe) && $request->enable_stripe = 'on')
                {
                    $arrEnv['ENABLE_STRIPE']         = $request->enable_stripe;
                    $arrEnv['STRIPE_KEY']            = $request->stripe_key;
                    $arrEnv['STRIPE_SECRET']         = $request->stripe_secret;
                    $arrEnv['STRIPE_WEBHOOK_SECRET'] = $request->stripe_webhook_secret;
                }
                else
                {
                    $arrEnv['ENABLE_STRIPE'] = 'off';
                }

                if(isset($request->enable_paypal) && $request->enable_paypal = 'on')
                {
                    $arrEnv['ENABLE_PAYPAL']     = $request->enable_paypal;
                    $arrEnv['PAYPAL_MODE']       = $request->paypal_mode;
                    $arrEnv['PAYPAL_CLIENT_ID']  = $request->paypal_client_id;
                    $arrEnv['PAYPAL_SECRET_KEY'] = $request->paypal_secret_key;
                }
                else
                {
                    $arrEnv['ENABLE_PAYPAL'] = 'off';
                }

                $env = Utility::setEnvironmentValue($arrEnv);

                if($env)
                {
                    return redirect()->back()->with('success', __('Payment Setting updated successfully'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Something is wrong'));
                }
            }
        }
        else
        {
            $details = $usr->decodeDetails();
            if($request->from == 'invoice_setting')
            {
                if($request->light_logo)
                {
                    $request->validate(['light_logo' => 'required|image|mimes:png|max:2048']);
                    if(!empty($details['light_logo']) && $details['light_logo'] != 'logo/logo.png')
                    {
                        Utility::checkFileExistsnDelete([$details['light_logo']]);
                    }
                    $light_logo = $usr->id . '_light' . time() . '.' . $request->light_logo->getClientOriginalExtension();
                    $request->light_logo->storeAs('invoice_logo', $light_logo);
                    $details['light_logo'] = 'invoice_logo/' . $light_logo;
                }

                if($request->dark_logo)
                {
                    $request->validate(['dark_logo' => 'required|image|mimes:png|max:2048']);
                    if(!empty($details['dark_logo']) && $details['dark_logo'] != 'logo/logo.png')
                    {
                        Utility::checkFileExistsnDelete([$details['dark_logo']]);
                    }
                    $dark_logo = $usr->id . '_dark' . time() . '.' . $request->dark_logo->getClientOriginalExtension();
                    $request->dark_logo->storeAs('invoice_logo', $dark_logo);
                    $details['dark_logo'] = 'invoice_logo/' . $dark_logo;
                }

                $details['invoice_footer_title'] = (!empty($request->invoice_footer_title)) ? $request->invoice_footer_title : '';
                $details['invoice_footer_note']  = (!empty($request->invoice_footer_note)) ? $request->invoice_footer_note : '';

                $usr->details = json_encode($details);
                $usr->save();

                return redirect()->route('settings')->with('success', __('Invoice Setting successfully updated!'));
            }
            elseif($request->from == 'billing_setting')
            {
                $validator = Validator::make(
                    $request->all(), [
                                       'address' => 'required',
                                       'city' => 'required',
                                       'state' => 'required',
                                       'zipcode' => 'required',
                                       'country' => 'required',
                                       'telephone' => 'required|numeric',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('settings')->with('error', $messages->first());
                }

                $post = $request->all();
                unset($post['_token'], $post['from']);

                foreach($post as $key => $data)
                {
                    $details[$key] = $data;
                }

                $usr->details = json_encode($details);
                $usr->save();

                return redirect()->route('settings')->with('success', __('Billing Setting successfully updated!'));
            }
            elseif($request->from == 'payment')
            {
                $validate = [];

                if(isset($request->enable_stripe) && $request->enable_stripe = 'on')
                {
                    $validate['stripe_key']    = 'required|string';
                    $validate['stripe_secret'] = 'required|string';
                }

                if(isset($request->enable_paypal) && $request->enable_paypal = 'on')
                {
                    $validate['paypal_client_id']  = 'required|string';
                    $validate['paypal_secret_key'] = 'required|string';
                }

                $validator = Validator::make(
                    $request->all(), $validate
                );

                if($validator->fails())
                {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                if(isset($request->enable_stripe) && $request->enable_stripe = 'on')
                {
                    $details['enable_stripe'] = $request->enable_stripe;
                    $details['stripe_key']    = $request->stripe_key;
                    $details['stripe_secret'] = $request->stripe_secret;
                }
                else
                {
                    $details['enable_stripe'] = 'off';
                }

                if(isset($request->enable_paypal) && $request->enable_paypal = 'on')
                {
                    $details['enable_paypal']     = $request->enable_paypal;
                    $details['paypal_mode']       = $request->paypal_mode;
                    $details['paypal_client_id']  = $request->paypal_client_id;
                    $details['paypal_secret_key'] = $request->paypal_secret_key;
                }
                else
                {
                    $details['enable_paypal'] = 'off';
                }

                $usr->details = json_encode($details);
                $usr->save();

                return redirect()->route('settings')->with('success', __('Payment Setting successfully updated!'));
            }
        }
    }

    public function testEmail(Request $request)
    {
        $user = Auth::user();
        if($user->type == 'admin')
        {
            $data                      = [];
            $data['mail_driver']       = $request->mail_driver;
            $data['mail_host']         = $request->mail_host;
            $data['mail_port']         = $request->mail_port;
            $data['mail_username']     = $request->mail_username;
            $data['mail_password']     = $request->mail_password;
            $data['mail_encryption']   = $request->mail_encryption;
            $data['mail_from_address'] = $request->mail_from_address;
            $data['mail_from_name']    = $request->mail_from_name;

            return view('users.test_email', compact('data'));
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function testEmailSend(Request $request)
    {
        if(Auth::user()->type == 'admin')
        {
            $validator = Validator::make(
                $request->all(), [
                                   'email' => 'required|email',
                                   'mail_driver' => 'required',
                                   'mail_host' => 'required',
                                   'mail_port' => 'required',
                                   'mail_username' => 'required',
                                   'mail_password' => 'required',
                                   'mail_from_address' => 'required',
                                   'mail_from_name' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            try
            {
                config(
                    [
                        'mail.driver' => $request->mail_driver,
                        'mail.host' => $request->mail_host,
                        'mail.port' => $request->mail_port,
                        'mail.encryption' => $request->mail_encryption,
                        'mail.username' => $request->mail_username,
                        'mail.password' => $request->mail_password,
                        // 'mail.from.address' => $request->mail_username,
                        // 'mail.from.name' => config('name'),
                        'mail.from.address' => $request->mail_from_address,
                        'mail.from.name' => $request->mail_from_name,
                    ]
                );
                Mail::to($request->email)->send(new EmailTest());
            }
            catch(\Exception $e)
            {
                return response()->json(
                    [
                        'is_success' => false,
                        'message' => $e->getMessage(),
                    ]
                );
                //            return redirect()->back()->with('error', 'Something is Wrong.');
            }

            return response()->json(
                [
                    'is_success' => true,
                    'message' => __('Email send Successfully'),
                ]
            );
        }
        else
        {
            return response()->json(
                [
                    'is_success' => false,
                    'message' => __('Permission Denied.'),
                ]
            );
        }
    }
}
