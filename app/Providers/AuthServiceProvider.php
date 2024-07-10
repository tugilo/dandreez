<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * ポリシーとモデルのマッピングを行います。
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * アプリケーションの認証・認可サービスを登録します。
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // 管理者アクセス権限を持っているかどうかをチェック
        Gate::define('access-admin', function (Login $login) {
            $canAccess = $login->userType->type === 'admin';
//            Log::info('Admin Access Check:', ['user_id' => $login->id, 'can_access' => $canAccess]);
            return $canAccess;
        });

        // 得意先アクセス権限を持っているかどうかをチェック
        Gate::define('access-customer', function (Login $login) {
            $canAccess = $login->userType->type === 'customer';
//            Log::info('Customer Access Check:', ['user_id' => $login->id, 'can_access' => $canAccess]);
            return $canAccess;
        });

        // 問屋アクセス権限を持っているかどうかをチェック
        Gate::define('access-saler', function (Login $login) {
            $canAccess = $login->userType->type === 'saler';
//            Log::info('Saler Access Check:', ['user_id' => $login->id, 'can_access' => $canAccess]);
            return $canAccess;
        });

        // 施工業者アクセス権限を持っているかどうかをチェック
        Gate::define('access-worker', function (Login $login) {
            $canAccess = $login->userType->type === 'worker';
//            Log::info('Worker Access Check:', ['user_id' => $login->id, 'can_access' => $canAccess]);
            return $canAccess;
        });

        // デフォルトのログインガードを設定
        Auth::shouldUse('login');

        // より詳細なアクセス制御もここに追加可能です
    }
}
