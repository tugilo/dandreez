<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Login;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    /**
     * ログインフォームを表示する
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理を行います。
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        Log::debug('Request:', $request->all());

        // 'login'ガードを設定します。
        $credentials = $request->only('login_id', 'password');
        Log::info('Input credentials:', $credentials);

        // カスタムクエリで有効なユーザーかどうか確認する
        $login = Login::where('login_id', $credentials['login_id'])->where('show_flg', 1)->first();

        if ($login && Auth::validate($credentials)) {
            Auth::login($login, $request->filled('remember'));
            $userType = $login->userType;
            Log::debug('Authentication successful');

            // セッションの再生成を行います。
            $request->session()->regenerate();

            // ユーザータイプに応じて適切なページへリダイレクトします。
            return $this->redirectTo($userType->type);
        }

        Log::warning('Authentication failed:', $credentials);
        return back()->withErrors([
            'login_id' => 'ログインIDまたはパスワードが正しくありません。',
        ])->withInput($request->only('login_id'));
    }

    /**
     * ユーザータイプに基づいてリダイレクト先を返します。
     *
     * @param string $type
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectTo($type)
    {
        switch ($type) {
            case 'admin':
                Log::debug('Redirecting to admin home');
                return redirect()->route('admin.home');
            case 'customer':
                Log::debug('Redirecting to customer home');
                return redirect()->route('customer.home');
            case 'saler':
                Log::debug('Redirecting to saler home');
                return redirect()->route('saler.home');
            case 'worker':
                Log::debug('Redirecting to worker home');
                return redirect()->route('worker.home');
            default:
                Log::debug('Redirecting to generic home');
                return redirect('/home');  // 未定義のユーザータイプの場合は汎用のホームへリダイレクト
        }
    }

    /**
     * ログアウト処理を行う
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * ユーザー種別に基づいて認証ガードを取得する
     *
     * @param \App\Models\UserType $userType
     * @return string
     */
    private function getGuardForUserType(UserType $userType)
    {
        switch ($userType->type) {
            case 'admin':
                return 'user';
            case 'customer':
                return 'customer_staff';
            case 'saler':
                return 'saler_staff';
            case 'worker':
                return 'worker';
            default:
                throw new \InvalidArgumentException('Invalid user type');
        }
    }
}
