<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class Login
 * 
 * このモデルはログインユーザーに関連するデータを管理します。
 * 各ユーザータイプ（システムユーザー、得意先、問屋、施工業者）に対応するリレーションを定義しています。
 */
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

    /**
     * ユーザー種別（user_typesテーブル）とのリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    /**
     * システムユーザー（usersテーブル）とのリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 得意先スタッフ（customer_staffsテーブル）とのリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customerStaff()
    {
        return $this->belongsTo(CustomerStaff::class, 'user_id');
    }

    /**
     * 問屋スタッフ（saler_staffsテーブル）とのリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salerStaff()
    {
        return $this->belongsTo(SalerStaff::class, 'user_id');
    }

    /**
     * 施工業者（workersテーブル）とのリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'user_id');
    }

    /**
     * 認証パスワードを取得
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * 関連するユーザー情報を取得
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRelatedUser()
    {
        switch ($this->userType->type) {
            case 'customer':
                return $this->customerStaff;
            case 'saler':
                return $this->salerStaff;
            case 'worker':
                return $this->worker;
            default:
                return $this->user;
        }
    }
}
