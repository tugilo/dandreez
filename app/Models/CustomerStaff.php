<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerStaff extends Model
{
    use HasFactory;

    protected $table = 'customer_staffs';
    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 顧客との1対多の関係
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
