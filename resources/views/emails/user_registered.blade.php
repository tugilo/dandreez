<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー登録完了</title>
</head>
<body>
    <p>新しいユーザーとして登録されました。</p>
    <p>以下の情報でログインしてください:</p>
    <ul>
        <li>ログインID: {{ $loginId }}</li>
        <li>パスワード: {{ $password }}</li>
        <li>ログインURL: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></li>
    </ul>
    <p>よろしくお願いいたします。</p>
</body>
</html>
