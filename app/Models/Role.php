<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 営業担当者との1対多の関係
    public function salerStaffs()
    {
        return $this->hasMany(SalerStaff::class);
    }

    // リレーション: ユーザーとの1対多の関係
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
