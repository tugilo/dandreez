<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assign extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    
    public $timestamps = true;

    // 日付として扱うカラムを指定
    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    
    // Saler（問屋）とのリレーション
    public function saler()
    {
        return $this->belongsTo(Saler::class);
    }

    // SalerStaff（問屋スタッフ）とのリレーション
    public function salerStaff()
    {
        return $this->belongsTo(SalerStaff::class);
    }

    // リレーション: 現場との1対多の関係
    public function workplace()
    {
        return $this->belongsTo(Workplace::class);
    }

    // リレーション: 建設会社との1対多の関係
    public function constructionCompany()
    {
        return $this->belongsTo(ConstructionCompany::class);
    }

    // リレーション: 作業員との1対多の関係
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
