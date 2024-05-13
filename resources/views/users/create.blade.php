@extends('adminlte::page')

@section('title', '新規ユーザー登録')

@section('content_header')
    <h1>新規ユーザー登録</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <!-- ユーザータイプ -->
            <div class="form-group">
                <label for="user_type">ユーザータイプ <span class="badge bg-danger">必須</span></label>
                <select id="user_type" class="form-control" name="user_type" required>
                    <option value="">選択してください</option>
                    @foreach ($userTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>


            <!-- 名前 -->
            <div class="form-group">
                <label for="name">名前 <span class="badge bg-danger">必須</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="名前">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="name_kana">名前（カナ）</label>
                <input type="text" name="name_kana" id="name_kana" class="form-control @error('name_kana') is-invalid @enderror" value="{{ old('name_kana') }}" required placeholder="名前（カナ）">
            </div>

            <!-- メールアドレス -->
            <div class="form-group">
                <label for="email">メールアドレス <span class="badge bg-danger">必須</span></label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="メールアドレス">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- ログインID -->
            <div class="form-group">
                <label for="login_id">ログインID <span class="badge bg-danger">必須</span></label>
                <input type="text" name="login_id" id="login_id" class="form-control @error('login_id') is-invalid @enderror" value="{{ old('login_id') }}" required placeholder="ログインID">
                @error('login_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- パスワード -->
            <div class="form-group">
                <label for="password">パスワード <span class="badge bg-danger">必須</span></label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="パスワード">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- パスワード確認 -->
            <div class="form-group">
                <label for="password_confirmation">パスワード（確認用） <span class="badge bg-danger">必須</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required placeholder="パスワード（確認用）">
                @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 権限 -->
            <div class="form-group">
                <label for="role_id">権限 <span class="badge bg-danger">必須</span></label>
                <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                    <option value="">選択してください</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- メールフラグ -->
            <div class="form-group">
                <label for="mail_flg">メールフラグ <span class="badge bg-danger">必須</span></label>
                <select name="mail_flg" id="mail_flg" class="form-control @error('mail_flg') is-invalid @enderror" required>
                    <option value="">選択してください</option>
                    <option value="0">無効</option>
                    <option value="1">有効</option>
                </select>
                @error('mail_flg')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">登録</button>
        </form>
    </div>
</div>
@stop