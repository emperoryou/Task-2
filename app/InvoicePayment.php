<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'client_id',
        'invoice_id',
        'amount',
        'date',
        'payment_id',
        'payment_type',
        'notes',
    ];

    //    public function payment()
    //    {
    //        return $this->hasOne('App\Payment', 'id', 'payment_id');
    //    }

    public function invoice()
    {
        return $this->hasOne('App\Invoice', 'id', 'invoice_id');
    }
}
