<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workplace;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SiteAssignmentViewController extends Controller
{
    /**
     * 現場別アサイン状況を表示
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function siteView(Request $request)
    {
        // リクエストから月を取得、デフォルトは現在の月
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        // ログに表示月の範囲を記録
        Log::info('表示月の範囲', ['start' => $startDate->toDateString(), 'end' => $endDate->toDateString(), 'requested_month' => $month]);

        // 当月の全ての現場を取得
        $workplaces = Workplace::with(['assigns' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->where('show_flg', 1);
        }, 'assigns.worker', 'customer'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('construction_start', [$startDate, $endDate])
                      ->orWhereBetween('construction_end', [$startDate, $endDate])
                      ->orWhere(function ($query) use ($startDate, $endDate) {
                          $query->where('construction_start', '<=', $startDate)
                                ->where('construction_end', '>=', $endDate);
                      });
            })
            ->get();

        // 取得した現場数をログに記録
        Log::info('取得した現場数', ['count' => $workplaces->count()]);

        // カレンダーデータを生成
        $calendar = $this->generateCalendar($startDate, $endDate);

        // ビューを返す
        return view('saler.assignments.site_view', compact('workplaces', 'month', 'calendar'));
    }

    /**
     * カレンダーデータを生成
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
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
}