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
                    @if($todayAssignment && $todayAssignment->workplace && $todayAssignment->start_time && $todayAssignment->end_time)
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
                    <h3 class="card-title">スケジュール</h3>
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
    <style>
    .fc .fc-toolbar {
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
    }

    .fc .fc-toolbar-title {
        font-size: 1.2em;
        margin: 0;
    }

    .fc .fc-button {
        padding: 5px 10px;
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        .fc .fc-toolbar {
            flex-direction: column;
        }
        .fc .fc-toolbar-title {
            margin: 10px 0;
        }
        .fc .fc-button-group {
            margin-bottom: 10px;
        }
    }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: '今日',
                month: '月',
                week: '週',
                day: '日'
            },
            javascriptCopydatesSet: function(dateInfo) {
                const view = dateInfo.view;
                const start = dateInfo.start;
                const end = dateInfo.end;
                let title = '';

                if (view.type === 'dayGridMonth') {
                    title = `${start.getFullYear()}年${start.getMonth() + 1}月`;
                } else if (view.type === 'timeGridWeek') {
                    const endDate = new Date(end);
                    endDate.setDate(endDate.getDate() - 1);
                    title = `${start.getFullYear()}年${start.getMonth() + 1}月${start.getDate()}日 - ${endDate.getDate()}日`;
                } else {
                    title = `${start.getFullYear()}年${start.getMonth() + 1}月${start.getDate()}日`;
                }

                calendar.setOption('headerToolbar', {
                    left: 'prev,next today',
                    center: title,
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                });
            },
            initialView: 'timeGridWeek',
            locale: 'ja',
            height: 'auto',
            allDaySlot: false,
            slotDuration: '01:00:00',
            slotLabelInterval: '01:00:00',
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                omitZeroMinute: true,
                meridiem: 'short'
            },
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

        // 作業開始・終了ボタンのイベントリスナー
        document.getElementById('startWork').addEventListener('click', function() {
            fetch('/worker/start-work', { 
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('endWork').addEventListener('click', function() {
            fetch('/worker/end-work', { 
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.message.includes('日報の作成をお願いします')) {
                    window.location.href = '{{ route('worker.report.create') }}';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
@stop