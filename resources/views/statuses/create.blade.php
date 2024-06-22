@extends('adminlte::page')

@section('title', '新規ステータス作成')

@section('content_header')
    <h1>新規ステータス作成</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('statuses.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">名前 <span class="badge bg-danger">必須</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label for="description">説明</label>
                <input type="text" name="description" id="description" class="form-control" value="{{ old('description') }}">
            </div>
            <div class="form-group">
                <label for="name_ja">日本語表記 <span class="badge bg-danger">必須</span></label>
                <input type="text" name="name_ja" id="name_ja" class="form-control" value="{{ old('name_ja') }}" required>
            </div>
            <div class="form-group">
                <label for="sort_order">表示順 <span class="badge bg-danger">必須</span></label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" required>
            </div>
            <div class="form-group">
                <label for="show_flg">表示フラグ <span class="badge bg-danger">必須</span></label>
                <select name="show_flg" id="show_flg" class="form-control" required>
                    <option value="1" {{ old('show_flg', 1) == 1 ? 'selected' : '' }}>表示</option>
                    <option value="0" {{ old('show_flg', 1) == 0 ? 'selected' : '' }}>非表示</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">作成</button>
        </form>
    </div>
</div>
@stop
