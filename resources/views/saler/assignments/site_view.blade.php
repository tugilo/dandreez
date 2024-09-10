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
                    @php
                    function getAssignmentTooltip($assigns, $workplace) {
                        $tooltipContent = "<strong>施工期間:</strong> {$workplace->construction_start->format('Y/m/d')} 〜 {$workplace->construction_end->format('Y/m/d')}<br>";
                
                        foreach ($assigns as $assign) {
                            $tooltipContent .= "
                                <strong>職人:</strong> {$assign->worker->name}<br>
                                <strong>開始時間:</strong> " . ($assign->start_time ? $assign->start_time->format('H:i') : '未設定') . "<br>
                                <strong>終了時間:</strong> " . ($assign->end_time ? $assign->end_time->format('H:i') : '未設定') . "<br>
                                <hr>
                            ";
                        }
                
                        return $tooltipContent;
                    }
                    @endphp
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
                        
                        // ツールチップの内容を設定
                        $tooltipContent = $isWithinConstruction && $assigns->isNotEmpty()
                            ? getAssignmentTooltip($assigns, $workplace)
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

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">月別アサイン状況</h3>
    </div>
    <div class="card-body">
        <div id="fullcalendar"></div>
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
                        <select id="workerSelect" class="form-control" multiple>
                            <!-- 職人オプションはJavaScriptで動的に追加 -->
                        </select>
                    </div>
                    <div id="workerTimesContainer">
                        <!-- 職人ごとの時間設定欄はJavaScriptで動的に追加 -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="cancelAssignment()">アサイン解除</button>
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
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css' rel='stylesheet' />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    .select2-container {
        width: 100% !important;
    }
    .select2-selection__choice {
        background-color: #007bff !important;
        color: white !important;
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
    /* FullCalendar用のスタイル */
    #fullcalendar {
        margin-top: 20px;
    }
    .fc-event {
        cursor: pointer;
        background-color: #007bff; /* 適宜色を調整 */
        border-color: #007bff; /* 適宜色を調整 */
        color: #fff;
        padding: 2px 4px;
        border-radius: 4px;
        margin-bottom: 2px;
    }

    .fc-event-title {
        font-weight: bold;
    }
    .fc-event-description {
        font-size: 0.8em;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/ja.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/locales/ja.js'></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    console.log('JavaScript code is loaded');

    // グローバル変数の宣言
    let assignmentCache = {};

    // ページ読み込み完了時の処理
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded and parsed');
        
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

        // Flatpickrのグローバル設定
        flatpickr.localize(flatpickr.l10ns.ja);

        // FullCalendarの初期化
        initializeFullCalendar();
    });

    function initializeFullCalendar() {
        console.log('Initializing FullCalendar');
        var calendarEl = document.getElementById('fullcalendar');
        if (!calendarEl) {
            console.error('Calendar element not found');
            return;
        }
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'ja',
            firstDay: 0,
            height: 'auto',
            dayCellContent: function(arg) {
                return arg.date.getDate();
            },
            events: function(info, successCallback, failureCallback) {
                console.log('Fetching events', info.startStr, info.endStr);
                fetchAssignmentData(info.start, info.end, successCallback, failureCallback);
            },
            eventClick: function(info) {
                console.log('Event clicked', info.event);
                window.location.href = `/saler/workplaces/saler/${info.event.extendedProps.workplaceId}/details`;
            },
            eventContent: function(arg) {
                return {
                    html: `
                        <div class="fc-content">
                            <div class="fc-title">${arg.event.title}</div>
                        </div>
                    `
                };
            },
            eventDidMount: function(info) {
                $(info.el).tooltip({
                    title: `
                        <strong>現場名:</strong> ${info.event.extendedProps.workplaceName}<br>
                        <strong>職人名:</strong> ${info.event.extendedProps.workerName}<br>
                        <strong>開始時間:</strong> ${info.event.extendedProps.startTime || '未設定'}<br>
                        <strong>終了時間:</strong> ${info.event.extendedProps.endTime || '未設定'}
                    `,
                    html: true,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            }
        });
        console.log('Calendar instance created');
        calendar.render();
        console.log('Calendar rendered');
    }

    function getColorForWorker(index) {
        const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#C3B091', '#9370DB', '#3CB371', '#20B2AA', '#BA55D3'];
        return colors[index % colors.length];
    }

    // 職人ごとの時間入力用Flatpickrの初期化
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

        // 開始時間のFlatpickr
        flatpickr(`#startTime${workerId}`, {
            ...commonConfig,
            defaultHour: 9,
            defaultMinute: 0
        });

        // 終了時間のFlatpickr
        flatpickr(`#endTime${workerId}`, {
            ...commonConfig,
            defaultHour: 17,
            defaultMinute: 0
        });
    }


    // アサインモーダルを開く関数
    function openAssignModal(workplaceId, date, workplaceName) {
        $('#workplaceId').val(workplaceId);
        $('#assignDate').val(date);
        
        $('#modalWorkplaceName').text(workplaceName);
        var formattedDate = moment(date).format('YYYY年M月D日(ddd)');
        $('#modalAssignDate').text(formattedDate);
        
        $('#workerCheckboxes').empty();
        $('#workerTimesContainer').empty();
        
        $.ajax({
            url: `/api/workers-for-workplace/${workplaceId}`,
            method: 'GET',
            success: function(workers) {
                let select = $('#workerSelect');
                select.empty();
                workers.forEach(worker => {
                    select.append(new Option(worker.name, worker.id));
                });
                select.select2({
                    placeholder: "職人を選択してください",
                    allowClear: true
                });
                
                // 既存のアサイン情報を取得して選択状態を設定
                $.ajax({
                    url: '/api/existing-assigns',
                    method: 'GET',
                    data: {
                        workplace_id: workplaceId,
                        assign_date: date  // 'date' から 'assign_date' に変更
                    },
                    success: function(response) {
                        if (response.success && response.assigns.length > 0) {
                            let selectedWorkers = response.assigns.map(assign => assign.worker_id.toString());
                            select.val(selectedWorkers).trigger('change');
                            response.assigns.forEach(assign => {
                                addWorkerTimeFields(assign.worker_id, assign.worker_name, assign.start_time, assign.end_time);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("既存のアサイン取得に失敗しました:", error);
                        console.log("エラーレスポンス:", xhr.responseText);
                        alert("既存のアサイン情報の取得中にエラーが発生しました。詳細: " + xhr.responseText);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("職人リストの取得に失敗しました:", error);
                alert("職人リストの取得中にエラーが発生しました。");
            }
        });

        $('#assignModal').modal('show');
    }

    // Select2の変更イベントを監視
    $('#workerSelect').on('change', function(e) {
        let selectedWorkers = $(this).val();
        $('#workerTimesContainer').empty();
        selectedWorkers.forEach(workerId => {
            let workerName = $(this).find(`option[value="${workerId}"]`).text();
            addWorkerTimeFields(workerId, workerName);
        });
    });
    // 職人の可用性をチェックする関数
    function checkWorkerAvailability(workerId, date) {
        let checkbox = $(`#worker${workerId}`);
        if (checkbox.is(':checked')) {
            // 職人の可用性をサーバーに問い合わせ
            $.ajax({
                url: `/api/check-worker-availability/${workerId}/${date}`,
                method: 'GET',
                success: function(response) {
                    if (response.available) {
                        // 職人が利用可能な場合、時間入力フィールドを追加
                        addWorkerTimeFields(workerId, $(`label[for="worker${workerId}"]`).text());
                    } else {
                        // 職人が利用不可能な場合、チェックを外してアラートを表示
                        checkbox.prop('checked', false);
                        alert('この職人は選択された日時に既にアサインされています。');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("職人の可用性チェックに失敗しました:", error);
                    checkbox.prop('checked', false);
                    alert("職人の可用性チェック中にエラーが発生しました。");
                }
            });
        } else {
            // チェックが外された場合、時間入力フィールドを削除
            removeWorkerTimeFields(workerId);
        }
    }

    // 職人の作業時間入力フィールドを追加する関数
    function addWorkerTimeFields(workerId, workerName, startTime = '09:00', endTime = '17:00') {
        let timeFieldsHtml = `
            <div id="workerTime${workerId}" class="worker-time-fields">
                <h6 class="mb-3">${workerName}</h6>
                <p class="text-muted mb-3">作業時間を設定してください</p>
                <div class="time-input-group">
                    <div class="time-input">
                        <label for="startTime${workerId}">開始時間</label>
                        <div class="input-with-icon">
                            <input type="text" class="form-control flatpickr-time" id="startTime${workerId}" placeholder="開始時間" value="${startTime}" readonly>
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

    // 職人の作業時間入力フィールドを削除する関数
    function removeWorkerTimeFields(workerId) {
        $(`#workerTime${workerId}`).remove();
    }

    // アサインを送信する関数
    function submitAssign() {
    // Select2で選択された職人のIDを取得
    let selectedWorkers = $('#workerSelect').val();

    // 職人が選択されていない場合はアラートを表示して処理を中断
    if (!selectedWorkers || selectedWorkers.length === 0) {
        alert('少なくとも1名の職人を選択してください。');
        return;
    }

    // フォームデータの作成
    let formData = {
        workplace_id: $('#workplaceId').val(),
        assign_date: $('#assignDate').val(),
        worker_assignments: []
    };

    // 各選択された職人の情報を formData に追加
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

    // アサイン情報をサーバーに送信
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
        error: function(xhr, status, error) {
            console.error("アサイン送信中にエラーが発生しました:", error);
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errorMessages = Object.values(xhr.responseJSON.errors).flat().join("\n");
                alert('バリデーションエラー:\n' + errorMessages);
            } else {
                alert('エラーが発生しました: ' + error);
            }
        }
    });
}



    // アサイン情報を非同期で取得する関数
    function fetchAssignmentData(start, end, successCallback, failureCallback) {
        console.log('Fetching assignment data', start, end);
        const cacheKey = `${start.toISOString()}_${end.toISOString()}`;
        
        if (assignmentCache[cacheKey]) {
            console.log('Using cached data');
            successCallback(convertToEvents(assignmentCache[cacheKey]));
            return;
        }

        $.ajax({
            url: '/api/monthly-assignments',
            method: 'GET',
            data: {
                start: start.toISOString(),
                end: end.toISOString()
            },
            success: function(data) {
                console.log('Assignment data received', data);
                assignmentCache[cacheKey] = data;
                successCallback(convertToEvents(data));
            },
            error: function(xhr, status, error) {
                console.error("アサインデータの取得に失敗しました:", error);
                failureCallback(error);
            }
        });
    }

    // APIから取得したデータをFullCalendarのイベント形式に変換する関数
    function convertToEvents(data) {
        var events = [];
        for (var workplaceId in data) {
            var workplace = data[workplaceId];
            workplace.assigns.forEach(function(assign) {
                events.push({
                    title: `${workplace.name} - ${assign.worker_name}`,
                    start: `${assign.date}T${assign.start_time}`,
                    end: `${assign.date}T${assign.end_time}`,
                    allDay: false,
                    extendedProps: {
                        workplaceId: workplaceId,
                        workplaceName: workplace.name,
                        workerName: assign.worker_name,
                        startTime: assign.start_time || '未設定',
                        endTime: assign.end_time || '未設定'
                    },
                    color: getColorForWorker(assign.worker_id)
                });
            });
        }
        console.log('Converted events:', events);
        return events;
    }

    function cancelAssignment() {
        if(!confirm('本当にこのアサインを解除しますか？')) {
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
                    alert('アサインの解除が成功しました');
                    location.reload();
                } else {
                    alert('アサインの解除に失敗しました: ' + (response.message || '不明なエラー'));
                }
            },
            error: function(xhr, status, error) {
                console.error('エラー:', status, error);
                console.log('レスポンス:', xhr.responseText);  // デバッグ用
                alert('エラーが発生しました: ' + error);
            }
        });
    }
</script>
@endsection