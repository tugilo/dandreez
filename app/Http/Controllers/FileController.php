<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'workplace_id' => 'required|exists:workplaces,id',
            'files.*' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        // 添付書類を保存
        foreach ($request->file('files') as $file) {
            $path = $file->store('files', 'public');
            File::create([
                'workplace_id' => $request->workplace_id,
                'path' => $path,
            ]);
        }

        return redirect()->route('workplaces.details', ['id' => $request->workplace_id])->with('success', '添付書類が追加されました。');
    }
}
