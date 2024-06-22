<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\Customer;
use App\Models\Saler;
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
     */
    public function index()
    {
        // 施工依頼の一覧を取得
        $workplaces = Workplace::with('customer', 'saler', 'customerStaff', 'salerStaff', 'workers', 'status')->get();
        // $workplacesをログに出力
        Log::info('Workplaces:', ['workplaces' => $workplaces]);
        return view('workplaces.index', compact('workplaces'));
    }

    /**
     * 新規施工依頼のフォームを表示
     */
    public function create()
    {
        // 有効な問屋の一覧を取得
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
        $workplace = Workplace::create([
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

        return redirect()->route('workplaces.details', ['id' => $workplace->id])->with('success', '施工依頼が登録されました。詳細を追加してください。');
    }

    /**
     * 施工依頼の詳細設定
     */
    public function details($id)
    {
        // 施工依頼の詳細情報を取得
        $workplace = Workplace::where('show_flg', 1)->findOrFail($id);
        $instructions = Instruction::where('workplace_id', $id)->where('show_flg', 1)->get();
        $photos = Photo::where('workplace_id', $id)->where('show_flg', 1)->get();  // 追加
        $files = File::where('workplace_id', $id)->where('show_flg', 1)->get();    // 追加
        $units = Unit::where('show_flg', 1)->get();
        return view('workplaces.details', compact('workplace', 'instructions', 'photos', 'files', 'units'));
    }
    
    /**
     * 施工指示を保存
     */
    public function storeInstructions(Request $request, $id)
    {
        // ログにリクエストデータを記録
        Log::info('リクエストデータ:', $request->all());
    
        // フィルタリング
        $filteredInstructions = array_filter($request->instructions, function ($instruction) {
            return !is_null($instruction['construction_location']) && !is_null($instruction['product_name']);
        });
    
        Log::info('フィルタリング後のデータ:', $filteredInstructions);
    
        // バリデーション
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
    
        return redirect()->route('workplaces.details', ['id' => $id])->with('success', '指示内容が追加されました。');
    }
    


    /**
     * 施工依頼の編集フォームを表示
     */
    public function edit($id)
    {
        // 施工依頼と有効な問屋の一覧を取得
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

        $workplace = Workplace::findOrFail($id);
        $workplace->update($validated);

        Log::info('施工依頼が更新されました。', ['workplace_id' => $id]);

        return redirect()->route('workplaces.index')->with('success', '施工依頼が更新されました。');
    }
}
