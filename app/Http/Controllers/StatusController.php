<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * コントローラーインスタンスの生成
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * ステータス一覧を表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 表示フラグが1のステータスのみを取得
        $statuses = Status::where('show_flg', 1)->orderBy('sort_order')->get();
        // 表示フラグが0のステータスのみを取得
        $deletedStatuses = Status::where('show_flg', 0)->orderBy('sort_order')->get();
        return view('statuses.index', compact('statuses', 'deletedStatuses'));
    }

    /**
     * 新規ステータス作成フォームを表示
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('statuses.create');
    }

    /**
     * 新規ステータスを保存
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'name_ja' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'show_flg' => 'required|boolean',
        ]);

        // ステータス作成
        Status::create($request->all());

        // ステータス一覧ページにリダイレクトし、成功メッセージを表示
        return redirect()->route('statuses.index')->with('success', 'ステータスが作成されました。');
    }

    /**
     * ステータス編集フォームを表示
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\View\View
     */
    public function edit(Status $status)
    {
        return view('statuses.edit', compact('status'));
    }

    /**
     * ステータスを更新
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Status $status)
    {
        // バリデーション
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'name_ja' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'show_flg' => 'required|boolean',
        ]);

        // ステータス更新
        $status->update($request->all());

        // ステータス一覧ページにリダイレクトし、成功メッセージを表示
        return redirect()->route('statuses.index')->with('success', 'ステータスが更新されました。');
    }

    /**
     * ステータスを論理削除（show_flgを0に設定）
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Status $status)
    {
        // 論理削除（show_flgを0に設定）
        $status->update(['show_flg' => 0]);

        // ステータス一覧ページにリダイレクトし、成功メッセージを表示
        return redirect()->route('statuses.index')->with('success', 'ステータスが削除されました。');
    }

    /**
     * 論理削除されたステータスを復活（show_flgを1に設定）
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Status $status)
    {
        // ステータス復活（show_flgを1に設定）
        $status->update(['show_flg' => 1]);

        // ステータス一覧ページにリダイレクトし、成功メッセージを表示
        return redirect()->route('statuses.index')->with('success', 'ステータスが復活されました。');
    }
}
