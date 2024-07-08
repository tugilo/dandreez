<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentPhoto extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public $timestamps = true;

    // リレーション: コメントとの1対多の関係
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
