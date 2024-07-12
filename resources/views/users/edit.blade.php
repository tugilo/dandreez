@extends('adminlte::page')

@section('title', 'ユーザー編集')

@section('content_header')
    <h1>ユーザー編集</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', $login->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="login_id">ログインID</label>
                    <input type="text" class="form-control" id="login_id" value="{{ $login->login_id }}" readonly>
                </div>

                <div class="form-group">
                    <label for="name">名前</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name_kana">フリガナ</label>
                    <input type="text" class="form-control @error('name_kana') is-invalid @enderror" id="name_kana" name="name_kana" value="{{ old('name_kana', $user->name_kana) }}">
                    @error('name_kana')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">新しいパスワード（変更する場合のみ）</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">新しいパスワード（確認）</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>

                <div class="form-group">
                    <label for="role_id">役割</label>
                    <select class="form-control @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
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

                <div class="form-group">
                    <label for="mail_flg">メール通知</label>
                    <select class="form-control @error('mail_flg') is-invalid @enderror" id="mail_flg" name="mail_flg" required>
                        <option value="1" {{ old('mail_flg', $user->mail_flg) == 1 ? 'selected' : '' }}>有効</option>
                        <option value="0" {{ old('mail_flg', $user->mail_flg) == 0 ? 'selected' : '' }}>無効</option>
                    </select>
                    @error('mail_flg')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                @if($userType !== 'admin' && $companies->isNotEmpty())
                    <div class="form-group">
                        <label for="company_id">所属会社</label>
                        <select class="form-control @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $userCompanyId) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif

                <button type="submit" class="btn btn-primary">更新</button>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop