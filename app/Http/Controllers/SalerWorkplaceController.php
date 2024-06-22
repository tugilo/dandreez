<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\Unit;
use App\Models\Instruction;
use App\Models\Photo;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SalerWorkplaceController extends Controller
{
    public function index()
    {
        // ログイン中の問屋IDを取得
        $salerId = Auth::user()->salerStaff->saler_id;

        // 施工依頼の一覧を取得
        $workplaces = Workplace::where('saler_id', $salerId)->with('customer')->get();
        return view('saler.workplaces.index', compact('workplaces'));
    }

    public function show($id)
    {
        // 施工依頼の詳細情報を取得
        $workplace = Workplace::with(['customer', 'instructions', 'photos', 'files'])->findOrFail($id);
        $instructions = Instruction::where('workplace_id', $id)->where('show_flg', 1)->get();
        $photos = Photo::where('workplace_id', $id)->where('show_flg', 1)->get();  
        $files = File::where('workplace_id', $id)->where('show_flg', 1)->get();
        $units = Unit::where('show_flg', 1)->get();
        return view('saler.workplaces.show', compact('workplace', 'instructions', 'photos', 'files', 'units'));
    }

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

        return redirect()->route('saler.workplaces.show', ['id' => $id])->with('success', '指示内容が追加されました。');
    }
}
