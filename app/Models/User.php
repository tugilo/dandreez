<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * マスアサインメントから保護する属性
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * シリアライズ時に隠す属性
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * キャストする属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    /**
     * ユーザーのログインIDを取得
     *
     * @return string
     */
    public function getLoginIdAttribute()
    {
        return $this->attributes['login_id'];
    }

    /**
     * ユーザーのログインIDを設定
     *
     * @param  string  $value
     * @return void
     */
    public function setLoginIdAttribute($value)
    {
        $this->attributes['login_id'] = $value;
    }
    /**
     * 役割とのリレーション (1対多)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}