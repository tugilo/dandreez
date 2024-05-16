<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'workers';
    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: 施工会社との1対多の関係
    public function constructionCompany()
    {
        return $this->belongsTo(ConstructionCompany::class, 'construction_company_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
