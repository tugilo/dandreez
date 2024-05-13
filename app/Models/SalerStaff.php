<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalerStaff extends Model
{
    use HasFactory;
    /**
     * * * モデルに関連付けるテーブル名
     * *
     * * @var string
     *  */
    protected $table = 'saler_staffs';
    
    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 営業者との1対多の関係
    public function saler()
    {
        return $this->belongsTo(Saler::class);
    }

    // リレーション: 役割との1対多の関係
    public function role()
    {
        // このユーザーに関連するRoleモデルを取得
        return $this->belongsTo(Role::class);
    }
}
