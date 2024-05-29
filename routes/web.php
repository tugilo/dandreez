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
use App\Http\Controllers\InstructionController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationContentController; // 新しく追加

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

// Workplaceコントローラーのリソースルート
Route::resource('workplaces', WorkplaceController::class);

// Instructionコントローラーのリソースルート
Route::post('workplaces/{id}/instructions', [WorkplaceController::class, 'storeInstructions'])->name('instructions.store');

// Photoコントローラーのリソースルート
Route::post('/workplaces/{workplaceId}/photos', [PhotoController::class, 'store'])->name('photos.store');
Route::put('workplaces/{workplaceId}/photos/{id}', [PhotoController::class, 'update'])->name('photos.update');
Route::delete('workplaces/{workplaceId}/photos/{id}', [PhotoController::class, 'destroy'])->name('photos.destroy');


Route::put('instructions/{id}', [WorkplaceController::class, 'updateInstruction'])->name('instructions.update');
Route::delete('instructions/{id}', [WorkplaceController::class, 'deleteInstruction'])->name('instructions.delete');

// Fileコントローラーのリソースルート
Route::post('workplaces/{id}/files', [FileController::class, 'store'])->name('files.store');

// Workplace詳細設定画面
Route::get('workplaces/{id}/details', [WorkplaceController::class, 'details'])->name('workplaces.details');

// NotificationContentコントローラーのリソースルート
Route::resource('notification_contents', NotificationContentController::class)->middleware('auth');
