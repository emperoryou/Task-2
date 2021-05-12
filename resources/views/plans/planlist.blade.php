<div class="row">
    @foreach ($plans as $key => $plan)
        @if($plan->status)
            <div class="col-xs-12 col-sm-12 col-md-{{$size}} col-lg-{{$size}} mt-4">
                <div class="card card-pricing popular text-center px-3 mb-5 mb-lg-0">
                    <span class="h6 w-60 mx-auto px-4 py-1 rounded-bottom bg-primary text-white">{{ $plan->name }}</span>
                    <div class="card-body delimiter-top">
                        <ul class="list-unstyled mb-4">
                            @if($plan->trial_days == 0)
                                <li>{{ __('Trial') }} : {{$plan->trial_days}} {{ __('Days') }}</li>
                            @endif
                            <li>{{ __('Monthly Price') }}: {{(env('CURRENCY') ? env('CURRENCY') : '$')}}{{$plan->monthly_price}}</li>
                            <li>{{ __('Annual Price') }}: {{(env('CURRENCY') ? env('CURRENCY') : '$')}}{{$plan->annual_price}}</li>
                            @if($plan->max_users != 0)
                                <li>{{ ($plan->max_users < 0)?__('Unlimited'):$plan->max_users }} {{__('Users')}}</li>
                            @endif
                            @if($plan->max_projects != 0)
                                <li>{{ ($plan->max_projects < 0)?__('Unlimited'):$plan->max_projects }} {{__('Projects')}}</li>
                            @endif
                            @if($plan->description)
                                <li>
                                    <small>{{$plan->description}}</small>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="card-footer delimiter-top">
                        <div class="row justify-content-center">
                            <?php $planStatus = true; ?>
                            @if(Auth::user()->type != 'client' && Auth::user()->plan != '' && \Auth::user()->plan == $plan->id && date('Y-m-d') < \Auth::user()->plan_expire_date && (Auth::user()->is_plan_purchased == 0 || Auth::user()->plan_expire_date < date('Y-m-d')) )
                                <button class="btn btn-sm btn-neutral mb-3">
                                    <a>
                                        @if(Auth::user()->is_trial_done && Auth::user()->is_plan_purchased == 0)
                                            @if(Auth::user()->plan_expire_date < date('Y-m-d'))
                                                {{__('Trial Expired : ')}}
                                            @else
                                                {{__('Trial Expire on : ')}}
                                            @endif
                                            <?php $planStatus = false; ?>
                                        @elseif(Auth::user()->plan != '' && Auth::user()->plan_expire_date < date('Y-m-d'))
                                            {{__('Plan Expired : ')}}
                                            <?php $planStatus = false; ?>
                                        @endif
                                        @if($planStatus)
                                            {{__('Plan Expire on : ')}}
                                        @endif
                                        {{ (date('d M Y',strtotime(\Auth::user()->plan_expire_date))) }}
                                    </a>
                                </button>
                                @if(Auth::user()->plan != $plan->id)
                                    @if(((env('ENABLE_STRIPE') == 'on' && !empty(env('STRIPE_KEY')) && !empty(env('STRIPE_SECRET'))) || (env('ENABLE_PAYPAL') == 'on' && !empty(env('PAYPAL_CLIENT_ID')) && !empty(env('PAYPAL_SECRET_KEY')))))
                                        <div class="col-auto mb-2">
                                            <a href="{{route('payment',\Illuminate\Support\Facades\Crypt::encrypt($plan->id))}}" id="interested_plan_{{ $plan->id }}" class="btn btn-xs btn-primary btn-icon rounded-pill">
                                                <span class="btn-inner--icon"><i class="fas fa-cart-plus"></i></span>
                                                <span class="btn-inner--text">{{ __('Subscribe') }}</span>
                                            </a>
                                        </div>
                                    @endif
                                @endif
                            @elseif(\Auth::user()->plan == $plan->id && date('Y-m-d') < \Auth::user()->plan_expire_date)
                                <button class="btn btn-sm btn-neutral mb-3">
                                    <a>{{__('Expire on ')}} {{ (date('d M Y',strtotime(\Auth::user()->plan_expire_date))) }}</a>
                                </button>
                            @elseif(((env('ENABLE_STRIPE') == 'on' && !empty(env('STRIPE_KEY')) && !empty(env('STRIPE_SECRET'))) || (env('ENABLE_PAYPAL') == 'on' && !empty(env('PAYPAL_CLIENT_ID')) && !empty(env('PAYPAL_SECRET_KEY')))))
                                @if(Auth::user()->is_trial_done == 0)
                                    <div class="col-auto mb-2">
                                        <a href="{{route('take.a.plan.trial',$plan->id)}}" id="trial_{{ $plan->id }}" class="btn btn-xs btn-primary btn-icon rounded-pill">
                                            <span class="btn-inner--icon"><i class="fas fa-cart-plus"></i></span>
                                            <span class="btn-inner--text">{{ __('Take a Trial') }}</span>
                                        </a>
                                    </div>
                                @endif
                                <div class="col-auto mb-2">
                                    <a href="{{route('payment',\Illuminate\Support\Facades\Crypt::encrypt($plan->id))}}" id="interested_plan_{{ $plan->id }}" class="btn btn-xs btn-primary btn-icon rounded-pill">
                                        <span class="btn-inner--icon"><i class="fas fa-cart-plus"></i></span>
                                        <span class="btn-inner--text">{{ __('Subscribe') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
