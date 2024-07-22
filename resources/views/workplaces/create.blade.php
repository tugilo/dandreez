@extends('adminlte::page')

@section('title', '新規施工依頼')

@section('content_header')
    <h1>
        <i class="fas fa-plus-circle"></i> 新規施工依頼
    </h1>
@stop

@section('content')
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
                        <label for="construction_start">施工開始日</label>
                        <div class="input-group">
                            <input type="text" name="construction_start" id="construction_start" class="form-control datepicker @error('construction_start') is-invalid @enderror" value="{{ old('construction_start') }}" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            </div>
                        </div>
                        @error('construction_start')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="construction_end">施工終了日</label>
                        <div class="input-group">
                            <input type="text" name="construction_end" id="construction_end" class="form-control datepicker @error('construction_end') is-invalid @enderror" value="{{ old('construction_end') }}" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            </div>
                        </div>
                        @error('construction_end')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="floor_space">延べ床面積</label>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
    <script src="{{ asset('js/address-search.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
            });

            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                language: 'ja',
                todayHighlight: true,
                orientation: "bottom auto",
                templates: {
                    leftArrow: '<i class="fas fa-chevron-left"></i>',
                    rightArrow: '<i class="fas fa-chevron-right"></i>'
                }
            }).on('show', function(e) {
                $('.datepicker-dropdown').addClass('shadow-sm');
            });

            // カレンダーアイコンクリックでDatepickerを表示
            $('.input-group-text').click(function() {
                $(this).prev('.datepicker').datepicker('show');
            });

            // 住所検索機能の初期化
            initAddressSearch('#zip', '#search-address', '#prefecture', '#city', '#address');
        });
    </script>
@stop