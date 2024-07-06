<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\File;
use App\Models\Workplace;

class FileController extends Controller
{
    /**
     * 添付書類を保存するメソッド
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $workplaceId 施工依頼のID
     * @return \Illuminate\Http\RedirectResponse リダイレクトレスポンス
     */
    public function store(Request $request, $workplaceId)
    {
        try {
            Log::info('Store method called with request:', $request->all());

            $validated = $request->validate([
                'files.*.file' => 'nullable|file',
            ]);

            Log::info('Validated request data:', $request->all());

            foreach ($request->files as $index => $fileInput) {
                if (isset($fileInput['file'])) {
                    $file = $fileInput['file'];
                    if ($file->isValid()) {
                        try {
                            $parent_directory = 'instructions/files/';
                            $directory = now()->format('Y/m') . '/' . $workplaceId . '/';
                            $filename = 'file_' . $workplaceId . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                            Log::info('File details:', [
                                'original_name' => $file->getClientOriginalName(),
                                'extension' => $file->getClientOriginalExtension(),
                                'size' => $file->getSize(),
                                'directory' => $parent_directory . $directory,
                                'filename' => $filename,
                            ]);

                            if (!Storage::exists($parent_directory . $directory)) {
                                Log::info('Directory does not exist, creating directory:', $parent_directory . $directory);
                                Storage::makeDirectory($parent_directory . $directory);
                            }

                            Log::info('Attempting to store file:', ['path' => $parent_directory . $directory . $filename]);
                            $path = $file->storeAs($parent_directory . $directory, $filename, 'public');

                            Log::info('Stored file path:', ['path' => $path]);

                            File::create([
                                'workplace_id' => $workplaceId,
                                'file_name' => $filename,
                                'directory' => $directory,
                                'title' => $fileInput['title'] ?? null,
                                'comment' => $fileInput['comment'] ?? null,
                            ]);

                            Log::info('File record created in database');
                        } catch (\Exception $e) {
                            Log::error('Error storing file:', ['message' => $e->getMessage(), 'fileInput' => $fileInput]);
                            return redirect()->route('workplaces.details', ['id' => $workplaceId])->with('error', 'ファイルの保存中にエラーが発生しました: ' . $e->getMessage());
                        }
                    } else {
                        Log::error('File is not valid:', ['file' => $file]);
                        return redirect()->route('workplaces.details', ['id' => $workplaceId])->with('error', 'ファイルが無効です。');
                    }
                } else {
                    Log::error('File input is not set', ['fileInput' => $fileInput]);
                    return redirect()->route('workplaces.details', ['id' => $workplaceId])->with('error', 'ファイルが選択されていません。');
                }
            }

            return redirect()->route('workplaces.details', ['id' => $workplaceId])->with('success', '添付書類が追加されました。');
        } catch (\Exception $e) {
            Log::error('Error in store method:', ['message' => $e->getMessage(), 'request' => $request->all()]);
            return redirect()->route('workplaces.details', ['id' => $workplaceId])->with('error', 'ファイルのアップロード中にエラーが発生しました: ' . $e->getMessage());
        }
    }
    
    /**
     * 添付書類を削除（show_flgを0に設定）するメソッド
     *
     * @param int $workplaceId 施工依頼のID
     * @param int $id 添付書類のID
     * @return \Illuminate\Http\RedirectResponse リダイレクトレスポンス
     */
    public function destroy($workplaceId, $id)
    {
        $file = File::findOrFail($id);
        $file->show_flg = 0; // フラグを0に設定
        $file->save();

        return redirect()->back()->with('success', '添付書類が削除されました。');
    }
    
    /**
     * 添付書類のタイトルとコメントを更新するメソッド
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $workplaceId 施工依頼のID
     * @param int $id 添付書類のID
     * @return \Illuminate\Http\RedirectResponse リダイレクトレスポンス
     */
    public function update(Request $request, $workplaceId, $id)
    {
        $file = File::findOrFail($id);

        // タイトルとコメントの更新
        $file->title = $request->input('title');
        $file->comment = $request->input('comment');
        $file->save();

        return redirect()->route('workplaces.details', ['id' => $workplaceId])->with('success', '添付書類の情報が更新されました。');
    }

    // FileController.php
    private function getRoutesByRole($role)
    {
        switch ($role) {
            case 'customer':
                return [
                    'storeRoute' => 'customer.workplaces.files.store',
                    'updateRoute' => 'customer.workplaces.files.update',
                    'destroyRoute' => 'customer.workplaces.files.destroy',
                ];
            case 'saler':
                return [
                    'storeRoute' => 'saler.workplaces.files.store',
                    'updateRoute' => 'saler.workplaces.files.update',
                    'destroyRoute' => 'saler.workplaces.files.destroy',
                ];
            default:
                abort(404);
        }
    }
}
