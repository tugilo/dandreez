<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Workplace;
use App\Models\Login;
use App\Models\CustomerStaff;
use Carbon\Carbon;

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

    /**
     * 得意先ダッシュボードを表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $login = Login::where('id', $user->id)->first();
        
        Log::info('Login Entry:', ['login' => $login]);

        $customerId = $this->getCustomerId($login);

        if ($customerId) {
            $dashboardData = $this->getDashboardData($customerId);
            return view('customer.home', $dashboardData);
        } else {
            Log::error('Customer ID not found for user_id: ' . $user->id);
            return redirect()->route('home')->withErrors('Customer data not found.');
        }
    }

    /**
     * ログインユーザーの顧客IDを取得
     *
     * @param Login $login
     * @return int|null
     */
    private function getCustomerId($login)
    {
        if ($login) {
            $customerStaff = CustomerStaff::where('id', $login->user_id)->first();
            return $customerStaff ? $customerStaff->customer_id : null;
        }
        return null;
    }

    /**
     * ダッシュボード用のデータを取得
     *
     * @param int $customerId
     * @return array
     */
    private function getDashboardData($customerId)
    {
        $now = Carbon::now();

        // カレンダーイベント用データ
        $events = $this->getCalendarEvents($customerId);

        // 直近の施工依頼サマリー
        $upcomingWorkplaces = Workplace::where('customer_id', $customerId)
            ->where('status_id', 3)
            ->whereBetween('construction_start', [$now, $now->copy()->addDays(7)])
            ->get();

        // 進行中の施工依頼カウンター
        $ongoingWorkplacesCount = Workplace::where('customer_id', $customerId)
            ->where('status_id', 3)
            ->where('construction_start', '<=', $now)
            ->where('construction_end', '>=', $now)
            ->count();

        // 未承認の施工依頼
        $pendingWorkplacesCount = Workplace::where('customer_id', $customerId)
            ->where('status_id', 1)
            ->count();

        // 重要なお知らせ（仮のデータ）
        $importantNotices = [
            ['title' => '施工依頼の承認が必要です', 'count' => $pendingWorkplacesCount],
            ['title' => 'システムメンテナンスのお知らせ', 'date' => '2024-07-20'],
        ];

        $importantNotices = collect([
            ['title' => '施工依頼の承認が必要です', 'count' => $pendingWorkplacesCount, 'date' => now()],
            ['title' => 'システムメンテナンスのお知らせ', 'date' => '2024-07-20'],
        ])->sortByDesc('date');
    
        return compact('events', 'upcomingWorkplaces', 'ongoingWorkplacesCount', 'pendingWorkplacesCount', 'importantNotices');
    }

    /**
     * カレンダーイベント用のデータを取得
     *
     * @param int $customerId
     * @return array
     */
    private function getCalendarEvents($customerId)
    {
        $workplaces = Workplace::where('customer_id', $customerId)
            ->where('status_id', 3)
            ->get();

        $events = [];
        foreach ($workplaces as $workplace) {
            $events[] = [
                'title' => $workplace->name,
                'start' => Carbon::parse($workplace->construction_start)->format('Y-m-d'),
                'end' => Carbon::parse($workplace->construction_end)->format('Y-m-d'),
                'url' => route('customer.workplaces.details', ['role' => 'customer', 'id' => $workplace->id]),
            ];
        }

        return $events;
    }
}