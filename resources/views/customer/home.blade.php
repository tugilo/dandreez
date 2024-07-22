@extends('adminlte::page')

@section('title', '得意先ダッシュボード')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css"/>
<style>
    #calendar {
        max-width: 1100px;
        margin: 0 auto;
    }
    .dashboard-card {
        height: 100%;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        margin-bottom: 1rem;
    }
    .dashboard-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
        font-weight: bold;
    }
    .quick-action-btn {
        margin-bottom: 10px;
    }
    .count-badge {
        font-size: 2rem;
        font-weight: bold;
    }
    .notice-item {
        border-left: 4px solid #007bff;
        margin-bottom: 10px;
        padding: 10px;
        background-color: #f8f9fa;
    }
    .notice-item.warning {
        border-left-color: #ffc107;
    }
    .upcoming-workplace {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    .upcoming-workplace:last-child {
        border-bottom: none;
    }
    .summary-card {
        text-align: center;
        padding: 20px;
    }
    .summary-card .icon {
        font-size: 3rem;
        margin-bottom: 10px;
    }
    .scrollable-card-body {
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@stop

@section('content_header')
    <h1>ダッシュボード</h1>
@stop

@section('content')
<div class="row">
    <!-- サマリー情報 -->
    <div class="col-md-3">
        <div class="card dashboard-card summary-card">
            <div class="icon text-primary">
                <i class="fas fa-hard-hat"></i>
            </div>
            <h3>進行中の施工</h3>
            <div class="count-badge">{{ $ongoingWorkplacesCount }}</div>
            <p>件</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card summary-card">
            <div class="icon text-success">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3>今後の予定</h3>
            <div class="count-badge">{{ $upcomingWorkplaces->count() }}</div>
            <p>件</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card summary-card">
            <div class="icon text-warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>未承認の依頼</h3>
            <div class="count-badge">{{ $pendingWorkplacesCount }}</div>
            <p>件</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-header">
                <h3 class="card-title">クイックアクション</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('customer.workplaces.create', ['role' => 'customer']) }}" class="btn btn-primary btn-block quick-action-btn">
                    <i class="fas fa-plus"></i> 新規施工依頼
                </a>
                <a href="{{ route('customer.workplaces.index', ['role' => 'customer']) }}" class="btn btn-secondary btn-block quick-action-btn">
                    <i class="fas fa-list"></i> 施工依頼一覧
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <!-- カレンダー -->
        <div class="card dashboard-card">
            <div class="card-header">
                <h3 class="card-title">施工予定カレンダー</h3>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- 今後の施工予定 -->
        <div class="card dashboard-card">
            <div class="card-header">
                <h3 class="card-title">今後の施工予定</h3>
            </div>
            <div class="card-body p-0 scrollable-card-body">
                @forelse($upcomingWorkplaces as $workplace)
                    <div class="upcoming-workplace">
                        <h5>{{ $workplace->name }}</h5>
                        <p class="mb-0">
                            <i class="fas fa-calendar-alt"></i> {{ $workplace->construction_start->format('Y/m/d') }} - {{ $workplace->construction_end->format('Y/m/d') }}
                        </p>
                    </div>
                @empty
                    <p class="p-3">予定されている施工はありません。</p>
                @endforelse
            </div>
        </div>

        <!-- 重要なお知らせやアラート -->
        <div class="card dashboard-card mt-4">
            <div class="card-header">
                <h3 class="card-title">重要なお知らせ</h3>
            </div>
            <div class="card-body scrollable-card-body">
                @forelse($importantNotices->sortByDesc('date') as $notice)
                    <div class="notice-item {{ isset($notice['count']) && $notice['count'] > 0 ? 'warning' : '' }}">
                        <h5>{{ $notice['title'] }}</h5>
                        @if(isset($notice['count']))
                            <span class="badge badge-warning">{{ $notice['count'] }} 件</span>
                        @endif
                        @if(isset($notice['date']))
                            <small class="text-muted"><i class="fas fa-clock"></i> {{ $notice['date'] }}</small>
                        @endif
                    </div>
                @empty
                    <p>現在、重要なお知らせはありません。</p>
                @endforelse
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