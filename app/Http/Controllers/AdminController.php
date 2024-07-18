<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Login;
use App\Models\UserType;
use App\Models\Workplace;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * 管理者ダッシュボードを表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = [
            'userStats' => $this->getUserStats(),
            'systemUsageStats' => $this->getSystemUsageStats(),
            'workplaceStats' => $this->getWorkplaceStats(),
            'recentActivities' => $this->getRecentActivities(),
        ];

        return view('admin.home', $data);
    }

    /**
     * ユーザー統計情報を取得
     *
     * @return array
     */
    /*
    private function getUserStats()
    {
        $now = Carbon::now();
        
        // 総ユーザー数（アクティブなユーザーのみ）
        $totalUsers = Login::where('show_flg', 1)->count();
        
        // ユーザータイプ別の総数
        $usersByType = Login::where('show_flg', 1)
            ->groupBy('user_type_id')
            ->selectRaw('user_type_id, count(*) as count')
            ->pluck('count', 'user_type_id')
            ->toArray();

        // ユーザータイプ名を取得
        $userTypes = UserType::whereIn('id', array_keys($usersByType))
            ->pluck('name', 'id')
            ->toArray();

        // ユーザータイプ名をキーとした配列に変換
        $usersByTypeName = array_combine(
            array_map(function($id) use ($userTypes) { return $userTypes[$id]; }, array_keys($usersByType)),
            array_values($usersByType)
        );
        
        // アクティブユーザー数（過去30日間にログインしたユーザー）
        $activeUsers = Login::where('show_flg', 1)
            ->where('last_login_at', '>', $now->subDays(30))
            ->count();
        
        // 新規ユーザー数（過去7日間に作成されたユーザー）
        $newUsers = Login::where('show_flg', 1)
            ->where('created_at', '>', $now->subDays(7))
            ->count();

        return [
            'totalUsers' => $totalUsers,
            'usersByType' => $usersByTypeName,
            'activeUsers' => $activeUsers,
            'newUsers' => $newUsers,
        ];
    }
    */
    /**
     * ユーザー統計情報を取得（ダミーデータ）
     *
     * 注: これは将来の実装のためのプレースホルダーです。
     * 実際のデータではなく、表示例としてのダミーデータを返します。
     *
     * @return array
     */
    private function getUserStats()
    {
        return [
            'totalUsers' => 1000,
            'usersByType' => [
                '管理者' => 10,
                '得意先' => 500,
                '問屋' => 200,
                '施工業者' => 290
            ],
            'activeUsers' => 750,
            'newUsers' => 50,
        ];
    }

    /**
     * システム利用状況の統計情報を取得
     *
     * @return array
     */
    private function getSystemUsageStats()
    {
        $now = Carbon::now();
        return [
            'totalLogins' => Login::where('last_login_at', '>', $now->subDays(30))->count(),
            'activeSessions' => Login::where('last_login_at', '>', $now->subHours(1))->count(),
            // 'popularPages' は将来の拡張のために保留
        ];
    }

    /**
     * 施工依頼の統計情報を取得
     *
     * @return array
     */
    /*
    private function getWorkplaceStats()
    {
        $now = Carbon::now();
        return [
            'totalWorkplaces' => Workplace::count(), // 総施工依頼数
            'workplacesByStatus' => Workplace::groupBy('status_id')
                ->selectRaw('status_id, count(*) as count')
                ->pluck('count', 'status_id'), // ステータス別の施工依頼数
            'newWorkplaces' => Workplace::where('created_at', '>', $now->subDays(7))->count(), // 過去7日間の新規施工依頼数
        ];
    }
        */
    /**
     * 施工依頼の統計情報を取得（ダミーデータ）
     *
     * 注: これは将来の実装のためのプレースホルダーです。
     * 実際のデータではなく、表示例としてのダミーデータを返します。
     *
     * @return array
     */
    private function getWorkplaceStats()
    {
        return [
            'totalWorkplaces' => 500,
            'workplacesByStatus' => [
                '未受領' => 50,
                '確認中' => 100,
                '受領済み' => 200,
                '進行中' => 100,
                '完了' => 50
            ],
            'newWorkplaces' => 30,
        ];
    }
    /**
     * 最近のアクティビティログを取得
     * 
     * 注: この機能は将来の拡張のために予約されています。
     * 現在は実装されていません。
     *
     * @return array
     */
    private function getRecentActivities()
    {
        // 将来の実装のためのプレースホルダー
        return [];
    }

}