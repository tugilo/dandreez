<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\Worker;
use App\Models\Assign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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

    /**
     * 複数の職人を施工依頼にアサインするメソッド
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAssign(Request $request)
    {
        Log::info('アサインリクエストを受信', $request->all());

        $validator = Validator::make($request->all(), [
            'workplace_id' => 'required|exists:workplaces,id',
            'assign_date' => 'required|date',
            'worker_assignments' => 'required|array',
            'worker_assignments.*.worker_id' => 'required|exists:workers,id',
            'worker_assignments.*.start_time' => 'required|date_format:H:i',
            'worker_assignments.*.end_time' => 'required|date_format:H:i|after:worker_assignments.*.start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $workplace = Workplace::findOrFail($request->workplace_id);
            
            foreach ($request->worker_assignments as $assignment) {
                Assign::updateOrCreate(
                    [
                        'workplace_id' => $workplace->id,
                        'worker_id' => $assignment['worker_id'],
                        'start_date' => $request->assign_date,
                    ],
                    [
                        'end_date' => $request->assign_date,
                        'start_time' => $assignment['start_time'],
                        'end_time' => $assignment['end_time'],
                        'saler_id' => $workplace->saler_id,
                        'saler_staff_id' => $workplace->saler_staff_id,
                        'construction_company_id' => $workplace->construction_company_id,
                        'show_flg' => 1,
                    ]
                );
            }

            if ($workplace->status_id == 1) {
                $workplace->status_id = 3;
                $workplace->save();
            }

            DB::commit();

            Log::info('アサインが正常に作成されました', [
                'workplace_id' => $workplace->id,
                'worker_count' => count($request->worker_assignments),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'アサインが正常に作成されました。',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('アサイン作成中にエラーが発生しました', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'アサインの作成に失敗しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 指定された日付での職人の可用性をチェックするメソッド
     *
     * @param int $workerId 職人ID
     * @param string $date チェックする日付（Y-m-d形式）
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkWorkerAvailability($workerId, $date)
    {
        Log::info('職人の可用性チェックを開始', ['worker_id' => $workerId, 'date' => $date]);

        try {
            // 職人の存在確認
            $worker = Worker::findOrFail($workerId);

            // 指定された日付の既存アサインを取得
            $existingAssigns = Assign::where('worker_id', $workerId)
                ->whereDate('start_date', $date)
                ->where('show_flg', 1)
                ->get();

            // アサインが存在しない場合は可用性あり
            if ($existingAssigns->isEmpty()) {
                Log::info('職人は指定された日付でアサイン可能です', ['worker_id' => $workerId, 'date' => $date]);
                return response()->json(['available' => true]);
            }

            // アサインが存在する場合、時間の重複をチェック
            // ここでは単純に既存のアサインがある場合は不可としていますが、
            // 必要に応じて時間の重複チェックロジックを実装できます
            Log::info('職人は指定された日付で既にアサインされています', ['worker_id' => $workerId, 'date' => $date]);
            return response()->json(['available' => false]);

        } catch (\Exception $e) {
            Log::error('職人の可用性チェック中にエラーが発生しました', [
                'worker_id' => $workerId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'available' => false,
                'error' => '可用性チェック中にエラーが発生しました。'
            ], 500);
        }
    }
    /**
     * 職人のアサインを作成または更新する
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignWorker(Request $request)
    {
        Log::info('職人アサインリクエストを受信', $request->all());

        // リクエストデータのバリデーション
        $validator = Validator::make($request->all(), [
            'worker_id' => 'required|exists:workers,id',
            'assign_date' => 'required|date',
            'assignments' => 'required|array',
            'assignments.*.workplace_id' => 'required|exists:workplaces,id',
            'assignments.*.start_time' => 'required|date_format:H:i',
            'assignments.*.end_time' => 'required|date_format:H:i|after:assignments.*.start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $worker = Worker::findOrFail($request->worker_id);
            $assignDate = Carbon::parse($request->assign_date);

            // 既存のアサインを取得し、show_flgを0に設定
            Assign::where('worker_id', $worker->id)
                  ->whereDate('start_date', $assignDate)
                  ->update(['show_flg' => 0]);

            // 新しいアサインを作成
            foreach ($request->assignments as $assignment) {
                $workplace = Workplace::findOrFail($assignment['workplace_id']);

                Assign::create([
                    'workplace_id' => $workplace->id,
                    'worker_id' => $worker->id,
                    'start_date' => $assignDate,
                    'end_date' => $assignDate,
                    'start_time' => $assignment['start_time'],
                    'end_time' => $assignment['end_time'],
                    'saler_id' => $workplace->saler_id,
                    'saler_staff_id' => $workplace->saler_staff_id,
                    'construction_company_id' => $worker->construction_company_id,
                    'show_flg' => 1,
                ]);
            }

            DB::commit();

            Log::info('職人アサインが正常に作成されました', [
                'worker_id' => $worker->id,
                'assign_date' => $assignDate,
                'assignment_count' => count($request->assignments),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'アサインが正常に作成されました。',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('職人アサイン作成中にエラーが発生しました', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'アサインの作成に失敗しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 既存のアサインを取得する
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExistingAssigns(Request $request)
    {
        Log::info('既存アサイン取得リクエストを受信', $request->all());

        $validator = Validator::make($request->all(), [
            'worker_id' => 'required|exists:workers,id',
            'assign_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $assigns = Assign::where('worker_id', $request->worker_id)
                             ->whereDate('start_date', $request->assign_date)
                             ->where('show_flg', 1)
                             ->with('workplace')
                             ->get()
                             ->map(function ($assign) {
                                 return [
                                     'workplace_id' => $assign->workplace_id,
                                     'workplace_name' => $assign->workplace->name,
                                     'start_time' => $assign->start_time->format('H:i'),
                                     'end_time' => $assign->end_time->format('H:i'),
                                 ];
                             });

            Log::info('既存アサインを取得しました', [
                'worker_id' => $request->worker_id,
                'assign_date' => $request->assign_date,
                'assign_count' => $assigns->count(),
            ]);

            return response()->json([
                'success' => true,
                'assigns' => $assigns,
            ]);

        } catch (\Exception $e) {
            Log::error('既存アサイン取得中にエラーが発生しました', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '既存アサインの取得に失敗しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * 月別のアサイン状況を取得する
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyAssignments(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $workplaces = Workplace::with(['assigns' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->where('show_flg', 1)
                  ->with('worker');
        }])
        ->where('show_flg', 1)
        ->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('construction_start', [$startDate, $endDate])
                ->orWhereBetween('construction_end', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('construction_start', '<=', $startDate)
                      ->where('construction_end', '>=', $endDate);
                });
        })
        ->get();

        $assignments = [];
        foreach ($workplaces as $workplace) {
            $assignments[$workplace->id] = [
                'name' => $workplace->name,
                'construction_start' => $workplace->construction_start->format('Y-m-d'),
                'construction_end' => $workplace->construction_end->format('Y-m-d'),
                'assigns' => []
            ];

            foreach ($workplace->assigns as $assign) {
                $assignments[$workplace->id]['assigns'][] = [
                    'date' => $assign->start_date->format('Y-m-d'),
                    'worker_name' => $assign->worker->name,
                    'start_time' => $assign->start_time->format('H:i'),
                    'end_time' => $assign->end_time->format('H:i')
                ];
            }
        }

        return response()->json($assignments);
    }
}
