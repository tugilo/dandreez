<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Photo;
use App\Models\Workplace;

class PhotoController extends Controller
{

    /**
     * 写真を保存するメソッド
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $workplaceId 施工依頼のID
     * @return \Illuminate\Http\RedirectResponse リダイレクトレスポンス
     */
    public function store(Request $request, $workplaceId)
    {
        // バリデーション
        $validated = $request->validate([
            'photos.*.file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photos.*.title' => 'nullable|string|max:255',
            'photos.*.comment' => 'nullable|string',
        ]);

        $workplace = Workplace::findOrFail($workplaceId);
        $parent_directory = 'instructions/photos/';

        foreach ($request->photos as $photoInput) {
            if (isset($photoInput['file'])) {
                $file = $photoInput['file'];
                $directory = now()->format('Y/m') . '/' . $workplace->id . '/';
                $filename = 'photo_' . $workplace->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // ディレクトリが存在しない場合は作成
                if (!Storage::exists($parent_directory . $directory)) {
                    Storage::makeDirectory($parent_directory . $directory);
                }

                // ファイルを保存
                $file->storeAs($parent_directory . $directory, $filename, 'public');

                // DBにレコードを作成
                Photo::create([
                    'workplace_id' => $workplace->id,
                    'file_name' => $filename,
                    'directory' => $directory,
                    'title' => $photoInput['title'] ?? null,
                    'comment' => $photoInput['comment'] ?? null,
                ]);
            }
        }

        return redirect()->route('workplaces.details', ['id' => $workplace->id])->with('success', '写真がアップロードされました。');
    }


    /**
     * 写真を削除（show_flgを0に設定）するメソッド
     *
     * @param int $workplaceId 施工依頼のID
     * @param int $id 写真のID
     * @return \Illuminate\Http\RedirectResponse リダイレクトレスポンス
     */
    public function destroy($workplaceId, $id)
    {
        $photo = Photo::findOrFail($id);
        $photo->show_flg = 0; // フラグを0に設定
        $photo->save();

        return redirect()->back()->with('success', '写真が削除されました。');
    }
    
    /**
     * 写真のタイトルとコメントを更新するメソッド
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $workplaceId 施工依頼のID
     * @param int $id 写真のID
     * @return \Illuminate\Http\RedirectResponse リダイレクトレスポンス
     */
    public function update(Request $request, $workplaceId, $id)
    {
        $photo = Photo::findOrFail($id);

        // タイトルとコメントの更新
        $photo->title = $request->input('title');
        $photo->comment = $request->input('comment');
        $photo->save();

        return redirect()->route('workplaces.details', ['id' => $workplaceId])->with('success', '写真の情報が更新されました。');
    }

}
