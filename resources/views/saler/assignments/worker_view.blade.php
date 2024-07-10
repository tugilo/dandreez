@extends('adminlte::page')

@section('title', '職人別アサイン状況')

@section('content_header')
    <h1>職人別アサイン状況</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <!-- 前月へのリンク -->
                <a href="{{ route('saler.assignments.workers', ['month' => Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left"></i> 前月
                </a>
            </div>
            <!-- 月選択フォーム -->
            <form action="{{ route('saler.assignments.sites') }}" method="GET" class="form-inline">
                @csrf
                <div class="form-group mx-2">
                    <div class="input-group date" id="monthpicker" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input" data-target="#monthpicker" name="month" value="{{ $month }}" />
                        <div class="input-group-append" data-target="#monthpicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">表示</button>
            </form>
            <div>
                <!-- 翌月へのリンク -->
                <a href="{{ route('saler.assignments.workers', ['month' => Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary">
                    翌月 <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
<script>
$(function () {
    $('#monthpicker').datetimepicker({
        format: 'YYYY-MM',
        viewMode: 'months',
        ignoreReadonly: true,
        allowInputToggle: true
    });

    // 月が変更されたら自動的にフォームをサブミット
    $("#monthpicker").on("change.datetimepicker", function (e) {
        $(this).closest('form').submit();
    });

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