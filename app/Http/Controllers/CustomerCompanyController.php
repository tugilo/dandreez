<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Zip;

use Illuminate\Http\Request;

class CustomerCompanyController extends Controller
{
    /**
     * 取引先会社の一覧を表示します。
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customerCompanies = Customer::where('show_flg', 1)->get();
        return view('customer_companies.index', compact('customerCompanies'));
    }

    /**
     * 取引先会社の登録フォームを表示します。
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $prefectures = Zip::getPrefectures();
        return view('customer_companies.create', compact('prefectures'));
    }

    /**
     * 新しい取引先会社を登録します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', // 名前は必須かつ255文字以内
        ]);
        
        $customerCompany = new Customer();
        $customerCompany->name = $request->name;
        $customerCompany->name_kana = $request->name_kana;
        $customerCompany->tel = $request->tel;
        $customerCompany->email = $request->email;
        $customerCompany->zip = $request->zip;
        $customerCompany->prefecture = $request->prefecture;
        $customerCompany->city = $request->city;
        $customerCompany->address = $request->address;
        $customerCompany->building = $request->building;
        $customerCompany->save();

        return redirect()->route('customer_companies.index')->with('success', '取引先会社が正常に登録されました。');
    }

    /**
     * 特定の取引先会社の詳細を表示します。
     *
     * @param  CustomerCompany $customerCompany
     * @return \Illuminate\View\View
     */
    public function show(Customer $customerCompany)
    {
        return view('customer_companies.show', compact('customerCompany'));
    }

    /**
     * 取引先会社の編集フォームを表示します。
     *
     * @param  CustomerCompany $customerCompany
     * @return \Illuminate\View\View
     */
    public function edit(Customer $customerCompany)
    {
        $prefectures = Zip::getPrefectures();
        return view('customer_companies.edit', compact('customerCompany', 'prefectures'));
    }

    /**
     * 特定の取引先会社の情報を更新します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  CustomerCompany $customerCompany
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Customer $customerCompany)
    {
        $request->validate([
            'name' => 'required|string|max:255', // 名前は必須かつ255文字以内
        ]);

        $customerCompany->name = $request->name;
        $customerCompany->name_kana = $request->name_kana;
        $customerCompany->tel = $request->tel;
        $customerCompany->email = $request->email;
        $customerCompany->zip = $request->zip;
        $customerCompany->prefecture = $request->prefecture;
        $customerCompany->city = $request->city;
        $customerCompany->address = $request->address;
        $customerCompany->building = $request->building;
        $customerCompany->save();

        return redirect()->route('customer_companies.index')->with('success', '取引先会社情報が更新されました。');
    }

    /**
     * 特定の取引先会社を削除します。
     *
     * @param  CustomerCompany $customerCompany
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Customer $customerCompany)
    {
        // show_flgを0に設定して論理削除
        $customerCompany->update(['show_flg' => 0]);
        return redirect()->route('customer_companies.index')->with('success', '取引先会社が削除されました。');
    }
}
