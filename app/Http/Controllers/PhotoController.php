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
            'photo' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
        ]);
    
        Log::info('Photo store request data:', $request->all());
    
        $photoPaths = [];
    
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $user_id = Auth::id();
            $timestamp = time();
            $random = bin2hex(random_bytes(8));
            $fileName = 'photo_' . $user_id . '_' . $timestamp . '_' . $random . '.' . $photo->getClientOriginalExtension();
    
            $datePath = Carbon::now()->format('Y/m/d');
            $fullPath = 'public/instructions/photos/' . $datePath;
    
            Storage::makeDirectory($fullPath);
    
            $photo->storeAs($fullPath, $fileName);
    
            $newPhoto = new Photo([
                'workplace_id' => $validated['workplace_id'],
                'title' => $validated['title'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'file_name' => $fileName,
                'directory' => $datePath . '/',
            ]);
            $newPhoto->save();
            $photoPaths[] = $fullPath . '/' . $fileName;
        }
    
        Log::info('写真がアップロードされました。', ['paths' => $photoPaths]);
    
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplaceId])
            ->with('success', '写真がアップロードされました。');
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
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $photo = Photo::findOrFail($id);
        $photo->title = $request->title;
        $photo->comment = $request->comment;
    
        if ($request->hasFile('photo')) {
            // 古い写真を削除
            Storage::delete('public/instructions/photos/' . $photo->directory . $photo->file_name);
    
            // 新しい写真をアップロード
            $file = $request->file('photo');
            $fileName = 'photo_' . Auth::id() . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file->getClientOriginalExtension();
            $datePath = Carbon::now()->format('Y/m/d');
            $fullPath = 'public/instructions/photos/' . $datePath;
            Storage::makeDirectory($fullPath);
            $file->storeAs($fullPath, $fileName);
    
            $photo->file_name = $fileName;
            $photo->directory = $datePath . '/';
        }
    
        $photo->save();
    
        return redirect()->route($this->getRoutesByRole($role)['detailsRoute'], ['role' => $role, 'id' => $workplaceId])
            ->with('success', '写真が更新されました。');
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
    
        return response()->json(['success' => true, 'message' => '写真が削除されました。']);
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
