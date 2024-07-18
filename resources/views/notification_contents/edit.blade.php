@extends('adminlte::page')

@section('title', '通知内容編集')

@section('content_header')
    <h1>
        <i class="fas fa-edit"></i> 通知内容編集
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">通知内容情報入力</h3>
            </div>
            <form action="{{ route('notification_contents.update', $notificationContent->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="code">コード <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $notificationContent->code) }}" required>
                        @error('code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="message">メッセージ <span class="badge bg-danger">必須</span></label>
                        <textarea name="message" id="message" class="form-control @error('message') is-invalid @enderror" rows="4" required>{{ old('message', $notificationContent->message) }}</textarea>
                        @error('message')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="show_flg">表示フラグ</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="show_flg" name="show_flg" value="1" {{ old('show_flg', $notificationContent->show_flg) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="show_flg">表示する</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 更新
                    </button>
                    <a href="{{ route('notification_contents.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop