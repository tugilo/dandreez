<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalerStaff extends Model
{
    use HasFactory;

    protected $table = 'saler_staffs';
    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 問屋との1対多の関係
    public function saler()
    {
        return $this->belongsTo(Saler::class, 'saler_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
