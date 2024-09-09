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
                <a href="{{ route('saler.assignments.workers', ['month' => Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left"></i> 前月
                </a>
            </div>
            <form action="{{ route('saler.assignments.workers') }}" method="GET" class="form-inline">
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
                                $assigns = $worker->assigns->filter(function($assign) use ($day) {
                                    return $assign->start_date->format('Y-m-d') === $day['date'];
                                });
                                $isAssigned = $assigns->isNotEmpty();
                                $isWorkday = $workplaces->contains(function ($workplace) use ($day) {
                                    return $day['date'] >= $workplace->construction_start->format('Y-m-d') &&
                                           $day['date'] <= $workplace->construction_end->format('Y-m-d');
                                });
                                $tooltipContent = '';
                                foreach ($assigns as $assign) {
                                    $tooltipContent .= "<strong>{$assign->workplace->name}</strong><br>" .
                                                       "時間: {$assign->start_time->format('H:i')} - {$assign->end_time->format('H:i')}<br>";
                                }
                            @endphp
                            <td class="{{ $isAssigned ? 'bg-success' : ($isWorkday ? 'bg-warning' : '') }}"
                                data-toggle="tooltip"
                                data-html="true"
                                title="{{ $tooltipContent }}"
                                style="cursor: pointer;"
                                onclick="openAssignModal('{{ $worker->id }}', '{{ $day['date'] }}')">
                                @if($isAssigned)
                                    <i class="fas fa-check text-white"></i>
                                @elseif($isWorkday)
                                    <i class="fas fa-exclamation-triangle text-white"></i>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalLabel">職人のアサイン</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4 p-3 bg-light rounded">
                    <h4 id="modalWorkerName" class="font-weight-bold text-primary mb-2"></h4>
                    <h5 id="modalAssignDate" class="text-secondary"></h5>
                </div>
                <form id="assignForm">
                    @csrf
                    <input type="hidden" id="worker_id" name="worker_id">
                    <input type="hidden" id="assign_date" name="assign_date">
                    <div class="form-group">
                        <label class="font-weight-bold">現場選択 <small class="text-muted">(1つ以上選択してください)</small></label>
                        <div id="workplaceCheckboxes" class="workplace-checkboxes">
                            <!-- チェックボックスはJavaScriptで動的に追加 -->
                        </div>
                    </div>
                    <div id="workplaceTimesContainer">
                        <!-- 現場ごとの時間設定欄はJavaScriptで動的に追加 -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="submitAssign()">保存</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table th, .table td {
        text-align: center;
        vertical-align: middle;
        padding: 0.5rem;
    }
    .table td:first-child {
        text-align: left;
        padding-left: 0.75rem;
    }
    .workplace-checkboxes {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ced4da;
        padding: 10px;
        border-radius: 4px;
    }
    .workplace-checkbox {
        margin-bottom: 5px;
    }
    .workplace-time-fields {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .time-input-group {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .time-input {
        width: 48%;
    }
    .time-input label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #495057;
    }
    .input-with-icon {
        position: relative;
    }
    .input-with-icon input {
        padding: 12px 30px 12px 12px;
        height: auto;
        font-size: 16px;
    }
    .input-with-icon i {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #007bff;
    }
    /* 職人別の現場選択フィールドのスタイル */
    .workplace-checkboxes {
        max-height: 300px; /* 最大高さを設定 */
        overflow-y: auto; /* 縦方向のスクロールを有効化 */
        border: 1px solid #ced4da;
        padding: 10px;
        border-radius: 4px;
    }

    /* 現場別アサイン状況のフィールドのスタイル */
    #workplaceTimesContainer {
        max-height: 400px; /* 最大高さを設定 */
        overflow-y: auto; /* 縦方向のスクロールを有効化 */
        padding-right: 10px; /* スクロールバーのためのパディング */
    }

    /* スクロールバーのスタイル（オプション） */
    .workplace-checkboxes::-webkit-scrollbar,
    #workplaceTimesContainer::-webkit-scrollbar {
        width: 8px;
    }

    .workplace-checkboxes::-webkit-scrollbar-thumb,
    #workplaceTimesContainer::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 4px;
    }

    .workplace-checkboxes::-webkit-scrollbar-track,
    #workplaceTimesContainer::-webkit-scrollbar-track {
        background-color: #f1f1f1;
    }
    .table td.bg-success, .table td.bg-warning {
        position: relative;
    }

    .table td.bg-success i, .table td.bg-warning i {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .table td.bg-warning {
        background-color: #ffc107 !important;
    }

    .table td.bg-warning i {
        color: #000;
    }

</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/ja.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
$(function () {
    // 月選択用のdatetimepickerの初期化
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

    // Flatpickrの日本語ロケールを設定
    flatpickr.localize(flatpickr.l10ns.ja);
});

// アサインモーダルを開く関数
function openAssignModal(workerId, date) {
    $('#worker_id').val(workerId);
    $('#assign_date').val(date);
    
    let worker = @json($workers->keyBy('id'));
    $('#modalWorkerName').text(worker[workerId].name);
    $('#modalAssignDate').text(moment(date).format('YYYY年M月D日(ddd)'));
    
    // 現場リストをクリア
    $('#workplaceCheckboxes').empty();
    $('#workplaceTimesContainer').empty();
    
    // 現場のチェックボックスを生成
    let workplaces = @json($workplaces);
    let selectedDate = moment(date);
    
    // 選択された日付が施工期間内にある現場のみをフィルタリング
    let relevantWorkplaces = workplaces.filter(function(workplace) {
        let constructionStart = moment(workplace.construction_start);
        let constructionEnd = moment(workplace.construction_end);
        return selectedDate.isBetween(constructionStart, constructionEnd, null, '[]');
    });
    
    // フィルタリングされた現場のチェックボックスを生成
    relevantWorkplaces.forEach(function(workplace) {
        $('#workplaceCheckboxes').append(`
            <div class="workplace-checkbox">
                <input type="checkbox" id="workplace${workplace.id}" name="workplace_ids[]" value="${workplace.id}" onchange="toggleWorkplaceTimeFields(${workplace.id})">
                <label for="workplace${workplace.id}">${workplace.name}</label>
            </div>
        `);
    });

    // 該当する現場がない場合のメッセージを表示
    if (relevantWorkplaces.length === 0) {
        $('#workplaceCheckboxes').append('<p>この日付に該当する現場はありません。</p>');
    }

    // 既存のアサインを取得して表示
    $.get(`/api/existing-assigns?worker_id=${workerId}&assign_date=${date}`, function(response) {
        if (response.success && response.assigns.length > 0) {
            response.assigns.forEach(assign => {
                $(`#workplace${assign.workplace_id}`).prop('checked', true);
                toggleWorkplaceTimeFields(assign.workplace_id, assign.start_time, assign.end_time);
            });
        }
    });

    $('#assignModal').modal('show');
}

// 現場の時間設定フィールドを切り替える関数
function toggleWorkplaceTimeFields(workplaceId, startTime = '', endTime = '') {
    let checkbox = $(`#workplace${workplaceId}`);
    if (checkbox.is(':checked')) {
        let timeFieldsHtml = `
            <div id="workplaceTime${workplaceId}" class="workplace-time-fields">
                <h6 class="mb-3">${checkbox.next('label').text()}</h6>
                <p class="text-muted mb-3">作業時間を設定してください</p>
                <div class="time-input-group">
                    <div class="time-input">
                        <label for="startTime${workplaceId}">開始時間</label>
                        <div class="input-with-icon">
                            <input type="text" class="form-control flatpickr-time" id="startTime${workplaceId}" name="assignments[${workplaceId}][start_time]" value="${startTime}" placeholder="開始時間" required>
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="time-input">
                        <label for="endTime${workplaceId}">終了時間</label>
                        <div class="input-with-icon">
                            <input type="text" class="form-control flatpickr-time" id="endTime${workplaceId}" name="assignments[${workplaceId}][end_time]" value="${endTime}" placeholder="終了時間" required>
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#workplaceTimesContainer').append(timeFieldsHtml);
        initializeFlatpickrForWorkplace(workplaceId);
    } else {
        $(`#workplaceTime${workplaceId}`).remove();
    }
}

// 現場ごとのFlatpickrを初期化する関数
function initializeFlatpickrForWorkplace(workplaceId) {
    const commonConfig = {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 30,
        allowInput: true,
        onClose: function(selectedDates, dateStr, instance) {
            // 値が空の場合、デフォルト値を設定
            if (!dateStr) {
                instance.setDate("09:00");
            }
        }
    };

    // 開始時間のFlatpickr設定
    flatpickr(`#startTime${workplaceId}`, {
        ...commonConfig,
        defaultHour: 9,
        defaultMinute: 0
    });

    // 終了時間のFlatpickr設定
    flatpickr(`#endTime${workplaceId}`, {
        ...commonConfig,
        defaultHour: 17,
        defaultMinute: 0
    });
}

// アサイン情報を送信する関数
function submitAssign() {
    let formData = {
        worker_id: $('#worker_id').val(),
        assign_date: $('#assign_date').val(),
        assignments: []
    };

    // チェックされた現場の情報を収集
    $('input[name="workplace_ids[]"]:checked').each(function() {
        let workplaceId = $(this).val();
        let startTime = $(`input[name="assignments[${workplaceId}][start_time]"]`).val();
        let endTime = $(`input[name="assignments[${workplaceId}][end_time]"]`).val();
        
        if (startTime && endTime) {
            formData.assignments.push({
                workplace_id: workplaceId,
                start_time: startTime,
                end_time: endTime
            });
        }
    });

    // バリデーション: 少なくとも1つの現場が選択されていることを確認
    if (formData.assignments.length === 0) {
        alert('少なくとも1つの現場を選択し、時間を設定してください。');
        return;
    }

    // APIにデータを送信
    $.ajax({
        url: '/api/assign-worker',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        processData: false,
        success: function(response) {
            if(response.success) {
                alert('アサインが成功しました');
                location.reload(); // ページをリロードして更新を反映
            } else {
                alert('アサインに失敗しました: ' + (response.message || '不明なエラー'));
            }
        },
        error: function(xhr) {
            let errorMessage = '不明なエラーが発生しました';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join("\n");
            }
            alert('エラー: ' + errorMessage);
        }
    });
}
</script>
@stop