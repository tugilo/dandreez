@extends('adminlte::page')

@section('title', 'ログインユーザー情報の編集')

@section('content_header')
    <h1>ログインユーザー情報の編集</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $login->id) }}">
            @csrf
            @method('PUT')
            <!-- 名前 -->
            <div class="form-group">
                <label for="name">名前</label>
                <input type="text" name="name" value="{{ $login->name }}" class="form-control" required>
            </div>

            <!-- 会社 -->
            @if($userType !== 'admin')
            <div class="form-group">
                <label for="company_id">会社</label>
                <select name="company_id" class="form-control">
                    <option value="">選択してください</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @if($login->user->company_id == $company->id) selected @endif>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- ロール -->
            <div class="form-group">
                <label for="role_id">ロール</label>
                <select name="role_id" class="form-control" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $login->role_id == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- メールアドレス -->
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" value="{{ $email }}" class="form-control" required>
            </div>

            <!-- パスワード -->
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" name="password" class="form-control">
                <small>変更する場合のみ入力してください。</small>
            </div>

            <!-- 確認用パスワード -->
            <div class="form-group">
                <label for="password_confirmation">パスワード（確認用）</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <!-- メールフラグ -->
            <div class="form-group">
                <label for="mail_flg">メールフラグ</label>
                <select name="mail_flg" class="form-control" required>
                    <option value="1" {{ $login->mail_flg == 1 ? 'selected' : '' }}>有効</option>
                    <option value="0" {{ $login->mail_flg == 0 ? 'selected' : '' }}>無効</option>
                </select>
            </div>

            <!-- 更新ボタン -->
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">更新</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
</div>
@stop
