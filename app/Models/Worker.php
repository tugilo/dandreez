<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル名
     *
     * @var string
     */
    protected $table = 'workers';


    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 建設会社との1対多の関係
    public function constructionCompany()
    {
        return $this->belongsTo(ConstructionCompany::class);
    }
    public function role()
    {
        // このユーザーに関連するRoleモデルを取得
        return $this->belongsTo(Role::class);
    }
}
