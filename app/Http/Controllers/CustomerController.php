<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Workplace;
use Carbon\Carbon;
use App\Models\Login;
use App\Models\Customer;
use App\Models\CustomerStaff;
use App\Models\SalerStaff;
use App\Models\Worker;

class CustomerController extends Controller
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
    //
    // App\Http\Controllers\CustomerController
    public function index()
    {
        $user = auth()->user();

        // ログイン情報を取得
        $login = Login::where('id', $user->id)->first();

        // デバッグ用に login 情報を確認
        Log::info('Login Entry:', ['login' => $login]);

        // user_type_id が 2 の場合のみなので、CustomerStaff から customer_id を取得
        $customerId = null;
        if ($login) {
            $customerStaff = CustomerStaff::where('id', $login->user_id)->first();
            if ($customerStaff) {
                $customerId = $customerStaff->customer_id;
            }
        }

        if ($customerId) {
            // 承認された施工依頼データを取得
            $workplaces = Workplace::where('customer_id', $customerId)
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
                    'url' => route('customer.workplaces.details', ['role' => 'customer', 'id' => $workplace->id]), // 詳細ページのURLを追加
                ];
            }

            return view('customer.home', compact('events'));
        } else {
            // Customer ID が見つからない場合のエラーハンドリング
            Log::error('Customer ID not found for user_id: ' . $user->id);
            return redirect()->route('home')->withErrors('Customer data not found.');
        }
    }

}
