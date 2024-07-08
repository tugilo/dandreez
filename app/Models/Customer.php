<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 顧客担当者との1対多の関係
    public function customerStaffs()
    {
        return $this->hasMany(CustomerStaff::class);
    }
}
