@extends('adminlte::page')

@section('title', '施工依頼の編集')

@section('content_header')
    <h1>施工依頼の編集</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route($updateRoute, ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- 施工名 -->
                <div class="form-group">
                    <label for="name">施工名 <span class="badge bg-danger">必須</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $workplace->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- 問屋 -->
                <div class="form-group">
                    <label for="saler_id">問屋 <span class="badge bg-danger">必須</span></label>
                    <select id="saler_id" class="form-control @error('saler_id') is-invalid @enderror" name="saler_id" required>
                        @foreach ($salers as $saler)
                            <option value="{{ $saler->id }}" {{ old('saler_id', $workplace->saler_id) == $saler->id ? 'selected' : '' }}>{{ $saler->name }}</option>
                        @endforeach
                    </select>
                    @error('saler_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- 施工開始日 -->
                <div class="form-group">
                    <label for="construction_start">施工開始日</label>
                    <input type="text" name="construction_start" id="construction_start" class="form-control datepicker @error('construction_start') is-invalid @enderror" value="{{ old('construction_start', $workplace->construction_start) }}">
                    @error('construction_start')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- 施工終了日 -->
                <div class="form-group">
                    <label for="construction_end">施工終了日</label>
                    <input type="text" name="construction_end" id="construction_end" class="form-control datepicker @error('construction_end') is-invalid @enderror" value="{{ old('construction_end', $workplace->construction_end) }}">
                    @error('construction_end')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- 延べ床面積 -->
                <div class="form-group">
                    <label for="floor_space">延べ床面積</label>
                    <input type="text" name="floor_space" id="floor_space" class="form-control @error('floor_space') is-invalid @enderror" value="{{ old('floor_space', $workplace->floor_space) }}">
                    @error('floor_space')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- 施工概要 -->
                <div class="form-group">
                    <label for="construction_outline">施工概要 <span class="badge bg-danger">必須</span></label>
                    <textarea name="construction_outline" id="construction_outline" class="form-control @error('construction_outline') is-invalid @enderror" rows="5">{{ old('construction_outline', $workplace->construction_outline) }}</textarea>
                    @error('construction_outline')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- メモ -->
                <div class="form-group">
                    <label for="memo">メモ</label>
                    <textarea name="memo" id="memo" class="form-control @error('memo') is-invalid @enderror" rows="3">{{ old('memo', $workplace->memo) }}</textarea>
                    @error('memo')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">更新</button>
            </form>
        </div>
    </div>
@stop


@section('css')
    <!-- Bootstrap Datepicker CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
@stop

@section('js')
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
    <script>
        $(function() {
            // Datepickerの初期化
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                language: 'ja',
                todayHighlight: true
            });
        });
    </script>
@stop
