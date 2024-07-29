@extends('adminlte::page')

@section('title', '問屋ダッシュボード')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css"/>
<style>
    #calendar { max-width: 1100px; margin: 0 auto; }
    .dashboard-card { height: 100%; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); margin-bottom: 1rem; }
    .dashboard-card .card-header { border-bottom: 1px solid rgba(0,0,0,.125); font-weight: bold; }
    .quick-action-btn { margin-bottom: 10px; }
    .count-badge { font-size: 2rem; font-weight: bold; }
    .scrollable-card-body { max-height: 300px; overflow-y: auto; }
</style>
@stop

@section('content_header')
    <h1>問屋ダッシュボード</h1>
@stop

@section('content')
<div class="row">
    <!-- 概要情報 -->
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-header">進行中の施工</div>
            <div class="card-body text-center">
                <div class="count-badge">{{ $ongoingWorkplacesCount }}</div>
                <p>件</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-header">新規施工依頼</div>
            <div class="card-body text-center">
                <div class="count-badge">{{ $newWorkplacesCount }}</div>
                <p>件</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-header">今週の施工予定</div>
            <div class="card-body text-center">
                <div class="count-badge">{{ $upcomingWorkplacesCount }}</div>
                <p>件</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-header">アサイン待ち</div>
            <div class="card-body text-center">
                <div class="count-badge">{{ $unassignedWorkplacesCount }}</div>
                <p>件</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- クイックアクション -->
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-header">クイックアクション</div>
            <div class="card-body">
                <a href="{{ route('saler.workplaces.create', ['role' => 'saler']) }}" class="btn btn-primary btn-block quick-action-btn">新規施工依頼登録</a>
                <a href="{{ route('saler.workplaces.index', ['role' => 'saler']) }}" class="btn btn-secondary btn-block quick-action-btn">施工依頼一覧</a>
                <a href="#" class="btn btn-info btn-block quick-action-btn">職人アサイン管理</a>
            </div>
        </div>
    </div>
    
    <!-- 未対応タスク -->
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-header">未対応タスク</div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($pendingTasks as $task => $count)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $task }}
                            <span class="badge badge-primary badge-pill">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    
    <!-- 最新の施工依頼 -->
    <div class="col-md-6">
        <div class="card dashboard-card">
            <div class="card-header">最新の施工依頼</div>
            <div class="card-body scrollable-card-body">
                <ul class="list-group">
                    @foreach($latestWorkplaces as $workplace)
                        <li class="list-group-item">
                            <h5>{{ $workplace->name }}</h5>
                            <p>得意先: {{ $workplace->customer->name ?? '未設定' }}</p>
                            <p>期間: 
                                @if($workplace->construction_start && $workplace->construction_end)
                                    {{ $workplace->construction_start->format('Y/m/d') }} - {{ $workplace->construction_end->format('Y/m/d') }}
                                @else
                                    未設定
                                @endif
                            </p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div></div>

<div class="row mt-4">
    <!-- カレンダー -->
    <div class="col-md-8">
        <div class="card dashboard-card">
            <div class="card-header">施工予定カレンダー</div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    
    <!-- 職人稼働状況 -->
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-header">職人稼働状況</div>
            <div class="card-body scrollable-card-body">
                <ul class="list-group">
                    @foreach($workerStatus as $worker)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $worker['name'] }}
                            <span class="badge {{ $worker['status'] === '稼働中' ? 'badge-success' : 'badge-secondary' }} badge-pill">{{ $worker['status'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/ja.js"></script>
<script>
$(document).ready(function() {
    $('#calendar').fullCalendar({
        locale: 'ja',
        defaultView: 'month',
        editable: false,
        eventLimit: true,
        events: @json($events),
        eventClick: function(event) {
            if (event.url) {
                window.location.href = event.url;
                return false;
            }
        },
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        }
    });
});
</script>
@stop