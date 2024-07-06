<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instruction;
use App\Models\Workplace;
use Illuminate\Support\Facades\Log;

class InstructionsController extends Controller
{
    /**
     * 指示内容の追加フォームを表示
     */
    public function create($workplace_id)
    {
        $workplace = Workplace::findOrFail($workplace_id);
        return view('instructions.create', compact('workplace'));
    }

    /**
     * 指示内容を保存
     */
    public function store(Request $request, $workplace_id)
    {
        // バリデーション
        $validated = $request->validate([
            'instructions.*.construction_location' => 'required|string|max:255',
            'instructions.*.construction_location_detail' => 'nullable|string|max:255',
            'instructions.*.product_name' => 'required|string|max:255',
            'instructions.*.product_number' => 'nullable|string|max:255',
            'instructions.*.amount' => 'nullable|integer',
            'instructions.*.unit_id' => 'required|exists:units,id',
        ]);

        // 各指示内容を保存
        foreach ($validated['instructions'] as $instructionData) {
            Instruction::create([
                'workplace_id' => $workplace_id,
                'construction_location' => $instructionData['construction_location'],
                'construction_location_detail' => $instructionData['construction_location_detail'],
                'product_name' => $instructionData['product_name'],
                'product_number' => $instructionData['product_number'],
                'amount' => $instructionData['amount'],
                'unit_id' => $instructionData['unit_id'],
                'show_flg' => 1
            ]);
        }

        Log::info('指示内容が登録されました。', ['workplace_id' => $workplace_id]);

        return redirect()->route('workplaces.index')->with('success', '指示内容が登録されました。');
    }
}
