<?php

namespace App\Http\Controllers;

use App\Models\Saler;
use App\Models\Zip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalerCompanyController extends Controller
{
    public function index()
    {
        $salerCompanies = Saler::where('show_flg', 1)->get();
        return view('saler_companies.index', compact('salerCompanies'));
    }

    public function create()
    {
        $prefectures = Zip::getPrefectures();
        return view('saler_companies.create', compact('prefectures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // その他必要なバリデーションルール
        ]);

        $salerCompany = new Saler();
        $salerCompany->name = $request->name;
        $salerCompany->name_kana = $request->name_kana;
        $salerCompany->tel = $request->tel;
        $salerCompany->email = $request->email;
        $salerCompany->zip = $request->zip;
        $salerCompany->prefecture = $request->prefecture;
        $salerCompany->city = $request->city;
        $salerCompany->address = $request->address;
        $salerCompany->building = $request->building;
        $salerCompany->save();

        return redirect()->route('saler_companies.index')->with('success', '新規問屋会社が登録されました。');
    }

    public function edit(Saler $salerCompany)
    {
        $prefectures = Zip::getPrefectures();
        return view('saler_companies.edit', compact('salerCompany', 'prefectures'));
    }

    public function update(Request $request, Saler $salerCompany)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // その他必要なバリデーションルール
        ]);

        $salerCompany->name = $request->name;
        $salerCompany->name_kana = $request->name_kana;
        $salerCompany->tel = $request->tel;
        $salerCompany->email = $request->email;
        $salerCompany->zip = $request->zip;
        $salerCompany->prefecture = $request->prefecture;
        $salerCompany->city = $request->city;
        $salerCompany->address = $request->address;
        $salerCompany->building = $request->building;
        $salerCompany->save();

        return redirect()->route('saler_companies.index')->with('success', '問屋会社情報が更新されました。');
    }

    public function destroy(Saler $salerCompany)
    {
        $salerCompany->update(['show_flg' => 0]);
        return redirect()->route('saler_companies.index')->with('success', '問屋会社が論理削除されました。');
    }
}
