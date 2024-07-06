@extends('adminlte::page')

@section('title', '通知内容編集')

@section('content_header')
    <h1>通知内容編集</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('notification_contents.update', $notificationContent->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="code">コード</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ $notificationContent->code }}" required>
                </div>
                <div class="form-group">
                    <label for="message">メッセージ</label>
                    <textarea name="message" id="message" class="form-control" rows="4" required>{{ $notificationContent->message }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">更新</button>
            </form>
        </div>
    </div>
@stop
