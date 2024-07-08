@extends('adminlte::page')

@section('title', '新規得意先会社登録')

@section('content_header')
    <h1>新規得意先会社登録</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('customer_companies.store') }}" method="POST">
            @csrf
            <!-- 会社名入力フィールド -->
            <div class="form-group">
                <label for="name">会社名</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="会社名を入力" value="{{ old('name') }}" required>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 会社名カナ入力フィールド -->
            <div class="form-group">
                <label for="name_kana">会社名カナ</label>
                <input type="text" name="name_kana" id="name_kana" class="form-control @error('name_kana') is-invalid @enderror" placeholder="会社名カナを入力" value="{{ old('name_kana') }}">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 電話番号入力フィールド -->
            <div class="form-group">
                <label for="tel">電話番号</label>
                <input type="text" name="tel" id="tel" class="form-control @error('tel') is-invalid @enderror" placeholder="電話番号を入力" value="{{ old('tel') }}">
                @error('tel')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- メールアドレス入力フィールド -->
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="メールアドレスを入力" value="{{ old('email') }}">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 郵便番号入力フィールド -->
            <div class="form-group">
                <label for="zip">郵便番号</label>
                <input type="text" name="zip" id="zip" class="form-control @error('zip') is-invalid @enderror" placeholder="郵便番号を入力" value="{{ old('zip') }}">
                @error('tel')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 都道府県選択フィールド -->
            <div class="form-group">
                <label for="prefecture">都道府県</label>
                <select name="prefecture" id="prefecture" class="form-control @error('prefecture') is-invalid @enderror">
                    <option value="">選択してください</option>
                    @foreach ($prefectures as $prefecture)
                        <option value="{{ $prefecture->prefecture }}" {{ old('prefecture') == $prefecture->prefecture ? 'selected' : '' }}>
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

            <!-- 市区町村入力フィールド -->
            <div class="form-group">
                <label for="city">市区町村</label>
                <input type="city" name="city" id="city" class="form-control @error('city') is-invalid @enderror" placeholder="市区町村を入力" value="{{ old('city') }}">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- その他の住所入力フィールド -->
            <div class="form-group">
                <label for="city">その他の住所</label>
                <input type="address" name="address" id="address" class="form-control @error('address') is-invalid @enderror" placeholder="その他の住所を入力" value="{{ old('address') }}">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 建物名入力フィールド -->
            <div class="form-group">
                <label for="city">建物名</label>
                <input type="building" name="building" id="building" class="form-control @error('building') is-invalid @enderror" placeholder="建物名を入力" value="{{ old('building') }}">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 登録ボタン -->
            <div class="form-group d-flex justify-content-center">
                <button type="submit" class="btn btn-success mr-3">登録</button>
                <a class="btn btn-secondary" href="{{ route('saler_companies.index') }}">キャンセル</a>
            </div>
        </form>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function() {
    $('#zip').on('keyup', function() {
        // ハイフンを含んで入力された郵便番号からハイフンを削除
        var zip = $(this).val().replace(/-/g, '').replace(/[^0-9]/g, '');

        // ハイフンを削除した郵便番号をフィールドに再設定
        $(this).val(zip);

        if (zip.length === 7) {
            // 郵便番号が正しく7桁入力されたときのみ住所情報を取得
            $.ajax({
                url: "https://zipcloud.ibsnet.co.jp/api/search",
                dataType: "jsonp",
                data: { zipcode: zip },
                success: function(data) {
                    if (data.results) {
                        // 取得成功した場合、各フィールドにデータを設定
                        $('#prefecture').val(data.results[0].address1);
                        $('#city').val(data.results[0].address2);
                        $('#address').val(data.results[0].address3);
                    } else {
                        // 該当する住所情報が見つからない場合、フィールドをクリアして警告
                        clearAddressFields();
                        alert('該当する住所情報が見つかりませんでした。');
                    }
                },
                beforeSend: function() {
                    // API呼び出し前に住所フィールドをクリア
                    clearAddressFields();
                }
            });
        } else {
            // 郵便番号が7桁未満の場合も住所フィールドをクリア
            clearAddressFields();
        }
    });

    function clearAddressFields() {
        // 都道府県、市区町村、住所のフィールドをクリアする
        $('#prefecture').val('');
        $('#city').val('');
        $('#address').val('');
    }
});
</script>
@stop
