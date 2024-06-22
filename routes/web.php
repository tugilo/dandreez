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
    Route::resource('customer_companies', CustomerCompanyController::class);
});

// 問屋用のルート
Route::prefix('saler')->middleware('auth', 'can:access-saler')->group(function () {
    Route::get('home', [SalerController::class, 'index'])->name('saler.home');
    Route::resource('saler_companies', SalerCompanyController::class);
    Route::get('workplaces', [SalerWorkplaceController::class, 'index'])->name('saler.workplaces.index');
    Route::get('workplaces/create', [SalerWorkplaceController::class, 'create'])->name('saler.workplaces.create');
    Route::post('workplaces', [SalerWorkplaceController::class, 'store'])->name('saler.workplaces.store');
    Route::get('workplaces/{id}/edit', [SalerWorkplaceController::class, 'edit'])->name('saler.workplaces.edit');
    Route::put('workplaces/{id}', [SalerWorkplaceController::class, 'update'])->name('saler.workplaces.update');
    Route::delete('workplaces/{id}', [SalerWorkplaceController::class, 'destroy'])->name('saler.workplaces.destroy');
    Route::get('workplaces/{id}', [SalerWorkplaceController::class, 'show'])->name('saler.workplaces.show');
    Route::post('workplaces/{id}/instructions', [SalerWorkplaceController::class, 'storeInstructions'])->name('saler.workplaces.instructions.store');
});

// 施工業者用のルート
Route::prefix('worker')->middleware('auth', 'can:access-worker')->group(function () {
    Route::get('home', [WorkerController::class, 'index'])->name('worker.home');
    Route::resource('construction_companies', ConstructionCompanyController::class);
});

// Workplace用のルート
Route::prefix('workplaces')->middleware('auth')->group(function () {
    Route::resource('/', WorkplaceController::class);
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
