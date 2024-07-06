@extends('adminlte::page')

@section('title', '施工者アサイン')

@section('content_header')
    <h1>施工者アサイン</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('workplaces.assign', $workplace->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="worker_id">施工者 <span class="badge bg-danger">必須</span></label>
                    <select id="worker_id" class="form-control" name="worker_id" required>
                        <option value="">選択してください</option>
                        @foreach ($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">アサイン</button>
            </form>
        </div>
    </div>
@stop
