<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationContent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = true;

    /**
     * 通知のリレーション
     * 通知コンテンツと通知は1対多の関係
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    // 表示される通知内容のみ取得するスコープ
    public function scopeVisible($query)
    {
        return $query->where('show_flg', 1);
    }

}
