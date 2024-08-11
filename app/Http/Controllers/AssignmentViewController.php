<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Assign;
use App\Models\Workplace;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AssignmentViewController extends Controller
{
    public function workerView(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        Log::info('表示月の範囲', ['start' => $startDate->toDateString(), 'end' => $endDate->toDateString(), 'requested_month' => $month]);

        $workers = Worker::with(['assigns' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->where('show_flg', 1);
        }, 'assigns.workplace.customer'])
            ->get();

        Log::info('取得した職人数', ['count' => $workers->count()]);

        foreach ($workers as $worker) {
            Log::info('職人のアサイン', [
                'worker_id' => $worker->id,
                'assign_count' => $worker->assigns->count(),
                'assigns' => $worker->assigns->map(function ($assign) {
                    return [
                        'id' => $assign->id,
                        'start_date' => $assign->start_date,
                        'end_date' => $assign->end_date,
                        'show_flg' => $assign->show_flg,
                        'workplace_name' => $assign->workplace->name,
                        'customer_name' => $assign->workplace->customer->name,
                    ];
                }),
            ]);
        }

        $calendar = $this->generateCalendar($startDate, $endDate);

        // 未アサインの現場を取得
        $unassignedWorkplaces = Workplace::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('construction_start', [$startDate, $endDate])
                  ->orWhereBetween('construction_end', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('construction_start', '<', $startDate)
                        ->where('construction_end', '>', $endDate);
                  });
        })->where('status_id', 3) // 承認済みの現場のみ
          ->get()
          ->map(function ($workplace) use ($startDate, $endDate) {
              $assignedDates = Assign::where('workplace_id', $workplace->id)
                  ->whereBetween('start_date', [$startDate, $endDate])
                  ->where('show_flg', 1)
                  ->pluck('start_date')
                  ->unique()
                  ->toArray();
    
              $constructionDates = collect(CarbonPeriod::create(
                  max($workplace->construction_start, $startDate),
                  min($workplace->construction_end, $endDate)
              ))->map(function ($date) {
                  return $date->format('Y-m-d');
              })->toArray();
    
              $unassignedDates = array_diff($constructionDates, $assignedDates);
              $workplace->unassigned_dates = $unassignedDates;
    
              return $workplace;
          })
          ->filter(function ($workplace) {
              return count($workplace->unassigned_dates) > 0;
          });
    
        Log::info('生成したカレンダーデータ', ['days' => count($calendar)]);

        return view('saler.assignments.worker_view', compact('workers', 'month', 'calendar', 'unassignedWorkplaces'));
        
    }

    /**
     * 指定された期間のカレンダーデータを生成する
     */
    private function generateCalendar($startDate, $endDate)
    {
        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $calendar[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->format('d'),
                'dayOfWeek' => $currentDate->isoFormat('ddd')
            ];
            $currentDate->addDay();
        }

        return $calendar;
    }

    /**
     * アサイン情報のツールチップ内容を生成する
     */
    public function getAssignmentTooltip($worker, $date)
    {
        $assign = $worker->assigns->where('start_date', $date)->first();
        if (!$assign) {
            return '';
        }
        
        Log::info('ツールチップ情報生成', ['worker' => $worker->id, 'date' => $date]);

        return "<strong>現場名:</strong> {$assign->workplace->name}<br>" .
               "<strong>住所:</strong> {$assign->workplace->address}<br>" .
               "<strong>開始時間:</strong> {$assign->start_time}";
    }

    public function storeAssignFromCalendar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workplace_id' => 'required|exists:workplaces,id',
            'worker_id' => 'required|exists:workers,id',
            'assign_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $workplace = Workplace::findOrFail($request->workplace_id);

            Assign::updateOrCreate(
                [
                    'workplace_id' => $request->workplace_id,
                    'worker_id' => $request->worker_id,
                    'start_date' => $request->assign_date,
                ],
                [
                    'end_date' => $request->assign_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'show_flg' => 1,
                ]
            );

            DB::commit();

            return response()->json(['success' => true, 'message' => 'アサインが正常に作成されました。']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'アサインの作成中にエラーが発生しました。: ' . $e->getMessage()], 500);
        }
    }
}