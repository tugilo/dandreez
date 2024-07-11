<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dailyreport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    protected $dates = [
        'report_day',
        'created_at',
        'updated_at'
    ];

    // リレーション: 現場との1対多の関係
    public function workplace()
    {
        return $this->belongsTo(Workplace::class);
    }

    // リレーション: 顧客との1対多の関係
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // リレーション: 顧客担当者との1対多の関係
    public function customerStaff()
    {
        return $this->belongsTo(CustomerStaff::class);
    }

    // リレーション: 営業者との1対多の関係
    public function saler()
    {
        return $this->belongsTo(Saler::class);
    }

    // リレーション: 営業担当者との1対多の関係
    public function salerStaff()
    {
        return $this->belongsTo(SalerStaff::class);
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

    // 新しく追加: アサインとの関係
    public function assign()
    {
        return $this->belongsTo(Assign::class);
    }
}