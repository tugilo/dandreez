<?php

namespace App\Http\Controllers;

use App\Models\ConstructionCompany;
use App\Models\Zip;
use Illuminate\Http\Request;

class ConstructionCompanyController extends Controller
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
     * 施工会社の一覧を表示します。
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $constructionCompanies = ConstructionCompany::where('show_flg', 1)->get();
        return view('construction_companies.index', compact('constructionCompanies'));
    }

    /**
     * 新規施工会社登録フォームを表示します。
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $prefectures = Zip::getPrefectures();
        return view('construction_companies.create', compact('prefectures'));
    }

    /**
     * 新規施工会社をデータベースに保存します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'zip' => 'nullable|string|max:255',
            'prefecture' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
        ]);

        $constructionCompany = new ConstructionCompany();
        $constructionCompany->name = $request->name;
        $constructionCompany->name_kana = $request->name_kana;
        $constructionCompany->tel = $request->tel;
        $constructionCompany->email = $request->email;
        $constructionCompany->zip = $request->zip;
        $constructionCompany->prefecture = $request->prefecture;
        $constructionCompany->city = $request->city;
        $constructionCompany->address = $request->address;
        $constructionCompany->building = $request->building;
        $constructionCompany->save();

        return redirect()->route('construction_companies.index')->with('success', '新しい施工会社が登録されました。');
    }

    /**
     * 指定した施工会社の編集フォームを表示します。
     *
     * @param  ConstructionCompany $constructionCompany
     * @return \Illuminate\View\View
     */
    public function edit(ConstructionCompany $constructionCompany)
    {
        $prefectures = Zip::getPrefectures();
        return view('construction_companies.edit', compact('constructionCompany', 'prefectures'));
    }

    /**
     * 指定した施工会社の情報を更新します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  ConstructionCompany $constructionCompany
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ConstructionCompany $constructionCompany)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'zip' => 'nullable|string|max:255',
            'prefecture' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
        ]);

        $constructionCompany->name = $request->name;
        $constructionCompany->name_kana = $request->name_kana;
        $constructionCompany->tel = $request->tel;
        $constructionCompany->email = $request->email;
        $constructionCompany->zip = $request->zip;
        $constructionCompany->prefecture = $request->prefecture;
        $constructionCompany->city = $request->city;
        $constructionCompany->address = $request->address;
        $constructionCompany->building = $request->building;
        $constructionCompany->save();
        return redirect()->route('construction_companies.index')->with('success', '施工会社情報が更新されました。');
    }

    /**
     * 指定した施工会社を論理削除します。
     *
     * @param  ConstructionCompany $constructionCompany
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ConstructionCompany $constructionCompany)
    {
        $constructionCompany->update(['show_flg' => 0]);
        return redirect()->route('construction_companies.index')->with('success', '施工会社が削除されました。');
    }
}
