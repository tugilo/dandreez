@extends('adminlte::page')

@section('title', '職人ホーム')

@section('content_header')
    <h1>ようこそ、{{ $worker->name }}さん</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="far fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">今日の予定</span>
                    @if($todayAssignment)
                        <span class="info-box-number">{{ $todayAssignment->workplace->name }}</span>
                        <span>{{ $todayAssignment->start_time->format('H:i') }} - {{ $todayAssignment->end_time->format('H:i') }}</span>
                    @else
                        <span class="info-box-number">予定なし</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="far fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">今週の作業時間</span>
                    <span class="info-box-number">{{ $recentWork->sum('work_hours') }} 時間</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="far fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">未提出の日報</span>
                    <span class="info-box-number">{{ $pendingReports->count() }} 件</span>
                </div>
            </div>
        </div>
        @if(count($notifications) > 0)
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="far fa-bell"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">未読のお知らせ</span>
                    <span class="info-box-number">{{ count($notifications) }} 件</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">今週の作業予定</h3>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">クイックアクション</h3>
                </div>
                <div class="card-body">
                    <button id="startWork" class="btn btn-success btn-block mb-3">作業開始</button>
                    <button id="endWork" class="btn btn-danger btn-block mb-3">作業終了</button>
                    <a href="{{ route('worker.report.create') }}" class="btn btn-primary btn-block">日報作成</a>
                </div>
            </div>
            @if($nextWorkplace)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">次の現場情報</h3>
                    </div>
                    <div class="card-body">
                        <h5>{{ $nextWorkplace->workplace->name }}</h5>
                        <p>{{ $nextWorkplace->workplace->address }}</p>
                        <p>日時: {{ $nextWorkplace->start_date->format('Y/m/d H:i') }} - {{ $nextWorkplace->end_date->format('H:i') }}</p>
                        @if($nextWorkplace->workplace->instructions->isNotEmpty())
                            <h6>作業内容:</h6>
                            <ul>
                                @foreach($nextWorkplace->workplace->instructions as $instruction)
                                    <li>{{ $instruction->construction_location }}: {{ $instruction->product_name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridWeek',
                locale: 'ja',
                events: [
                    @foreach($weeklyAssignments as $date => $assignments)
                        @foreach($assignments as $assignment)
                            {
                                title: '{{ $assignment->workplace->name }}',
                                start: '{{ $assignment->start_date->format('Y-m-d') }}T{{ $assignment->start_time ? $assignment->start_time->format('H:i:s') : '09:00:00' }}',
                                end: '{{ $assignment->start_date->format('Y-m-d') }}T{{ $assignment->end_time ? $assignment->end_time->format('H:i:s') : '17:00:00' }}',
                                url: '{{ route('worker.assignment.show', $assignment->id) }}'
                            },
                        @endforeach
                    @endforeach
                ]
            });
            calendar.render();
        });

        // 作業開始・終了ボタンのイベントリスナー
        document.getElementById('startWork').addEventListener('click', function() {
            // 作業開始処理のAjaxリクエスト
            alert('作業を開始しました。');  // 仮の実装
        });

        document.getElementById('endWork').addEventListener('click', function() {
            // 作業終了処理のAjaxリクエスト
            alert('作業を終了しました。');  // 仮の実装
        });
    </script>
@stop