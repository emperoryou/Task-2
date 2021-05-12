@extends('layouts.admin')

@section('title')
    {{__('Site Settings')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 order-lg-2">
            <div class="card">
                <div class="list-group list-group-flush" id="tabs">
                    <div data-href="#tabs-1" class="list-group-item text-primary">
                        <div class="media">
                            <i class="fas fa-cog pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('Site Setting')}}</a>
                                <p class="mb-0 text-sm">{{__('Details about your personal information')}}</p>
                            </div>
                        </div>
                    </div>
                    <div data-href="#tabs-2" class="list-group-item">
                        <div class="media">
                            <i class="fas fa-envelope pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('Mailer Settings')}}</a>
                                <p class="mb-0 text-sm">{{__('Details about your mail setting information')}}</p>
                            </div>
                        </div>
                    </div>
                    <div data-href="#tabs-4" class="list-group-item">
                        <div class="media">
                            <i class="fas fa-comments pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('Pusher Settings')}}</a>
                                <p class="mb-0 text-sm">{{__('Details about your pusher setting information for chat')}}</p>
                            </div>
                        </div>
                    </div>
                    <div data-href="#tabs-3" class="list-group-item">
                        <div class="media">
                            <i class="fas fa-money-check-alt pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('Payment Settings')}}</a>
                                <p class="mb-0 text-sm">{{__('Details about your Payment setting information')}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 order-lg-1">
            <div id="tabs-1" class="tabs-card">
                <div class="card">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{__('Basic Setting')}}</h5>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['settings.store'],'id' => 'update_setting','enctype' => 'multipart/form-data']) }}
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('full_logo', __('Logo'),['class' => 'form-control-label']) }}
                                    <input type="file" name="full_logo" id="full_logo" class="custom-input-file"/>
                                    <label for="full_logo">
                                        <i class="fa fa-upload"></i>
                                        <span>{{__('Choose a file…')}}</span>
                                    </label>
                                    @error('full_logo')
                                    <span class="full_logo" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 pt-5">
                                <img src="{{ asset(Storage::url('logo/logo.png')) }}" class="img_setting"/>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('favicon', __('Favicon'),['class' => 'form-control-label']) }}
                                    <input type="file" name="favicon" id="favicon" class="custom-input-file"/>
                                    <label for="favicon">
                                        <i class="fa fa-upload"></i>
                                        <span>{{__('Choose a file…')}}</span>
                                    </label>
                                    @error('favicon')
                                    <span class="favicon" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 pt-5">
                                <img src="{{ asset(Storage::url('logo/favicon.png')) }}" class="img_setting"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('header_text', __('Title Text'),['class' => 'form-control-label']) }}
                                    {{ Form::text('header_text', \App\Utility::getValByName('header_text'), ['class' => 'form-control','placeholder' => __('Enter Header Title Text')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('footer_text', __('Footer Text'),['class' => 'form-control-label']) }}
                                    {{ Form::text('footer_text', \App\Utility::getValByName('footer_text'), ['class' => 'form-control','placeholder' => __('Enter Footer Text')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{Form::label('default_language',__('Default Language'),['class'=>'form-control-label text-dark']) }}
                                    <select name="default_language" id="default_language" class="form-control select2">
                                        @foreach(Utility::languages() as $language)
                                            <option @if(Utility::getValByName('default_language') == $language) selected @endif value="{{$language}}">{{Str::upper($language)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 mt-lg-5">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" name="enable_landing" id="enable_landing" {{ (\App\Utility::getValByName('enable_landing') == 'on') ? 'checked' : '' }}>
                                    <label class="custom-control-label form-control-label" for="enable_landing">{{__('Enable Landing Page')}}</label>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('footer_link_1', __('Footer Link Title 1'),['class' => 'form-control-label']) }}
                                    {{ Form::text('footer_link_1', \App\Utility::getValByName('footer_link_1'), ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Footer Link Title 1')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('footer_value_1', __('Footer Link href 1'),['class' => 'form-control-label']) }}
                                    {{ Form::text('footer_value_1', \App\Utility::getValByName('footer_value_1'), ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Footer Link 1')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('footer_link_2', __('Footer Link Title 2'),['class' => 'form-control-label']) }}
                                    {{ Form::text('footer_link_2', \App\Utility::getValByName('footer_link_2'), ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Footer Link Title 2')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('footer_value_2', __('Footer Link href 2'),['class' => 'form-control-label']) }}
                                    {{ Form::text('footer_value_2', \App\Utility::getValByName('footer_value_2'), ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Footer Link 2')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('footer_link_3', __('Footer Link Title 3'),['class' => 'form-control-label']) }}
                                    {{ Form::text('footer_link_3', \App\Utility::getValByName('footer_link_3'), ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Footer Link Title 3')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('footer_value_3', __('Footer Link href 3'),['class' => 'form-control-label']) }}
                                    {{ Form::text('footer_value_3', \App\Utility::getValByName('footer_value_3'), ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Footer Link 3')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            {{ Form::hidden('from','site_setting') }}
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{__('Save changes')}}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <div id="tabs-2" class="tabs-card d-none">
                <div class="card">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{__('Mailer Settings')}}</h5>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['settings.store'],'id' => 'update_setting']) }}
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_driver', __('Mail Driver'),['class' => 'form-control-label']) }}
                                    {{ Form::text('mail_driver', env('MAIL_DRIVER'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail Driver')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_host', __('Mail Host'),['class' => 'form-control-label']) }}
                                    {{ Form::text('mail_host', env('MAIL_HOST'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail Host')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_port', __('Mail Port'),['class' => 'form-control-label']) }}
                                    {{ Form::number('mail_port', env('MAIL_PORT'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail Port'),'min' => '0']) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_username', __('Mail Username'),['class' => 'form-control-label']) }}
                                    {{ Form::text('mail_username', env('MAIL_USERNAME'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail Username')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_password', __('Mail Password'),['class' => 'form-control-label']) }}
                                    {{ Form::text('mail_password', env('MAIL_PASSWORD'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail Password')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_encryption', __('Mail Encryption'),['class' => 'form-control-label']) }}
                                    {{ Form::text('mail_encryption', env('MAIL_ENCRYPTION'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail Encryption')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_from_address', __('Mail From Address'),['class' => 'form-control-label']) }}
                                    {{ Form::text('mail_from_address', env('MAIL_FROM_ADDRESS'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail From Address')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('mail_from_name', __('Mail From Name'),['class' => 'form-control-label']) }}
                                    {{ Form::text('mail_from_name', env('MAIL_FROM_NAME'), ['class' => 'form-control','required'=>'required','placeholder' => __('Mail From Name')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="text-left">
                                    <button type="button" class="btn btn-sm btn-warning rounded-pill send_email" data-title="{{__('Send Test Mail')}}" data-url="{{route('test.email')}}">{{__('Send Test Mail')}}</button>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="text-right">
                                    {{ Form::hidden('from','mail') }}
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{__('Save changes')}}</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <div id="tabs-4" class="tabs-card d-none">
                <div class="card">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{__('Pusher Settings')}}</h5>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['settings.store'],'id' => 'update_setting']) }}
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('pusher_app_id', __('Pusher App Id'),['class' => 'form-control-label']) }}
                                    {{ Form::text('pusher_app_id', env('PUSHER_APP_ID'), ['class' => 'form-control','required'=>'required','placeholder' => __('Pusher App Id')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('pusher_app_key', __('Pusher App Key'),['class' => 'form-control-label']) }}
                                    {{ Form::text('pusher_app_key', env('PUSHER_APP_KEY'), ['class' => 'form-control','required'=>'required','placeholder' => __('Pusher App Key')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('pusher_app_secret', __('Pusher App Secret'),['class' => 'form-control-label']) }}
                                    {{ Form::text('pusher_app_secret', env('PUSHER_APP_SECRET'), ['class' => 'form-control','required'=>'required','placeholder' => __('Pusher App Secret')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('pusher_app_cluster', __('Pusher App Cluster'),['class' => 'form-control-label']) }}
                                    {{ Form::text('pusher_app_cluster', env('PUSHER_APP_CLUSTER'), ['class' => 'form-control','required'=>'required','placeholder' => __('Pusher App Cluster')]) }}
                                </div>
                            </div>
                            <div class="col-12">
                                <small><a href="https://pusher.com/channels" target="_blank">{{__('You can Make Pusher channel Account from here and Get your App Id and Secret key')}}</a></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="text-right">
                                    {{ Form::hidden('from','pusher') }}
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{__('Save changes')}}</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <div id="tabs-3" class="tabs-card d-none">
                <div class="card">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{__('Payment Settings')}}</h5>
                        <small>{{__('This detail will use for make purchase of plan.')}}</small>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['settings.store'],'id' => 'update_setting']) }}
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('currency', __('Currency'),['class' => 'form-control-label']) }}
                                    {{ Form::text('currency', env('CURRENCY'), ['class' => 'form-control','required'=>'required','placeholder' => __('Currency')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('currency_code', __('Currency Code'),['class' => 'form-control-label']) }}
                                    {{ Form::text('currency_code', env('CURRENCY_CODE'), ['class' => 'form-control','required'=>'required','placeholder' => __('Currency Code')]) }}
                                    <small>{{__('Note : Add currency code as per three-letter ISO code.')}} <a href="https://stripe.com/docs/currencies" target="_blank">{{__('you can find out here..')}}</a></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <hr>
                            </div>
                            <div class="col-6 py-2">
                                <h5 class="h5">{{__('Stripe')}}</h5>
                            </div>
                            <div class="col-6 py-2 text-right">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" name="enable_stripe" id="enable_stripe" {{(env('ENABLE_STRIPE') == 'on') ? 'checked' : ''}}>
                                    <label class="custom-control-label form-control-label" for="enable_stripe">{{__('Enable Stripe')}}</label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('stripe_key', __('Stripe Key'),['class' => 'form-control-label']) }}
                                    {{ Form::text('stripe_key', env('STRIPE_KEY'), ['class' => 'form-control','placeholder' => __('Stripe Key')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('stripe_secret', __('Stripe Secret'),['class' => 'form-control-label']) }}
                                    {{ Form::text('stripe_secret', env('STRIPE_SECRET'), ['class' => 'form-control','placeholder' => __('Stripe Secret')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('stripe_webhook_secret', __('Stripe Webhook Secret'),['class' => 'form-control-label']) }}
                                    {{ Form::text('stripe_webhook_secret', env('STRIPE_WEBHOOK_SECRET'), ['class' => 'form-control','placeholder' => __('Stripe Webhook Secret')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <hr>
                            </div>
                            <div class="col-6 py-2">
                                <h5 class="h5">{{__('PayPal')}}</h5>
                            </div>
                            <div class="col-6 py-2 text-right">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" name="enable_paypal" id="enable_paypal" {{(env('ENABLE_PAYPAL') == 'on') ? 'checked' : ''}}>
                                    <label class="custom-control-label form-control-label" for="enable_paypal">{{__('Enable Paypal')}}</label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 pb-4">
                                <label class="paypal-label form-control-label" for="paypal_mode">{{__('Paypal Mode')}}</label> <br>
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-primary btn-sm {{ env('PAYPAL_MODE') == '' || env('PAYPAL_MODE') == 'sandbox' ? 'active' : '' }}">
                                        <input type="radio" name="paypal_mode" value="sandbox" {{ env('PAYPAL_MODE') == '' || env('PAYPAL_MODE') == 'sandbox' ? 'checked' : '' }}>{{ __('Sandbox') }}
                                    </label>
                                    <label class="btn btn-primary btn-sm {{ env('PAYPAL_MODE') == 'live' ? 'active' : '' }}">
                                        <input type="radio" name="paypal_mode" value="live" {{ env('PAYPAL_MODE') == 'live' ? 'checked' : '' }}>{{ __('Live') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('paypal_client_id', __('Client ID'),['class' => 'form-control-label']) }}
                                    {{ Form::text('paypal_client_id', env('PAYPAL_CLIENT_ID'), ['class' => 'form-control','placeholder' => __('Client ID')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('paypal_secret_key', __('Secret Key'),['class' => 'form-control-label']) }}
                                    {{ Form::text('paypal_secret_key', env('PAYPAL_SECRET_KEY'), ['class' => 'form-control','placeholder' => __('Secret Key')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="text-right">
                                    {{ Form::hidden('from','payment') }}
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{__('Save changes')}}</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        // For Sidebar Tabs
        $(document).ready(function () {
            $('.list-group-item').on('click', function () {
                var href = $(this).attr('data-href');
                $('.tabs-card').addClass('d-none');
                $(href).removeClass('d-none');
                $('#tabs .list-group-item').removeClass('text-primary');
                $(this).addClass('text-primary');
            });
        });

        // For Test Email Send
        $(document).on("click", '.send_email', function (e) {
            e.preventDefault();
            var title = $(this).attr('data-title');
            var size = 'md';
            var url = $(this).attr('data-url');
            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');

                $.post(url, {
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),
                }, function (data) {
                    $('#commonModal .modal-body').html(data);
                });
            }
        });
        $(document).on('submit', '#test_email', function (e) {
            e.preventDefault();
            $("#email_sanding").show();
            var post = $(this).serialize();
            var url = $(this).attr('action');
            $.ajax({
                type: "post",
                url: url,
                data: post,
                cache: false,
                success: function (data) {
                    if (data.is_success) {
                        show_toastr('Success', data.message, 'success');
                    } else {
                        show_toastr('Error', data.message, 'error');
                    }
                    $("#email_sanding").hide();
                }
            });
        })
    </script>
@endpush
