<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = true;

    /**
     * 通知の送信者のリレーション
     * 通知は得意先スタッフ、問屋スタッフ、施工業者スタッフから送信される
     */
    public function sender()
    {
        return $this->morphTo();
    }
    
    /**
     * 通知コンテンツのリレーション
     * 通知と通知コンテンツは多対1の関係
     */
    public function notificationContent()
    {
        return $this->belongsTo(NotificationContent::class);
    }

    /**
     * 通知の受信者のリレーション
     * 通知は得意先スタッフ、問屋スタッフ、施工業者スタッフに送信される
     */
    public function receivers()
    {
        return $this->hasMany(NotificationReceiver::class);
    }
}
