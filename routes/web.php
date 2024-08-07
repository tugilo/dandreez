<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkplaceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\NotificationContentController;
use App\Http\Controllers\InstructionController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\FileController;

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
    Route::get('workplaces', [WorkplaceController::class, 'index'])->name('customer.workplaces.index')->defaults('role', 'customer');
    Route::get('workplaces/create', [WorkplaceController::class, 'create'])->name('customer.workplaces.create')->defaults('role', 'customer');
    Route::post('workplaces', [WorkplaceController::class, 'store'])->name('customer.workplaces.store')->defaults('role', 'customer');
    Route::get('workplaces/{role}/{id}/edit', [WorkplaceController::class, 'edit'])->name('customer.workplaces.edit')->defaults('role', 'customer');
    Route::put('workplaces/{role}/{id}', [WorkplaceController::class, 'update'])->name('customer.workplaces.update')->defaults('role', 'customer');
    Route::delete('workplaces/{id}', [WorkplaceController::class, 'destroy'])->name('customer.workplaces.destroy')->defaults('role', 'customer');
    Route::get('workplaces/{role}/{id}/details', [WorkplaceController::class, 'details'])->name('customer.workplaces.details');
    Route::post('workplaces/{role}/{id}/instructions', [WorkplaceController::class, 'storeInstructions'])->name('customer.workplaces.instructions.store')->defaults('role', 'customer');
    Route::put('workplaces/{role}/{id}/instructions', [WorkplaceController::class, 'updateInstruction'])->name('customer.workplaces.instructions.update')->defaults('role', 'customer');
    Route::delete('workplaces/{role}/{id}/instructions', [WorkplaceController::class, 'deleteInstruction'])->name('customer.workplaces.instructions.delete')->defaults('role', 'customer');
    Route::post('workplaces/{role}/{workplaceId}/files', [FileController::class, 'store'])->name('customer.workplaces.files.store')->defaults('role', 'customer');
    Route::put('workplaces/{role}/{workplaceId}/files/{id}', [FileController::class, 'update'])->name('customer.workplaces.files.update')->defaults('role', 'customer');
    Route::delete('workplaces/{role}/{workplaceId}/files/{id}', [FileController::class, 'destroy'])->name('customer.workplaces.files.destroy')->defaults('role', 'customer');
    Route::post('workplaces/{role}/{workplaceId}/photos', [PhotoController::class, 'store'])->name('customer.workplaces.photos.store')->defaults('role', 'customer');
    Route::put('workplaces/{role}/{workplaceId}/photos/{id}', [PhotoController::class, 'update'])->name('customer.workplaces.photos.update')->defaults('role', 'customer');
    Route::delete('workplaces/{role}/{workplaceId}/photos/{id}', [PhotoController::class, 'destroy'])->name('customer.workplaces.photos.destroy')->defaults('role', 'customer');
});

// 問屋用のルート
Route::prefix('saler')->middleware('auth', 'can:access-saler')->group(function () {
    Route::get('home', [SalerController::class, 'index'])->name('saler.home');
    Route::get('workplaces', [WorkplaceController::class, 'index'])->name('saler.workplaces.index')->defaults('role', 'saler');
    Route::get('workplaces/create', [WorkplaceController::class, 'create'])->name('saler.workplaces.create')->defaults('role', 'saler');
    Route::post('workplaces', [WorkplaceController::class, 'store'])->name('saler.workplaces.store')->defaults('role', 'saler');
    Route::get('workplaces/{role}/{id}/edit', [WorkplaceController::class, 'edit'])->name('saler.workplaces.edit')->defaults('role', 'saler');
    Route::put('workplaces/{role}/{id}', [WorkplaceController::class, 'update'])->name('saler.workplaces.update')->defaults('role', 'saler');
    Route::delete('workplaces/{id}', [WorkplaceController::class, 'destroy'])->name('saler.workplaces.destroy')->defaults('role', 'saler');
    Route::get('workplaces/{role}/{id}/details', [WorkplaceController::class, 'details'])->name('saler.workplaces.details')->defaults('role', 'saler');
    Route::post('workplaces/{role}/{id}/instructions', [WorkplaceController::class, 'storeInstructions'])->name('saler.workplaces.instructions.store')->defaults('role', 'saler');
    Route::put('workplaces/{role}/{id}/instructions', [WorkplaceController::class, 'updateInstruction'])->name('saler.workplaces.instructions.update')->defaults('role', 'saler');
    Route::delete('workplaces/{role}/{id}/instructions', [WorkplaceController::class, 'deleteInstruction'])->name('saler.workplaces.instructions.delete')->defaults('role', 'saler');
    Route::post('workplaces/{role}/{workplaceId}/files', [FileController::class, 'store'])->name('saler.workplaces.files.store')->defaults('role', 'saler');
    Route::put('workplaces/{role}/{workplaceId}/files/{id}', [FileController::class, 'update'])->name('saler.workplaces.files.update')->defaults('role', 'saler');
    Route::delete('workplaces/{role}/{workplaceId}/files/{id}', [FileController::class, 'destroy'])->name('saler.workplaces.files.destroy')->defaults('role', 'saler');
    Route::post('workplaces/{role}/{workplaceId}/photos', [PhotoController::class, 'store'])->name('saler.workplaces.photos.store')->defaults('role', 'saler');
    Route::put('workplaces/{role}/{workplaceId}/photos/{id}', [PhotoController::class, 'update'])->name('saler.workplaces.photos.update')->defaults('role', 'saler');
    Route::delete('workplaces/{role}/{workplaceId}/photos/{id}', [PhotoController::class, 'destroy'])->name('saler.workplaces.photos.destroy')->defaults('role', 'saler');
    // 承認ルート
    Route::post('workplaces/{role}/{id}/approve', [WorkplaceController::class, 'approve'])->name('saler.workplaces.approve')->defaults('role', 'saler');
    Route::post('workplaces/{role}/{id}/reject', [WorkplaceController::class, 'reject'])->name('saler.workplaces.reject')->defaults('role', 'saler');
    // アサインルート
    Route::post('workplaces/{id}/assign', [WorkplaceController::class, 'storeAssign'])->name('saler.workplaces.assign.store')->defaults('role', 'saler');
    Route::post('workplaces/{id}/unassign', [WorkplaceController::class, 'unassignWorker'])->name('saler.workplaces.unassign')->defaults('role', 'saler');
});

// 施工業者用のルート
Route::prefix('worker')->middleware('auth', 'can:access-worker')->group(function () {
    Route::get('home', [WorkerController::class, 'index'])->name('worker.home');
    Route::resource('construction_companies', ConstructionCompanyController::class);
});

// NotificationContent用のルート
Route::resource('notification_contents', NotificationContentController::class)->middleware('auth');

<<<<<<< HEAD
// SalerCompanyコントローラーのリソースルート
Route::resource('saler_companies', SalerCompanyController::class);

// ConstructionCompanyコントローラーのリソースルート
Route::resource('construction_companies', ConstructionCompanyController::class);

// WorkplaceControllerのリソースルート
Route::resource('workplaces', WorkplaceController::class);
Route::get('workplaces/{id}/details', [WorkplaceController::class, 'details'])->name('workplaces.details');

// Instructions, Photos, Files へのルート
Route::post('workplaces/{id}/instructions', [InstructionController::class, 'store'])->name('instructions.store');
Route::post('workplaces/{id}/photos', [PhotoController::class, 'store'])->name('photos.store');
Route::post('workplaces/{id}/files', [FileController::class, 'store'])->name('files.store');

Route::get('workplaces/{id}/details', [WorkplaceController::class, 'details'])->name('workplaces.details');

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

// 通知内容管理のルート設定
Route::resource('notification_contents', NotificationContentController::class);
=======
Route::post('/check-overlap', [WorkplaceController::class, 'checkOverlap'])->name('workplaces.check-overlap')->middleware('web');
>>>>>>> develop
