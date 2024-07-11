<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationReceiver extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = true;

    /**
     * 通知のリレーション
     * 通知と受信者は多対1の関係
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * 受信者のリレーション
     * 受信者は得意先スタッフ、問屋スタッフ、施工業者スタッフ
     */
    public function recipient()
    {
        return $this->morphTo();
    }
}
