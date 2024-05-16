<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ConstructionCompany;
use App\Models\Saler;
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
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;

class UserController extends Controller
{
    /**
     * ユーザー登録フォームを表示します。
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // ユーザー種別とロールを取得
        $roles = Role::where('show_flg', true)->get();
        $userTypes = UserType::where('show_flg', true)->get();
        return view('users.create', compact('userTypes', 'roles'));
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
                throw new \InvalidArgumentException('無効なユーザータイプです');
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

        // 登録後にユーザーにメールを送信
        $loginUrl = route('login');
        Mail::to($request->email)->send(new UserRegistered($request->login_id, $request->password, $loginUrl));

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
        // ログイン情報と関連ユーザー情報を取得
        $logins = Login::with([
            'userType',
            'user.role',
            'customerStaff.customer',
            'customerStaff.role',
            'salerStaff.saler',
            'salerStaff.role',
            'worker.constructionCompany',
            'worker.role'
        ])->get();

        // 各ログイン情報に対応するユーザー情報をマッピング
        $users = $logins->map(function ($login) {
            $relatedUser = $login->getRelatedUser();

            $userData = [
                'id' => $login->id,
                'userType' => $login->userType->name,
                'name' => $relatedUser->name ?? 'N/A',
                'email' => $relatedUser->email ?? 'N/A',
                'login_id' => $login->login_id,
                'roleName' => $relatedUser->role->name ?? 'N/A',
                'companyName' => 'N/A',
                'createdAt' => $login->created_at->format('Y-m-d H:i:s'),
                'updatedAt' => $login->updated_at->format('Y-m-d H:i:s'),
            ];

            // ユーザー種別に応じた会社名を設定
            switch ($login->userType->type) {
                case 'customer':
                    $userData['companyName'] = $relatedUser->customer->name ?? 'N/A';
                    break;
                case 'saler':
                    $userData['companyName'] = $relatedUser->saler->name ?? 'N/A';
                    break;
                case 'worker':
                    $userData['companyName'] = $relatedUser->constructionCompany->name ?? 'N/A';
                    break;
            }

            return $userData;
        });

        return view('users.index', compact('users'));
    }

    /**
     * 特定のログインユーザーの編集フォームを表示します。
     * ログインユーザーの種別に応じて異なる会社データを選択肢として表示します。
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // 'userType' リレーションを含めて 'Login' モデルをロード
        $login = Login::with('userType')->findOrFail($id);

        // デバッグログを追加
        Log::info('Edit method called', ['login' => $login]);

        // userTypeがnullの場合、エラーメッセージを表示してリダイレクト
        if (is_null($login->userType)) {
            Log::error('UserType is null', ['login' => $login]);
            return redirect()->route('users.index')->with('error', '関連付けられたユーザータイプが見つかりません。');
        }

        $roles = Role::all();
        $userType = $login->userType->type;
        Log::info('UserType retrieved', ['userType' => $userType]);

        $companies = [];
        $email = '';

        // ユーザー種別に応じた会社データを取得
        switch ($userType) {
            case 'customer':
                $user = $login->customerStaff;
                $companies = Customer::where('show_flg', 1)->get();
                $email = $user ? $user->email : '';
                break;
            case 'saler':
                $user = $login->salerStaff;
                $companies = Saler::where('show_flg', 1)->get();
                $email = $user ? $user->email : '';
                break;
            case 'worker':
                $user = $login->worker;
                $companies = ConstructionCompany::where('show_flg', 1)->get();
                $email = $user ? $user->email : '';
                break;
            default:
                $user = $login->user;
                $email = $user ? $user->email : ''; // システム管理者など
                break;
        }

        Log::info('Companies retrieved', ['companies' => $companies]);

        return view('users.edit', compact('login', 'roles', 'companies', 'userType', 'email'));
    }

    /**
     * ユーザー情報を更新します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $login = Login::with('userType')->findOrFail($id);

        // リクエストデータのログを追加
        Log::info('Update method called', ['request' => $request->all()]);

        // 共通のバリデーションルールを設定
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'mail_flg' => 'required|boolean',
            'company_id' => 'nullable|exists:customers,id|exists:salers,id|exists:construction_companies,id',
        ]);

        // ユーザー種別に応じた追加のバリデーション
        $userType = $login->userType->type;
        switch ($userType) {
            case 'admin':
                $emailRule = 'required|string|email|max:255|unique:users,email,' . $login->user_id;
                break;
            case 'customer':
                $emailRule = 'required|string|email|max:255|unique:customer_staffs,email,' . $login->user_id;
                break;
            case 'saler':
                $emailRule = 'required|string|email|max:255|unique:saler_staffs,email,' . $login->user_id;
                break;
            case 'worker':
                $emailRule = 'required|string|email|max:255|unique:workers,email,' . $login->user_id;
                break;
            default:
                throw new \InvalidArgumentException('無効なユーザータイプです');
        }

        $validator->addRules(['email' => $emailRule]);

        if ($validator->fails()) {
            Log::warning('ユーザー更新失敗', ['errors' => $validator->errors()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ログイン情報を更新
        $login->name = $request->name;
        $login->save();

        // ユーザー種別に応じてユーザー情報を更新
        switch ($userType) {
            case 'admin':
                $user = User::findOrFail($login->user_id);
                break;
            case 'customer':
                $user = CustomerStaff::findOrFail($login->user_id);
                $user->customer_id = $request->company_id; // 会社IDを設定
                break;
            case 'saler':
                $user = SalerStaff::findOrFail($login->user_id);
                $user->saler_id = $request->company_id; // 会社IDを設定
                break;
            case 'worker':
                $user = Worker::findOrFail($login->user_id);
                $user->construction_company_id = $request->company_id; // 会社IDを設定
                break;
            default:
                throw new \InvalidArgumentException('無効なユーザータイプです');
        }

        $user->name = $request->name;
        $user->name_kana = $request->name_kana;
        $user->email = $request->email;
        $user->role_id = $request->role_id;
        $user->mail_flg = $request->mail_flg;

        if ($request->filled('password')) {
            if (!Hash::check($request->password, $user->password)) {
                // 入力されたパスワードが現在のものと異なる場合は更新する
                $user->password = Hash::make($request->password);
            }
        }

        $user->save();

        // 更新後はユーザー一覧画面にリダイレクト
        return redirect()->route('users.index')->with('success', 'ユーザー情報が更新されました。');
    }

    /**
     * ユーザーを論理削除します。
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $login = Login::findOrFail($id);
        $user = $login->getRelatedUser();

        if ($user) {
            $user->update(['show_flg' => 0]);
        }

        // ログイン情報も論理削除
        $login->update(['show_flg' => 0]);

        return redirect()->route('users.index')->with('success', 'ユーザーが削除されました。');
    }
}
