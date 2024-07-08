<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 顧客との1対多の関係
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // リレーション: 顧客担当者との1対多の関係
    public function customerStaff()
    {
        return $this->belongsTo(CustomerStaff::class);
    }

    // リレーション: 請求書明細との1対多の関係
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }
}
