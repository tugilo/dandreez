<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Login;
use App\Models\User;
use App\Models\CustomerStaff;
use App\Models\SalerStaff;
use App\Models\Worker;
use App\Models\UserType;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * ユーザー登録フォームを表示する
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // ユーザー種別を取得
        $roles = Role::where('show_flg', true)->get();
        $userTypes = UserType::where('show_flg', true)->get();
        return view('users.create', compact('userTypes','roles'));
    }

    /**
     * ユーザー登録処理を行う
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // 共通のバリデーションルールを設定
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255|unique:logins,login_id',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|integer',
        ]);

        // ユーザー種別に応じた追加のバリデーション
        $userType = UserType::findOrFail($request->user_type);
        switch ($userType->type) {
            case 'admin':
                $emailRule = 'required|string|email|max:255|unique:users,email';
                break;
            case 'customer':
                $emailRule = 'required|string|email|max:255|unique:customer_staffs,email';
                break;
            case 'saler':
                $emailRule = 'required|string|email|max:255|unique:saler_staffs,email';
                break;
            case 'worker':
                $emailRule = 'required|string|email|max:255|unique:workers,email';
                break;
            default:
                throw new \InvalidArgumentException('Invalid user type');
        }
        
        $validator->addRules(['email' => $emailRule]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ログイン情報を作成
        $login = new Login();
        $login->name = $request->name;
        $login->login_id = $request->login_id;
        $login->password = Hash::make($request->password);
        $login->user_type_id = $userType->id;

        // ユーザー種別に応じてユーザー情報を作成
        switch ($userType->type) {
            case 'admin':
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->save();
                $login->user_id = $user->id;
                break;
            case 'customer':
                $customerStaff = new CustomerStaff();
                $customerStaff->name = $request->name;
                $customerStaff->email = $request->email;
                $customerStaff->save();
                $login->user_id = $customerStaff->id;
                break;
            case 'saler':
                $salerStaff = new SalerStaff();
                $salerStaff->name = $request->name;
                $salerStaff->email = $request->email;
                $salerStaff->save();
                $login->user_id = $salerStaff->id;
                break;
            case 'worker':
                $worker = new Worker();
                $worker->name = $request->name;
                $worker->email = $request->email;
                $worker->save();
                $login->user_id = $worker->id;
                break;
            default:
                throw new \InvalidArgumentException('Invalid user type');
        }

        // ログイン情報を保存
        $login->save();

        // ユーザー一覧画面にリダイレクト
        return redirect()->route('users.index');
    }
}