@extends('adminlte::page')

@section('title', '職人別アサイン状況')

@section('content_header')
    <h1>職人別アサイン状況</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <!-- 月選択フォーム -->
        <form action="{{ route('saler.assignments.workers') }}" method="GET" class="form-inline">
            @csrf
            <div class="form-group mr-2">
                <label for="month" class="mr-2">月選択:</label>
                <input type="month" id="month" name="month" value="{{ $month }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">表示</button>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 150px;">職人名</th>
                        @foreach($calendar as $day)
                            <th>{{ $day['day'] }}<br>{{ $day['dayOfWeek'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($workers as $worker)
                        <tr>
                            <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $worker->name }}
                            </td>
                            @foreach($calendar as $day)
                                @php
                                    $assign = $worker->assigns->first(function($assign) use ($day) {
                                        return $assign->start_date->format('Y-m-d') === $day['date'];
                                    });
                                    $tooltipContent = $assign 
                                        ? "<strong>現場名:</strong> {$assign->workplace->name}<br>" .
                                          "<strong>得意先名:</strong> {$assign->workplace->customer->name}<br>" .
                                          "<strong>施工期間:</strong> {$assign->workplace->construction_start->format('Y/m/d')} 〜 {$assign->workplace->construction_end->format('Y/m/d')}"
                                        : '';
                                @endphp
                                <td class="{{ $assign ? 'bg-success' : '' }}"
                                    data-toggle="tooltip"
                                    data-html="true"
                                    title="{{ $tooltipContent }}">
                                    @if($assign)
                                        <a href="{{ route('saler.workplaces.details', ['role' => 'saler', 'id' => $assign->workplace_id]) }}"
                                           class="text-white">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* テーブルのスタイル調整 */
    .table th, .table td {
        text-align: center;
        vertical-align: middle;
        padding: 0.5rem;
    }
    /* 職人名のセルのスタイル */
    .table td:first-child {
        text-align: left;
        padding-left: 0.75rem;
    }
</style>
@stop

@section('js')
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip({
        boundary: 'window'
    });

    console.log('現在の月:', '{{ $month }}');
    console.log('職人数:', {{ $workers->count() }});
    console.log('カレンダー日数:', {{ count($calendar) }});
    
    @foreach($workers as $worker)
        console.log('職人ID {{ $worker->id }} のアサイン数:', {{ $worker->assigns->count() }});
    @endforeach

    $('#month').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@stop