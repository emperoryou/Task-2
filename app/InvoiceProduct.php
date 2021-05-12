<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    protected $fillable = [
        'invoice_id',
        'item',
        'price',
        'type',
    ];

    // Get item based tax
    public function tax()
    {
        if($this->invoice_id != 0)
        {
            $invoice = Invoice::find($this->invoice_id);
            $tax     = ($this->price * (!empty($invoice->tax) ? $invoice->tax->rate : 0)) / 100.00;
        }
        else
        {
            $tax = 10;
        }

        return $tax;
    }
}
