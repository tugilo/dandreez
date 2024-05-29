<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instruction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;
    
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // リレーション: 現場との1対多の関係
    public function workplace()
    {
        return $this->belongsTo(Workplace::class);
    }

    // リレーション: 単位との1対多の関係
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    
}
