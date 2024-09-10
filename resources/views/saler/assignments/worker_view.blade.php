@extends('adminlte::page')

@section('title', '職人別アサイン状況')

@section('content_header')
    <h1>職人別アサイン状況</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">アサイン用カレンダー</h3><br>
        <p class="text-muted">任意の日付を選択し現場を割り当てることができます。割当て済みの日付をクリックすると、割当ての修正や削除ができます。</p>
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
                        <select id="workplaceSelect" class="form-control" multiple>
                            <!-- 現場オプションはJavaScriptで動的に追加 -->
                        </select>
                    </div>
                    <div id="workplaceTimesContainer">
                        <!-- 現場ごとの時間設定欄はJavaScriptで動的に追加 -->
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    /* FullCalendar用のスタイル */
    #fullcalendar {
        margin-top: 20px;
    }
    .fc-event {
        cursor: pointer;
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        padding: 2px 4px;
        border-radius: 4px;
        margin-bottom: 2px;
    }
    .fc-event-title {
        font-weight: bold;
    }
    /* select2のスタイル調整 */
    .select2-container {
        width: 100% !important;
    }
    .select2-selection--multiple {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border: 1px solid #006fe6;
        color: #fff;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
        background-color: #0056b3;
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
            events: function(info, successCallback, failureCallback) {
                fetchWorkerAssignments(info.start, info.end, successCallback, failureCallback);
            },
            eventClick: function(info) {
                openAssignModal(info.event.extendedProps.workerId, info.event.start);
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
        calendar.render();
    }
    function fetchWorkerAssignments(start, end, successCallback, failureCallback) {
        $.ajax({
            url: '/api/worker-assignments',
            method: 'GET',
            data: {
                start: start.toISOString(),
                end: end.toISOString()
            },
            success: function(data) {
                let events = [];
                for (let workerId in data) {
                    let worker = data[workerId];
                    worker.assigns.forEach(function(assign) {
                        events.push({
                            title: `${worker.name} - ${assign.workplace_name}`,
                            start: `${assign.date}T${assign.start_time}`,
                            end: `${assign.date}T${assign.end_time}`,
                            allDay: false,
                            extendedProps: {
                                workerId: workerId,
                                workerName: worker.name,
                                workplaceName: assign.workplace_name,
                                startTime: assign.start_time || '未設定',
                                endTime: assign.end_time || '未設定'
                            },
                            color: getColorForWorker(workerId)
                        });
                    });
                }
                successCallback(events);
            },
            error: function(xhr, status, error) {
                console.error("アサインデータの取得に失敗しました:", error);
                failureCallback(error);
            }
        });
    }
    function getColorForWorker(index) {
        const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#C3B091', '#9370DB', '#3CB371', '#20B2AA', '#BA55D3'];
        return colors[index % colors.length];
    }

    // アサインモーダルを開く関数
    function openAssignModal(workerId, date) {
        $('#worker_id').val(workerId);
        $('#assign_date').val(date);
        
        let worker = @json($workers->keyBy('id'));
        $('#modalWorkerName').text(worker[workerId].name);
        $('#modalAssignDate').text(moment(date).format('YYYY年M月D日(ddd)'));
        
        // 現場リストをクリア
        $('#workplaceSelect').empty();
        $('#workplaceTimesContainer').empty();
        
        $.ajax({
            url: `/api/workplaces-for-worker/${workerId}`,
            method: 'GET',
            data: { date: date },
            success: function(workplaces) {
                let select = $('#workplaceSelect');
                workplaces.forEach(workplace => {
                    select.append(new Option(workplace.name, workplace.id));
                });
                select.select2({
                    placeholder: "現場を選択してください",
                    allowClear: true
                });
                
                // 既存のアサイン情報を取得して選択状態を設定
                $.ajax({
                    url: '/api/existing-assigns',
                    method: 'GET',
                    data: {
                        worker_id: workerId,
                        assign_date: date
                    },
                    success: function(response) {
                        if (response.success && response.assigns.length > 0) {
                            let selectedWorkplaces = response.assigns.map(assign => assign.workplace_id.toString());
                            select.val(selectedWorkplaces).trigger('change');
                            $('#workplaceTimesContainer').empty(); // 既存のフィールドをクリア
                            response.assigns.forEach(assign => {
                                addWorkplaceTimeFields(assign.workplace_id, assign.workplace_name, assign.start_time, assign.end_time);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("既存のアサイン取得に失敗しました:", error);
                        alert("既存のアサイン情報の取得中にエラーが発生しました。");
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("現場リストの取得に失敗しました:", error);
                alert("現場リストの取得中にエラーが発生しました。");
            }
        });

        $('#assignModal').modal('show');
    }

    // Select2の変更イベントを監視
    $('#workplaceSelect').on('change', function(e) {
        let selectedWorkplaces = $(this).val();
        $('#workplaceTimesContainer').empty();
        selectedWorkplaces.forEach(workplaceId => {
            let workplaceName = $(this).find(`option[value="${workplaceId}"]`).text();
            addWorkplaceTimeFields(workplaceId, workplaceName);
        });
    });

    function addWorkplaceTimeFields(workplaceId, workplaceName, startTime = '09:00', endTime = '17:00') {
        let timeFieldsHtml = `
            <div id="workplaceTime${workplaceId}" class="workplace-time-fields">
                <h6 class="mb-3">${workplaceName}</h6>
                <p class="text-muted mb-3">作業時間を設定してください</p>
                <div class="time-input-group">
                    <div class="time-input">
                        <label for="startTime${workplaceId}">開始時間</label>
                        <div class="input-with-icon">
                            <input type="text" class="form-control flatpickr-time" id="startTime${workplaceId}" placeholder="開始時間" value="${startTime}" readonly>
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="time-input">
                        <label for="endTime${workplaceId}">終了時間</label>
                        <div class="input-with-icon">
                            <input type="text" class="form-control flatpickr-time" id="endTime${workplaceId}" placeholder="終了時間" value="${endTime}" readonly>
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#workplaceTimesContainer').append(timeFieldsHtml);
        initializeFlatpickrForWorkplace(workplaceId);
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
                if (!dateStr) {
                    instance.setDate("09:00");
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                instance._input.readOnly = false;
            }
        };

        flatpickr(`#startTime${workplaceId}`, {
            ...commonConfig,
            defaultHour: 9,
            defaultMinute: 0
        });

        flatpickr(`#endTime${workplaceId}`, {
            ...commonConfig,
            defaultHour: 17,
            defaultMinute: 0
        });
    }
    // アサイン情報を送信する関数
    function submitAssign() {
        let selectedWorkplaces = $('#workplaceSelect').val();

        if (!selectedWorkplaces || selectedWorkplaces.length === 0) {
            alert('少なくとも1つの現場を選択してください。');
            return;
        }

        let formData = {
            worker_id: $('#worker_id').val(),
            assign_date: $('#assign_date').val(),
            assignments: []
        };

        selectedWorkplaces.forEach(workplaceId => {
            let startTime = $(`#startTime${workplaceId}`).val();
            let endTime = $(`#endTime${workplaceId}`).val();
            if (startTime && endTime) {
                formData.assignments.push({
                    workplace_id: workplaceId,
                    start_time: startTime,
                    end_time: endTime
                });
            }
        });

        $.ajax({
            url: '/api/assign-worker',
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
                let errorMessage = '不明なエラーが発生しました';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join("\n");
                }
                alert('エラー: ' + errorMessage);
            }
        });
    }

    function cancelAssignment() {
        if(!confirm('本当にこのアサインを解除しますか？')) {
            return;
        }

        $.ajax({
            url: '/api/cancel-worker-assignment',
            method: 'POST',
            data: JSON.stringify({
                worker_id: $('#worker_id').val(),
                assign_date: $('#assign_date').val()
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
                alert('エラーが発生しました: ' + error);
            }
        });
    }
</script>
@stop