@extends('adminlte::page')

@section('title', '日報作成')

@section('content_header')
    <h1>日報作成</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('worker.report.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="assign_id">現場</label>
                    <select name="assign_id" id="assign_id" class="form-control" required>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}">
                                {{ $assignment->workplace->name }} 
                                ({{ $assignment->start_date->format('Y-m-d') }} - {{ $assignment->end_date->format('Y-m-d') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="report_day">報告日</label>
                    <input type="date" name="report_day" id="report_day" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label for="work_hours">作業時間（時間）</label>
                    <input type="number" name="work_hours" id="work_hours" class="form-control" required min="0" step="0.5">
                </div>
                <div class="form-group">
                    <label for="comment">作業内容</label>
                    <textarea name="comment" id="comment" class="form-control" required rows="4"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">日報を提出</button>
            </form>
        </div>
    </div>
@stop