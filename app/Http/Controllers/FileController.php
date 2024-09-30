<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FileController extends Controller
{
    /**
     * ファイルをアップロードして保存する
     *
     * @param \Illuminate\Http\Request $request
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $workplaceId 施工依頼ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $role, $workplaceId)
    {
        Log::info('File store method called', ['role' => $role, 'workplaceId' => $workplaceId]);
        Log::info('Request data', ['data' => $request->all()]);
        
        // バリデーションルールの設定とバリデーション実行
        try {
            $validated = $request->validate([
                'workplace_id' => 'required|exists:workplaces,id',
                'files' => 'required|array',
                'files.*.file' => 'required|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
                'files.*.title' => 'nullable|string|max:255',
                'files.*.comment' => 'nullable|string|max:255',
            ]);
            Log::info('Validation passed', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json(['error' => $e->errors()], 422);
        }
            
        Log::info('Validation passed', ['validated' => $validated]);
    
        // リクエストデータのログ記録
        Log::info('File store request data:', $request->all());
    
        $filePaths = [];
    
        // ファイルのアップロード処理
        if (isset($validated['files'])) {
            foreach ($validated['files'] as $index => $fileData) {
                Log::info('Processing file', ['index' => $index, 'fileData' => $fileData]);
                
                if (isset($fileData['file']) && $fileData['file']->isValid()) {
                    // ファイル名の生成
                    $user_id = Auth::id();
                    $timestamp = time();
                    $random = bin2hex(random_bytes(8));
                    $fileName = 'file_' . $user_id . '_' . $timestamp . '_' . $random . '.' . $fileData['file']->getClientOriginalExtension();
        
                    // 日付ベースのパス生成
                    $datePath = Carbon::now()->format('Y/m/d');
                    $fullPath = 'public/files/' . $datePath;
        
                    Log::info('File details', [
                        'fileName' => $fileName,
                        'datePath' => $datePath,
                        'fullPath' => $fullPath
                    ]);
        
                    // ディレクトリの作成
                    if (!Storage::makeDirectory($fullPath)) {
                        Log::error('Failed to create directory', ['path' => $fullPath]);
                        return response()->json(['error' => 'Failed to create directory'], 500);
                    }
        
                    // ファイルの保存
                    try {
                        $fileData['file']->storeAs($fullPath, $fileName);
                        Log::info('File stored successfully', ['path' => $fullPath . '/' . $fileName]);
                    } catch (\Exception $e) {
                        Log::error('Failed to store file', ['error' => $e->getMessage()]);
                        return response()->json(['error' => 'Failed to store file: ' . $e->getMessage()], 500);
                    }

                    // データベースにファイル情報を保存
                    try {
                        $file = new File([
                            'workplace_id' => $validated['workplace_id'],
                            'title' => $fileData['title'] ?? null,
                            'comment' => $fileData['comment'] ?? null,
                            'file_name' => $fileName,
                            'directory' => $datePath . '/',
                        ]);
                        $file->save();
                        Log::info('File record saved to database', ['file' => $file]);
                    } catch (\Exception $e) {
                        Log::error('Failed to save file record to database', ['error' => $e->getMessage()]);
                    }
    
                    $filePaths[] = $fullPath . '/' . $fileName;
                } else {
                    Log::warning('Invalid file', ['index' => $index]);
                    return response()->json(['error' => 'Invalid file'], 422);

                }
            }
        } else {
            Log::warning('No files in request');
            return response()->json(['error' => 'No files in request'], 422);

        }
    
        // ファイルがアップロードされたことをログに記録
        Log::info('File upload process completed', ['paths' => $filePaths]);
    
        // アップロード成功のメッセージとともにリダイレクト
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplaceId])->with('success', 'ファイルがアップロードされました。');
    }

    /**
     * ファイル情報を更新する
     *
     * @param \Illuminate\Http\Request $request
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $workplaceId 施工依頼ID
     * @param int $id ファイルID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $role, $workplaceId, $id)
    {
        // 指定されたIDのファイルを取得
        $file = File::find($id);
        if ($file) {
            // ファイルのタイトルとコメントを更新
            $file->title = $request->title;
            $file->comment = $request->comment;
            $file->save();
        }

        // 更新成功のメッセージとともにリダイレクト
        return back()->with('success', 'ファイルが更新されました。');
    }

    /**
     * ファイルを論理削除する
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $workplaceId 施工依頼ID
     * @param int $id ファイルID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($role, $workplaceId, $id)
    {
        $file = File::findOrFail($id);
        $file->show_flg = 0;
        $file->save();
    
        return response()->json(['success' => true, 'message' => 'ファイルが削除されました。']);
    }
    /**
     * ユーザーの役割に応じたルートを取得する
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @return array
     */
    private function getRoutesByRole($role)
    {
        // ユーザーの役割に応じたルート設定を返す
        $routes = [
            'customer' => [
                'detailsRoute' => 'customer.workplaces.details',
            ],
            'saler' => [
                'detailsRoute' => 'saler.workplaces.details',
            ],
        ];

        return $routes[$role] ?? [];
    }
}
