<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saler extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 営業担当者との1対多の関係
    public function salerStaffs()
    {
        return $this->hasMany(SalerStaff::class);
    }
}
