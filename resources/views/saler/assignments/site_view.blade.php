@extends('adminlte::page')

@section('title', '現場別アサイン状況')

@section('content_header')
    <h1>現場別アサイン状況</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">アサイン用カレンダー</h3><br>
        <p class="text-muted">任意の日付を選択し職人を割り当てることができます。割当て済みの日付をクリックすると、割当ての修正や削除ができます。</p>
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
                            <a href="{{ route('saler.workplaces.details', ['role' => 'saler', 'id' => $workplace->id]) }}">
                                {{ $workplace->name }}
                            </a>
                        </td>
                        @foreach($calendar as $day)
                            @php
                                // 日付が施工期間内かどうかを判定
                                $date = Carbon\Carbon::parse($day['date']);
                                $isWithinConstruction = $date->between($workplace->construction_start, $workplace->construction_end);
                                
                                // その日のアサインを取得
                                $assigns = $workplace->assigns->filter(function($assign) use ($day) {
                                    return $assign->start_date->format('Y-m-d') === $day['date'];
                                });
                                
                                // アサインされた職人の名前を取得
                                $workerNames = $assigns->pluck('worker.name')->implode(' | ');
                                
                                // ツールチップの内容を設定
                                $tooltipContent = $isWithinConstruction
                                    ? "<strong>施工期間:</strong> {$workplace->construction_start->format('Y/m/d')} 〜 {$workplace->construction_end->format('Y/m/d')}<br>" .
                                      ($workerNames ? "<strong>職人:</strong> [{$workerNames}]" : "")
                                    : '';
                            @endphp
                            <td class="{{ $isWithinConstruction ? ($assigns->isNotEmpty() ? 'bg-success' : 'bg-warning') : '' }}"
                                data-toggle="tooltip"
                                data-html="true"
                                title="{{ $tooltipContent }}"
                                onclick="openAssignModal('{{ $workplace->id }}', '{{ $day['date'] }}', '{{ $workplace->name }}')"
                                style="cursor: pointer">
                                @if($assigns->isNotEmpty())
                                    <i class="fas fa-check"></i>
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

<!-- 新しい月別カレンダー -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">月別アサイン状況</h3>
    </div>
    <div class="card-body">
        <div id="monthly-calendar"></div>
    </div>
</div>

<!-- アサインモーダル -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalLabel">アサイン追加</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4 p-3 bg-light rounded">
                    <h4 id="modalWorkplaceName" class="font-weight-bold text-primary mb-2"></h4>
                    <h5 id="modalAssignDate" class="text-secondary"></h5>
                </div>
                <form id="assignForm">
                    @csrf
                    <input type="hidden" id="workplaceId" name="workplace_id">
                    <input type="hidden" id="assignDate" name="assign_date">
                    <div class="form-group">
                        <label class="font-weight-bold">職人選択 <small class="text-muted">(1名以上選択してください)</small></label>
                        <div id="workerCheckboxes" class="worker-checkboxes">
                            <!-- チェックボックスはJavaScriptで動的に追加 -->
                        </div>
                    </div>
                    <div id="workerTimesContainer">
                        <!-- 職人ごとの時間設定欄はJavaScriptで動的に追加 -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="deleteAssign()">削除</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="submitAssign()">アサイン</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">
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
    /* 職人選択エリアのスタイル */
    .worker-checkboxes {
        max-height: 300px; /* 最大高さを設定 */
        overflow-y: auto; /* 縦方向のスクロールを有効化 */
        border: 1px solid #ced4da;
        padding: 10px;
        border-radius: 4px;
    }
    .worker-checkbox {
        margin-bottom: 5px;
    }
    /* 職人ごとの時間設定エリアのスタイル */
    #workerTimesContainer {
        max-height: 400px; /* 最大高さを設定 */
        overflow-y: auto; /* 縦方向のスクロールを有効化 */
        padding-right: 10px; /* スクロールバーのためのパディング */
    }
    .worker-time-fields {
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
    /* スクロールバーのスタイル（オプション） */
    .worker-checkboxes::-webkit-scrollbar,
    #workerTimesContainer::-webkit-scrollbar {
        width: 8px;
    }
    .worker-checkboxes::-webkit-scrollbar-thumb,
    #workerTimesContainer::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 4px;
    }
    .worker-checkboxes::-webkit-scrollbar-track,
    #workerTimesContainer::-webkit-scrollbar-track {
        background-color: #f1f1f1;
    }
    /* 月別カレンダー用のスタイル */
    #monthly-calendar table {
        font-size: 0.8em;
    }
    #monthly-calendar th, #monthly-calendar td {
        text-align: center;
        vertical-align: middle;
        padding: 2px !important;
    }
    #monthly-calendar td {
        height: 30px;
    }
    .construction-period {
        background-color: #e9ecef;
    }
    .assigned {
        background-color: #28a745;
        color: white;
    }

</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/ja.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/ja.js"></script>
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
    // Flatpickrのグローバル設定
    flatpickr.localize(flatpickr.l10ns.ja);

    // 初期表示時に月別カレンダーを生成
    generateMonthlyCalendar('{{ $month }}');

    // 月が変更されたときに月別カレンダーを更新
    $("#monthpicker").on("change.datetimepicker", function (e) {
        let selectedMonth = e.date.format('YYYY-MM');
        generateMonthlyCalendar(selectedMonth);
    });
});

function initializeFlatpickrForWorker(workerId) {
    const commonConfig = {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 30,
        allowInput: true,
        onClose: function(selectedDates, dateStr, instance) {
            // 入力値をバリデート
            if (!dateStr) {
                instance.setDate("09:00");  // デフォルト値を設定
            }
        }
    };

    flatpickr(`#startTime${workerId}`, {
        ...commonConfig,
        defaultHour: 9,
        defaultMinute: 0
    });

    flatpickr(`#endTime${workerId}`, {
        ...commonConfig,
        defaultHour: 17,
        defaultMinute: 0
    });
}

function openAssignModal(workplaceId, date, workplaceName) {
    $('#workplaceId').val(workplaceId);
    $('#assignDate').val(date);
    
    $('#modalWorkplaceName').text(workplaceName);
    var formattedDate = moment(date).format('YYYY年M月D日(ddd)');
    $('#modalAssignDate').text(formattedDate);
    
    // 職人リストをクリア
    $('#workerCheckboxes').empty();
    $('#workerTimesContainer').empty();
    
    $.get(`/api/workers-for-workplace/${workplaceId}`, function(workers) {
        let checkboxes = '';
        workers.forEach(worker => {
            checkboxes += `
                <div class="worker-checkbox">
                    <input type="checkbox" id="worker${worker.id}" name="worker_ids[]" value="${worker.id}" onchange="checkWorkerAvailability(${worker.id}, '${date}')">
                    <label for="worker${worker.id}">${worker.name}</label>
                </div>`;
        });
        $('#workerCheckboxes').html(checkboxes);
        
        // 既存のアサインを取得
        $.get(`/api/existing-assigns?workplace_id=${workplaceId}&date=${date}`, function(assigns) {
            if (assigns.length > 0) {
                // 既存のアサインがある場合、それらを表示
                assigns.forEach(assign => {
                    $(`#worker${assign.worker_id}`).prop('checked', true);
                    addWorkerTimeFields(assign.worker_id, assign.worker_name, assign.start_time, assign.end_time);
                });
            }
        });
    });

    $('#assignModal').modal('show');
}

function checkWorkerAvailability(workerId, date) {
    let checkbox = $(`#worker${workerId}`);
    if (checkbox.is(':checked')) {
        $.get(`/api/check-worker-availability/${workerId}/${date}`, function(response) {
            if (response.available) {
                addWorkerTimeFields(workerId, $(`label[for="worker${workerId}"]`).text());
            } else {
                checkbox.prop('checked', false);
                alert('この職人は選択された日時に既にアサインされています。');
            }
        });
    } else {
        removeWorkerTimeFields(workerId);
    }
}

function addWorkerTimeFields(workerId, workerName) {
    let timeFieldsHtml = `
        <div id="workerTime${workerId}" class="worker-time-fields">
            <h6 class="mb-3">${workerName}</h6>
            <p class="text-muted mb-3">作業時間を設定してください</p>
            <div class="time-input-group">
                <div class="time-input">
                    <label for="startTime${workerId}">開始時間</label>
                    <div class="input-with-icon">
                        <input type="text" class="form-control flatpickr-time" id="startTime${workerId}" placeholder="開始時間"  value="${startTime}" readonly>
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="time-input">
                    <label for="endTime${workerId}">終了時間</label>
                    <div class="input-with-icon">
                        <input type="text" class="form-control flatpickr-time" id="endTime${workerId}" placeholder="終了時間" value="${endTime}" readonly>
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('#workerTimesContainer').append(timeFieldsHtml);
    initializeFlatpickrForWorker(workerId);
}
function removeWorkerTimeFields(workerId) {
    $(`#workerTime${workerId}`).remove();
}

function submitAssign() {
    let selectedWorkers = $('input[name="worker_ids[]"]:checked').map(function(){
        return $(this).val();
    }).get();

    if (selectedWorkers.length === 0) {
        alert('少なくとも1名の職人を選択してください。');
        return;
    }

    let formData = {
        workplace_id: $('#workplaceId').val(),
        assign_date: $('#assignDate').val(),
        worker_assignments: []
    };

    selectedWorkers.forEach(workerId => {
        let startTime = $(`#startTime${workerId}`).val();
        let endTime = $(`#endTime${workerId}`).val();
        if (!startTime || !endTime) {
            alert('全ての職人の作業時間を正しく設定してください。');
            return;
        }
        formData.worker_assignments.push({
            worker_id: workerId,
            start_time: startTime,
            end_time: endTime
        });
    });

    $.ajax({
        url: '/api/assign',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        processData: false,
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
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('monthly-calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'ja',
        events: function(info, successCallback, failureCallback) {
            $.ajax({
                url: '/api/monthly-assignments',
                method: 'GET',
                data: { 
                    start: info.startStr,
                    end: info.endStr
                },
                success: function(data) {
                    var events = [];
                    for (var workplaceId in data) {
                        var workplace = data[workplaceId];
                        var assignsByDate = {};
                        
                        workplace.assigns.forEach(function(assign) {
                            if (!assignsByDate[assign.date]) {
                                assignsByDate[assign.date] = [];
                            }
                            assignsByDate[assign.date].push(assign.worker_name);
                        });
                        
                        for (var date in assignsByDate) {
                            var workerNames = assignsByDate[date].join(', ');
                            events.push({
                                title: `${workplace.name} - ${workerNames}`,
                                start: date,
                                end: date,
                                backgroundColor: '#007bff',
                                textColor: '#fff',
                                url: `/saler/workplaces/saler/${workplaceId}/details`,
                                cursor: 'pointer'
                            });
                        }
                    }
                    successCallback(events);
                }
            });
        },
        eventDidMount: function(info) {
            var titleEl = info.el.getElementsByClassName('fc-event-title')[0];
            titleEl.style.fontSize = '1.2em';
        }
    });
    calendar.render();
});
function deleteAssign() {
    if(!confirm('本当にこのアサインを削除しますか？')) {
        return;
    }

    $.ajax({
        url: '/api/assign',
        method: 'DELETE',
        data: JSON.stringify({
            workplace_id: $('#workplaceId').val(),
            assign_date: $('#assignDate').val()
        }),
        contentType: 'application/json',
        processData: false,
        success: function(response) {
            if(response.success) {
                alert('アサインの削除が成功しました');
                location.reload();
            } else {
                alert('アサインの削除に失敗しました: ' + (response.message || '不明なエラー'));
            }
        },
        error: function(xhr) {
            alert('エラーが発生しました');
        }
    });
}
</script>
@endsection