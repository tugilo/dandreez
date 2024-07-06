<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Photo;
use Carbon\Carbon;


class PhotoController extends Controller
{
    /**
     * 写真をアップロードして保存する
     *
     * @param \Illuminate\Http\Request $request
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $workplaceId 施工依頼ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $role, $workplaceId)
    {
        $validated = $request->validate([
            'workplace_id' => 'required|exists:workplaces,id',
            'photos.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'photos.*.title' => 'nullable|string|max:255',
            'photos.*.comment' => 'nullable|string|max:255',
        ]);
    
        Log::info('Photo store request data:', $request->all());
    
        $photoPaths = [];
    
        if (isset($validated['photos'])) {
            foreach ($validated['photos'] as $photoData) {
                if (isset($photoData['file']) && $photoData['file']->isValid()) {
                    $user_id = Auth::id();
                    $timestamp = time();
                    $random = bin2hex(random_bytes(8)); // 16桁のランダムな文字列
                    $fileName = 'photo_' . $user_id . '_' . $timestamp . '_' . $random . '.' . $photoData['file']->getClientOriginalExtension();
    
                    $datePath = Carbon::now()->format('Y/m/d');
                    $fullPath = 'public/instructions/photos/' . $datePath;
    
                    // ディレクトリを作成
                    Storage::makeDirectory($fullPath);
    
                    $photoData['file']->storeAs($fullPath, $fileName);
    
                    $photo = new Photo([
                        'workplace_id' => $validated['workplace_id'],
                        'title' => $photoData['title'] ?? null,
                        'comment' => $photoData['comment'] ?? null,
                        'file_name' => $fileName,
                        'directory' => $datePath . '/',
                    ]);
                    $photo->save();
                    $photoPaths[] = $fullPath . '/' . $fileName;
                }
            }
        }
    
        Log::info('写真がアップロードされました。', ['paths' => $photoPaths]);
    
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplaceId])->with('success', '写真がアップロードされました。');
    }
    
    
    /**
     * 写真の更新
     *
     * @param \Illuminate\Http\Request $request
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $workplaceId 施工依頼ID
     * @param int $id 写真ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $role, $workplaceId, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
        ]);

        $photo = Photo::findOrFail($id);
        $photo->update($request->only('title', 'comment'));

        Log::info('写真のタイトル・コメントがが更新されました。', ['photo_id' => $id]);

        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplaceId])->with('success', '写真のタイトル・コメントがが更新されました。');
    }

    /**
     * 写真の削除
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @param int $workplaceId 施工依頼ID
     * @param int $id 写真ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($role, $workplaceId, $id)
    {
        $photo = Photo::findOrFail($id);
        $photo->show_flg = 0;
        $photo->save();
    
        Log::info('写真が論理削除されました。', ['photo_id' => $id]);
    
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplaceId])->with('success', '写真が削除されました。');
    }
    /**
     * 役割に応じたルートを取得するヘルパーメソッド
     *
     * @param string $role ユーザーの役割（customerまたはsaler）
     * @return array
     */
    // PhotoController.php
    private function getRoutesByRole($role)
    {
        switch ($role) {
            case 'customer':
                return [
                    'storeRoute' => 'customer.workplaces.photos.store',
                    'updateRoute' => 'customer.workplaces.photos.update',
                    'destroyRoute' => 'customer.workplaces.photos.destroy',
                    'detailsRoute' => 'customer.workplaces.details',
                ];
            case 'saler':
                return [
                    'storeRoute' => 'saler.workplaces.photos.store',
                    'updateRoute' => 'saler.workplaces.photos.update',
                    'destroyRoute' => 'saler.workplaces.photos.destroy',
                    'detailsRoute' => 'saler.workplaces.details',
                ];
            default:
                abort(404);
        }
    }
}
