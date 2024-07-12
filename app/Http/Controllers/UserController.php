<?php

namespace App\Http\Controllers;

use App\Models\Login;
use App\Models\User;
use App\Models\CustomerStaff;
use App\Models\SalerStaff;
use App\Models\Worker;
use App\Models\UserType;
use App\Models\Role;
use App\Models\Customer;
use App\Models\Saler;
use App\Models\ConstructionCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * ユーザー登録フォームを表示します。
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        Log::info('ユーザー登録フォームの表示を開始');

        // ユーザー種別とロールを取得
        $roles = Role::where('show_flg', true)->get();
        $userTypes = UserType::where('show_flg', true)->get();

        Log::info('ユーザー登録フォームの表示を完了', [
            'roles_count' => $roles->count(),
            'user_types_count' => $userTypes->count()
        ]);

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
        Log::info('ユーザー登録処理を開始', $request->all());

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
        $emailRule = $this->getEmailValidationRule($userType->type);
        $validator->addRules(['email' => $emailRule]);

        if ($validator->fails()) {
            Log::warning('ユーザー登録失敗: バリデーションエラー', ['errors' => $validator->errors()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ログイン情報を作成
        $login = new Login();
        $login->name = $request->name;
        $login->login_id = $request->login_id;
        $login->password = Hash::make($request->password);
        $login->user_type_id = $userType->id;

        // ユーザー種別に応じてユーザー情報を作成
        $user = $this->createUserByType($userType->type, $request);

        if ($user) {
            $login->user_id = $user->id;
            $login->save();
            Log::info('ユーザー登録完了', ['id' => $login->id, 'email' => $user->email]);

            // 登録後にユーザーにメールを送信
            $this->sendWelcomeEmail($user, $request->login_id, $request->password);

            return redirect()->route('users.index')->with('success', '新しいユーザーが登録されました。');
        } else {
            Log::error('ユーザー登録失敗: ユーザー作成エラー');
            return redirect()->back()->withErrors(['error' => 'ユーザーの作成に失敗しました。'])->withInput();
        }
    }

/**
     * ユーザー一覧を表示します。
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        Log::info('ユーザー一覧の表示を開始');

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
            $userData = $this->mapUserData($login, $relatedUser);
            return $userData;
        });

        Log::info('ユーザー一覧の表示を完了', ['users_count' => $users->count()]);

        return view('users.index', compact('users'));
    }

    /**
     * 特定のログインユーザーの編集フォームを表示します。
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        Log::info('ユーザー編集フォームの表示を開始', ['user_id' => $id]);

        // ログイン情報とユーザータイプを取得
        $login = Login::with('userType')->findOrFail($id);
        Log::debug('Login情報', ['login' => $login]);

        $userType = $login->userType->type;
        Log::debug('ユーザータイプ', ['user_type' => $userType]);

        $roles = Role::all();
        Log::debug('役割一覧', ['roles_count' => $roles->count()]);

        $user = $login->getRelatedUser();
        Log::debug('関連ユーザー情報', ['user' => $user]);

        $companies = $this->getCompanies($userType);
        Log::debug('会社一覧', ['companies_count' => $companies->count()]);

        $userCompanyId = $this->getUserCompanyId($user, $userType);

        Log::info('ユーザー編集フォームの表示を完了', ['user_id' => $id]);

        return view('users.edit', compact('login', 'roles', 'companies', 'userType', 'user', 'userCompanyId'));
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
        Log::info('ユーザー情報更新処理を開始', ['user_id' => $id]);

        $login = Login::findOrFail($id);
        $userType = $login->userType->type;
        $user = $login->getRelatedUser();

        $validator = Validator::make($request->all(), $this->getUpdateValidationRules($userType, $user->id));

        if ($validator->fails()) {
            Log::warning('ユーザー情報更新失敗: バリデーションエラー', ['errors' => $validator->errors()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ログイン情報の更新
        $login->name = $request->name;
        if ($request->filled('password')) {
            $login->password = Hash::make($request->password);
        }
        $login->save();

        // ユーザー情報の更新
        $user = $this->updateUserByType($userType, $user, $request);

        Log::info('ユーザー情報更新完了', ['user_id' => $id]);

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
        Log::info('ユーザー削除処理を開始', ['user_id' => $id]);

        $login = Login::findOrFail($id);
        $user = $login->getRelatedUser();

        if ($user) {
            $user->update(['show_flg' => 0]);
            Log::info('関連ユーザー情報を論理削除', ['user_id' => $user->id]);
        }

        $login->update(['show_flg' => 0]);
        Log::info('ログイン情報を論理削除', ['login_id' => $login->id]);

        Log::info('ユーザー削除処理を完了', ['user_id' => $id]);

        return redirect()->route('users.index')->with('success', 'ユーザーが削除されました。');
    }

    /**
     * ユーザータイプに応じたメールアドレスのバリデーションルールを取得します。
     *
     * @param string $userType
     * @return string
     */
    private function getEmailValidationRule($userType)
    {
        $table = $this->getTableName($userType);
        return "required|string|email|max:255|unique:{$table},email";
    }

    /**
     * ユーザータイプに応じたテーブル名を取得します。
     *
     * @param string $userType
     * @return string
     */
    private function getTableName($userType)
    {
        switch ($userType) {
            case 'admin':
                return 'users';
            case 'customer':
                return 'customer_staffs';
            case 'saler':
                return 'saler_staffs';
            case 'worker':
                return 'workers';
            default:
                throw new \InvalidArgumentException('Invalid user type');
        }
    }

    /**
     * ユーザータイプに応じたユーザーを作成します。
     *
     * @param string $userType
     * @param Request $request
     * @return mixed
     */
    private function createUserByType($userType, $request)
    {
        switch ($userType) {
            case 'admin':
                return User::create($this->getUserData($request));
            case 'customer':
                return CustomerStaff::create($this->getUserData($request));
            case 'saler':
                return SalerStaff::create($this->getUserData($request));
            case 'worker':
                return Worker::create($this->getUserData($request));
            default:
                Log::error('無効なユーザータイプ', ['user_type' => $userType]);
                return null;
        }
    }

    /**
     * ユーザーデータを取得します。
     *
     * @param Request $request
     * @return array
     */
    private function getUserData($request)
    {
        return [
            'name' => $request->name,
            'name_kana' => $request->name_kana,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'mail_flg' => $request->mail_flg,
        ];
    }

    /**
     * ユーザーデータをマッピングします。
     *
     * @param Login $login
     * @param mixed $relatedUser
     * @return array
     */
    private function mapUserData($login, $relatedUser)
    {
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

        $userData['companyName'] = $this->getCompanyName($login->userType->type, $relatedUser);

        return $userData;
    }

    /**
     * ユーザータイプに応じた会社名を取得します。
     *
     * @param string $userType
     * @param mixed $user
     * @return string
     */
    private function getCompanyName($userType, $user)
    {
        switch ($userType) {
            case 'customer':
                return $user->customer->name ?? 'N/A';
            case 'saler':
                return $user->saler->name ?? 'N/A';
            case 'worker':
                return $user->constructionCompany->name ?? 'N/A';
            default:
                return 'N/A';
        }
    }

    /**
     * 更新用のバリデーションルールを取得します。
     *
     * @param string $userType
     * @param int $userId
     * @return array
     */
    private function getUpdateValidationRules($userType, $userId)
    {
        return [
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique($this->getTableName($userType))->ignore($userId)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'mail_flg' => 'required|boolean',
            'company_id' => [
                'nullable',
                Rule::exists($this->getCompanyTableName($userType), 'id')
            ],
        ];
    }

    /**
     * ユーザータイプに応じた会社テーブル名を取得します。
     *
     * @param string $userType
     * @return string
     */
    private function getCompanyTableName($userType)
    {
        switch ($userType) {
            case 'customer':
                return 'customers';
            case 'saler':
                return 'salers';
            case 'worker':
                return 'construction_companies';
            default:
                throw new \InvalidArgumentException('Invalid user type for company');
        }
    }

    /**
     * ユーザータイプに応じたユーザー情報を更新します。
     *
     * @param string $userType
     * @param mixed $user
     * @param Request $request
     * @return mixed
     */
    private function updateUserByType($userType, $user, $request)
    {
        $userData = $this->getUserData($request);
        
        if ($userType !== 'admin' && $request->filled('company_id')) {
            $companyColumn = $this->getCompanyColumn($userType);
            $userData[$companyColumn] = $request->company_id;
        }

        $user->update($userData);
        return $user;
    }

    /**
     * ユーザータイプに応じた会社カラム名を取得します。
     *
     * @param string $userType
     * @return string
     */
    private function getCompanyColumn($userType)
    {
        switch ($userType) {
            case 'customer':
                return 'customer_id';
            case 'saler':
                return 'saler_id';
            case 'worker':
                return 'construction_company_id';
            default:
                throw new \InvalidArgumentException('Invalid user type for company column');
        }
    }
    /**
     * ユーザータイプに応じた会社一覧を取得します。
     *
     * @param string $userType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCompanies($userType)
    {
        Log::info('会社一覧の取得を開始', ['user_type' => $userType]);

        $companies = collect();

        switch ($userType) {
            case 'customer':
                $companies = Customer::where('show_flg', 1)->get();
                break;
            case 'saler':
                $companies = Saler::where('show_flg', 1)->get();
                break;
            case 'worker':
                $companies = ConstructionCompany::where('show_flg', 1)->get();
                break;
            default:
                Log::warning('無効なユーザータイプ', ['user_type' => $userType]);
                break;
        }

        Log::info('会社一覧の取得を完了', ['companies_count' => $companies->count()]);

        return $companies;
    }
    /**
     * ユーザーの会社IDを取得します。
     *
     * @param mixed $user
     * @param string $userType
     * @return int|null
     */
    private function getUserCompanyId($user, $userType)
    {
        switch ($userType) {
            case 'customer':
                return $user->customer_id ?? null;
            case 'saler':
                return $user->saler_id ?? null;
            case 'worker':
                return $user->construction_company_id ?? null;
            default:
                return null;
        }
    }
}