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
use App\Http\Controllers\NotificationContentController;
use App\Http\Controllers\SalerWorkplaceController;
use App\Http\Controllers\StatusController;

// 標準の認証ルート（ログイン、ログアウト、パスワードリセット）
Auth::routes();

// ホームページへのルート
Route::get('/', function() {
    return redirect()->route('login');
});

// ホームページがログイン後に表示されるように
Route::get('/home', [HomeController::class, 'index'])->name('home');

// システム管理者のルート
Route::prefix('admin')->middleware('auth', 'can:admin')->group(function () {
    Route::get('home', [AdminController::class, 'index'])->name('admin.home');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('statuses', StatusController::class);
    Route::post('statuses/{status}/restore', [StatusController::class, 'restore'])->name('statuses.restore');
});

// 得意先用のルート
Route::prefix('customer')->middleware('auth', 'can:access-customer')->group(function () {
    Route::get('home', [CustomerController::class, 'index'])->name('customer.home');
    Route::resource('workplaces', WorkplaceController::class)->names([
        'index' => 'customer.workplaces.index',
        'create' => 'customer.workplaces.create',
        'store' => 'customer.workplaces.store',
        'show' => 'customer.workplaces.show',
        'edit' => 'customer.workplaces.edit',
        'update' => 'customer.workplaces.update',
        'destroy' => 'customer.workplaces.destroy',
    ]);

    // 施工依頼の詳細ページ
    Route::get('workplaces/{id}/details', [WorkplaceController::class, 'details'])->name('customer.workplaces.details');
    // 指示内容の保存、更新、削除のルート
    Route::post('workplaces/{id}/instructions', [WorkplaceController::class, 'storeInstructions'])->name('customer.instructions.store');
    Route::put('workplaces/{id}/instructions', [WorkplaceController::class, 'updateInstruction'])->name('customer.instructions.update');
    Route::delete('workplaces/{id}/instructions', [WorkplaceController::class, 'deleteInstruction'])->name('customer.instructions.delete');
    Route::post('workplaces/{id}/files', [FileController::class, 'store'])->name('customer.workplaces.files.store');
    Route::put('workplaces/{workplaceId}/files/{id}', [FileController::class, 'update'])->name('customer.workplaces.files.update');
    Route::delete('workplaces/{workplaceId}/files/{id}', [FileController::class, 'destroy'])->name('customer.workplaces.files.destroy');
    Route::post('workplaces/{workplaceId}/photos', [PhotoController::class, 'store'])->name('customer.workplaces.photos.store');
    Route::put('workplaces/{workplaceId}/photos/{id}', [PhotoController::class, 'update'])->name('customer.workplaces.photos.update');
    Route::delete('workplaces/{workplaceId}/photos/{id}', [PhotoController::class, 'destroy'])->name('customer.workplaces.photos.destroy');
});

// 問屋用のルート
Route::prefix('saler')->middleware('auth', 'can:access-saler')->group(function () {
    Route::get('home', [SalerController::class, 'index'])->name('saler.home');
    Route::resource('saler_companies', SalerCompanyController::class);
    Route::resource('workplaces', SalerWorkplaceController::class)->names([
        'index' => 'saler.workplaces.index',
        'create' => 'saler.workplaces.create',
        'store' => 'saler.workplaces.store',
        'show' => 'saler.workplaces.show',
        'edit' => 'saler.workplaces.edit',
        'update' => 'saler.workplaces.update',
        'destroy' => 'saler.workplaces.destroy'
    ]);
    Route::post('workplaces/{id}/instructions', [SalerWorkplaceController::class, 'storeInstructions'])->name('saler.workplaces.instructions.store');
});

// 施工業者用のルート
Route::prefix('worker')->middleware('auth', 'can:access-worker')->group(function () {
    Route::get('home', [WorkerController::class, 'index'])->name('worker.home');
    Route::resource('construction_companies', ConstructionCompanyController::class);
});

// Workplace用のルート
Route::prefix('workplaces')->middleware('auth')->group(function () {
    Route::post('{id}/instructions', [WorkplaceController::class, 'storeInstructions'])->name('instructions.store');
    Route::put('{id}/instructions', [WorkplaceController::class, 'updateInstruction'])->name('instructions.update');
    Route::delete('{id}/instructions', [WorkplaceController::class, 'deleteInstruction'])->name('instructions.delete');
    Route::post('{id}/files', [FileController::class, 'store'])->name('files.store');
    Route::put('{workplaceId}/files/{id}', [FileController::class, 'update'])->name('files.update');
    Route::delete('{workplaceId}/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::post('{workplaceId}/photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::put('{workplaceId}/photos/{id}', [PhotoController::class, 'update'])->name('photos.update');
    Route::delete('{workplaceId}/photos/{id}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::get('{id}/details', [WorkplaceController::class, 'details'])->name('workplaces.details');
});

// NotificationContent用のルート
Route::resource('notification_contents', NotificationContentController::class)->middleware('auth');
