@extends('adminlte::page')

@section('title', '現場別アサイン状況')

@section('content_header')
    <h1>現場別アサイン状況</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <!-- 前月へのリンク -->
                <a href="{{ route('saler.assignments.sites', ['month' => Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary">
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
                <a href="{{ route('saler.assignments.sites', ['month' => Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary">
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
                        <th style="width: 200px;">現場名</th>
                        @foreach($calendar as $day)
                            <th>{{ $day['day'] }}<br>{{ $day['dayOfWeek'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($workplaces as $workplace)
                        <tr>
                            <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $workplace->name }}
                            </td>
                            @foreach($calendar as $day)
                                @php
                                    $date = Carbon\Carbon::parse($day['date']);
                                    $isWithinConstruction = $date->between($workplace->construction_start, $workplace->construction_end);
                                    $assigns = $workplace->assigns->filter(function($assign) use ($day) {
                                        return $assign->start_date->format('Y-m-d') === $day['date'];
                                    });
                                    $workerNames = $assigns->pluck('worker.name')->implode(' | ');
                                    $tooltipContent = $isWithinConstruction
                                        ? "<strong>施工期間:</strong> {$workplace->construction_start->format('Y/m/d')} 〜 {$workplace->construction_end->format('Y/m/d')}<br>" .
                                          ($workerNames ? "<strong>職人:</strong> [{$workerNames}]" : "")
                                        : '';
                                @endphp
                                <td class="{{ $isWithinConstruction ? ($assigns->isNotEmpty() ? 'bg-success' : 'bg-warning') : '' }}"
                                    data-toggle="tooltip"
                                    data-html="true"
                                    title="{{ $tooltipContent }}"
                                    @if($isWithinConstruction && $assigns->isEmpty())
                                        onclick="openAssignModal('{{ $workplace->id }}', '{{ $day['date'] }}')"
                                    @endif
                                    style="cursor: {{ $isWithinConstruction && $assigns->isEmpty() ? 'pointer' : 'default' }}">
                                    @if($assigns->isNotEmpty())
                                        <a href="{{ route('saler.workplaces.details', ['role' => 'saler', 'id' => $workplace->id]) }}"
                                           class="text-white">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @elseif($isWithinConstruction)
                                        <i class="fas fa-exclamation-triangle text-danger"></i>
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

<!-- アサインモーダル -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">アサイン追加</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignForm">
                    @csrf
                    <input type="hidden" id="workplaceId" name="workplace_id">
                    <input type="hidden" id="assignDate" name="assign_date">
                    <div class="form-group">
                        <label for="workerId">職人</label>
                        <select class="form-control" id="workerId" name="worker_id" required>
                            <!-- 職人のオプションはJavaScriptで動的に追加 -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="startTime">開始時間</label>
                        <input type="time" class="form-control" id="startTime" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="endTime">終了時間</label>
                        <input type="time" class="form-control" id="endTime" name="end_time" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="submitAssign()">アサイン</button>
            </div>
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
    /* 現場名のセルのスタイル */
    .table td:first-child {
        text-align: left;
        padding-left: 0.75rem;
    }
    /* 工事期間中だがアサインがない日のスタイル */
    .bg-warning {
        background-color: #ffeeba !important;
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
    
    // ツールチップの初期化
    $('[data-toggle="tooltip"]').tooltip({
        boundary: 'window'
    });

    // デバッグ情報のログ出力
    console.log('現在の月:', '{{ $month }}');
    console.log('現場数:', {{ $workplaces->count() }});
    console.log('カレンダー日数:', {{ count($calendar) }});
    
    @foreach($workplaces as $workplace)
        console.log('現場ID {{ $workplace->id }} のアサイン数:', {{ $workplace->assigns->count() }});
    @endforeach

    // 月選択時に自動でフォームをサブミット
    $('#month').on('change', function() {
        $(this).closest('form').submit();
    });
});

function openAssignModal(workplaceId, date) {
    $('#workplaceId').val(workplaceId);
    $('#assignDate').val(date);
    
    // 職人リストを取得し、セレクトボックスに追加
    $.get(`/api/workers-for-workplace/${workplaceId}`, function(workers) {
        let options = '';
        workers.forEach(worker => {
            options += `<option value="${worker.id}">${worker.name}</option>`;
        });
        $('#workerId').html(options);
    });

    $('#assignModal').modal('show');
}

function submitAssign() {
    $.ajax({
        url: '/api/assign-from-calendar',
        method: 'POST',
        data: $('#assignForm').serialize(),
        success: function(response) {
            if(response.success) {
                alert('アサインが成功しました');
                location.reload();
            } else {
                alert('アサインに失敗しました: ' + (response.message || '不明なエラー'));
            }
        },
        error: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errorMessages = Object.values(xhr.responseJSON.errors).flat().join("\n");
                alert('バリデーションエラー:\n' + errorMessages);
            } else {
                alert('エラーが発生しました');
            }
        }
    });
}
</script>
@stop