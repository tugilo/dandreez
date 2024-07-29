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
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">施工依頼アサイン</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="assignForm-{{ $workplace->id }}" action="{{ route($role . '.workplaces.assign.store', ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                        <!-- 職人選択フォーム -->
                        <div class="form-group">
                            <label for="worker_{{ $workplace->id }}">職人選択</label>
                            <select class="form-control worker-select" id="worker_{{ $workplace->id }}" name="worker_id" data-workplace-id="{{ $workplace->id }}">
                                <option value="">選択してください</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- 開始時間入力フォーム -->
                        <div class="form-group">
                            <label for="start_time_{{ $workplace->id }}">開始時間</label>
                            <input type="time" class="form-control" id="start_time_{{ $workplace->id }}" name="start_time">
                        </div>
                        <!-- 終了時間入力フォーム -->
                        <div class="form-group">
                            <label for="end_time_{{ $workplace->id }}">終了時間</label>
                            <input type="time" class="form-control" id="end_time_{{ $workplace->id }}" name="end_time">
                        </div>
                        <!-- カレンダー表示エリア -->
                        <div class="calendar-container">
                            <div id="calendar-{{ $workplace->id }}" class="workplace-calendar"></div>
                        </div>
                        <!-- 選択された日付を格納する隠しフィールド -->
                        <input type="hidden" name="selected_dates" id="selectedDates-{{ $workplace->id }}">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                    <button type="submit" form="assignForm-{{ $workplace->id }}" class="btn btn-primary" id="assignButton-{{ $workplace->id }}">アサインする</button>
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css"/>
<style>
    /* ボタングループのスタイル */
    .btn-group .btn {
        margin-right: 5px;
    }
    /* バッジのスタイル */
    .badge {
        font-size: 100%;
    }
    /* カレンダーコンテナのスタイル */
    .calendar-container {
        height: 500px;
        margin-bottom: 20px;
    }
    /* カレンダーのスタイル */
    .workplace-calendar {
        height: 100%;
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
    .construction-period-label {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        text-align: center;
        color: #ff0000;
        font-size: 0.8em;
        padding: 2px;
        pointer-events: none;
    }

</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/ja.js"></script>
<script>
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
    
        // 各カレンダーの初期化
        $('.workplace-calendar').each(function() {
            var workplaceId = $(this).attr('id').split('-')[1];
            var workplace = @json($workplaces).find(w => w.id == workplaceId);
            var calendar = $(this);
            var assigns = @json($assigns)[workplaceId] || [];

            calendar.fullCalendar({
                locale: 'ja',
                defaultView: 'month',
                selectable: true,
                unselectAuto: false,
                timeZone: 'local', // ローカルタイムゾーンを使用
                events: [
                    // 施工期間を背景色で表示
                    {
                        start: moment(workplace.construction_start).startOf('day'),
                        end: moment(workplace.construction_end).endOf('day'),
                        rendering: 'background',
                        color: '#ffcccc'
                    },
                    // 施工期間のラベルを表示（各日に表示）
                    {
                        start: moment(workplace.construction_start).startOf('day'),
                        end: moment(workplace.construction_end).endOf('day'),
                        title: '施工期間',
                        allDay: true,
                        textColor: '#ff0000',
                        backgroundColor: 'transparent',
                        borderColor: 'transparent',
                        rendering: 'background',
                        overlap: false
                    },
                    // 既存のアサインを表示
                    ...assigns.map(assign => ({
                        title: assign.worker.name,
                        start: moment(assign.start_date).format('YYYY-MM-DD') + 'T' + assign.start_time,
                        end: moment(assign.start_date).format('YYYY-MM-DD') + 'T' + assign.end_time,
                        color: '#3788d8'
                    }))
                ],
                // 施工期間外の選択を制限
                selectConstraint: {
                    start: moment(workplace.construction_start).startOf('day'),
                    end: moment(workplace.construction_end).endOf('day').add(1, 'day')
                },
                select: function(start, end) {
                    handleDateSelection(start, end, workplaceId);
                },
                eventClick: function(calEvent, jsEvent, view) {
                    handleEventClick(calEvent, jsEvent, view, workplaceId);
                },
                eventRender: function(event, element) {
                    if (event.rendering !== 'background') {
                        element.css('cursor', 'pointer');
                        element.attr('title', event.title);
                    }
                }
            });

            // 既存のアサインを取得して表示
            getExistingAssigns(workplaceId, null, calendar);
        });

        // 職人選択時のイベントハンドラ
        $('.worker-select').change(function() {
            var workplaceId = $(this).data('workplace-id');
            var workerId = $(this).val();
            var calendar = $('#calendar-' + workplaceId);
            
            // カレンダーをリセット
            calendar.fullCalendar('removeEvents', function(event) {
                return event.type === 'assign';
            });
            
            // 選択した日付をリセット
            $('#selectedDates-' + workplaceId).val('[]');
            
            if (workerId) {
                updateCalendarEvents(workplaceId, workerId);
            }
        });
    });
    
    // カレンダーイベントを更新する関数
    function updateCalendarEvents(workplaceId, workerId) {
        var calendar = $('#calendar-' + workplaceId);
        var workplace = @json($workplaces).find(w => w.id == workplaceId);
        var assigns = @json($assigns)[workplaceId] || [];
    
        calendar.fullCalendar('removeEvents');
        calendar.fullCalendar('addEventSource', [
            {
                start: workplace.construction_start,
                end: workplace.construction_end,
                rendering: 'background',
                color: '#ffcccc'
            }
        ]);
    
        if (workerId) {
            var workerAssigns = assigns.filter(a => a.worker_id == workerId);
            calendar.fullCalendar('addEventSource', workerAssigns.map(assign => ({
                title: '既存のアサイン',
                start: assign.start_date,
                end: assign.end_date,
                color: '#3788d8'
            })));
        }
    
        var otherAssigns = assigns.filter(a => a.worker_id != workerId);
        calendar.fullCalendar('addEventSource', otherAssigns.map(assign => ({
            title: '他の職人のアサイン',
            start: assign.start_date,
            end: assign.end_date,
            color: '#28a745'
        })));
    }
    
    // 日付選択時のハンドラ
    function handleDateSelection(start, end, workplaceId) {
        var workerId = $('#worker_' + workplaceId).val();
        var startTime = $('#start_time_' + workplaceId).val();
        var endTime = $('#end_time_' + workplaceId).val();
        
        if (!workerId) {
            alert('職人を選択してください。');
            $('#calendar-' + workplaceId).fullCalendar('unselect');
            return;
        }
        
        if (!startTime || !endTime) {
            alert('開始時間と終了時間を設定してください。');
            $('#calendar-' + workplaceId).fullCalendar('unselect');
            return;
        }
        
        // ここで重複チェックのAjax呼び出しを行う
        $.ajax({
            url: '{{ route("workplaces.check-overlap") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                worker_id: workerId,
                start_date: start.format('YYYY-MM-DD'),
                end_date: end.format('YYYY-MM-DD'),
                start_time: startTime,
                end_time: endTime,
                workplace_id: workplaceId
            },
            success: function(response) {
                if (response.overlap) {
                    alert('選択された期間は他の現場とアサインが重複しています。');
                    $('#calendar-' + workplaceId).fullCalendar('unselect');
                } else {
                    // イベントを追加
                    $('#calendar-' + workplaceId).fullCalendar('renderEvent', {
                        title: '職人アサイン',
                        start: start.format('YYYY-MM-DD') + 'T' + startTime,
                        end: start.format('YYYY-MM-DD') + 'T' + endTime,
                        type: 'assign'
                    }, true);
                    
                    // 選択した日付を保存
                    var selectedDates = JSON.parse($('#selectedDates-' + workplaceId).val() || '[]');
                    selectedDates.push({
                        date: start.format('YYYY-MM-DD'),
                        start_time: startTime,
                        end_time: endTime
                    });
                    $('#selectedDates-' + workplaceId).val(JSON.stringify(selectedDates));
                }
            }
        });
    }    
    // イベントクリック時のハンドラ
    function handleEventClick(calEvent, jsEvent, view, workplaceId) {
        if (calEvent.type === 'assign') {
            if (confirm('このアサインを削除しますか？')) {
                $('#calendar-' + workplaceId).fullCalendar('removeEvents', calEvent._id);
                
                // 選択した日付から削除
                var selectedDates = JSON.parse($('#selectedDates-' + workplaceId).val() || '[]');
                selectedDates = selectedDates.filter(function(date) {
                    return date.date !== calEvent.start.format('YYYY-MM-DD');
                });
                $('#selectedDates-' + workplaceId).val(JSON.stringify(selectedDates));
            }
        }
    }
    
    // アサインモーダルを開く関数
    function openAssignModal(workplaceId) {
        $('#assignModal-' + workplaceId).modal('show');
    }
    
    // 承認モーダルを開く関数
    function openApproveModal(workplaceId) {
        $('#approveModal-' + workplaceId).modal('show');
    }
    
    // 削除モーダルを開く関数
    function openDeleteModal(workplaceId) {
        $('#deleteModal-' + workplaceId).modal('show');
    }
    
    // 既存のアサインを取得する関数
    function getExistingAssigns(workplaceId, workerId, calendar) {
        $.ajax({
            url: '{{ route("workplaces.get-existing-assigns") }}',
            method: 'GET',
            data: {
                workplace_id: workplaceId,
                worker_id: workerId
            },
            success: function(response) {
                if (response.success) {
                    response.assigns.forEach(function(assign) {
                        calendar.fullCalendar('renderEvent', {
                            id: assign.id,
                            title: '職人アサイン',
                            start: assign.date + 'T' + assign.start_time,
                            end: assign.date + 'T' + assign.end_time,
                            type: 'assign'
                        }, true);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching existing assigns:', error);
            }
        });
    }
    </script>
@stop
