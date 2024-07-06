<?php

namespace App\Http\Controllers;

use App\Models\NotificationContent;
use Illuminate\Http\Request;

class NotificationContentController extends Controller
{
    /**
     * 通知内容の一覧を表示
     */
    public function index()
    {
        $notificationContents = NotificationContent::visible()->get();
        return view('notification_contents.index', compact('notificationContents'));
    }

    /**
     * 通知内容の新規作成フォームを表示
     */
    public function create()
    {
        return view('notification_contents.create');
    }

    /**
     * 通知内容を保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'message' => 'required|string|max:500',
        ]);

        NotificationContent::create($validated);

        return redirect()->route('notification_contents.index')->with('success', '通知内容が登録されました。');
    }

    /**
     * 通知内容の編集フォームを表示
     */
    public function edit($id)
    {
        $notificationContent = NotificationContent::findOrFail($id);
        return view('notification_contents.edit', compact('notificationContent'));
    }

    /**
     * 通知内容を更新
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'message' => 'required|string|max:500',
        ]);

        $notificationContent = NotificationContent::findOrFail($id);
        $notificationContent->update($validated);

        return redirect()->route('notification_contents.index')->with('success', '通知内容が更新されました。');
    }

    /**
     * 通知内容を論理削除
     */
    public function destroy($id)
    {
        $notificationContent = NotificationContent::findOrFail($id);
        $notificationContent->update(['show_flg' => 0]);

        return redirect()->route('notification_contents.index')->with('success', '通知内容が削除されました。');
    }
}
