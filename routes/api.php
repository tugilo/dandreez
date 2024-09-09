<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\WorkplaceController;
use App\Models\Zip;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 現場に関連する職人を取得するルート
Route::get('/workers-for-workplace/{workplace}', [ApiController::class, 'getWorkersForWorkplace']);

// アサインを作成するルート
Route::post('/assign', [ApiController::class, 'createAssign']);

// カレンダーからのアサイン作成ルート
Route::post('/assign-from-calendar', [WorkplaceController::class, 'storeAssignFromCalendar']);

// 職人の可用性をチェックするルート
Route::get('/check-worker-availability/{workerId}/{date}', [ApiController::class, 'checkWorkerAvailability']);

// 職人のアサイン作成・更新ルート
Route::post('/assign-worker', [ApiController::class, 'assignWorker']);

// 既存のアサイン取得ルート
Route::get('/existing-assigns', [ApiController::class, 'getExistingAssigns']);

// 月別アサイン状況を取得するルート
Route::get('/monthly-assignments', [ApiController::class, 'getMonthlyAssignments']);
// 郵便番号から住所を取得するルート
Route::get('/address', function (Request $request) {
    $zip = $request->query('zip');
    $address = Zip::where('zip', $zip)->first();
    if ($address) {
        return response()->json([
            'prefecture' => $address->prefecture,
            'city' => $address->city,
            'address' => $address->address
        ]);
    }
    return response()->json(null);
});