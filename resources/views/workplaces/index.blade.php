@extends('adminlte::page')

@section('title', '施工依頼一覧')

@section('content_header')
    <h1>
        <i class="fas fa-list"></i> 施工依頼一覧
    </h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">施工依頼</h3>
        <div class="card-tools">
            <a href="{{ route($createRoute, ['role' => $role]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> 新規作成
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="workplaces-table" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>操作</th>
                    <th>ID</th>
                    <th>得意先名</th>
                    <th>施工依頼名</th>
                    <th>施工期間</th>
                    <th>ステータス</th>
                    <th>作成日</th>
                    <th>削除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($workplaces as $workplace)
                    <tr>
                        <td>
                            <div class="btn-group" role="group">
                                <!-- 編集ボタン -->
                                <a href="{{ route($editRoute, ['role' => $role, 'id' => $workplace->id]) }}" class="btn btn-sm btn-warning" data-toggle="tooltip" title="編集">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- 詳細ボタン -->
                                <a href="{{ route($detailsRoute, ['role' => $role, 'id' => $workplace->id]) }}" class="btn btn-sm btn-info" data-toggle="tooltip" title="詳細">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                @if($role === 'saler')
                                    <!-- 承認ボタン（問屋のみ表示） -->
                                    <button type="button" class="btn btn-sm btn-success" data-toggle="tooltip" title="承認" onclick="openApproveModal({{ $workplace->id }})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <!-- アサインボタン（問屋のみ表示） -->
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="tooltip" title="アサイン" onclick="openAssignModal({{ $workplace->id }})">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td>{{ $workplace->id }}</td>
                        <td>{{ $workplace->customer->name ?? '未設定' }}</td>
                        <td>{{ $workplace->name ?? '未設定' }}</td>
                        <td>
                            @if ($workplace->construction_start && $workplace->construction_end)
                                {{ $workplace->construction_start->format('Y/m/d') }} 〜 {{ $workplace->construction_end->format('Y/m/d') }}
                            @else
                                未設定
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $workplace->status->color ?? 'badge-secondary' }}">
                                {{ $workplace->status->name_ja ?? '未設定' }}
                            </span>
                        </td>
                        <td>{{ $workplace->created_at ? $workplace->created_at->format('Y/m/d H:i') : '未設定' }}</td>
                        <td>
                            <!-- 削除ボタン -->
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="tooltip" title="削除" onclick="openDeleteModal({{ $workplace->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach ($workplaces as $workplace)
    @if($role === 'saler')
    <!-- 承認モーダル -->
    <div class="modal fade" id="approveModal-{{ $workplace->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">施工依頼の承認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>ID:</strong> {{ $workplace->id }}</p>
                    <p><strong>得意先:</strong> {{ $workplace->customer->name ?? '未設定' }}</p>
                    <p><strong>施工名:</strong> {{ $workplace->name ?? '未設定' }}</p>
                    <p><strong>施工期間:</strong> 
                        @if ($workplace->construction_start && $workplace->construction_end)
                            {{ $workplace->construction_start->format('Y/m/d') }} ～ {{ $workplace->construction_end->format('Y/m/d') }}
                        @else
                            未設定
                        @endif
                    </p>
                    <p><strong>施工場所:</strong> 
                        {{ $workplace->prefecture ?? '' }} 
                        {{ $workplace->city ?? '' }} 
                        {{ $workplace->address ?? '' }} 
                        {{ $workplace->building ?? '' }}
                    </p>
                </div>
                <div class="modal-footer">
                    <!-- 承認ボタン -->
                    <form action="{{ route($role . '.workplaces.approve', ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">承認</button>
                    </form>
                    <!-- 拒否ボタン -->
                    <form action="{{ route($role . '.workplaces.reject', ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">拒否</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- アサインモーダル -->
    <div class="modal fade" id="assignModal-{{ $workplace->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">職人のアサイン</h5>
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
                        <h4 id="modalWorkplaceName-{{ $workplace->id }}" class="font-weight-bold text-primary mb-2">{{ $workplace->name }}</h4>
                        <h5 id="modalAssignDates-{{ $workplace->id }}" class="text-secondary">
                            {{ $workplace->construction_start->format('Y/m/d') }} ～ {{ $workplace->construction_end->format('Y/m/d') }}
                        </h5>
                    </div>
                    <form id="assignForm-{{ $workplace->id }}">
                        @csrf
                        <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                        
                        <div class="form-group">
                            <label class="font-weight-bold">日付選択</label>
                            <div id="assign-calendar-{{ $workplace->id }}" class="assign-calendar mb-3"></div>
                            <div>
                                <button type="button" class="btn btn-primary btn-sm mr-2" onclick="selectAllDates({{ $workplace->id }})">全て選択</button>
                                <button type="button" class="btn btn-primary btn-sm mr-2" onclick="selectWeekdayDates({{ $workplace->id }})">平日のみ選択</button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllDates({{ $workplace->id }})">全解除</button>
                            </div>
                            <div id="selected-dates-{{ $workplace->id }}" class="mt-2"></div>
                        </div>

                        <div class="calendar-legend">
                            <span><i class="fas fa-exclamation-triangle text-danger"></i> 未アサイン</span>
                            <span><i class="fas fa-check text-success"></i> アサイン済み</span>
                            <span><i class="fas fa-square text-primary"></i> 選択中</span>
                        </div>
                    
                        <div class="form-group mt-4">
                            <label class="font-weight-bold">職人選択 <small class="text-muted">(1人以上選択してください)</small></label>
                            <select id="workerSelect-{{ $workplace->id }}" class="form-control" multiple>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="workerTimesContainer-{{ $workplace->id }}">
                            <!-- 職人ごとの時間設定欄はJavaScriptで動的に追加 -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                    <button type="button" class="btn btn-custom" id="assignButton-{{ $workplace->id }}" onclick="submitAssign({{ $workplace->id }})">アサイン</button>
                </div>
            </div>
        </div>
    </div>
@endif

    <!-- 削除確認モーダル -->
    <div class="modal fade" id="deleteModal-{{ $workplace->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">削除確認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>この施工依頼を削除してもよろしいですか？</p>
                    <p><strong>施工名:</strong> {{ $workplace->name ?? '未設定' }}</p>
                </div>
                <div class="modal-footer">
                    <!-- 削除フォーム -->
                    <form action="{{ route($destroyRoute, ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">削除</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
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
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // グローバル関数の定義
    let isSubmitting = false;
    function openAssignModal(workplaceId) {
        var modal = $(`#assignModal-${workplaceId}`);
        modal.modal('show');
    
        // カレンダーの初期化
        var startDate = moment($(`#modalAssignDates-${workplaceId}`).text().split('～')[0].trim(), 'YYYY/MM/DD').toDate();
        var endDate = moment($(`#modalAssignDates-${workplaceId}`).text().split('～')[1].trim(), 'YYYY/MM/DD').toDate();
        initAssignCalendar(workplaceId, startDate, endDate);
    
        // 既存のアサイン情報を取得
        getExistingAssigns(workplaceId);
    }
    
    function initAssignCalendar(workplaceId, startDate, endDate) {
        const calendarEl = $(`#assign-calendar-${workplaceId}`);
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
    function updateExistingAssigns(workplaceId) {
        $.ajax({
            url: '{{ route("workplaces.get-existing-assigns") }}',
            method: 'GET',
            data: { workplace_id: workplaceId },
            success: function(response) {
                if (response.success) {
                    response.assigns.forEach(function(assign) {
                        const cell = $(`#assign-calendar-${workplaceId} .calendar-cell[data-date="${assign.date}"]`);
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

    function updateSelectedDates(workplaceId) {
        const selectedDates = $(`#assign-calendar-${workplaceId} .calendar-cell.selected`).map(function() {
            return $(this).data('date');
        }).get();

        const container = $(`#selected-dates-${workplaceId}`);
        container.empty();
        selectedDates.forEach(date => {
            container.append(`
                <span class="badge badge-primary mr-1 mb-1">
                    ${date}
                    <i class="fas fa-times ml-1" onclick="removeDate('${workplaceId}', '${date}')"></i>
                </span>
            `);
        });

        // カレンダーのアイコンを更新
        $(`#assign-calendar-${workplaceId} .calendar-cell`).each(function() {
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


    function removeDate(workplaceId, date) {
        const cell = $(`#assign-calendar-${workplaceId} .calendar-cell[data-date="${date}"]`);
        cell.removeClass('selected');
        if (!cell.hasClass('assigned')) {
            cell.addClass('unassigned');
            cell.find('i').removeClass('fa-check').addClass('fa-exclamation-triangle');
        }
        updateSelectedDates(workplaceId);
    }

    function selectAllDates(workplaceId) {
        $(`#assign-calendar-${workplaceId} .calendar-cell`).addClass('selected');
        $(`#assign-calendar-${workplaceId} .calendar-cell i`).removeClass('fa-exclamation-triangle').addClass('fa-check');
        updateSelectedDates(workplaceId);
    }    

    function clearAllDates(workplaceId) {
        $(`#assign-calendar-${workplaceId} .calendar-cell`).removeClass('selected');
        $(`#assign-calendar-${workplaceId} .calendar-cell i`).removeClass('fa-check').addClass('fa-exclamation-triangle');
        updateSelectedDates(workplaceId);
    }
    function selectWeekdayDates(workplaceId) {
        $(`#assign-calendar-${workplaceId} .calendar-cell`).removeClass('selected');
        $(`#assign-calendar-${workplaceId} .calendar-cell i`).removeClass('fa-check').addClass('fa-exclamation-triangle');
        $(`#assign-calendar-${workplaceId} .calendar-cell`).each(function() {
            const date = moment($(this).data('date'));
            if (date.day() !== 0 && date.day() !== 6) {
                $(this).addClass('selected');
                $(this).find('i').removeClass('fa-exclamation-triangle').addClass('fa-check');
            }
        });
        updateSelectedDates(workplaceId);
    }
    function addWorkerTimeFields(workplaceId, workerId, workerName) {
        var container = $(`#workerTimesContainer-${workplaceId}`);
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
    
    function getExistingAssigns(workplaceId) {
        $.ajax({
            url: '{{ route("workplaces.get-existing-assigns") }}',
            method: 'GET',
            data: {
                workplace_id: workplaceId
            },
            success: function(response) {
                if (response.success) {
                    var select = $(`#workerSelect-${workplaceId}`);
                    select.val(null).trigger('change');
                    var selectedWorkers = [];
    
                    response.assigns.forEach(function(assign) {
                        selectedWorkers.push(assign.worker_id.toString());
                        addWorkerTimeFields(workplaceId, assign.worker_id, $(`#workerSelect-${workplaceId} option[value="${assign.worker_id}"]`).text());
                        $(`#workerTimesContainer-${workplaceId} input[name="workers[${assign.worker_id}][start_time]"]`).val(assign.start_time);
                        $(`#workerTimesContainer-${workplaceId} input[name="workers[${assign.worker_id}][end_time]"]`).val(assign.end_time);
                    });
    
                    select.val(selectedWorkers).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching existing assigns:', error);
            }
        });
    }
    
    function submitAssign(workplaceId) {
        // ダブルサブミット防止
        if (isSubmitting) {
            console.log('既に送信処理中です');
            return;
        }
        isSubmitting = true;

        var form = $(`#assignForm-${workplaceId}`);
        var formData = new FormData(form[0]);
        
        // 選択された日付を追加
        var selectedDates = $(`#assign-calendar-${workplaceId} .calendar-cell.selected`).map(function() {
            return $(this).data('date');
        }).get();
        formData.append('selected_dates', JSON.stringify(selectedDates));

        // 選択された職人とその時間情報を追加
        var workers = [];
        $(`#workerTimesContainer-${workplaceId} .worker-time-fields`).each(function() {
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
                    $(`#assignModal-${workplaceId}`).modal('hide');
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

    function openApproveModal(workplaceId) {
        $(`#approveModal-${workplaceId}`).modal('show');
    }
    
    function openDeleteModal(workplaceId) {
        $(`#deleteModal-${workplaceId}`).modal('show');
    }
    
    function checkOverlap(workplaceId, workerId, startDate, endDate, startTime, endTime) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: '{{ route("workplaces.check-overlap") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    workplace_id: workplaceId,
                    worker_id: workerId,
                    start_date: startDate,
                    end_date: endDate,
                    start_time: startTime,
                    end_time: endTime
                },
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
        });
    }
    
    $(document).ready(function() {
        // DataTablesの初期化
        $('#workplaces-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Japanese.json"
            },
            "order": [[ 1, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [0, 7] }
            ]
        });
    
        // ツールチップの初期化
        $('[data-toggle="tooltip"]').tooltip();
    
        // 職人選択時のイベントハンドラ
        $('.modal').on('change', '[id^=workerSelect-]', function() {
            var workplaceId = $(this).attr('id').split('-')[1];
            var selectedWorkers = $(this).val();
            var container = $(`#workerTimesContainer-${workplaceId}`);
            container.empty();
    
            selectedWorkers.forEach(function(workerId) {
                var workerName = $(`#workerSelect-${workplaceId} option[value="${workerId}"]`).text();
                addWorkerTimeFields(workplaceId, workerId, workerName);
            });
        });
    
        // Select2の初期化
        $('[id^=workerSelect-]').select2({
            placeholder: "職人を選択してください",
            allowClear: true
        });
    
        // アサインボタンのクリックイベント
        $(document).on('click', '[id^=assignButton-]', function() {
            var workplaceId = $(this).attr('id').split('-')[1];
            submitAssign(workplaceId);
        });
    });
    </script>
@stop
