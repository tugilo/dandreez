<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\WorkplaceController;
use Illuminate\Support\Facades\Log;


class ApiController extends Controller
{
    public function getWorkersForWorkplace(Workplace $workplace)
    {
        // この現場に関連付けられた職人を取得
        // 注: この関係性は適切なモデル設計に基づいて調整する必要があります
        Log::info('Fetching workers for workplace', ['workplace_id' => $workplace->id]);

        // 現場に関連付けられた建設会社がある場合
        if ($workplace->constructionCompany) {
            $workers = $workplace->constructionCompany->workers;
        } else {
            // 建設会社が関連付けられていない場合、すべての職人を取得
            $workers = Worker::all();
        }

        Log::info('Workers fetched', ['count' => $workers->count()]);

        return response()->json($workers);
    }

    public function createAssign(Request $request)
    {
        // WorkplaceControllerのstoreAssignメソッドを呼び出す
        $workplaceController = new WorkplaceController();
        $response = $workplaceController->storeAssign($request);

        // JSONレスポンスを返す
        if ($response->getStatusCode() === 302) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'アサインの作成に失敗しました。']);
        }
    }
}