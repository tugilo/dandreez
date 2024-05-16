<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalerController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\CustomerCompanyController;
use App\Http\Controllers\SalerCompanyController;
use App\Http\Controllers\ConstructionCompanyController;
use App\Http\Controllers\WorkplaceController;

// 標準の認証ルート（ログイン、ログアウト、パスワードリセット）
Auth::routes();

// ホームページへのルート
Route::get('/', function() {
    return redirect()->route('login');
});

// ホームページがログイン後に表示されるように
Route::get('/home', [HomeController::class, 'index'])->name('home');

// システム管理者のホーム
Route::get('/admin/home', [AdminController::class, 'index'])->name('admin.home');
Route::resource('users', UserController::class)->except(['show']);

// 得意先用のホームページ
Route::get('/customer/home', [CustomerController::class, 'index'])->name('customer.home')->middleware('auth');

// 問屋用のホームページ
Route::get('/saler/home', [SalerController::class, 'index'])->name('saler.home')->middleware('auth');

// 施工業者用のホームページ
Route::get('/worker/home', [WorkerController::class, 'index'])->name('worker.home')->middleware('auth');

// CustomerCompanyコントローラのリソースルート
Route::resource('customer_companies', CustomerCompanyController::class);

// SalerCompanyコントローラーのリソースルート
Route::resource('saler_companies', SalerCompanyController::class);

// ConstructionCompanyコントローラーのリソースルート
Route::resource('construction_companies', ConstructionCompanyController::class);

// WorkplaceControllerのリソースルート
Route::resource('workplaces', WorkplaceController::class)->middleware('auth');

// 施工依頼の受注確認ルート
Route::post('workplaces/{id}/approve', [WorkplaceController::class, 'approve'])->name('workplaces.approve')->middleware('auth');

// 施工者アサインフォームの表示ルート
Route::get('workplaces/{id}/assign', [WorkplaceController::class, 'assignForm'])->name('workplaces.assignForm')->middleware('auth');

// 施工者アサインの処理ルート
Route::post('workplaces/{id}/assign', [WorkplaceController::class, 'assign'])->name('workplaces.assign')->middleware('auth');

// 施工依頼の編集
Route::get('workplaces/{id}/edit', [WorkplaceController::class, 'edit'])->name('workplaces.edit');
Route::put('workplaces/{id}', [WorkplaceController::class, 'update'])->name('workplaces.update');

// 施工指示の追加
Route::get('workplaces/{id}/instructions/create', [WorkplaceController::class, 'addInstruction'])->name('instructions.create');
Route::post('workplaces/{id}/instructions', [WorkplaceController::class, 'storeInstruction'])->name('instructions.store');