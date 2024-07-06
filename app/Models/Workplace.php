<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workplace extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = true;

    /**
     * リレーション: 得意先との1対多の関係
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * リレーション: 得意先担当者との1対多の関係
     */
    public function customerStaff()
    {
        return $this->belongsTo(CustomerStaff::class);
    }

    /**
     * リレーション: 営業者との1対多の関係
     */
    public function saler()
    {
        return $this->belongsTo(Saler::class);
    }

    /**
     * リレーション: 営業担当者との1対多の関係
     */
    public function salerStaff()
    {
        return $this->belongsTo(SalerStaff::class);
    }

    /**
     * リレーション: 写真との1対多の関係
     */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * リレーション: ファイルとの1対多の関係
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * リレーション: 通知との1対多の関係
     */
    public function notices()
    {
        return $this->hasMany(Notice::class);
    }

    /**
     * リレーション: 作業指示との1対多の関係
     */
    public function instructions()
    {
        return $this->hasMany(Instruction::class);
    }

    /**
     * リレーション: 施工者との多対多の関係
     */
    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'assigns', 'workplace_id', 'worker_id');
    }
    
    /**
     * リレーション: ステータスとの1対多の関係
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

}
