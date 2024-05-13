<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = true;

    public function logins()
    {
        return $this->hasMany(Login::class, 'user_type_id');
    }
}
