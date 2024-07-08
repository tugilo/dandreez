<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 請求書との1対多の関係
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // リレーション: 現場との1対多の関係
    public function workplace()
    {
        return $this->belongsTo(Workplace::class);
    }

    // リレーション: 顧客との1対多の関係
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
