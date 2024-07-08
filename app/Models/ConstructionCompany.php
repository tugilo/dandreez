<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstructionCompany extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 作業員との1対多の関係
    public function workers()
    {
        return $this->hasMany(Worker::class);
    }
    // Assign（割り当て）とのリレーション
    public function assigns()
    {
        return $this->hasMany(Assign::class);
    }
}
