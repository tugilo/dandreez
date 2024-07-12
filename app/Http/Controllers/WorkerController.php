<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Assign;
use App\Models\Workplace;
use App\Models\Notification;
use App\Models\Dailyreport;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WorkerController extends Controller
{
    public function index()
    {
        $worker = auth()->user()->worker;
        
        $todayAssignment = $worker->assigns()
            ->with('workplace')
            ->whereDate('start_date', now())
            ->first();

        $weeklyAssignments = $this->getWeeklyAssignments($worker);
        $recentWork = $this->getRecentWork($worker);
        $pendingReports = $this->getPendingReports($worker);
        $notifications = $this->getNotifications($worker);
        $nextWorkplace = $this->getNextWorkplace($worker);

        return view('worker.home', compact(
            'worker',
            'todayAssignment',
            'weeklyAssignments',
            'recentWork',
            'pendingReports',
            'notifications',
            'nextWorkplace'
        ));
    }

    public function createReport()
    {
        $worker = auth()->user()->worker;
        $assignments = $worker->assigns()->whereDate('start_date', '<=', Carbon::today())
                              ->whereDate('end_date', '>=', Carbon::today())
                              ->with('workplace')
                              ->get();

        return view('worker.reports.create', compact('assignments'));
    }

    public function storeReport(Request $request)
    {
        Log::info('storeReport method called', ['request' => $request->all()]);
    
        try {
            $validatedData = $request->validate([
                'assign_id' => 'required|exists:assigns,id',
                'report_day' => 'required|date',
                'work_hours' => 'required|numeric|min:0',
                'comment' => 'required|string',
            ]);
    
            Log::info('Validation passed', ['validatedData' => $validatedData]);
    
            $assign = Assign::with('workplace.customer', 'workplace.saler')->findOrFail($validatedData['assign_id']);
            $workplace = $assign->workplace;
    
            Log::info('Assign and Workplace retrieved', [
                'assign_id' => $assign->id,
                'workplace_id' => $workplace->id
            ]);
    
            $report = new Dailyreport();
            $report->worker_id = auth()->user()->worker->id;
            $report->workplace_id = $workplace->id;
            $report->assign_id = $assign->id;
            $report->customer_id = $workplace->customer_id;
            $report->customer_staff_id = $workplace->customer_staff_id ?? $workplace->customer->customerStaffs()->first()->id ?? null;
            $report->saler_id = $workplace->saler_id;
            $report->saler_staff_id = $workplace->saler_staff_id ?? $workplace->saler->salerStaffs()->first()->id ?? null;
            $report->construction_company_id = $assign->construction_company_id;
            $report->report_day = $validatedData['report_day'];
            $report->work_hours = $validatedData['work_hours'];
            $report->comment = $validatedData['comment'];
    
            Log::info('Daily report object created', ['report' => $report->toArray()]);
    
            $report->save();
    
            Log::info('Daily report saved successfully', ['report_id' => $report->id]);
    
            return redirect()->route('worker.home')->with('success', '日報が正常に提出されました。');
        } catch (\Exception $e) {
            Log::error('Error in storeReport method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }        

    public function showAssignment(Assign $assign)
    {
        // 現在のワーカーに関連するアサインのみを表示できるようにする
        if ($assign->worker_id !== auth()->user()->worker->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('worker.assignments.show', compact('assign'));
    }

    public function downloadFile(File $file)
    {
        // ファイルが現在のワーカーに関連するものかチェック
        $worker = auth()->user()->worker;
        $relatedWorkplaceIds = $worker->assigns()->pluck('workplace_id');
        
        if (!$relatedWorkplaceIds->contains($file->workplace_id)) {
            abort(403, 'Unauthorized action.');
        }
    
        $filePath = storage_path('app/public/' . $file->directory . $file->file_name);
        return response()->download($filePath, $file->title);
    }

    private function getWeeklyAssignments($worker)
    {
        return $worker->assigns()
            ->whereBetween('start_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->with('workplace')
            ->get()
            ->map(function ($assign) {
                $assign->start_time = $assign->start_time ?? Carbon::parse('09:00');
                $assign->end_time = $assign->end_time ?? Carbon::parse('17:00');
                return $assign;
            })
            ->groupBy(function($assign) {
                return $assign->start_date->format('Y-m-d');
            });
    }

    private function getRecentWork($worker)
    {
        return $worker->assigns()
            ->whereBetween('start_date', [Carbon::now()->subWeek(), Carbon::now()])
            ->with('workplace')
            ->get();
    }

    private function getPendingReports($worker)
    {
        // この実装は仮のものです。実際のデータベース構造に合わせて調整が必要です。
        return $worker->assigns()
            ->whereDoesntHave('dailyreports')
            ->where('start_date', '<', Carbon::today())
            ->pluck('start_date');
    }

    private function getNotifications($worker)
    {
        // 一時的に空の配列を返す
        return [];
    }

    private function getNextWorkplace($worker)
    {
        return $worker->assigns()
            ->where('start_date', '>', Carbon::now())
            ->with('workplace', 'workplace.instructions')
            ->first();
    }

    public function startWork(Request $request)
    {
        $worker = auth()->user()->worker;
        $currentAssignment = $worker->getCurrentAssignment();
        
        if ($currentAssignment) {
            // 作業開始時間を記録
            $currentAssignment->update(['actual_start_time' => now()]);
            return response()->json(['message' => '作業を開始しました。']);
        }
        
        return response()->json(['message' => '本日の予定がありません。'], 400);
    }

    public function endWork(Request $request)
    {
        $worker = auth()->user()->worker;
        $currentAssignment = $worker->getCurrentAssignment();
        
        if ($currentAssignment && $currentAssignment->actual_start_time) {
            // 作業終了時間を記録
            $currentAssignment->update(['actual_end_time' => now()]);
            return response()->json(['message' => '作業を終了しました。日報の作成をお願いします。']);
        }
        
        return response()->json(['message' => '作業が開始されていません。'], 400);
    }
    public function workplaceIndex()
    {
        Log::info('現場一覧の表示を開始');

        $worker = auth()->user()->worker;
        Log::debug('worker', ['worker' => $worker]);

        $workplaces = Workplace::whereHas('assigns', function($query) use ($worker) {
            $query->where('worker_id', $worker->id)
                  ->whereDate('start_date', '>=', now());
        })
        ->with(['customer', 'assigns' => function($query) use ($worker) {
            $query->where('worker_id', $worker->id)
                  ->orderBy('start_date');
        }])
        ->get()
        ->map(function ($workplace) {
            $workplace->period_start = $workplace->assigns->min('start_date');
            $workplace->period_end = $workplace->assigns->max('end_date');
            return $workplace;
        });
        Log::debug('workplaces', ['workplaces' => $workplaces]);
  
        return view('worker.workplaces.index', compact('workplaces'));
    }

    public function workplaceShow(Workplace $workplace)
    {
        // 現在のワーカーに関連するアサインメントのみを取得
        $assignments = $workplace->assigns()
            ->where('worker_id', auth()->user()->worker->id)
            ->orderBy('start_date')
            ->get();

        $workplace->period_start = $assignments->min('start_date');
        $workplace->period_end = $assignments->max('end_date');

        return view('worker.workplaces.show', compact('workplace', 'assignments'));
    }
}