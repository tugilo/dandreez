@extends('adminlte::page')

@section('title', '新規施工依頼')

@section('content_header')
    <h1>
        <i class="fas fa-plus-circle"></i> 新規施工依頼
    </h1>
@stop

@section('content')
<!--select2を利用バージョン-->
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">施工依頼情報入力</h3>
            </div>
            <form action="{{ route($storeRoute) }}" method="POST">
                @csrf
                <div class="card-body">
                    @if ($role === 'saler')
                        <div class="form-group">
                            <label for="customer_id">得意先 <span class="badge bg-danger">必須</span></label>
                            <select id="customer_id" class="form-control select2 @error('customer_id') is-invalid @enderror" name="customer_id" required>
                                <option value="">選択してください</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @else
                        <input type="hidden" name="customer_id" value="{{ Auth::user()->customerStaff->customer_id }}">
                    @endif

                    @if ($role === 'customer')
                        <div class="form-group">
                            <label for="saler_id">問屋 <span class="badge bg-danger">必須</span></label>
                            <select id="saler_id" class="form-control select2 @error('saler_id') is-invalid @enderror" name="saler_id" required>
                                <option value="">選択してください</option>
                                @foreach ($salers as $saler)
                                    <option value="{{ $saler->id }}" {{ old('saler_id') == $saler->id ? 'selected' : '' }}>{{ $saler->name }}</option>
                                @endforeach
                            </select>
                            @error('saler_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @else
                        <input type="hidden" name="saler_id" value="{{ Auth::user()->salerStaff->saler_id }}">
                    @endif

                    <div class="form-group">
                        <label for="name">施工名 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="construction_period">施工期間 <span class="badge bg-danger">必須</span></label>
                        <div class="input-group">
                            <input type="text" name="construction_period" id="construction_period" class="form-control @error('construction_period') is-invalid @enderror" value="{{ old('construction_period') }}" required readonly>
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            </div>
                        </div>
                        @error('construction_period')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- 隠しフィールドを追加 -->
                    <input type="hidden" name="construction_start" id="construction_start" value="{{ old('construction_start') }}">
                    <input type="hidden" name="construction_end" id="construction_end" value="{{ old('construction_end') }}">
                    
                    <div class="form-group">
                        <label for="floor_space">延べ壁面積</label>
                        <div class="input-group">
                            <input type="text" name="floor_space" id="floor_space" class="form-control @error('floor_space') is-invalid @enderror" value="{{ old('floor_space') }}">
                            <div class="input-group-append">
                                <span class="input-group-text">㎡</span>
                            </div>
                        </div>
                        @error('floor_space')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="zip">郵便番号</label>
                        <div class="input-group">
                            <input type="text" name="zip" id="zip" class="form-control @error('zip') is-invalid @enderror" value="{{ old('zip') }}">
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
                        <select name="prefecture" id="prefecture" class="form-control select2 @error('prefecture') is-invalid @enderror">
                            <option value="">選択してください</option>
                            @foreach($prefectures as $pref)
                                <option value="{{ $pref->prefecture }}" {{ old('prefecture') == $pref->prefecture ? 'selected' : '' }}>
                                    {{ $pref->prefecture }}
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
                        <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}">
                        @error('city')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">番地</label>
                        <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}">
                        @error('address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="building">建物名・部屋番号</label>
                        <input type="text" name="building" id="building" class="form-control @error('building') is-invalid @enderror" value="{{ old('building') }}">
                        @error('building')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="construction_outline">概要 <span class="badge bg-danger">必須</span></label>
                        <textarea name="construction_outline" id="construction_outline" class="form-control @error('construction_outline') is-invalid @enderror" rows="5" required>{{ old('construction_outline') }}</textarea>
                        @error('construction_outline')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="memo">メモ</label>
                        <textarea name="memo" id="memo" class="form-control @error('memo') is-invalid @enderror" rows="3">{{ old('memo') }}</textarea>
                        @error('memo')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 登録
                    </button>
                    <a href="{{ route($indexRoute, ['role' => $role]) }}" class="btn btn-secondary">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .datepicker {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
        }
        .datepicker table tr td.today {
            background-color: #FFF0C8;
            border-color: #FFB733;
        }
        .datepicker table tr td.active {
            background-color: #007bff;
            border-color: #007bff;
        }
        .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px); /* Bootstrap4標準の高さ */
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }        
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ asset('js/address-search.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Select2の初期化
            $('.select2').select2({
                placeholder: "選択してください", // プレースホルダー
                allowClear: true // 「クリア」ボタンを追加
                width: '100%' // 幅を100%に設定
            });
            // 施工期間のDateRangePicker初期化
            $('#construction_period').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' ~ ',
                    applyLabel: '適用',
                    cancelLabel: 'キャンセル',
                    fromLabel: '開始日',
                    toLabel: '終了日',
                    customRangeLabel: 'カスタム',
                    weekLabel: 'W',
                    daysOfWeek: ['日', '月', '火', '水', '木', '金', '土'],
                    monthNames: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
                    firstDay: 1
                },
                showDropdowns: true,
                minYear: parseInt(moment().format('YYYY'),10),
                maxYear: parseInt(moment().add(10, 'year').format('YYYY'),10),
                opens: 'center',
                drops: 'auto'
            }, function(start, end, label) {
                // 日付が選択されたときに隠しフィールドを更新
                $('#construction_start').val(start.format('YYYY-MM-DD'));
                $('#construction_end').val(end.format('YYYY-MM-DD'));
            });

            // カレンダーアイコンクリックでDateRangePickerを表示
            $('.input-group-text').click(function() {
                $('#construction_period').data('daterangepicker').toggle();
            });

            // フォーム送信時に日付が選択されているか確認
            $('form').submit(function(e) {
                var startDate = $('#construction_start').val();
                var endDate = $('#construction_end').val();
                if (!startDate || !endDate) {
                    e.preventDefault();
                    alert('施工期間を選択してください。');
                }
            });

            // 住所検索機能の初期化
            initAddressSearch('#zip', '#search-address', '#prefecture', '#city', '#address');
        });
    </script>
@stop