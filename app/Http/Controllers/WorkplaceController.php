<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\Assign;
use App\Models\ConstructionCompany;
use App\Models\Worker;
use App\Models\Login;
use App\Models\Saler;
use App\Models\SalerStaff;
use App\Models\Customer;
use App\Models\Unit;
use App\Models\Instruction;
use App\Models\Photo;
use App\Models\File;
use App\Models\Zip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;
class WorkplaceController extends Controller
{
    /**
     * 施工依頼の一覧を表示
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @return \Illuminate\View\View
     */
    public function index($role)
    {
        $workplaces = Workplace::with(['customer', 'saler', 'customerStaff', 'salerStaff', 'workers', 'status', 'assigns.worker', 'assigns.constructionCompany'])
            ->get()
            ->map(function ($workplace) {
                $workplace->construction_start = $workplace->construction_start ? $workplace->construction_start->format('Y-m-d') : null;
                $workplace->construction_end = $workplace->construction_end ? $workplace->construction_end->format('Y-m-d') : null;
                return $workplace;
            });
    
        // 既存のアサイン情報を取得
        $assigns = Assign::with('worker')
            ->where('show_flg', 1)
            ->get()
            ->groupBy('workplace_id');
    
        $constructionCompanies = ConstructionCompany::where('show_flg', 1)->get();
        $workers = Worker::where('show_flg', 1)->get();
    
        $routes = $this->getRoutesByRole($role);
    
        return view('workplaces.index', array_merge(
            compact('workplaces', 'role', 'constructionCompanies', 'workers', 'assigns'),
            $routes
        ));
    }    
    /**
     * 新規施工依頼のフォームを表示
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @return \Illuminate\View\View
     */
    public function create($role)
    {
        Log::info('createメソッドが呼び出されました。', ['role' => $role]);

        if ($role == 'customer') {
            $salers = Saler::where('show_flg', 1)->get();
            $customer = Auth::user()->customerStaff->customer;
            $prefectures = Zip::getPrefectures();
            $routes = $this->getRoutesByRole($role);
            $storeRoute = $routes['storeRoute'];
            return view('workplaces.create', array_merge(compact('salers', 'customer', 'prefectures', 'role', 'storeRoute'), $routes));
        } elseif ($role == 'saler') {
            $customers = Customer::where('show_flg', 1)->get();
            $saler = Auth::user()->salerStaff->saler;
            $prefectures = Zip::getPrefectures();
            $routes = $this->getRoutesByRole($role);
            $storeRoute = $routes['storeRoute'];
            return view('workplaces.create', array_merge(compact('customers', 'saler', 'prefectures', 'role', 'storeRoute'), $routes));
        }
    }

    /**
     * 新規施工依頼を保存
     *
     * @param \Illuminate\Http\Request $request リクエストオブジェクト
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $role)
    {
        $validated = $request->validate([
            'saler_id' => 'required|exists:salers,id',
            'saler_staff_id' => 'nullable|exists:saler_staffs,id',
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'construction_start' => 'nullable|date',
            'construction_end' => 'nullable|date',
            'floor_space' => 'nullable|string|max:10',
            'construction_outline' => 'required|string|max:300',
            'memo' => 'nullable|string|max:300',
            'zip' => 'nullable|string|max:7',
            'prefecture' => 'nullable|string|max:128',
            'city' => 'nullable|string|max:128',
            'address' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:13',
        ]);

        Log::info('リクエストデータ:', $request->all());

        $customerStaff = $role == 'customer' ? Auth::user()->customerStaff : null;
        $customer = $role == 'customer' ? $customerStaff->customer : Customer::find($request->customer_id);
        $salerStaff = $role == 'saler' ? Auth::user()->salerStaff : null;

        if ((!$customer || !$customerStaff) && $role == 'customer') {
            return redirect()->route($this->getRoutesByRole($role)['indexRoute'])->with('error', 'ユーザー情報の取得に失敗しました。');
        }

        $workplace = Workplace::create([
            'customer_id' => $customer->id,
            'customer_staff_id' => $customerStaff ? $customerStaff->id : null,
            'saler_id' => $request->saler_id,
            'saler_staff_id' => $salerStaff ? $salerStaff->id : $request->saler_staff_id,
            'name' => $request->name,
            'construction_start' => $request->construction_start,
            'construction_end' => $request->construction_end,
            'floor_space' => $request->floor_space,
            'construction_outline' => $request->construction_outline,
            'memo' => $request->memo,
            'zip' => $request->zip,
            'prefecture' => $request->prefecture,
            'city' => $request->city,
            'address' => $request->address,
            'building' => $request->building,
            'tel' => $request->tel,
        ]);

        Log::info('施工依頼が登録されました。', ['customer_id' => $customer->id]);

        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplace->id])->with('success', '施工依頼が登録されました。詳細を追加してください。');
    }

    /**
     * 施工依頼の詳細設定
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 施工依頼ID
     * @return \Illuminate\View\View
     */
    public function details($role, $id)
    {
        Log::info('detailsメソッドが呼び出されました。', ['role' => $role, 'id' => $id]);

        $workplace = Workplace::where('show_flg', 1)->findOrFail($id);
        $instructions = Instruction::where('workplace_id', $id)->where('show_flg', 1)->get();
        $photos = Photo::where('workplace_id', $id)->where('show_flg', 1)->get();
        $files = File::where('workplace_id', $id)->where('show_flg', 1)->get();
        $units = Unit::where('show_flg', 1)->get();

        $routes = $this->getRoutesByRole($role);
        $storeRoute = $routes['storeRoute'];
        
        // 合計を計算
        $totalAmount = $instructions->filter(function ($instruction) {
            return $instruction->unit->name === 'm'; // unitが'm'のものをフィルタリング
        })->sum('amount');

        $data = compact('workplace', 'instructions', 'photos', 'files', 'units', 'role', 'storeRoute', 'totalAmount');

        // 問屋（Saler）の場合のみ、$workersを追加
        if ($role === 'saler') {
            $workers = Worker::where('show_flg', 1)->get();
            $data['workers'] = $workers;
        }

        Log::info('detailsメソッドのデータ', $data);

        return view('workplaces.details', array_merge($data, $routes));
    }

    /**
     * 職人を施工依頼にアサインするメソッド
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id 施工依頼ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAssign(Request $request, $id)
    {
        Log::info('storeAssign request data:', [
            'request_all' => $request->all(),
            'workplace_id' => $id
        ]);
    
        $validator = Validator::make($request->all(), [
            'workplace_id' => 'required|exists:workplaces,id',
            'workers' => 'required|json',
            'selected_dates' => 'required|json',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
    
        $workplace = Workplace::findOrFail($id);
        $workers = json_decode($request->input('workers'), true);
        $selectedDates = json_decode($request->input('selected_dates'), true);
    
        DB::beginTransaction();
    
        try {
            foreach ($workers as $workerData) {
                $workerId = $workerData['worker_id'];
                $startTime = $workerData['start_time'];
                $endTime = $workerData['end_time'];
    
                $worker = Worker::findOrFail($workerId);
    
                foreach ($selectedDates as $date) {
                    $conflictingAssign = Assign::where('worker_id', $workerId)
                        ->where('start_date', $date)
                        ->where('workplace_id', '!=', $id)
                        ->where('show_flg', 1)
                        ->first();
    
                    if ($conflictingAssign) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "職人 {$worker->name} は {$date} に既に別の現場にアサインされています。"
                        ], 422);
                    }
    
                    // 既存のアサインを探す
                    $existingAssign = Assign::where('workplace_id', $id)
                        ->where('worker_id', $workerId)
                        ->where('start_date', $date)
                        ->where('show_flg', 1)
                        ->first();
    
                    if ($existingAssign) {
                        // 既存のアサインを更新
                        $existingAssign->update([
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                        ]);
                    } else {
                        // 新しいアサインを作成
                        Assign::create([
                            'workplace_id' => $id,
                            'worker_id' => $workerId,
                            'start_date' => $date,
                            'end_date' => $date,
                            'construction_company_id' => $worker->construction_company_id,
                            'saler_id' => $workplace->saler_id,
                            'saler_staff_id' => $workplace->saler_staff_id,
                            'show_flg' => 1,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                        ]);
                    }
                }
            }
    
            if ($workplace->status_id == 1) {
                $workplace->status_id = 3;
                $workplace->save();
            }
    
            DB::commit();
    
            Log::info('Assign updated successfully', [
                'workplace_id' => $id,
                'workers' => $workers,
                'selected_dates' => $selectedDates
            ]);
    
            return response()->json(['success' => true, 'message' => '職人のアサインが更新されました。']);
        } catch (\Exception $e) {
            DB::rollBack();
    
            Log::error('Error in storeAssign', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json(['success' => false, 'message' => 'アサインの更新中にエラーが発生しました。']);
        }
    }
    
    /**
     * 施工指示を保存
     *
     * @param \Illuminate\Http\Request $request リクエストオブジェクト
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 施工依頼ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeInstructions(Request $request, $role, $id)
    {
        Log::info('storeInstructionsメソッドが呼び出されました。', ['role' => $role, 'id' => $id]);
        Log::info('リクエストデータ:', $request->all());
    
        $validated = $request->validate([
            'construction_location' => 'required|string|max:255',
            'construction_location_detail' => 'nullable|string|max:255',
            'product_name' => 'required|string|max:255',
            'product_number' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
        ]);
    
        $instruction = new Instruction($validated);
        $instruction->workplace_id = $id;
        $instruction->save();
    
        Log::info('施工指示が追加されました。', ['instruction' => $instruction]);
    
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $id])
            ->with('success', '指示内容が追加されました。');
    }
        
    /**
     * 指示内容を更新するメソッド
     *
     * @param \Illuminate\Http\Request $request リクエストオブジェクト
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 指示ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateInstruction(Request $request, $role, $id)
    {
        $instructionId = $request->input('instruction_id');
        $instruction = Instruction::findOrFail($instructionId);
    
        $validated = $request->validate([
            'construction_location' => 'required|string|max:255',
            'construction_location_detail' => 'nullable|string|max:255',
            'product_name' => 'required|string|max:255',
            'product_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
        ]);
    
        $instruction->update($validated);
    
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $id])
            ->with('success', '指示内容が更新されました。');
    }    
    /**
     * 指示内容を削除するメソッド
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 指示ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteInstruction($workplaceId, $instructionId)
    {
        $instruction = Instruction::findOrFail($instructionId);
        $instruction->show_flg = 0;
        $instruction->save();
    
        return response()->json(['success' => true, 'message' => '指示内容が削除されました。']);
    }

    /**
     * 施工依頼の編集フォームを表示
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 施工依頼ID
     * @return \Illuminate\View\View
     */
    public function edit($role, $id)
    {
        Log::info('editメソッドが呼び出されました。', ['role' => $role, 'id' => $id]);
    
        $workplace = Workplace::find($id);
        if (!$workplace) {
            Log::error('施工依頼が見つかりません', ['id' => $id]);
            abort(404, '施工依頼が見つかりません');
        }
    
        Log::info('取得した施工依頼', ['workplace' => $workplace]);
    
        $salers = Saler::where('show_flg', 1)->get();
        $routes = $this->getRoutesByRole($role);
        $viewPath = resource_path('views/workplaces/edit.blade.php');
        $prefectures = Zip::getPrefectures();
   
        if (!file_exists($viewPath)) {
            Log::error('editメソッドのビューが存在しません', ['viewPath' => $viewPath]);
        } else {
            Log::info('editメソッドのビューが存在します', ['viewPath' => $viewPath]);
        }
    
        return view('workplaces.edit', array_merge(compact('workplace','prefectures', 'salers', 'role'), $routes));
    }
            
    /**
     * 施工依頼を更新
     *
     * @param \Illuminate\Http\Request $request リクエストオブジェクト
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 施工依頼ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $role, $id)
    {
        Log::info('updateメソッドが呼び出されました。', ['role' => $role, 'id' => $id]);
    
        $workplace = Workplace::findOrFail($id);
        $validated = $request->validate([
            'saler_id' => 'required|exists:salers,id',
            'saler_staff_id' => 'nullable|exists:saler_staffs,id',
            'name' => 'required|string|max:255',
            'construction_start' => 'nullable|date',
            'construction_end' => 'nullable|date',
            'floor_space' => 'nullable|string|max:10',
            'construction_outline' => 'required|string|max:300',
            'memo' => 'nullable|string|max:300',
            'zip' => 'nullable|string|max:7',
            'prefecture' => 'nullable|string|max:128',
            'city' => 'nullable|string|max:128',
            'address' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:13',
        ]);
    
        $workplace->update($validated);
    
        Log::info('施工依頼が更新されました。', ['workplace' => $workplace]);
    
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplace->id])->with('success', '施工依頼が更新されました。');
    }
        
    /**
     * 施工依頼を削除
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 施工依頼ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($role, $id)
    {
        $workplace = Workplace::findOrFail($id);
        $workplace->delete();

        return redirect()->route($this->getRoutesByRole($role)['indexRoute'])->with('success', '施工依頼が削除されました。');
    }

    /**
     * 施工依頼を承認する
     *
     * @param \Illuminate\Http\Request $request
     * @param string $role ユーザーの役割
     * @param int $id 施工依頼ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, $role, $id)
    {
        $workplace = Workplace::findOrFail($id);
        $workplace->status_id = 3; // 承認 (accepted)
        $workplace->save();

        Log::info('施工依頼が承認されました。', ['workplace_id' => $id]);

        return redirect()->route($this->getRoutesByRole($role)['indexRoute'], ['role' => $role, 'id' => $id])->with('success', '施工依頼が承認されました。');
    }

    /**
     * 施工依頼を否認する
     *
     * @param \Illuminate\Http\Request $request
     * @param string $role ユーザーの役割
     * @param int $id 施工依頼ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, $role, $id)
    {
        $workplace = Workplace::findOrFail($id);
        $workplace->status_id = 4; // 承認 (reject)
        $workplace->save();

        Log::info('施工依頼が否認されました。', ['workplace_id' => $id]);

        return redirect()->route($this->getRoutesByRole($role)['indexRoute'], ['role' => $role, 'id' => $id])->with('success', '施工依頼が否認されました。');
    }

    /**
     * 役割に応じたルートを取得するヘルパーメソッド
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @return array
     */
    private function getRoutesByRole($role)
    {
        switch ($role) {
            case 'customer':
                return [
                    'indexRoute' => 'customer.workplaces.index',
                    'createRoute' => 'customer.workplaces.create',
                    'editRoute' => 'customer.workplaces.edit',
                    'detailsRoute' => 'customer.workplaces.details',
                    'destroyRoute' => 'customer.workplaces.destroy',
                    'storeRoute' => 'customer.workplaces.store',
                    'updateRoute' => 'customer.workplaces.update',
                    'instructionsStoreRoute' => 'customer.workplaces.instructions.store',
                    'instructionsUpdateRoute' => 'customer.workplaces.instructions.update',
                    'instructionsDestroyRoute' => 'customer.workplaces.instructions.destroy',
                    'photoStoreRoute' => 'customer.workplaces.photos.store',
                    'photoUpdateRoute' => 'customer.workplaces.photos.update',
                    'photoDestroyRoute' => 'customer.workplaces.photos.destroy',
                    'fileStoreRoute' => 'customer.workplaces.files.store',
                    'fileUpdateRoute' => 'customer.workplaces.files.update',
                    'fileDeleteRoute' => 'customer.workplaces.files.destroy',
                ];
            case 'saler':
                return [
                    'indexRoute' => 'saler.workplaces.index',
                    'createRoute' => 'saler.workplaces.create',
                    'editRoute' => 'saler.workplaces.edit',
                    'detailsRoute' => 'saler.workplaces.details',
                    'destroyRoute' => 'saler.workplaces.destroy',
                    'storeRoute' => 'saler.workplaces.store',
                    'updateRoute' => 'saler.workplaces.update',
                    'instructionsStoreRoute' => 'saler.workplaces.instructions.store',
                    'instructionsUpdateRoute' => 'saler.workplaces.instructions.update',
                    'instructionsDestroyRoute' => 'saler.workplaces.instructions.destroy',
                    'photoStoreRoute' => 'saler.workplaces.photos.store',
                    'photoUpdateRoute' => 'saler.workplaces.photos.update',
                    'photoDestroyRoute' => 'saler.workplaces.photos.destroy',
                    'fileStoreRoute' => 'saler.workplaces.files.store',
                    'fileUpdateRoute' => 'saler.workplaces.files.update',
                    'fileDeleteRoute' => 'saler.workplaces.files.destroy',
                ];
            default:
                abort(404);
        }
    }

    
    /**
     * * 職人のアサインを解除するメソッド
     * *
     * * @param \Illuminate\Http\Request $request
     * * @param int $id 施工依頼ID
     * * @param string $role ユーザーの役割
     * * @return \Illuminate\Http\RedirectResponse
     * */

     public function unassignWorker(Request $request, $id, $role)
     {
         // アサインを見つけて論理削除
         $assign = Assign::findOrFail($request->input('assign_id'));
         $assign->update(['show_flg' => 0]);
 
         // 成功メッセージとともにリダイレクト
         return redirect()->back()->with('success', '職人のアサインを解除しました。');
     }


    
     /**
      * * アサインの重複をチェックするメソッド
      *
      * @param \Illuminate\Http\Request $request
      * @return \Illuminate\Http\JsonResponse
      */
      public function checkOverlap(Request $request)
      {
          $workplaceId = $request->input('workplace_id');
          $workerIds = $request->input('worker_ids', []);
          $selectedDates = json_decode($request->input('selected_dates'), true) ?? [];
      
          $overlappingAssigns = Assign::whereIn('worker_id', $workerIds)
              ->where('show_flg', 1)
              ->where('workplace_id', '!=', $workplaceId)
              ->whereIn('start_date', $selectedDates)
              ->with(['worker', 'workplace'])
              ->get();
      
          $overlappingWorkerIds = $overlappingAssigns->pluck('worker_id')->unique();
      
          $assignedWorkers = Assign::where('workplace_id', $workplaceId)
              ->where('show_flg', 1)
              ->whereIn('start_date', $selectedDates)
              ->pluck('worker_id')
              ->unique();
      
          return response()->json([
              'overlapping' => $overlappingAssigns->isNotEmpty(),
              'overlappingWorkerIds' => $overlappingWorkerIds,
              'assignedWorkerIds' => $assignedWorkers 
          ]);
      }
      public function getWorkerAssignments(Request $request)
      {
          $workerId = $request->input('worker_id');
          $workplaceId = $request->input('workplace_id');
      
          $assignments = Assign::where('worker_id', $workerId)
              ->where('show_flg', 1)
              ->with('workplace')
              ->get()
              ->map(function ($assign) use ($workplaceId) {
                  return [
                      'start_date' => $assign->start_date,
                      'end_date' => $assign->end_date,
                      'workplace_id' => $assign->workplace_id,
                      'is_current_workplace' => $assign->workplace_id == $workplaceId
                  ];
              });
      
          return response()->json(['assignments' => $assignments]);
      }

      public function getExistingAssigns(Request $request)
      {
          try {
              $workplaceId = $request->input('workplace_id');
              $workerId = $request->input('worker_id');
      
              Log::info('Fetching existing assigns', [
                  'workplace_id' => $workplaceId,
                  'worker_id' => $workerId
              ]);
      
              $query = Assign::where('show_flg', 1);
      
              if ($workplaceId) {
                  $query->where('workplace_id', $workplaceId);
              }
      
              if ($workerId) {
                  $query->where('worker_id', $workerId);
              }
      
              $assigns = $query->get();
      
              $formattedAssigns = $assigns->map(function ($assign) {
                  return [
                      'id' => $assign->id,
                      'workplace_id' => $assign->workplace_id,
                      'worker_id' => $assign->worker_id,
                      'date' => $assign->start_date->format('Y-m-d'),
                      'start_time' => $assign->start_time,
                      'end_time' => $assign->end_time,
                  ];
              });
      
              Log::info('Existing assigns fetched successfully', [
                  'count' => $assigns->count(),
                  'assigns' => $formattedAssigns
              ]);
      
              return response()->json([
                  'success' => true,
                  'assigns' => $formattedAssigns
              ]);
          } catch (\Exception $e) {
              Log::error('Error fetching existing assigns', [
                  'message' => $e->getMessage(),
                  'trace' => $e->getTraceAsString()
              ]);
      
              return response()->json([
                  'success' => false,
                  'message' => 'エラーが発生しました。'
              ], 500);
          }
      }

      /**
     * カレンダーからのアサイン登録・更新
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAssignFromCalendar(Request $request)
    {
        Log::info('storeAssignFromCalendar request data:', $request->all());

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
            $worker = Worker::findOrFail($request->worker_id);

            $assignData = [
                'workplace_id' => $request->workplace_id,
                'worker_id' => $request->worker_id,
                'start_date' => $request->assign_date,
                'end_date' => $request->assign_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'saler_id' => $workplace->saler_id,
                'saler_staff_id' => $workplace->saler_staff_id,
                'show_flg' => 1,
            ];

            // 建設会社IDがある場合のみ設定
            if ($workplace->construction_company_id) {
                $assignData['construction_company_id'] = $workplace->construction_company_id;
            } elseif ($worker->construction_company_id) {
                $assignData['construction_company_id'] = $worker->construction_company_id;
            }

            $assign = Assign::updateOrCreate(
                [
                    'workplace_id' => $request->workplace_id,
                    'worker_id' => $request->worker_id,
                    'start_date' => $request->assign_date,
                ],
                $assignData
            );

            if ($workplace->status_id == 1) {
                $workplace->status_id = 3;
                $workplace->save();
            }

            DB::commit();

            Log::info('Assign created/updated successfully', ['assign_id' => $assign->id]);

            return response()->json(['success' => true, 'message' => 'アサインが正常に更新されました。']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in storeAssignFromCalendar', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false, 'message' => 'アサインの更新中にエラーが発生しました。: ' . $e->getMessage()], 500);
        }
    }
    public function destroyAssign($workplaceId, $assignId)
    {
        $assign = Assign::findOrFail($assignId);
        $assign->delete();
    
        return response()->json(['success' => true, 'message' => 'アサインが削除されました。']);
    }
}
