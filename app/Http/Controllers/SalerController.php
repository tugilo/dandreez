<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

}
