<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SalerController extends Controller
{
    /**
     * コントローラーインスタンスの生成
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    // App\Http\Controllers\SalerController
    public function index()
    {
        return view('saler.home');
    }

    /**
     * 施工依頼の一覧を表示
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function workplaces()
    {
        $salerId = Auth::user()->salerStaff->saler_id;
        $workplaces = Workplace::where('saler_id', $salerId)->get();

        return view('saler.workplaces', compact('workplaces'));
    }

}
