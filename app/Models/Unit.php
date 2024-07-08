<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;


    // リレーション: 建設会社との1対多の関係
    public function constructionCompany()
    {
        return $this->belongsTo(ConstructionCompany::class);
    }
}
