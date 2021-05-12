@extends('layouts.admin')

@section('title')
    {{__('Site Settings')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4 order-lg-2">
            <div class="card">
                <div class="list-group list-group-flush" id="tabs">
                    <div data-href="#tabs-1" class="list-group-item text-primary">
                        <div class="media">
                            <i class="fas fa-cog pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('Invoice Setting')}}</a>
                                <p class="mb-0 text-sm">{{__('Detail of your Invoice.')}}</p>
                            </div>
                        </div>
                    </div>
                    <div data-href="#tabs-4" class="list-group-item">
                        <div class="media">
                            <i class="fas fa-money-check-alt pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('Payment Settings')}}</a>
                                <p class="mb-0 text-sm">{{__('Details about your Payment setting information')}}</p>
                            </div>
                        </div>
                    </div>
                    <div data-href="#tabs-2" class="list-group-item">
                        <div class="media">
                            <i class="fas fa-file pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('My Billing Detail')}}</a>
                                <p class="mb-0 text-sm">{{__('This detail will show in your Invoice.')}}</p>
                            </div>
                        </div>
                    </div>
                    <div data-href="#tabs-3" class="list-group-item">
                        <div class="media">
                            <i class="fas fa-percent pt-1"></i>
                            <div class="media-body ml-3">
                                <a href="#" class="stretched-link h6 mb-1">{{__('Tax')}}</a>
                                <p class="mb-0 text-sm">{{__('You can manage your tax rate here.')}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 order-lg-1">
            <div id="tabs-1" class="tabs-card">
                <div class="card">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{__('Invoice Setting')}}</h5>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['settings.store'],'id' => 'update_setting','enctype' => 'multipart/form-data']) }}
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('light_logo', __('Light Logo'),['class' => 'form-control-label']) }}
                                    <input type="file" name="light_logo" id="light_logo" class="custom-input-file"/>
                                    <label for="light_logo">
                                        <i class="fa fa-upload"></i>
                                        <span>{{__('Choose a file…')}}</span>
                                    </label>
                                    @error('light_logo')
                                    <span class="light_logo" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 pt-5">
                                @if(!empty($details['light_logo']))
                                    <img src="{{ asset(Storage::url($details['light_logo'])) }}" class="img_setting"/>
                                @else
                                    <img src="{{ asset(Storage::url('logo/logo.png')) }}" class="img_setting"/>
                                @endif
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('dark_logo', __('Dark Logo'),['class' => 'form-control-label']) }}
                                    <input type="file" name="dark_logo" id="dark_logo" class="custom-input-file"/>
                                    <label for="dark_logo">
                                        <i class="fa fa-upload"></i>
                                        <span>{{__('Choose a file…')}}</span>
                                    </label>
                                    @error('dark_logo')
                                    <span class="dark_logo" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 pt-5">
                                @if(!empty($details['dark_logo']))
                                    <img src="{{ asset(Storage::url($details['dark_logo'])) }}" class="img_setting"/>
                                @else
                                    <img src="{{ asset(Storage::url('logo/logo.png')) }}" class="img_setting"/>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('invoice_footer_title', __('Invoice Footer Title'),['class' => 'form-control-label']) }}
                                    <input type="text" name="invoice_footer_title" id="invoice_footer_title" class="form-control" value="{{$details['invoice_footer_title']}}"/>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('invoice_footer_note', __('Invoice Footer Note'),['class' => 'form-control-label']) }}
                                    <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
                                    {{ Form::textarea('invoice_footer_note', $details['invoice_footer_note'], ['class' => 'form-control','rows' => '1','data-toggle' => 'autosize']) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 pt-3">
                                <a href="{{ route('invoice.template.setting') }}" class="btn btn-sm btn-primary rounded-pill">{{__('Invoice Template Setting')}}</a>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-right pt-3">
                                {{ Form::hidden('from','invoice_setting') }}
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{__('Save changes')}}</button>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <div id="tabs-2" class="tabs-card d-none">
                <div class="card">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{__('My Billing Detail')}}</h5>
                        <small>{{__('This detail will show in your Invoice.')}}</small>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['settings.store'],'id' => 'update_billing_setting','enctype' => 'multipart/form-data']) }}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('address', __('Address'),['class' => 'form-control-label']) }}
                                    {{ Form::text('address', $details['address'], ['class' => 'form-control','required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('city', __('City'),['class' => 'form-control-label']) }}
                                    {{ Form::text('city', $details['city'], ['class' => 'form-control','required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('state', __('State'),['class' => 'form-control-label']) }}
                                    {{ Form::text('state', $details['state'], ['class' => 'form-control','required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('zipcode', __('Zip/Post Code'),['class' => 'form-control-label']) }}
                                    {{ Form::text('zipcode', $details['zipcode'], ['class' => 'form-control','required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('country', __('Country'),['class' => 'form-control-label']) }}
                                    {{ Form::text('country', $details['country'], ['class' => 'form-control','required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('telephone', __('Telephone'),['class' => 'form-control-label']) }}
                                    {{ Form::text('telephone', $details['telephone'], ['class' => 'form-control','required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            {{ Form::hidden('from','billing_setting') }}
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{__('Save changes')}}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <div id="tabs-3" class="tabs-card d-none">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="mb-0">{{__('Tax')}}</h6>
                            </div>
                            <div class="col-auto">
                                <div class="actions">
                                    <a href="#" class="action-item" data-url="{{ route('taxes.create') }}" data-ajax-popup="true" data-size="md" data-title="{{__('Add Tax')}}">
                                        <i class="fas fa-plus"></i>
                                        <span class="d-sm-inline-block">{{__('Add')}}</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Rate %')}}</th>
                                    <th class="w-25">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(Auth::user()->taxes->count() > 0)
                                    @foreach(Auth::user()->taxes as $tax)
                                        <tr>
                                            <td>{{ $tax->name }}</td>
                                            <td>{{ $tax->rate }}</td>
                                            <td>
                                                <div class="actions">
                                                    <a href="#" class="action-item px-2" data-url="{{ route('taxes.edit',$tax) }}" data-ajax-popup="true" data-size="md" data-title="{{__('Edit')}}" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="action-item text-danger px-2" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?')}}|{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-tax-{{$tax->id}}').submit();">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['taxes.destroy',$tax->id],'id'=>'delete-tax-'.$tax->id]) !!}
                                                {!! Form::close() !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="3"><h6 class="text-center">{{__('No Taxes Found.')}}</h6></th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabs-4" class="tabs-card d-none">
                <div class="card">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{__('Payment Settings')}}</h5>
                        <small>{{__('This detail will use for collect payment on invoice from clients. On invoice client will find out pay now button based on your below configuration.')}}</small>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['settings.store'],'id' => 'update_setting']) }}
                        <div class="row">
                            <div class="col-6 py-2">
                                <h5 class="h5">{{__('Stripe')}}</h5>
                            </div>
                            <div class="col-6 py-2 text-right">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" name="enable_stripe" id="enable_stripe" {{($details['enable_stripe'] == 'on') ? 'checked' : ''}}>
                                    <label class="custom-control-label form-control-label" for="enable_stripe">{{__('Enable Stripe')}}</label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('stripe_key', __('Stripe Key'),['class' => 'form-control-label']) }}
                                    {{ Form::text('stripe_key', $details['stripe_key'], ['class' => 'form-control','placeholder' => __('Stripe Key')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('stripe_secret', __('Stripe Secret'),['class' => 'form-control-label']) }}
                                    {{ Form::text('stripe_secret', $details['stripe_secret'], ['class' => 'form-control','placeholder' => __('Stripe Secret')]) }}
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
                                    <input type="checkbox" class="custom-control-input" name="enable_paypal" id="enable_paypal" {{($details['enable_paypal'] == 'on') ? 'checked' : ''}}>
                                    <label class="custom-control-label form-control-label" for="enable_paypal">{{__('Enable Paypal')}}</label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 pb-4">
                                <label class="paypal-label form-control-label" for="paypal_mode">{{__('Paypal Mode')}}</label> <br>
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-primary btn-sm {{ $details['paypal_mode'] == '' || $details['paypal_mode'] == 'sandbox' ? 'active' : '' }}">
                                        <input type="radio" name="paypal_mode" value="sandbox" {{ $details['paypal_mode'] == '' || $details['paypal_mode'] == 'sandbox' ? 'checked' : '' }}>{{ __('Sandbox') }}
                                    </label>
                                    <label class="btn btn-primary btn-sm {{ $details['paypal_mode'] == 'live' ? 'active' : '' }}">
                                        <input type="radio" name="paypal_mode" value="live" {{ $details['paypal_mode'] == 'live' ? 'checked' : '' }}>{{ __('Live') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('paypal_client_id', __('Client ID'),['class' => 'form-control-label']) }}
                                    {{ Form::text('paypal_client_id', $details['paypal_client_id'], ['class' => 'form-control','placeholder' => __('Client ID')]) }}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    {{ Form::label('paypal_secret_key', __('Secret Key'),['class' => 'form-control-label']) }}
                                    {{ Form::text('paypal_secret_key', $details['paypal_secret_key'], ['class' => 'form-control','placeholder' => __('Secret Key')]) }}
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
    </script>
@endpush
