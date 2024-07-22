<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\Worker;
use App\Models\Login;
use App\Models\SalerStaff;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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

    /**
     * 問屋スタッフ用ダッシュボードを表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $login = Login::where('id', $user->id)->first();

        Log::info('Login Entry:', ['login' => $login]);

        $salerId = $this->getSalerId($login);

        if ($salerId) {
            $dashboardData = $this->getDashboardData($salerId);
            return view('saler.home', $dashboardData);
        } else {
            Log::error('Saler ID not found for user_id: ' . $user->id);
            return redirect()->route('home')->withErrors('Saler data not found.');
        }
    }

    /**
     * ログインユーザーの問屋IDを取得
     *
     * @param Login $login
     * @return int|null
     */
    private function getSalerId($login)
    {
        if ($login) {
            $salerStaff = SalerStaff::where('id', $login->user_id)->first();
            return $salerStaff ? $salerStaff->saler_id : null;
        }
        return null;
    }

    /**
     * ダッシュボード用のデータを取得
     *
     * @param int $salerId
     * @return array
     */
    private function getDashboardData($salerId)
    {
        $now = Carbon::now();

        // カレンダーイベント用データ
        $events = $this->getCalendarEvents($salerId);

        // 進行中の施工件数
        $ongoingWorkplacesCount = Workplace::where('saler_id', $salerId)
            ->where('status_id', 3)
            ->where('construction_start', '<=', $now)
            ->where('construction_end', '>=', $now)
            ->count();

        // 未対応の新規施工依頼件数
        $newWorkplacesCount = Workplace::where('saler_id', $salerId)
            ->where('status_id', 1)
            ->count();

        // 今後1週間の施工予定件数
        $upcomingWorkplacesCount = Workplace::where('saler_id', $salerId)
            ->where('status_id', 3)
            ->where('construction_start', '>', $now)
            ->where('construction_start', '<=', $now->copy()->addDays(7))
            ->count();

        // アサイン待ちの施工依頼件数
        $unassignedWorkplacesCount = Workplace::where('saler_id', $salerId)
            ->where('status_id', 3)
            ->whereDoesntHave('assigns')
            ->count();

        // 最新の施工依頼リスト
        $latestWorkplaces = Workplace::where('saler_id', $salerId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 未対応タスク
        $pendingTasks = $this->getPendingTasks($salerId);

        // 進行中の施工一覧
        $ongoingWorkplaces = Workplace::where('saler_id', $salerId)
            ->where('status_id', 3)
            ->where('construction_start', '<=', $now)
            ->where('construction_end', '>=', $now)
            ->get();

        // 職人稼働状況
        $workerStatus = $this->getWorkerStatus($salerId);

        return compact('events', 'ongoingWorkplacesCount', 'newWorkplacesCount', 'upcomingWorkplacesCount', 
                       'unassignedWorkplacesCount', 'latestWorkplaces', 'pendingTasks', 'ongoingWorkplaces', 'workerStatus');
    }

    /**
     * カレンダーイベント用のデータを取得
     *
     * @param int $salerId
     * @return array
     */
    private function getCalendarEvents($salerId)
    {
        $workplaces = Workplace::where('saler_id', $salerId)
            ->where('status_id', 3)
            ->get();

        $events = [];
        foreach ($workplaces as $workplace) {
            $events[] = [
                'title' => $workplace->name,
                'start' => Carbon::parse($workplace->construction_start)->format('Y-m-d'),
                'end' => Carbon::parse($workplace->construction_end)->format('Y-m-d'),
                'url' => route('saler.workplaces.details', ['role' => 'saler', 'id' => $workplace->id]),
            ];
        }

        return $events;
    }

    /**
     * 未対応タスクを取得
     *
     * @param int $salerId
     * @return array
     */
    private function getPendingTasks($salerId)
    {
        // この部分は実際のアプリケーションロジックに合わせて実装してください
        return [
            '指示内容未設定の施工依頼' => 3,
            '写真・ファイル未アップロードの施工依頼' => 2,
            '職人未アサインの施工依頼' => 4,
        ];
    }

    /**
     * 職人の稼働状況を取得
     *
     * @param int $salerId
     * @return array
     */
    private function getWorkerStatus($salerId)
    {
        // この部分は実際のアプリケーションロジックに合わせて実装してください
        $workers = Worker::all(); // または特定の問屋に関連する職人のみを取得
        $status = [];
        foreach ($workers as $worker) {
            $status[] = [
                'name' => $worker->name,
                'status' => $worker->getCurrentAssignment() ? '稼働中' : '待機中',
            ];
        }
        return $status;
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