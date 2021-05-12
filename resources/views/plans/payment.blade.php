@extends('layouts.admin')

@section('title')
    {{__('Buy Plan')}}
@endsection

@push('css')
    <style>
        #card-element {
            border: 1px solid #e4e6fc;
            border-radius: 5px;
            padding: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="float-right">
        <a href="{{ route('change.user.plan', $plan->id) }}" class="btn btn-sm btn-white btn-icon rounded-pill shadow hover-translate-y-n3">
            <span class="btn-inner--icon"><i class="fas fa-coins"></i></span>
            <span class="btn-inner--text">{{ __('Change Plan') }}</span>
        </a>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-center mt-5">
            <div class="col-xl-12">
                <div class="card payment-card">
                    <div class="card-body">

                        <form role="form" action="{{ route('prepare.payment') }}" method="post" class="require-validation" id="payment-form">
                            @csrf
                            <input type="hidden" name="code" value="{{\Illuminate\Support\Facades\Crypt::encrypt($plan->id)}}">

                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-xl btn-primary active">
                                    <input type="radio" name="payment_frequency" value="monthly" data-price="{{(env('CURRENCY') ? env('CURRENCY') : '$') . $plan->monthly_price }}" autocomplete="off" checked="">{{ __('Monthly Payments') }}<br>
                                    {{(env('CURRENCY') ? env('CURRENCY') : '$') . $plan->monthly_price }}
                                </label>
                                <label class="btn btn-xl btn-primary">
                                    <input type="radio" name="payment_frequency" value="annual" data-price="{{(env('CURRENCY') ? env('CURRENCY') : '$') . $plan->annual_price }}" autocomplete="off">{{ __('Annual Payments') }}<br>
                                    {{(env('CURRENCY') ? env('CURRENCY') : '$') . $plan->annual_price }}
                                </label>
                            </div>

                            <div class="btn-group btn-group-toggle mt-5" data-toggle="buttons">
                                @if((env('ENABLE_PAYPAL') == 'on' && !empty(env('PAYPAL_CLIENT_ID')) && !empty(env('PAYPAL_SECRET_KEY'))))
                                    <label class="btn btn-xl btn-primary {{ ((env('ENABLE_STRIPE') == 'off' && env('ENABLE_PAYPAL') == 'on') || env('ENABLE_STRIPE') == 'on') ? "active" : "" }}">
                                        <input type="radio" name="payment_processor" value="paypal" autocomplete="off" {{ ((env('ENABLE_STRIPE') == 'on' && env('ENABLE_PAYPAL') == 'on') || env('ENABLE_STRIPE') == 'on') ? "checked" : "" }}>
                                        {{ __('Paypal') }}
                                    </label>
                                @endif
                                @if((env('ENABLE_STRIPE') == 'on' && !empty(env('STRIPE_KEY')) && !empty(env('STRIPE_SECRET'))))
                                    <label class="btn btn-xl btn-primary {{ (env('ENABLE_STRIPE') == 'on' && env('ENABLE_PAYPAL') == 'off') ? "active" : "" }}">
                                        <input type="radio" name="payment_processor" value="stripe" autocomplete="off" {{ (env('ENABLE_STRIPE') == 'off' && env('ENABLE_PAYPAL') == 'on') ? "checked" : "" }}>
                                        {{ __('Stripe') }}
                                    </label>
                                @endif
                            </div>

                            <div class="btn-group btn-group-toggle mt-5" data-toggle="buttons">
                                <label class="btn btn-xl btn-primary active">
                                    <input type="radio" name="payment_type" id="one_time_type" value="one-time" autocomplete="off" checked="">
                                    {{ __('One Time') }}
                                </label>
                                <label class="btn btn-xl btn-primary">
                                    <input type="radio" name="payment_type" id="recurring_type" value="recurring" autocomplete="off">
                                    {{ __('Reccuring') }}
                                </label>
                            </div>

                            <div class="form-group mt-5">
                                <input type="text" id="coupon" name="coupon" class="form-control" placeholder="{{__('Enter Coupon Code Here')}}">
                            </div>
                            <button class="btn btn-primary btn-sm rounded-pill d-flex my-4 px-5 float-right" type="submit">
                                <i class="mdi mdi-cash-multiple mr-1"></i> {{__('Checkout')}} (<span class="final-price"></span>)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <?php $stripe_session = Session::get('stripe_session');?>

    <?php if(isset($stripe_session) && $stripe_session): ?>
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        var stripe = Stripe('{{ env('STRIPE_KEY') }}');
        stripe.redirectToCheckout({
            sessionId: '{{ $stripe_session->id }}',
        }).then((result) => {
            // console.log(result);
        });
    </script>
    <?php endif ?>

    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">

        $(document).on('change', 'input[name="payment_frequency"], input[name="payment_type"]', function (e) {
            var price = $('input[name="payment_frequency"]:checked').attr('data-price');
            var frequency = $('input[name="payment_frequency"]:checked').val();
            var type = $('input[name="payment_type"]:checked').val();

            var total = per = '';

            if (frequency == 'monthly') {
                var per = '/month';
                $('#recurring_type').parent().show();
            } else if (frequency == 'annual') {
                var per = '/year';
                $('#recurring_type').parent().show();
            }

            if (type == 'recurring') {
                var total = price + per;
            } else if (type == 'one-time') {
                var total = price;
            }
            $('.final-price').text(total);

        });

        $('input[name="payment_frequency"]:first').trigger('change');

        // Apply Coupon
        $(document).on('click', '.apply-coupon', function (e) {
            e.preventDefault();

            var ele = $(this);
            var coupon = ele.closest('.row').find('.coupon').val();

            if (coupon != '') {
                $.ajax({
                    url: '{{route('apply.coupon')}}',
                    datType: 'json',
                    data: {
                        plan_id: '{{ $plan->id }}',
                        coupon: coupon
                    },
                    success: function (data) {
                        $('#stripe_coupon, #paypal_coupon').val(coupon);
                        if (data.is_success) {
                            $('.coupon-tr').show().find('.coupon-price').text(data.discount_price);
                            $('.final-price').text(data.final_price);
                            show_toastr('Success', data.message, 'success');
                        } else {
                            $('.coupon-tr').hide().find('.coupon-price').text('');
                            $('.final-price').text(data.final_price);
                            show_toastr('Error', data.message, 'error');
                        }
                    }
                })
            } else {
                show_toastr('Error', '{{__('Invalid Coupon Code.')}}', 'error');
            }
        });

    </script>
@endpush
