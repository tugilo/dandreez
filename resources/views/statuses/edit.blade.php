@extends('adminlte::page')

@section('title', 'ステータス編集')

@section('content_header')
    <h1>ステータス編集</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('statuses.update', $status->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">名前 <span class="badge bg-danger">必須</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $status->name) }}" required>
            </div>
            <div class="form-group">
                <label for="description">説明</label>
                <input type="text" name="description" id="description" class="form-control" value="{{ old('description', $status->description) }}">
            </div>
            <div class="form-group">
                <label for="name_ja">日本語表記 <span class="badge bg-danger">必須</span></label>
                <input type="text" name="name_ja" id="name_ja" class="form-control" value="{{ old('name_ja', $status->name_ja) }}" required>
            </div>
            <div class="form-group">
                <label for="sort_order">表示順 <span class="badge bg-danger">必須</span></label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $status->sort_order) }}" required>
            </div>
            <div class="form-group">
                <label for="show_flg">表示フラグ <span class="badge bg-danger">必須</span></label>
                <select name="show_flg" id="show_flg" class="form-control" required>
                    <option value="1" {{ old('show_flg', $status->show_flg) == 1 ? 'selected' : '' }}>表示</option>
                    <option value="0" {{ old('show_flg', $status->show_flg) == 0 ? 'selected' : '' }}>非表示</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">更新</button>
        </form>
    </div>
</div>
@stop
