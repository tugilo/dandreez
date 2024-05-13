<?php
// app/Models/Login.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Login extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'login_id',
        'email',
        'password',
        'user_type_id',
        'user_id',
    ];

    protected $hidden = [
        'password',
    ];

     // リレーション: UserTypeモデルとの関連
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }
    // リレーション: Userモデルとの関連
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // リレーション: CustomerStaffモデルとの関連
    public function customerStaff()
    {
        return $this->belongsTo(CustomerStaff::class, 'user_id');
    }

    // リレーション: SalerStaffモデルとの関連
    public function salerStaff()
    {
        return $this->belongsTo(SalerStaff::class, 'user_id');
    }

    // リレーション: Workerモデルとの関連
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'user_id');
    }
    public function getAuthPassword()
    {
        return $this->password;
    }
}