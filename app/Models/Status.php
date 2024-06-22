<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $table = 'statuses';
    protected $guarded = ['id'];
    
    public function workplaces()
    {
        return $this->hasMany(Workplace::class);
    }

    // 表示フラグが1のステータスを並び順で取得するメソッド
    public static function getVisibleOrderedStatuses()
    {
        return self::where('show_flg', 1)->orderBy('sort_order')->get();
    }

}
