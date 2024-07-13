@extends('adminlte::page')

@section('title', 'ユーザー編集')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> ユーザー編集</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">ユーザー情報</h3>
            </div>
            <form action="{{ route('users.update', $login->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if($userType !== 'admin' && $companies->isNotEmpty())
                        <div class="form-group">
                            <label for="company_id">所属会社 <span class="badge badge-danger">必須</span></label>
                            <select class="form-control select2 @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
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

                    <div class="form-group">
                        <label for="login_id">ログインID</label>
                        <input type="text" class="form-control" id="login_id" value="{{ $login->login_id }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="name">名前 <span class="badge badge-danger">必須</span></label>
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
                        <label for="email">メールアドレス <span class="badge badge-danger">必須</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">新しいパスワード（変更する場合のみ）</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="generate-password">
                                    <i class="fas fa-key"></i> 生成
                                </button>
                            </div>
                        </div>
                        <small id="passwordHelpBlock" class="form-text text-muted">
                            生成されたパスワード: <span id="generated-password"></span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="copy-password" style="display: none;">
                                <i class="fas fa-copy"></i> コピー
                            </button>
                        </small>
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
                        <label for="role_id">役割 <span class="badge badge-danger">必須</span></label>
                        <select class="form-control select2 @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
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
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="mail_flg" name="mail_flg" value="1" {{ old('mail_flg', $user->mail_flg) == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="mail_flg">有効</label>
                        </div>
                        @error('mail_flg')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 更新
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
            });

            $('#generate-password').click(function() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%^&*';
                let password = '';
                for (let i = 0; i < 12; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                $('#password').val(password);
                $('#password_confirmation').val(password);
                $('#generated-password').text(password);
                $('#copy-password').show();
            });

            $('#copy-password').click(function() {
                const generatedPassword = $('#generated-password').text();
                navigator.clipboard.writeText(generatedPassword).then(function() {
                    alert('パスワードがクリップボードにコピーされました。');
                }, function() {
                    alert('パスワードのコピーに失敗しました。');
                });
            });
        });
    </script>
@stop