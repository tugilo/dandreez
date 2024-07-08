<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Login;
use App\Models\SalerStaff;


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
    public function index()
    {
        $user = auth()->user();

        // ログインユーザー情報をログに出力
        Log::info('User Info:', ['user' => $user]);

        // ログイン情報を取得
        $login = Login::where('id', $user->id)->first(); // ここを修正

        // デバッグ用に login 情報を確認
        Log::info('Login Entry:', ['login' => $login]);

        // SalerStaff から saler_id を取得
        $salerId = null;
        if ($login) {
            $salerStaff = SalerStaff::where('id', $login->user_id)->first(); // ここを修正
            if ($salerStaff) {
                $salerId = $salerStaff->saler_id;
            }
        }

        if ($salerId) {
            // 承認された施工依頼データを取得
            $workplaces = Workplace::where('saler_id', $salerId)
                                    ->where('status_id', 3) // 承認済みの施工依頼
                                    ->get();

            // デバッグ用に取得したデータをログに書き出す
            Log::info('承認された施工依頼:', ['workplaces' => $workplaces]);

            // カレンダーイベント用のデータを整形
            $events = [];
            foreach ($workplaces as $workplace) {
                Log::info('施工依頼データ:', [
                    'name' => $workplace->name,
                    'construction_start' => $workplace->construction_start,
                    'construction_end' => $workplace->construction_end,
                ]);

                $events[] = [
                    'title' => $workplace->name,
                    'start' => Carbon::parse($workplace->construction_start)->format('Y-m-d'),
                    'end' => Carbon::parse($workplace->construction_end)->format('Y-m-d'),
                    'url' => route('saler.workplaces.details', ['role' => 'saler', 'id' => $workplace->id]), // 詳細ページのURLを追加
                ];
            }

            return view('saler.home', compact('events'));
        } else {
            // Saler ID が見つからない場合のエラーハンドリング
            Log::error('Saler ID not found for user_id: ' . $user->id);
            return redirect()->route('home')->withErrors('Saler data not found.');
        }
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
