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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
                $workplace->construction_start = $workplace->construction_start->format('Y-m-d');
                $workplace->construction_end = $workplace->construction_end->format('Y-m-d');
                return $workplace;
            });
    
        foreach ($workplaces as $workplace) {
            $workplace->assignedWorkers = $workplace->assigns->where('show_flg', 1);
        }
        
        $constructionCompanies = ConstructionCompany::where('show_flg', 1)->get();
        $workers = Worker::where('show_flg', 1)->get();
    
        $routes = $this->getRoutesByRole($role);
    
        return view('workplaces.index', array_merge(
            compact('workplaces', 'role', 'constructionCompanies', 'workers'),
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
            $routes = $this->getRoutesByRole($role);
            $storeRoute = $routes['storeRoute'];
            return view('workplaces.create', array_merge(compact('salers', 'customer', 'role', 'storeRoute'), $routes));
        } elseif ($role == 'saler') {
            $customers = Customer::where('show_flg', 1)->get();
            $saler = Auth::user()->salerStaff->saler;
            $routes = $this->getRoutesByRole($role);
            $storeRoute = $routes['storeRoute'];
            return view('workplaces.create', array_merge(compact('customers', 'saler', 'role', 'storeRoute'), $routes));
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

    
        Log::info('detailsメソッドのデータ', ['workplace' => $workplace, 'instructions' => $instructions, 'photos' => $photos, 'files' => $files, 'units' => $units]);
    
        return view('workplaces.details', array_merge(compact('workplace', 'instructions', 'photos', 'files', 'units', 'role', 'storeRoute', 'totalAmount'), $routes));
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

        $filteredInstructions = array_filter($request->instructions, function ($instruction) {
            return !is_null($instruction['construction_location']) && !is_null($instruction['product_name']);
        });

        Log::info('フィルタリング後のデータ:', $filteredInstructions);

        $validated = Validator::make(['instructions' => $filteredInstructions], [
            'instructions' => 'required|array',
            'instructions.*.construction_location' => 'required|string|max:255',
            'instructions.*.construction_location_detail' => 'nullable|string|max:255',
            'instructions.*.product_name' => 'required|string|max:255',
            'instructions.*.product_number' => 'nullable|string|max:255',
            'instructions.*.amount' => 'nullable|numeric|min:0',
            'instructions.*.unit_id' => 'required|exists:units,id',
        ]);

        if ($validated->fails()) {
            return redirect()->back()->withErrors($validated)->withInput();
        }

        foreach ($filteredInstructions as $instructionData) {
            Instruction::create([
                'workplace_id' => $id,
                'construction_location' => $instructionData['construction_location'],
                'construction_location_detail' => $instructionData['construction_location_detail'],
                'product_name' => $instructionData['product_name'],
                'product_number' => $instructionData['product_number'],
                'amount' => $instructionData['amount'],
                'unit_id' => $instructionData['unit_id'],
            ]);
        }

        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $id])->with('success', '指示内容が追加されました。');
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
        $instruction = Instruction::findOrFail($id);

        $validated = $request->validate([
            'construction_location' => 'required|string|max:255',
            'construction_location_detail' => 'nullable|string|max:255',
            'product_name' => 'required|string|max:255',
            'product_number' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
        ]);

        $instruction->update($validated);

        return response()->json(['success' => true, 'message' => '指示内容が更新されました。']);
    }

    /**
     * 指示内容を削除するメソッド
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $id 指示ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteInstruction($role, $id)
    {
        $instruction = Instruction::findOrFail($id);
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
    
        if (!file_exists($viewPath)) {
            Log::error('editメソッドのビューが存在しません', ['viewPath' => $viewPath]);
        } else {
            Log::info('editメソッドのビューが存在します', ['viewPath' => $viewPath]);
        }
    
        return view('workplaces.edit', array_merge(compact('workplace', 'salers', 'role'), $routes));
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
 * 職人を施工依頼にアサインするメソッド
 *
 * @param \Illuminate\Http\Request $request
 * @param int $id 施工依頼ID
 * @param string $role ユーザーの役割
 * @return \Illuminate\Http\RedirectResponse
 */
public function storeAssign(Request $request, $id, $role)
{
    // リクエストデータとルートパラメータをログに出力
    Log::info('storeAssign request data:', [
        'request_all' => $request->all(),
        'route_id' => $id,
        'role' => $role,
    ]);

    // ログインユーザーの情報を取得
    $user = auth()->user();
    $login = Login::where('id', $user->id)->first();
    $salerStaff = SalerStaff::where('id', $login->user_id)->first();
    $salerId = $salerStaff->saler_id;
    $salerStaffId = $salerStaff->id;

    $workplaceId = $id;
    $constructionCompanyId = $request->input('construction_company_id');
    $workerIds = $request->input('worker_ids', []);

    // チェックされていない職人のアサインを論理削除（show_flgを0に設定）
    Assign::where('workplace_id', $workplaceId)
        ->where('construction_company_id', $constructionCompanyId)
        ->whereNotIn('worker_id', $workerIds)
        ->where('show_flg', 1)
        ->update(['show_flg' => 0]);

    // 各職人をアサインする
    foreach ($workerIds as $workerId) {
        Assign::updateOrCreate(
            [
                'workplace_id' => $workplaceId,
                'construction_company_id' => $constructionCompanyId,
                'worker_id' => $workerId,
            ],
            [
                'saler_id' => $salerId,
                'saler_staff_id' => $salerStaffId,
                'show_flg' => 1, 
            ]
        );
    }
    
    // show_flg=0のレコードを1に更新（既存のデータを復活させる）
    Assign::where('workplace_id', $workplaceId)
        ->where('construction_company_id', $constructionCompanyId)
        ->whereIn('worker_id', $workerIds)
        ->where('show_flg', 0)
        ->update(['show_flg' => 1]);

    // 施工依頼のステータスを確認し、必要に応じて更新
    $workplace = Workplace::findOrFail($workplaceId);
    if ($workplace->status_id == 1) {  // 1は未受領のステータスIDと仮定
        $workplace->status_id = 3;  // 3は受領済みのステータスIDと仮定
        $workplace->save();
        
        Log::info('施工依頼のステータスを更新しました。', [
            'workplace_id' => $workplaceId,
            'old_status' => 1,
            'new_status' => 3
        ]);
    }

    // 成功メッセージとともにリダイレクト
    return redirect()->route($this->getRoutesByRole($role)['indexRoute'], ['role' => $role])
                    ->with('success', '職人のアサインが完了しました。');
}
    /**
     * 職人のアサインを解除するメソッド
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id 施工依頼ID
     * @param string $role ユーザーの役割
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unassignWorker(Request $request, $id, $role)
    {
        // アサインを見つけて論理削除
        $assign = Assign::findOrFail($request->input('assign_id'));
        $assign->update(['show_flg' => 0]);

        // 成功メッセージとともにリダイレクト
        return redirect()->back()->with('success', '職人のアサインを解除しました。');
    }
    public function checkOverlap(Request $request)
    {
        // リクエストから必要なデータを取得
        $workplaceId = $request->input('workplace_id');
        $workerIds = $request->input('worker_ids');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        // 重複するアサインを検索
        $overlappingAssigns = Assign::whereIn('worker_id', $workerIds)
            ->where('show_flg', 1)
            ->where('workplace_id', '!=', $workplaceId)
            ->whereHas('workplace', function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('construction_start', [$startDate, $endDate])
                        ->orWhereBetween('construction_end', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('construction_start', '<=', $startDate)
                                ->where('construction_end', '>=', $endDate);
                        });
                });
            })
            ->with(['worker', 'workplace'])
            ->get();
    
        // 重複している職人のIDを取得
        $overlappingWorkerIds = $overlappingAssigns->pluck('worker_id')->unique();
    
        // 現在の施工依頼に割り当てられている職人を取得
        $assignedWorkers = Assign::where('workplace_id', $workplaceId)
            ->where('show_flg', 1)
            ->pluck('worker_id')
            ->unique();
    
        // レスポンスを返す
        return response()->json([
            'overlapping' => $overlappingAssigns->isNotEmpty(),
            'overlappingWorkerIds' => $overlappingWorkerIds,
            'assignedWorkerIds' => $assignedWorkers // 新しく追加
        ]);
    }
}
