@extends('layouts.admin')

@section('title')
    {{__('Invoice ').\App\Utility::invoiceNumberFormat($invoice->invoice_id)}}
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

@section('action-button')
    @if(Auth::user()->id == $invoice->created_by)
        <a href="#" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0" data-url="{{ route('invoices.products.add',$invoice->id) }}" data-ajax-popup="true" data-size="md" data-title="{{__('Add Item')}}" data-toggle="tooltip" data-original-title="{{__('Add Item')}}">
            <span class="btn-inner--icon"><i class="fas fa-plus"></i></span>
        </a>
        <a href="#" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0" data-size="lg" data-url="{{ route('invoices.payments.create',$invoice->id) }}" data-ajax-popup="true" data-title="{{__('Add Payment')}}" data-toggle="tooltip" data-original-title="{{__('Add Payment')}}">
            <span class="btn-inner--icon"><i class="fas fa-shopping-cart"></i></span>
        </a>
        <a href="{{ route('invoice.sent',$invoice->id) }}" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0" data-toggle="tooltip" data-original-title="{{__('Send Invoice Mail')}}">
            <span class="btn-inner--icon"><i class="fas fa-reply"></i></span>
        </a>
        <a href="{{ route('invoice.payment.reminder',$invoice->id) }}" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0" data-title="{{__('Payment Reminder')}}" data-toggle="tooltip" data-original-title="{{__('Payment Reminder')}}">
            <span class="btn-inner--icon"><i class="fas fa-money-check"></i></span>
        </a>
        <a href="#" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0" data-url="{{ route('invoices.edit',$invoice->id) }}" data-ajax-popup="true" data-size="md" data-title="{{__('Edit ').\App\Utility::invoiceNumberFormat($invoice->invoice_id)}}" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
            <span class="btn-inner--icon"><i class="fas fa-edit"></i></span>
        </a>
    @endif
    @if($invoice->client_id == Auth::user()->id)
        @if($invoice->getDue() > 0)
            @if($creator_detail['enable_stripe'] == 'on' || $creator_detail['enable_paypal'] == 'on')
                <a href="#" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0" data-toggle="modal" data-target="#paymentModal" data-original-title="{{__('Add Payment')}}" title="{{__('Add Payment')}}">
                    <span class="btn-inner--icon"><i class="fas fa-shopping-cart"></i></span>
                </a>
            @endif
        @endif
        <a href="#" data-url="{{ route('invoice.custom.send',$invoice->id) }}" data-ajax-popup="true" data-title="{{__('Send Invoice')}}" data-toggle="tooltip" data-original-title="{{__('Send Invoice')}}" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0">
            <span class="btn-inner--icon"><i class="fas fa-share-square"></i></span>
        </a>
    @endif
    <a href="{{ route('get.invoice',Crypt::encrypt($invoice->id)) }}" class="btn btn-sm btn-white btn-icon-only rounded-circle ml-0" data-toggle="tooltip" data-original-title="{{__('Print Invoice')}}" target="_blanks">
        <span><i class="fa fa-print"></i></span>
    </a>
    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-white rounded-circle btn-icon-only ml-0" data-toggle="tooltip" data-original-title="{{__('Back')}}">
        <span class="btn-inner--icon"><i class="fas fa-arrow-left"></i></span>
    </a>
@endsection

@section('content')
    <div class="card card-body p-md-5">
        <div class="row align-items-center mb-5">
            <div class="col-sm-6 mb-3 mb-sm-0">
                @if(Auth::user()->mode == 'dark')
                    @if($invoice->client_id == Auth::user()->id)
                        <img src="{{ asset(Storage::url($right_address['dark_logo'])) }}" alt="" height="40">
                    @else
                        <img src="{{ asset(Storage::url($left_address['dark_logo'])) }}" alt="" height="40">
                    @endif
                @else
                    @if($invoice->client_id == Auth::user()->id)
                        <img src="{{ asset(Storage::url($right_address['light_logo'])) }}" alt="" height="40">
                    @else
                        <img src="{{ asset(Storage::url($left_address['light_logo'])) }}" alt="" height="40">
                    @endif
                @endif

            </div>
            <div class="col-sm-6 text-sm-right">
                <span class="badge badge-pill badge-{{\App\Invoice::$status_color[$invoice->status]}} ml-3">{{__(\App\Invoice::$status[$invoice->status])}}</span>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-6 col-md-6">
                <h6 class="">{{__('From :')}}</h6>
                <p class="text-sm font-weight-700 mb-0">
                    {{ Auth::user()->name }}
                </p>
                <span class="text-sm">
                    {{ $left_address['address'] }} <br>
                    {{ $left_address['city'] }}
                    @if(isset($left_address['city']) && !empty($left_address['city'])), @endif
                    {{$left_address['state']}}
                    @if(isset($left_address['zipcode']) && !empty($left_address['zipcode']))-@endif {{$left_address['zipcode']}}<br>
                    {{$left_address['country']}} <br>
                    {{$left_address['telephone']}}
                </span>
            </div>
            <div class="col-lg-6 col-md-6 text-right">
                <h6 class="">{{__('To :')}}</h6>
                <p class="text-sm font-weight-700 mb-0">
                    @if($invoice->client_id == Auth::user()->id)
                        {{ $invoice->user->name }}
                    @else
                        {{ $invoice->client->name }}
                    @endif
                </p>
                <span class="text-sm">
                    {{ $right_address['address'] }} <br>
                    {{ $right_address['city'] }}
                    @if(isset($right_address['city']) && !empty($right_address['city'])), @endif
                    {{$right_address['state']}}
                    @if(isset($right_address['zipcode']) && !empty($right_address['zipcode']))-@endif {{$right_address['zipcode']}}<br>
                    {{$right_address['country']}} <br>
                    {{$right_address['telephone']}}
                </span>
            </div>
        </div>
        <div class="row pt-3">
            <div class="col-md-4 text-sm">
                <strong>{{__('Project')}}</strong><br>
                {{$invoice->project->name}}<br>
            </div>
            <div class="col-md-4 text-sm text-center">
                <strong>{{__('Due Date')}}</strong><br>
                {{\App\Utility::getDateFormated($invoice->due_date)}}<br>
            </div>
            <div class="col-md-4 text-sm text-right">
                <strong>{{__('Due Amount')}}</strong><br>
                {{\App\Utility::projectCurrencyFormat($invoice->project_id,$invoice->getDue(),true)}}<br>
            </div>
            <div class="col-12">
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-6 text-left pb-3">
                <h5>{{__('Item List')}}</h5>
            </div>
            @if(Auth::user()->id == $invoice->created_by)
                <div class="col-6 text-right pb-3">
                    <button type="button" class="btn btn-warning btn-xs btn-icon" data-url="{{ route('invoices.products.add',$invoice->id) }}" data-ajax-popup="true" data-size="md" data-title="{{__('Add Item')}}">
                    <span class="btn-inner--icon">
                      <i class="fas fa-plus"></i>
                    </span>
                        <span class="btn-inner--text">{{__('Add Item')}}</span>
                    </button>
                </div>
            @endif
            <div class="col-12">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th class="px-0 bg-transparent border-top-0">{{__('Item')}}</th>
                            <th class="px-0 bg-transparent border-top-0">{{__('Price')}}</th>
                            <th class="px-0 bg-transparent border-top-0">{{__('Tax')}}</th>
                            @if(Auth::user()->id == $invoice->created_by)
                                <th class="px-0 bg-transparent border-top-0 text-right">{{__('Action')}}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-0">
                                    <span class="h6 text-sm">{{ $item->item }}</span>
                                </td>
                                <td class="px-0">{{ \App\Utility::projectCurrencyFormat($invoice->project_id,$item->price,true) }}</td>
                                <td class="px-0">{{ \App\Utility::projectCurrencyFormat($invoice->project_id,$item->tax(),true) }}</td>
                                @if(Auth::user()->id == $invoice->created_by)
                                    <td class="px-0 text-right">
                                        <a href="#" class="table-action table-action-delete text-danger" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" data-confirm-yes="document.getElementById('delete-form-{{$item->id}}').submit();">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['invoices.products.delete', $invoice->id,$item->id],'id'=>'delete-form-'.$item->id]) !!}
                                        {!! Form::close() !!}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card my-5 bg-secondary">
                    <div class="card-body">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-6 order-md-2 mb-4 mb-md-0">
                                <div class="d-flex align-items-center justify-content-md-end">
                                    <span class="d-inline-block mr-3 mb-0">{{__('Total value:')}}</span>
                                    <span class="h4 mb-0">{{ \App\Utility::projectCurrencyFormat($invoice->project_id,($invoice->getSubTotal()+$invoice->getTax()),true) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 order-md-1">
                                <div class="text text-sm">
                                    <span class="font-weight-bold">{{__('Subtotal')}}</span><br>
                                    <span class="text-dark">{{ \App\Utility::projectCurrencyFormat($invoice->project_id,$invoice->getSubTotal(),true) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 order-md-1">
                                <div class="text text-sm">
                                    <span class="font-weight-bold">{{(!empty($invoice->tax)?$invoice->tax->name:'Tax')}} ({{(!empty($invoice->tax)?$invoice->tax->rate:'0')}} %)</span><br>
                                    <span class="text-dark">{{ \App\Utility::projectCurrencyFormat($invoice->project_id,$invoice->getTax(),true) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <h5 class="pb-3">{{__('Payment History')}}</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{__('Transaction ID')}}</th>
                            <th>{{__('Payment Date')}}</th>
                            <th>{{__('Payment Type')}}</th>
                            <th>{{__('Note')}}</th>
                            <th>{{__('Amount')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($invoice->payments->count())
                            @foreach($invoice->payments as $payment)
                                <tr>
                                    <td>{{sprintf("%05d", $payment->transaction_id)}}</td>
                                    <td>{{ \App\Utility::getDateFormated($payment->date) }}</td>
                                    <td>{{$payment->payment_type}}</td>
                                    <td>{{(!empty($payment->notes)) ? $payment->notes : '-'}}</td>
                                    <td>{{\App\Utility::projectCurrencyFormat($invoice->project_id,$payment->amount,true)}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <th scope="col" colspan="5"><h6 class="text-center">{{__('No Record Found.')}}</h6></th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($invoice->client_id == Auth::user()->id)
        @if($invoice->getDue() > 0)
            <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-pills pb-3" role="tablist">
                                @if($creator_detail['enable_stripe'] == 'on')
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#stripe-payment" role="tab" aria-controls="stripe" aria-selected="true">{{ __('Stripe') }}</a>
                                    </li>
                                @endif
                                @if($creator_detail['enable_paypal'] == 'on')
                                    <li class="nav-item">
                                        <a class="nav-link {{ ($creator_detail['enable_stripe'] == 'off' && $creator_detail['enable_paypal'] == 'on') ? "active" : "" }}" data-toggle="tab" href="#paypal-payment" role="tab" aria-controls="paypal" aria-selected="false">{{ __('Paypal') }}</a>
                                    </li>
                                @endif
                            </ul>

                            <div class="tab-content">
                                @if($creator_detail['enable_stripe'] == 'on')
                                    <div class="tab-pane fade {{ (($creator_detail['enable_stripe'] == 'on' && $creator_detail['enable_paypal'] == 'on') || $creator_detail['enable_stripe'] == 'on') ? "show active" : "" }}" id="stripe-payment" role="tabpanel" aria-labelledby="stripe-payment">
                                        <form method="post" action="{{ route('client.invoice.payment',[$invoice->id]) }}" class="require-validation" id="payment-form">
                                            @csrf
                                            <div class="border p-3 mb-3 rounded">
                                                <div class="row">
                                                    <div class="col-sm-8">
                                                        <div class="custom-radio">
                                                            <label class="font-16 font-weight-bold">{{__('Credit / Debit Card')}}</label>
                                                        </div>
                                                        <p class="mb-0 pt-1">{{__('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.')}}</p>
                                                    </div>
                                                    <div class="col-sm-4 text-sm-right mt-3 mt-sm-0">
                                                        <img src="{{asset('assets/img/payments/master.png')}}" height="24" alt="master-card-img">
                                                        <img src="{{asset('assets/img/payments/paypal.png')}}" height="24" alt="paypal-card-img">
                                                        <img src="{{asset('assets/img/payments/visa.png')}}" height="24" alt="visa-card-img">
                                                        <img src="{{asset('assets/img/payments/american-express.png')}}" height="24" alt="american-express-card-img">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <hr>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="card-name-on" class="form-control-label">{{__('Name on card')}}</label>
                                                            <input type="text" name="name" id="card-name-on" class="form-control required" placeholder="{{\Auth::user()->name}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div id="card-element">
                                                            <!-- A Stripe Element will be inserted here. -->
                                                        </div>
                                                        <div id="card-errors" role="alert"></div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <br>
                                                        <label for="amount" class="form-control-label">{{ __('Amount') }}</label>
                                                        <div class="input-group">
                                                            <span class="input-group-prepend"><span class="input-group-text">{{ $invoice->project->currency }}</span></span>
                                                            <input class="form-control" required="required" min="0" name="amount" type="number" value="{{$invoice->getDue()}}" min="0" step="0.01" max="{{$invoice->getDue()}}" id="amount">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <div class="form-group">
                                                            {{ Form::label('notes', __('Note'),['class' => 'form-control-label']) }}
                                                            <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
                                                            {{ Form::textarea('notes', null, ['class' => 'form-control','rows' => '1','data-toggle' => 'autosize']) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="error" style="display: none;">
                                                            <div class='alert-danger alert'>{{__('Please correct the errors and try again.')}}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mt-3 text-sm-right">
                                                <button class="btn btn-primary btn-sm rounded-pill" type="submit">{{ __('Make Payment') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                                @if($creator_detail['enable_paypal'] == 'on')
                                    <div class="tab-pane fade {{ ($creator_detail['enable_stripe'] == 'off' && $creator_detail['enable_paypal'] == 'on') ? "show active" : "" }}" id="paypal-payment" role="tabpanel" aria-labelledby="paypal-payment">
                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST" id="payment-form" action="{{ route('client.pay.with.paypal', $invoice->id) }}">
                                            @csrf
                                            <div class="border p-3 mb-3 rounded">
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount" class="form-control-label">{{ __('Amount') }}</label>
                                                        <div class="input-group">
                                                            <span class="input-group-prepend"><span class="input-group-text">{{ $invoice->project->currency }}</span></span>
                                                            <input class="form-control" required="required" min="0" name="amount" type="number" value="{{$invoice->getDue()}}" min="0" step="0.01" max="{{$invoice->getDue()}}" id="amount">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <div class="form-group">
                                                            {{ Form::label('notes', __('Note'),['class' => 'form-control-label']) }}
                                                            <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
                                                            {{ Form::textarea('notes', null, ['class' => 'form-control','rows' => '1','data-toggle' => 'autosize']) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mt-3 text-sm-right">
                                                <input type="hidden" value="invoice" name="from">
                                                <input type="hidden" value="{{$invoice->created_by}}" name="invoice_creator">
                                                <button class="btn btn-primary btn-sm rounded-pill" name="submit" type="submit">{{ __('Make Payment') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection

@push('script')
    <script>
        function fillClient(project_id, selected = 0) {
            $.ajax({
                url: '{{route('project.client.json')}}',
                data: {project_id: project_id},
                type: 'POST',
                success: function (data) {
                    $('#client_id').html('');

                    if (data != '') {
                        $('#no_client').addClass('d-none');
                        $.each(data, function (key, data) {
                            var selected = '';
                            if (key == selected) {
                                selected = 'selected';
                            }
                            $("#client_id").append('<option value="' + key + '" ' + selected + '>' + data + '</option>');
                        });
                    } else {
                        $('#no_client').removeClass('d-none');
                    }
                }
            })
        }

        $(document).on('click', '.items_tab', function () {
            $('#from').val($(this).attr('id'));
        })
    </script>
    @if($invoice->client_id == Auth::user()->id)
        @if($invoice->getDue() > 0 && $creator_detail['enable_stripe'] == 'on')
            <script src="https://js.stripe.com/v3/"></script>
            <script type="text/javascript">
                var stripe = Stripe('{{ $creator_detail['stripe_key'] }}');
                var elements = stripe.elements();

                // Custom styling can be passed to options when creating an Element.
                var style = {
                    base: {
                        // Add your base input styles here. For example:
                        fontSize: '14px',
                        color: '#32325d',
                    },
                };

                // Create an instance of the card Element.
                var card = elements.create('card', {style: style});

                // Add an instance of the card Element into the `card-element` <div>.
                card.mount('#card-element');

                // Create a token or display an error when the form is submitted.
                var form = document.getElementById('payment-form');
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    stripe.createToken(card).then(function (result) {
                        if (result.error) {
                            toastr('Error', result.error.message, 'error');
                        } else {
                            // Send the token to your server.
                            stripeTokenHandler(result.token);
                        }
                    });
                });

                function stripeTokenHandler(token) {
                    // Insert the token ID into the form so it gets submitted to the server
                    var form = document.getElementById('payment-form');
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', token.id);
                    form.appendChild(hiddenInput);

                    // Submit the form
                    form.submit();
                }
            </script>
        @endif
    @endif
@endpush
