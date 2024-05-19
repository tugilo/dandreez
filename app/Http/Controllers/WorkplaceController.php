<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\Customer;
use App\Models\Saler;
use App\Models\Worker;
use App\Models\Instruction;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WorkplaceController extends Controller
{
    /**
     * 施工依頼の一覧を表示
     */
    public function index()
    {
        $workplaces = Workplace::with(['customer', 'saler', 'customerStaff', 'salerStaff', 'workers'])->get();
        return view('workplaces.index', compact('workplaces'));
    }

    /**
     * 新規施工依頼のフォームを表示
     */
    public function create()
    {
        $salers = Saler::where('show_flg', 1)->get();
        return view('workplaces.create', compact('salers'));
    }

    /**
     * 新規施工依頼を保存
     */
    public function store(Request $request)
    {
        // バリデーション
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
    
        // ログにリクエストデータを記録
        Log::info('リクエストデータ:', $request->all());
    
        // ログイン中のユーザー情報を取得
        $customer = Auth::user()->customerStaff->customer;
        $customerStaff = Auth::user()->customerStaff;
    
        if (!$customer || !$customerStaff) {
            return redirect()->route('workplaces.index')->with('error', 'ユーザー情報の取得に失敗しました。');
        }
    
        // 施工依頼の作成
        Workplace::create([
            'customer_id' => $customer->id,
            'customer_staff_id' => $customerStaff->id,
            'saler_id' => $request->saler_id,
            'saler_staff_id' => $request->saler_staff_id ?? null,
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
    
        return redirect()->route('workplaces.index')->with('success', '施工依頼が登録されました。');
    }
        
    /**
     * 施工依頼の編集フォームを表示
     */
    public function edit($id)
    {
        $workplace = Workplace::findOrFail($id);
        $salers = Saler::where('show_flg', 1)->get();
        return view('workplaces.edit', compact('workplace', 'salers'));
    }

    /**
     * 施工依頼を更新
     */
    public function update(Request $request, $id)
    {
        // バリデーション
        $validated = $request->validate([
            'saler_id' => 'required|exists:salers,id',
            'name' => 'required|string|max:255',
            'construction_start' => 'nullable|date',
            'construction_end' => 'nullable|date',
            'floor_space' => 'nullable|string|max:10',
            'construction_outline' => 'required|string|max:300',
            'memo' => 'nullable|string|max:300',
        ]);

        $workplace = Workplace::findOrFail($id);
        $workplace->update($validated);

        Log::info('施工依頼が更新されました。', ['workplace_id' => $id]);

        return redirect()->route('workplaces.index')->with('success', '施工依頼が更新されました。');
    }

    /**
     * 施工指示の追加フォームを表示
     */
    public function addInstruction($id)
    {
        $workplace = Workplace::findOrFail($id);
        $units = Unit::all();
        return view('instructions.create', compact('workplace', 'units'));
    }

    /**
     * 施工指示を保存
     */
    public function storeInstruction(Request $request, $id)
    {
        // バリデーション
        $validated = $request->validate([
            'construction_location' => 'required|string|max:255',
            'construction_location_detail' => 'nullable|string|max:255',
            'product_name' => 'required|string|max:255',
            'product_number' => 'nullable|string|max:255',
            'amount' => 'nullable|integer',
            'unit_id' => 'required|exists:units,id',
        ]);

        // 施工指示の作成
        Instruction::create([
            'workplace_id' => $id,
            'construction_location' => $validated['construction_location'],
            'construction_location_detail' => $validated['construction_location_detail'],
            'product_name' => $validated['product_name'],
            'product_number' => $validated['product_number'],
            'amount' => $validated['amount'],
            'unit_id' => $validated['unit_id'],
        ]);

        Log::info('施工指示が追加されました。', ['workplace_id' => $id]);

        return redirect()->route('workplaces.index')->with('success', '施工指示が追加されました。');
    }
}
