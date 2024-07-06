<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zip extends Model
{
    use HasFactory;

    protected $table = 'zips';
    protected $guarded = ['id'];

    /**
     * データベースからすべての都道府県を取得する。
     * 都道府県をid順に並べ替えて返します。
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPrefectures()
    {
        return self::distinct()->orderBy('id')->get(['prefecture']);
    }



    /**
     * 都市とのリレーション
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
