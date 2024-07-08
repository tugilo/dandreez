@extends('adminlte::page')

@section('title', '新規通知内容作成')

@section('content_header')
    <h1>新規通知内容作成</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('notification_contents.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="code">コード</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" required>
                </div>
                <div class="form-group">
                    <label for="message">メッセージ</label>
                    <textarea name="message" id="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
    </div>
@stop
