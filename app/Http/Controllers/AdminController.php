<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * システム管理者用ホームページを表示します。
     * 表示するのはログインしているユーザーの情報です。
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user(); // 現在のユーザー情報を取得
        return view('admin.home', compact('user')); // ユーザー情報をビューに渡す
    }
}
