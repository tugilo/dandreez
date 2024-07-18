@extends('adminlte::page')

@section('title', '新規ステータス作成')

@section('content_header')
    <h1>
        <i class="fas fa-plus"></i> 新規ステータス作成
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">ステータス情報入力</h3>
            </div>
            <form action="{{ route('statuses.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">名前 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">説明</label>
                        <input type="text" name="description" id="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}">
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name_ja">日本語表記 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="name_ja" id="name_ja" class="form-control @error('name_ja') is-invalid @enderror" value="{{ old('name_ja') }}" required>
                        @error('name_ja')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="color">色 <span class="badge bg-danger">必須</span></label>
                        <select name="color" id="color" class="form-control @error('color') is-invalid @enderror" required>
                            <option value="badge-primary" {{ old('color') == 'badge-primary' ? 'selected' : '' }}>Primary</option>
                            <option value="badge-secondary" {{ old('color') == 'badge-secondary' ? 'selected' : '' }}>Secondary</option>
                            <option value="badge-success" {{ old('color') == 'badge-success' ? 'selected' : '' }}>Success</option>
                            <option value="badge-danger" {{ old('color') == 'badge-danger' ? 'selected' : '' }}>Danger</option>
                            <option value="badge-warning" {{ old('color') == 'badge-warning' ? 'selected' : '' }}>Warning</option>
                            <option value="badge-info" {{ old('color') == 'badge-info' ? 'selected' : '' }}>Info</option>
                            <option value="badge-light" {{ old('color') == 'badge-light' ? 'selected' : '' }}>Light</option>
                            <option value="badge-dark" {{ old('color') == 'badge-dark' ? 'selected' : '' }}>Dark</option>
                        </select>
                        @error('color')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="sort_order">表示順 <span class="badge bg-danger">必須</span></label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" required>
                        @error('sort_order')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="show_flg">表示フラグ <span class="badge bg-danger">必須</span></label>
                        <select name="show_flg" id="show_flg" class="form-control @error('show_flg') is-invalid @enderror" required>
                            <option value="1" {{ old('show_flg', 1) == 1 ? 'selected' : '' }}>表示</option>
                            <option value="0" {{ old('show_flg', 1) == 0 ? 'selected' : '' }}>非表示</option>
                        </select>
                        @error('show_flg')
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
                    <a href="{{ route('statuses.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop