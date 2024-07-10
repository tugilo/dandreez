<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Assign;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        }, 'assigns.workplace.customer'])  // customer リレーションを追加
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

        Log::info('生成したカレンダーデータ', ['days' => count($calendar)]);

        return view('saler.assignments.worker_view', compact('workers', 'month', 'calendar'));
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
}