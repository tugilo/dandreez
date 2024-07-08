<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
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
=======
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
        // バリデーションルールの設定とバリデーション実行
        $validated = $request->validate([
            'workplace_id' => 'required|exists:workplaces,id',
            'files.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            'files.*.title' => 'nullable|string|max:255',
            'files.*.comment' => 'nullable|string|max:255',
        ]);

        // リクエストデータのログ記録
        Log::info('File store request data:', $request->all());

        $filePaths = [];

        // ファイルのアップロード処理
        if (isset($validated['files'])) {
            foreach ($validated['files'] as $fileData) {
                if (isset($fileData['file']) && $fileData['file']->isValid()) {
                    // ファイル名の生成
                    $user_id = Auth::id();
                    $timestamp = time();
                    $random = bin2hex(random_bytes(8)); // 16桁のランダムな文字列
                    $fileName = 'file_' . $user_id . '_' . $timestamp . '_' . $random . '.' . $fileData['file']->getClientOriginalExtension();

                    // 日付ベースのパス生成
                    $datePath = Carbon::now()->format('Y/m/d');
                    $fullPath = 'public/files/' . $datePath;

                    // ディレクトリの作成
                    Storage::makeDirectory($fullPath);

                    // ファイルの保存
                    $fileData['file']->storeAs($fullPath, $fileName);

                    // データベースにファイル情報を保存
                    $file = new File([
                        'workplace_id' => $validated['workplace_id'],
                        'title' => $fileData['title'] ?? null,
                        'comment' => $fileData['comment'] ?? null,
                        'file_name' => $fileName,
                        'directory' => $datePath . '/',
                    ]);
                    $file->save();
                    $filePaths[] = $fullPath . '/' . $fileName;
                }
            }
        }

        // ファイルがアップロードされたことをログに記録
        Log::info('ファイルがアップロードされました。', ['paths' => $filePaths]);

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
        // 指定されたIDのファイルを取得し、論理削除を実行
        $file = File::findOrFail($id);
        $file->show_flg = 0;
        $file->save();

        // ファイルが論理削除されたことをログに記録
        Log::info('ファイルが論理削除されました。', ['file_id' => $id]);

        // 削除成功のメッセージとともにリダイレクト
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplaceId])->with('success', 'ファイルが削除されました。');
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
>>>>>>> develop
    }
}
