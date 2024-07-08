<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use App\Models\Login;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $login = Login::where('user_id', $user->id)->first();

                if ($login) {
                    switch ($login->user_type_id) {
                        case 2: // CustomerStaff
                            Config::set('adminlte.dashboard_url', route('customer.home'));
                            break;
                        case 3: // SalerStaff
                            Config::set('adminlte.dashboard_url', route('saler.home'));
                            break;
                        case 4: // Worker
                            Config::set('adminlte.dashboard_url', route('worker.home'));
                            break;
                        default:
                            Config::set('adminlte.dashboard_url', route('home'));
                    }
                }
            }
        });
    }
}
