@extends('adminlte::page')

@section('title', '問屋会社編集')

@section('content_header')
    <h1>
        <i class="fas fa-building"></i> 問屋会社編集
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">問屋会社情報入力</h3>
            </div>
            <form action="{{ route('saler_companies.update', $salerCompany->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">会社名 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $salerCompany->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name_kana">会社名カナ</label>
                        <input type="text" name="name_kana" id="name_kana" class="form-control @error('name_kana') is-invalid @enderror" value="{{ old('name_kana', $salerCompany->name_kana) }}">
                        @error('name_kana')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tel">電話番号</label>
                        <input type="tel" name="tel" id="tel" class="form-control @error('tel') is-invalid @enderror" value="{{ old('tel', $salerCompany->tel) }}">
                        @error('tel')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">メールアドレス</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $salerCompany->email) }}">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="zip">郵便番号</label>
                        <div class="input-group">
                            <input type="text" name="zip" id="zip" class="form-control @error('zip') is-invalid @enderror" value="{{ old('zip', $salerCompany->zip) }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="search-address">
                                    <i class="fas fa-search"></i> 住所検索
                                </button>
                            </div>
                        </div>
                        @error('zip')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="prefecture">都道府県</label>
                        <select name="prefecture" id="prefecture" class="form-control @error('prefecture') is-invalid @enderror">
                            <option value="">選択してください</option>
                            @foreach ($prefectures as $prefecture)
                                <option value="{{ $prefecture->prefecture }}" {{ (old('prefecture', $salerCompany->prefecture) == $prefecture->prefecture) ? 'selected' : '' }}>
                                    {{ $prefecture->prefecture }}
                                </option>
                            @endforeach
                        </select>
                        @error('prefecture')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="city">市区町村</label>
                        <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $salerCompany->city) }}">
                        @error('city')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">その他の住所</label>
                        <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $salerCompany->address) }}">
                        @error('address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="building">建物名</label>
                        <input type="text" name="building" id="building" class="form-control @error('building') is-invalid @enderror" value="{{ old('building', $salerCompany->building) }}">
                        @error('building')
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
                    <a href="{{ route('saler_companies.index') }}" class="btn btn-secondary">
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
    <script src="{{ asset('js/address-search.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
            });

            // 住所検索機能の初期化
            initAddressSearch('#zip', '#search-address', '#prefecture', '#city', '#address');
        });
    </script>
@stop