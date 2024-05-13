<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Login;
use App\Models\CustomerStaff;
use App\Models\SalerStaff;
use App\Models\Worker;
use App\Models\UserType;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; 

class UserController extends Controller
{
    public function create()
    {
        // ユーザー種別を取得
        $roles = Role::where('show_flg', true)->get();
        $userTypes = UserType::where('show_flg', true)->get();
        return view('users.create', compact('userTypes','roles'));
    }
    

    /**
     * ユーザー登録処理を行います。
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {

        // 共通のバリデーションルールを設定
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255|unique:logins,login_id',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|integer',
            'role_id' => 'required|exists:roles,id',
            'mail_flg' => 'required|boolean',
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
            Log::warning('ユーザー登録失敗', ['errors' => $validator->errors()]);
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
                $user->name_kana = $request->name_kana;
                $user->email = $request->email;
                $user->role_id = $request->role_id;
                $user->mail_flg = $request->mail_flg;
                $user->save();
                $login->user_id = $user->id;
                break;
            case 'customer':
                $customerStaff = new CustomerStaff();
                $customerStaff->name = $request->name;
                $customerStaff->name_kana = $request->name_kana;
                $customerStaff->email = $request->email;
                $customerStaff->role_id = $request->role_id;
                $customerStaff->mail_flg = $request->mail_flg;
                $customerStaff->save();
                $login->user_id = $customerStaff->id;
                break;
            case 'saler':
                $salerStaff = new SalerStaff();
                $salerStaff->name = $request->name;
                $salerStaff->name_kana = $request->name_kana;
                $salerStaff->email = $request->email;
                $salerStaff->role_id = $request->role_id;
                $salerStaff->mail_flg = $request->mail_flg;
                $salerStaff->save();
                $login->user_id = $salerStaff->id;
                break;
            case 'worker':
                $worker = new Worker();
                $worker->name = $request->name;
                $worker->name_kana = $request->name_kana;
                $worker->email = $request->email;
                $worker->role_id = $request->role_id;
                $worker->mail_flg = $request->mail_flg;
                $worker->save();
                $login->user_id = $worker->id;
                break;
            default:
                throw new \InvalidArgumentException('無効なユーザータイプです');
        }

        $login->save();
        // ユーザー保存後のログ
        Log::info('ユーザー登録完了', ['id' => $login->id, 'email' => $login->userType->name]);

        // 登録後はユーザー一覧画面にリダイレクト
        return redirect()->route('users.index')->with('success', '新しいユーザーが登録されました。');
    }
    
    /**
     * ユーザー一覧を表示します。
     * システム管理者の場合、全てのユーザータイプの一覧が表示されます。
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
    // ログインユーザーのユーザータイプを確認
    if (Auth::user()->userType->type === 'admin') {
        // システム管理者の場合、すべてのユーザータイプのデータを取得する
        $users = Login::with(['user.role', 'customerStaff.role', 'salerStaff.role', 'worker.role'])
        ->get()
        ->map(function ($login) {
            // ユーザータイプに応じた適切なユーザー情報を取得し、統一フォーマットで出力する
            if ($login->user) {
                $user = $login->user;
                $roleName = $user->role->name ?? 'N/A';
            } elseif ($login->customerStaff) {
                $user = $login->customerStaff;
                $roleName = $user->role->name ?? 'N/A';
            } elseif ($login->salerStaff) {
                $user = $login->salerStaff;
                $roleName = $user->role->name ?? 'N/A';
            } elseif ($login->worker) {
                $user = $login->worker;
                $roleName = $user->role->name ?? 'N/A';
            } else {
                return null; // 一致するユーザーデータがない場合はnullを返す
            }

            return [
                'id' => $login->id,
                'userType' => $login->userType->name,
                'name' => $user->name,
                'email' => $user->email,
                'roleName' => $roleName,
                'createdAt' => $login->created_at->format('Y-m-d H:i:s'),
                'updatedAt' => $login->updated_at->format('Y-m-d H:i:s'),
            ];
        })
        ->filter();
    } else {
        // 一般ユーザーの場合、自分自身の情報のみを表示
        $users = [];  // 実際のビジネスロジックに応じて適切なデータ取得が必要
    }

        return view('users.index', compact('users'));
    }

    /**
     * ユーザー編集フォームを表示
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * ユーザー情報を更新
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'mail_flg' => 'required|boolean',
        ]);
    
        $user->name = $request->name;
        $user->name_kana = $request->name_kana;
        $user->email = $request->email;
        $user->role_id = $request->role_id;
        $user->mail_flg = $request->mail_flg;
    
        if ($request->filled('password')) {
            if (Hash::check($request->password, $user->password)) {
                // 入力されたパスワードが現在のものと同じ場合は更新しない
                unset($request['password']);
            } else {
                // 入力されたパスワードが現在のものと異なる場合は更新する
                $user->password = Hash::make($request->password);
            }
        }
    
        $user->save();
    
        return redirect()->route('users.index')->with('success', 'ユーザー情報が更新されました。');
    }

     /**
     * ユーザーを論理削除
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->update(['show_flg' => 0]);

        return redirect()->route('users.index')->with('success', 'ユーザーが削除されました。');
    }

}