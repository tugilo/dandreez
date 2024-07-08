<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
//        'passwords' => 'logins',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            //'provider' => 'users',
            'provider' => 'logins',
        ],
    
        'api' => [
            'driver' => 'token',
//            'provider' => 'users',
            'provider' => 'logins',
            'hash' => false,
        ],
    
        // 新しい'login'ガードを追加
        'login' => [
            'driver' => 'session', // ドライバー(認証方式)を指定
            'provider' => 'logins', // プロバイダー(ユーザーモデル)を指定
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
/*
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
*/
        // 新しい'logins'プロバイダーを追加
        'logins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Login::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */
    'passwords' => [
        'logins' => [  // 'logins'に対応するパスワードリセット設定
            'provider' => 'logins',  // 'logins'プロバイダーを参照
            'table' => 'password_resets',  // パスワードリセットテーブル名
            'expire' => 60,  // リセットトークンの有効期限（分）
            'throttle' => 60,
        ],
    ],


/*
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
*/
    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,  // パスワード確認のタイムアウト（秒）
    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | This option defines the field that should be used as the username for
    | authentication purposes. By default, Laravel uses the 'email' field,
    | but you can change it to any field in your User model that you want.
    |
    */

    'username' => 'login_id',  // 認証で使用するユーザ名フィールド
];
