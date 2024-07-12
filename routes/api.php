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
Route::get('/workers-for-workplace/{workplace}', [ApiController::class, 'getWorkersForWorkplace']);
Route::post('/assign', [ApiController::class, 'createAssign']);

Route::post('/assign-from-calendar', [WorkplaceController::class, 'storeAssignFromCalendar']);
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