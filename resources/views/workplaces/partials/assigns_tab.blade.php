@if($role === 'saler')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">アサイン情報</h3>
        <button type="button" class="btn btn-primary float-right" id="openAssignModalBtn">追加</button>
    </div>
    <div class="card-body">
        <!-- アサイン一覧表示 -->
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>作業者</th>
                    <th>開始日</th>
                    <th>終了日</th>
                    <th>開始時間</th>
                    <th>終了時間</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workplace->assigns as $assign)
                    <tr>
                        <td>{{ $assign->worker->name }}</td>
                        <td>{{ $assign->start_date->format('Y-m-d') }}</td>
                        <td>{{ $assign->end_date->format('Y-m-d') }}</td>
                        <td>{{ $assign->start_time ? $assign->start_time->format('H:i') : '未設定' }}</td>
                        <td>{{ $assign->end_time ? $assign->end_time->format('H:i') : '未設定' }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm edit-assign" data-id="{{ $assign->id }}">編集</button>
                            <button type="button" class="btn btn-danger btn-sm delete-assign" data-id="{{ $assign->id }}">削除</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- アサインモーダル -->
<!-- アサインモーダル -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">職人のアサイン</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card mb-3">
                    <div class="card-header" id="instructionsHeader">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#instructionsCollapse" aria-expanded="false" aria-controls="instructionsCollapse">
                                使用手順
                            </button>
                        </h5>
                    </div>
                    <div id="instructionsCollapse" class="collapse" aria-labelledby="instructionsHeader">
                        <div class="card-body">
                            <ol>
                                <li>カレンダーから日付を選択</li>
                                <li>アサインする職人を選択</li>
                                <li>各職人の作業時間を設定</li>
                                <li>「アサイン」ボタンをクリック</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="mb-4 p-3 bg-light rounded">
                    <h4 id="modalWorkplaceName" class="font-weight-bold text-primary mb-2">{{ $workplace->name }}</h4>
                    <h5 id="modalAssignDates" class="text-secondary">
                        {{ $workplace->construction_start->format('Y/m/d') }} ～ {{ $workplace->construction_end->format('Y/m/d') }}
                    </h5>
                </div>
                <form id="assignForm">
                    @csrf
                    <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">日付選択</label>
                        <div id="assign-calendar" class="assign-calendar mb-3"></div>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm mr-2" onclick="selectAllDates()">全て選択</button>
                            <button type="button" class="btn btn-primary btn-sm mr-2" onclick="selectWeekdayDates()">平日のみ選択</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllDates()">全解除</button>
                        </div>
                        <div id="selected-dates" class="mt-2"></div>
                    </div>

                    <div class="calendar-legend">
                        <span><i class="fas fa-exclamation-triangle text-danger"></i> 未アサイン</span>
                        <span><i class="fas fa-check text-success"></i> アサイン済み</span>
                        <span><i class="fas fa-square text-primary"></i> 選択中</span>
                    </div>
                
                    <div class="form-group mt-4">
                        <label class="font-weight-bold">職人選択 <small class="text-muted">(1人以上選択してください)</small></label>
                        <select id="workerSelect" class="form-control" multiple>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="workerTimesContainer">
                        <!-- 職人ごとの時間設定欄はJavaScriptで動的に追加 -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-custom" id="assignButton">アサイン</button>
            </div>
        </div>
    </div>
</div>
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* ボタングループのスタイル */
    .btn-group .btn {
        margin-right: 5px;
    }
    /* バッジのスタイル */
    .badge {
        font-size: 100%;
    }
    /* モーダルダイアログのスクロール設定 */
    .modal-dialog-scrollable {
        max-height: 90vh;
    }
    .modal-dialog-scrollable .modal-content {
        max-height: calc(90vh - 60px);
    }
    /* モーダル本文のスクロール設定 */
    .modal-body {
        overflow-y: auto;
    }
    /* 職人選択フィールドのスタイル */
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
    /* 時間入力フィールドのスタイル */
    .worker-time-fields {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .worker-time-fields h6 {
        margin-bottom: 10px;
    }
    .assign-calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
    }
    .calendar-cell {
        background-color: #fff;
        border: 1px solid #ddd;
        padding: 5px;
        text-align: center;
        cursor: pointer;
    }
    .calendar-cell.weekend {
        background-color: #e6f3ff;
    }
    .calendar-cell.selected {
        background-color: #007bff;
        color: white;
    }
    .calendar-cell.selected i {
        color: white !important;
    }
    .calendar-legend {
        margin-top: 10px;
        font-size: 0.9em;
    }
    .calendar-legend span {
        margin-right: 15px;
    }
    .worker-selection {
        background-color: #f0f0f0;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }
    .btn-custom {
        background-color: #28a745;
        color: white;
        transition: all 0.3s;
    }
    .btn-custom:hover {
        background-color: #218838;
        color: white;
    }
    .instructions {
        background-color: #e9ecef;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .calendar-cell.unassigned i {
        color: #dc3545; /* 赤色 */
    }
    .calendar-cell.assigned i {
        color: #28a745; /* 緑色 */
    }
    .calendar-cell.selected i {
        color: white;
    }
</style>
@endsection

@push('js')
<!-- jQuery (すでに含まれている可能性がありますが、念のため) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- Bootstrap Datepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Font Awesome (アイコン用、CSSで読み込んでいる場合は不要) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
$(function() {
    // アサインモーダルを開く
    $('#openAssignModalBtn').click(function() {
        openAssignModal({{ $workplace->id }});
    });

    // Select2の初期化
    $('#workerSelect').select2({
        placeholder: "職人を選択してください",
        allowClear: true
    });

    // 職人選択時のイベントハンドラ
    $('#workerSelect').on('change', function() {
        var selectedWorkers = $(this).val();
        var container = $('#workerTimesContainer');
        container.empty();

        selectedWorkers.forEach(function(workerId) {
            var workerName = $(`#workerSelect option[value="${workerId}"]`).text();
            addWorkerTimeFields(workerId, workerName);
        });
    });

    // アサインボタンのクリックイベント
    $('#assignButton').click(function() {
        submitAssign({{ $workplace->id }});
    });
});

// グローバル変数の定義
let isSubmitting = false;

// アサインモーダルを開く関数
function openAssignModal(workplaceId) {
    $('#assignModal').modal('show');

    // カレンダーの初期化
    var startDate = moment($('#modalAssignDates').text().split('～')[0].trim(), 'YYYY/MM/DD').toDate();
    var endDate = moment($('#modalAssignDates').text().split('～')[1].trim(), 'YYYY/MM/DD').toDate();
    initAssignCalendar(workplaceId, startDate, endDate);

    // 既存のアサイン情報を取得
    getExistingAssigns(workplaceId);
}

// カレンダーの初期化関数
function initAssignCalendar(workplaceId, startDate, endDate) {
    const calendarEl = $('#assign-calendar');
    calendarEl.empty();

    const currentDate = moment(startDate);
    const endMoment = moment(endDate);

    const days = ['日', '月', '火', '水', '木', '金', '土'];

    while (currentDate <= endMoment) {
        const dayOfWeek = days[currentDate.day()];
        const date = currentDate.format('YYYY-MM-DD');
        
        const dateCell = $(`
            <div class="calendar-cell unassigned" data-date="${date}">
                <div>${currentDate.format('MM/DD')}</div>
                <div>(${dayOfWeek})</div>
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        `);

        dateCell.on('click', function() {
            $(this).toggleClass('selected');
            if ($(this).hasClass('selected')) {
                $(this).find('i').removeClass('fa-exclamation-triangle').addClass('fa-check');
            } else {
                $(this).find('i').removeClass('fa-check').addClass('fa-exclamation-triangle');
            }
            updateSelectedDates(workplaceId);
        });

        calendarEl.append(dateCell);
        currentDate.add(1, 'days');
    }

    updateExistingAssigns(workplaceId);
}

// 既存のアサイン情報を更新する関数
function updateExistingAssigns(workplaceId) {
    $.ajax({
        url: '{{ route("workplaces.get-existing-assigns") }}',
        method: 'GET',
        data: { workplace_id: workplaceId },
        success: function(response) {
            if (response.success) {
                response.assigns.forEach(function(assign) {
                    const cell = $(`#assign-calendar .calendar-cell[data-date="${assign.date}"]`);
                    cell.removeClass('unassigned').addClass('assigned');
                    cell.find('i').removeClass('fa-exclamation-triangle').addClass('fa-check');
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching existing assigns:', error);
        }
    });
}

// 選択された日付を更新する関数
function updateSelectedDates(workplaceId) {
    const selectedDates = $('#assign-calendar .calendar-cell.selected').map(function() {
        return $(this).data('date');
    }).get();

    const container = $('#selected-dates');
    container.empty();
    selectedDates.forEach(date => {
        container.append(`
            <span class="badge badge-primary mr-1 mb-1">
                ${date}
                <i class="fas fa-times ml-1" onclick="removeDate('${date}')"></i>
            </span>
        `);
    });

    // カレンダーのアイコンを更新
    $('#assign-calendar .calendar-cell').each(function() {
        const cellDate = $(this).data('date');
        const isSelected = selectedDates.includes(cellDate);
        const isAssigned = $(this).hasClass('assigned');

        $(this).removeClass('selected unassigned assigned');
        $(this).find('i').removeClass('fa-check fa-exclamation-triangle').addClass(isAssigned ? 'fa-check' : 'fa-exclamation-triangle');

        if (isSelected) {
            $(this).addClass('selected');
            $(this).find('i').removeClass('fa-exclamation-triangle').addClass('fa-check');
        } else if (!isAssigned) {
            $(this).addClass('unassigned');
        }
    });
}

// 日付を削除する関数
function removeDate(date) {
    const cell = $(`#assign-calendar .calendar-cell[data-date="${date}"]`);
    cell.removeClass('selected');
    if (!cell.hasClass('assigned')) {
        cell.addClass('unassigned');
        cell.find('i').removeClass('fa-check').addClass('fa-exclamation-triangle');
    }
    updateSelectedDates();
}

// 全ての日付を選択する関数
function selectAllDates() {
    $('#assign-calendar .calendar-cell').addClass('selected');
    $('#assign-calendar .calendar-cell i').removeClass('fa-exclamation-triangle').addClass('fa-check');
    updateSelectedDates();
}    

// 全ての日付の選択を解除する関数
function clearAllDates() {
    $('#assign-calendar .calendar-cell').removeClass('selected');
    $('#assign-calendar .calendar-cell i').removeClass('fa-check').addClass('fa-exclamation-triangle');
    updateSelectedDates();
}

// 平日のみを選択する関数
function selectWeekdayDates() {
    $('#assign-calendar .calendar-cell').removeClass('selected');
    $('#assign-calendar .calendar-cell i').removeClass('fa-check').addClass('fa-exclamation-triangle');
    $('#assign-calendar .calendar-cell').each(function() {
        const date = moment($(this).data('date'));
        if (date.day() !== 0 && date.day() !== 6) {
            $(this).addClass('selected');
            $(this).find('i').removeClass('fa-exclamation-triangle').addClass('fa-check');
        }
    });
    updateSelectedDates();
}

// 職人ごとの時間入力フィールドを追加する関数
function addWorkerTimeFields(workerId, workerName) {
    var container = $('#workerTimesContainer');
    var html = `
        <div class="worker-time-fields mb-3">
            <h6>${workerName}</h6>
            <div class="form-row">
                <div class="col">
                    <label>開始時間</label>
                    <input type="time" class="form-control" name="workers[${workerId}][start_time]" value="09:00" required>
                </div>
                <div class="col">
                    <label>終了時間</label>
                    <input type="time" class="form-control" name="workers[${workerId}][end_time]" value="17:00" required>
                </div>
            </div>
        </div>
    `;
    container.append(html);
}

// 既存のアサイン情報を取得する関数
function getExistingAssigns(workplaceId) {
    $.ajax({
        url: '{{ route("workplaces.get-existing-assigns") }}',
        method: 'GET',
        data: {
            workplace_id: workplaceId
        },
        success: function(response) {
            if (response.success) {
                var select = $('#workerSelect');
                select.val(null).trigger('change');
                var selectedWorkers = [];

                response.assigns.forEach(function(assign) {
                    selectedWorkers.push(assign.worker_id.toString());
                    addWorkerTimeFields(assign.worker_id, $(`#workerSelect option[value="${assign.worker_id}"]`).text());
                    $(`#workerTimesContainer input[name="workers[${assign.worker_id}][start_time]"]`).val(assign.start_time);
                    $(`#workerTimesContainer input[name="workers[${assign.worker_id}][end_time]"]`).val(assign.end_time);
                });

                select.val(selectedWorkers).trigger('change');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching existing assigns:', error);
        }
    });
}

// アサインを送信する関数
function submitAssign(workplaceId) {
    // ダブルサブミット防止
    if (isSubmitting) {
        console.log('既に送信処理中です');
        return;
    }
    isSubmitting = true;

    var form = $('#assignForm');
    var formData = new FormData(form[0]);
    
    // 選択された日付を追加
    var selectedDates = $('#assign-calendar .calendar-cell.selected').map(function() {
        return $(this).data('date');
    }).get();
    formData.append('selected_dates', JSON.stringify(selectedDates));

    // 選択された職人とその時間情報を追加
    var workers = [];
    $('#workerTimesContainer .worker-time-fields').each(function() {
        var workerId = $(this).find('input[name^="workers"]').attr('name').match(/\d+/)[0];
        var startTime = $(this).find('input[name$="[start_time]"]').val();
        var endTime = $(this).find('input[name$="[end_time]"]').val();
        workers.push({
            worker_id: workerId,
            start_time: startTime,
            end_time: endTime
        });
    });
    formData.append('workers', JSON.stringify(workers));

    $.ajax({
        url: '{{ route("saler.workplaces.assign.store", ["id" => "__id__"]) }}'.replace('__id__', workplaceId),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            isSubmitting = false;  // リセット
            if(response.success) {
                alert('アサインが成功しました');
                $('#assignModal').modal('hide');
                location.reload();
            } else {
                alert('アサインに失敗しました: ' + (response.message || '不明なエラー'));
            }
        },
        error: function(xhr) {
            isSubmitting = false;  // リセット
            var errorMessage = '不明なエラーが発生しました';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join("\n");
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('エラー: ' + errorMessage);
        }
    });
}
</script>
@endpush
@else
<!-- 得意先（customer）向けの表示 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">アサイン情報</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>作業者</th>
                    <th>開始日</th>
                    <th>終了日</th>
                    <th>開始時間</th>
                    <th>終了時間</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workplace->assigns as $assign)
                    <tr>
                        <td>{{ $assign->worker->name }}</td>
                        <td>{{ $assign->start_date->format('Y-m-d') }}</td>
                        <td>{{ $assign->end_date->format('Y-m-d') }}</td>
                        <td>{{ $assign->start_time ? $assign->start_time->format('H:i') : '未設定' }}</td>
                        <td>{{ $assign->end_time ? $assign->end_time->format('H:i') : '未設定' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif