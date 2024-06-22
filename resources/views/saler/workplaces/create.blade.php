@extends('adminlte::page')

@section('title', '施工依頼作成')

@section('content_header')
    <h1>施工依頼作成</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('saler.workplaces.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">施工依頼名</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <!-- その他のフィールドを追加 -->
                <button type="submit" class="btn btn-primary">作成</button>
            </form>
        </div>
    </div>
@stop
