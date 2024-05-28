<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'workplace_id' => 'required|exists:workplaces,id',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // 写真を保存
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('photos', 'public');
            Photo::create([
                'workplace_id' => $request->workplace_id,
                'path' => $path,
            ]);
        }

        return redirect()->route('workplaces.details', ['id' => $request->workplace_id])->with('success', '写真が追加されました。');
    }
}
