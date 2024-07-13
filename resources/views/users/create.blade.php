@extends('adminlte::page')

@section('title', '新規ユーザー登録')

@section('content_header')
    <h1>
        <i class="fas fa-user-plus"></i> 新規ユーザー登録
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">ユーザー情報入力</h3>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_type">ユーザータイプ <span class="badge bg-danger">必須</span></label>
                                <select id="user_type" class="form-control select2" name="user_type" required>
                                    <option value="">選択してください</option>
                                    @foreach ($userTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" id="company-group" style="display: none;">
                                <label for="company_id">所属会社 <span class="badge badge-danger">必須</span></label>
                                <div class="input-group">
                                    <select id="company_id" class="form-control select2 @error('company_id') is-invalid @enderror" name="company_id">
                                        <option value="">選択してください</option>
                                    </select>
                                    <div class="input-group-append">
                                        <a href="#" class="btn btn-outline-secondary" id="new-company-link" style="display: none;">
                                            <i class="fas fa-plus"></i> 新規会社登録
                                        </a>
                                    </div>
                                </div>
                                @error('company_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
        
        
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
                                <input type="text" name="name_kana" id="name_kana" class="form-control @error('name_kana') is-invalid @enderror" value="{{ old('name_kana') }}" placeholder="名前（カナ）">
                            </div>
                            <div class="form-group">
                                <label for="email">メールアドレス <span class="badge bg-danger">必須</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="メールアドレス">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="login_id">ログインID <span class="badge bg-danger">必須</span></label>
                                <input type="text" name="login_id" id="login_id" class="form-control @error('login_id') is-invalid @enderror" value="{{ old('login_id') }}" required placeholder="ログインID">
                                @error('login_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password">パスワード <span class="badge bg-danger">必須</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="パスワード">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="generate-password">
                                            <i class="fas fa-key"></i> 生成
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="toggle-password">
                                            <i class="fas fa-eye"></i>
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
                                <label for="password_confirmation">パスワード（確認用） <span class="badge bg-danger">必須</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required placeholder="パスワード（確認用）">
                                @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="role_id">権限 <span class="badge bg-danger">必須</span></label>
                                <select name="role_id" id="role_id" class="form-control select2 @error('role_id') is-invalid @enderror" required>
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
                            <div class="form-group">
                                <label for="mail_flg">メール通知</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="mail_flg" name="mail_flg" value="1">
                                    <label class="custom-control-label" for="mail_flg">有効</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 登録
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

        $('#toggle-password').click(function() {
            const passwordField = $('#password');
            const passwordFieldType = passwordField.attr('type');
            if (passwordFieldType === 'password') {
                passwordField.attr('type', 'text');
                $(this).html('<i class="fas fa-eye-slash"></i>');
            } else {
                passwordField.attr('type', 'password');
                $(this).html('<i class="fas fa-eye"></i>');
            }
        });

        $('#copy-password').click(function() {
            const generatedPassword = $('#generated-password').text();
            navigator.clipboard.writeText(generatedPassword).then(function() {
                alert('パスワードがクリップボードにコピーされました。');
            }, function() {
                alert('パスワードのコピーに失敗しました。');
            });
        });
        $('#user_type').change(function() {
            var userTypeId = $(this).val();
            if (userTypeId == 1) { // システム管理者の場合
                    $('#company-group').hide();
                    $('#company_id').prop('required', false);
                    $('#new-company-link').hide();
                } else {
                    $('#company-group').show();
                    $('#company_id').prop('required', true);
                    $.ajax({
                        url: '{{ route("users.getCompaniesByType") }}',
                        method: 'GET',
                        data: { user_type_id: userTypeId },
                        success: function(data) {
                            var options = '<option value="">選択してください</option>';
                            $.each(data, function(index, company) {
                                options += '<option value="' + company.id + '">' + company.name + '</option>';
                            });
                            $('#company_id').html(options);
                            updateNewCompanyLink(userTypeId);
                        }
                    });
                }
        });

        function updateNewCompanyLink(userTypeId) {
                var link = $('#new-company-link');
                switch(userTypeId) {
                    case '2': // customer
                        link.attr('href', '{{ route("customer_companies.create") }}');
                        link.show();
                        break;
                    case '3': // saler
                        link.attr('href', '{{ route("saler_companies.create") }}');
                        link.show();
                        break;
                    case '4': // worker
                        link.attr('href', '{{ route("construction_companies.create") }}');
                        link.show();
                        break;
                    default:
                        link.hide();
                }
            }

    });
</script>@stop