@extends('adminlte::page')

@section('title', '施工依頼一覧')

@section('content_header')
    <h1>施工依頼一覧</h1>
@stop

@section('css')
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css' rel='stylesheet' />
    <style>
        #workplaces-table th, #workplaces-table td {
            white-space: nowrap;
            padding-top: 8px;
            padding-bottom: 8px;
            vertical-align: middle;
            line-height: 1.42857143;
        }
        .btn-icon {
            width: 30px;
            padding: 0;
        }
        .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: left;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-top: 0.5rem;
        }
        .card-body {
            overflow-x: auto;
        }
        /* モーダルのスタイル */
        .modal-dialog-scrollable {
            max-height: 90vh;
        }

        .modal-body {
            max-height: calc(90vh - 100px);
            overflow-y: auto;
        }

        .calendar-container {
            height: 500px;
            margin-bottom: 20px;
        }

        .workplace-calendar {
            height: 100%;
        }

        /* FullCalendarのスタイル調整 */
        .fc .fc-toolbar-title {
            font-size: 1.2em;
        }

        .fc .fc-button {
            font-size: 0.9em;
            padding: 0.3em 0.6em;
        }
        .construction-period {
            border: 2px solid #007bff;  // 青い枠線
            border-radius: 5px;
            opacity: 0.2;
        }

    </style>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route($createRoute, ['role' => $role]) }}" class="btn btn-primary">新規作成</a>
        </div>
        <div class="card-body">
            <table id="workplaces-table" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th class="btn-icon">編集</th>
                    <th class="btn-icon">詳細</th>
                    @if($role === 'saler')
                        <th class="btn-icon">承認</th>
                        <th class="btn-icon">アサイン</th>
                    @endif
                    <th>ID</th>
                    <th>得意先名</th>
                    <th>施工依頼名</th>
                    <th>施工期間</th>
                    <th>ステータス</th>
                    <th>作成日</th>
                    <th class="btn-icon">削除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($workplaces as $workplace)
                    <tr>
                        <td class="text-center">
                            <a href="{{ route($editRoute, ['role' => $role, 'id' => $workplace->id]) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="{{ route($detailsRoute, ['role' => $role, 'id' => $workplace->id]) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                        @if($role === 'saler')
                            <td>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal-{{ $workplace->id }}">承認</button>
                            </td>
                            <td>
                                @if($workplace->assignedWorkers->isNotEmpty())
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#assignModal-{{ $workplace->id }}">
                                        <i class="fas fa-check-circle"></i> アサイン
                                    </button>
                                @else
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#assignModal-{{ $workplace->id }}">
                                        <i class="fas fa-user-plus"></i> アサイン
                                    </button>
                                @endif
                            </td>
                        @endif
                        <td>{{ $workplace->id }}</td>
                        <td>{{ $workplace->customer->name }}</td>
                        <td>{{ $workplace->name }}</td>
                        <td>
                            @if ($workplace->construction_start && $workplace->construction_end)
                                {{ $workplace->construction_start->format('Y/m/d') }} 〜 {{ $workplace->construction_end->format('Y/m/d') }}
                            @else
                                未設定
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $workplace->status->color }} p-2" style="width: 80px; display: inline-block; text-align: center;">{{ $workplace->status->name_ja }}</span>
                        </td>
                        <td>{{ $workplace->created_at }}</td>
                        <td class="text-center">
                            <form method="POST" action="{{ route($destroyRoute, ['role' => $role, 'id' => $workplace->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @foreach ($workplaces as $workplace)
        @if($role === 'saler')
        <!-- 承認モーダル -->
        <div class="modal fade" id="approveModal-{{ $workplace->id }}" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel-{{ $workplace->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel-{{ $workplace->id }}">施工依頼の承認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>ID:</strong> {{ $workplace->id }}</p>
                        <p><strong>得意先:</strong> {{ $workplace->customer->name }}</p>
                        <p><strong>施工名:</strong> {{ $workplace->name }}</p>
                        <p><strong>施工期間:</strong> {{ $workplace->construction_start }} ～ {{ $workplace->construction_end }}</p>
                        <p><strong>施工場所:</strong> {{ $workplace->zip }} {{ $workplace->prefecture }} {{ $workplace->city }} {{ $workplace->address }} {{ $workplace->building }}</p>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route($role . '.workplaces.approve', ['role' => $role, 'id' => $workplace->id]) }}" method="POST" onsubmit="return confirmApproval()">
                            @csrf
                            <button type="submit" class="btn btn-primary">承認</button>
                        </form>
                        <form action="{{ route($role . '.workplaces.reject', ['role' => $role, 'id' => $workplace->id]) }}" method="POST" onsubmit="return confirmRejection()">
                            @csrf
                            <button type="submit" class="btn btn-danger">拒否</button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- アサインモーダル -->
        <div class="modal fade" id="assignModal-{{ $workplace->id }}" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel-{{ $workplace->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignModalLabel-{{ $workplace->id }}">施工依頼アサイン</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="worker_{{ $workplace->id }}">職人選択</label>
                            <select class="form-control worker-select" id="worker_{{ $workplace->id }}" data-workplace-id="{{ $workplace->id }}">
                                <option value="">選択してください</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="calendar-container">
                            <div id="calendar-{{ $workplace->id }}" class="workplace-calendar"></div>
                        </div>
                        <form id="assignForm-{{ $workplace->id }}" action="{{ route($role . '.workplaces.assign.store', ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="worker_id" id="selectedWorker-{{ $workplace->id }}">
                            <input type="hidden" name="selected_dates" id="selectedDates-{{ $workplace->id }}">
                            <input type="hidden" name="construction_company_id" value="{{ $constructionCompanies->first()->id }}">
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" id="assignButton-{{ $workplace->id }}">アサインする</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@stop

@section('css')
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css' rel='stylesheet' />
    <style>
        #workplaces-table th, #workplaces-table td {
            white-space: nowrap;
            padding-top: 8px;
            padding-bottom: 8px;
            vertical-align: middle;
            line-height: 1.42857143;
        }
        .btn-icon {
            width: 30px;
            padding: 0;
        }
        .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: left;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-top: 0.5rem;
        }
        .card-body {
            overflow-x: auto;
        }
        .fc-day-today {
            background-color: inherit !important;
        }
        .fc-day-selected {
            background-color: #3788d8 !important;
            opacity: 0.3;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/locales/ja.js'></script>

<script>
    // グローバル変数
    const workplaces = @json($workplaces);
    const calendars = {};
    let initialAssigns = {};
    let removedDatesGlobal = {};

    // 各ワークプレイスに対して removedDatesGlobal を初期化
    workplaces.forEach(workplace => {
        removedDatesGlobal[workplace.id] = [];
    });

    $(document).ready(function () {
        // DataTablesの初期化
        $('#workplaces-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/ja.json"
            }
        });

        // 職人選択時の処理
        $('.worker-select').change(function() {
            const workplaceId = $(this).data('workplace-id');
            const workerId = $(this).val();
            $(`#selectedWorker-${workplaceId}`).val(workerId);
            if (workerId) {
                fetchWorkerAssignments(workerId, workplaceId);
            } else {
                resetCalendar(workplaceId);
            }
        });

        // モーダルが表示されたときの処理
        $('.modal').on('shown.bs.modal', function () {
            const workplaceId = $(this).attr('id').split('-')[1];
            initializeCalendar(workplaceId);
            $(`#worker_${workplaceId}`).val('').trigger('change');
        });

        // アサインフォーム送信時の処理
        // フォーム送信時の処理
        $('form[id^="assignForm-"]').submit(function(e) {
            e.preventDefault();
            const workplaceId = $(this).attr('id').split('-')[1];
            const selectedDates = $(`#selectedDates-${workplaceId}`).val();
            const removedDates = JSON.stringify(removedDatesGlobal[workplaceId] || []);
            
            if ((!selectedDates || JSON.parse(selectedDates).length === 0) && (!removedDates || JSON.parse(removedDates).length === 0)) {
                alert('アサインする日付を選択するか、既存のアサインを解除してください。');
                return false;
            }

            // フォームにremovedDatesの値を設定
            if (!$(this).find('input[name="removed_dates"]').length) {
                $(`<input>`).attr({
                    type: 'hidden',
                    name: 'removed_dates',
                    value: removedDates
                }).appendTo(this);
            } else {
                $(this).find('input[name="removed_dates"]').val(removedDates);
            }

            console.log('Submitting form with:', {
                selectedDates: selectedDates,
                removedDates: removedDates
            });

            this.submit();
        });
    });

    // 日付をフォーマットする関数（タイムゾーン考慮版）
    function formatDate(dateString) {
        if (!dateString) return null;
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return null;
        
        // UTCからJSTへの変換（9時間追加）
        date.setHours(date.getHours() + 9);
        
        return date.toISOString().split('T')[0];
    }
    // 日付を1日進める関数（タイムゾーン考慮版）
    function addOneDay(dateString) {
        if (!dateString) return null;
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return null;
        
        // UTCからJSTへの変換（9時間追加）
        date.setHours(date.getHours() + 9);
        
        // 1日追加
        date.setDate(date.getDate() + 1);
        
        return date.toISOString().split('T')[0];
    }

    /**
     * カレンダーを初期化する関数
     * @param {string} workplaceId 施工依頼ID
     */
     function initializeCalendar(workplaceId) {
    const calendarEl = document.getElementById(`calendar-${workplaceId}`);
    if (!calendarEl) return;

    const workplace = workplaces.find(w => w.id == workplaceId);
    if (!workplace) return;

    console.log('Workplace:', workplace);
    console.log('Raw construction start:', workplace.construction_start);
    console.log('Raw construction end:', workplace.construction_end);

    const startDate = formatDate(workplace.construction_start);
    const endDate = formatDate(workplace.construction_end);

    console.log('Formatted construction period:', startDate, 'to', endDate);

    const calendarOptions = {
        initialView: 'dayGridMonth',
        locale: 'ja',
        selectable: true,
        unselectAuto: false,
        initialDate: startDate,
        validRange: {
            start: startDate,
            end: addOneDay(endDate)
        },
        selectConstraint: {
            start: startDate,
            end: addOneDay(endDate)
        },
        select: function(info) {
            handleDateSelection(info.startStr, workplaceId);
        },
        eventClick: function(info) {
            handleDateSelection(info.event.startStr, workplaceId);
        },
        events: [
            {
                title: '工期',
                start: startDate,
                end: addOneDay(endDate),
                display: 'background',
                color: '#f0f0f0',
                classNames: ['construction-period']
            }
        ]
    };

    const calendar = new FullCalendar.Calendar(calendarEl, calendarOptions);
    calendars[workplaceId] = calendar;

    // 既存のアサインを取得
    $.ajax({
        url: 'workplaces/get-existing-assigns',
        method: 'GET',
        data: {
            workplace_id: workplaceId,
            worker_id: $('#worker_' + workplaceId).val()
        },
        success: function(response) {
            console.log('Raw response from server:', response);
            if (response.success) {
                const existingAssigns = response.assigns || [];
                const selectedDates = existingAssigns.map(assign => formatDate(assign.start_date));
                
                initialAssigns[workplaceId] = selectedDates;
                removedDatesGlobal[workplaceId] = [];  // グローバル変数を初期化

                $(`#selectedDates-${workplaceId}`).val(JSON.stringify(selectedDates));
                $(`#removedDates-${workplaceId}`).val(JSON.stringify([]));

                console.log('Initializing calendar with existing assigns:', selectedDates);
                console.log('Initial assigns set:', initialAssigns[workplaceId]);
                console.log('Initial removedDates:', JSON.stringify(removedDatesGlobal[workplaceId]));

                const events = existingAssigns.map(assign => ({
                    start: formatDate(assign.start_date),
                    end: formatDate(assign.end_date),
                    display: 'background',
                    backgroundColor: '#28a745',
                    classNames: ['existing-assign'],
                    allDay: true,
                    extendedProps: { type: 'existing-assign', initialAssign: true, assignId: assign.id }
                }));

                calendar.addEventSource(events);
                console.log('Added events to calendar:', events);

                calendar.render();
                updateAssignButton(workplaceId);
            } else {
                console.error('Failed to fetch existing assigns:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching existing assigns:', error);
            console.error('XHR object:', xhr);
        }
    });
}
    /**
     * 職人のアサイン状況を取得する関数
     * @param {string} workerId 職人ID
     * @param {string} workplaceId 施工依頼ID
     */
    function fetchWorkerAssignments(workerId, workplaceId) {
        $.ajax({
            url: '{{ route("workplaces.get-worker-assignments") }}',
            method: 'GET',
            data: {
                worker_id: workerId,
                workplace_id: workplaceId
            },
            success: function(response) {
                updateCalendar(workplaceId, response.assignments);
            }
        });
    }
    
    /**
     * カレンダーを更新する関数
     * @param {string} workplaceId 施工依頼ID
     * @param {Array} assignments アサイン情報の配列
     */
    function updateCalendar(workplaceId, assignments) {
        const calendar = calendars[workplaceId];
        if (!calendar) return;

        // 工期と選択された日付のイベントを除いて全て削除
        calendar.getEvents().forEach(event => {
            if (!event.classNames.includes('construction-period') && event.backgroundColor !== '#007bff') {
                event.remove();
            }
        });

        assignments.forEach(assignment => {
            calendar.addEvent({
                start: assignment.start_date,
                end: assignment.end_date,
                display: 'background',
                color: assignment.is_current_workplace ? '#28a745' : '#dc3545',
                allDay: true
            });
        });
    }

    /**
     * 日付選択時の処理を行う関数
     * @param {string} dateStr 選択された日付
     * @param {string} workplaceId 施工依頼ID
     */

    function handleDateSelection(dateStr, workplaceId) {
        const calendar = calendars[workplaceId];
        if (!calendar) return;

        const workplace = workplaces.find(w => w.id == workplaceId);
        if (!workplace) return;

        const startDate = formatDate(workplace.construction_start);
        const endDate = formatDate(workplace.construction_end);

        console.log('Selected date:', dateStr);
        console.log('Construction period:', startDate, 'to', endDate);

        // 選択された日付が工期外の場合、処理を中断
        if (dateStr < startDate || dateStr > endDate) {
            console.log('Selected date is outside the construction period');
            return;
        }

        console.log(`Handling date selection: ${dateStr} for workplace: ${workplaceId}`);
        

        console.log('Initial assigns for this workplace:', initialAssigns[workplaceId]);

        const existingEvents = calendar.getEvents().filter(event => 
            event.startStr === dateStr && !event.classNames.includes('construction-period')
        );

        console.log('Existing events:', existingEvents.map(e => ({
            start: e.startStr,
            end: e.endStr,
            backgroundColor: e.backgroundColor,
            classNames: e.classNames,
            extendedProps: e.extendedProps
        })));

        let selectedDates = JSON.parse($(`#selectedDates-${workplaceId}`).val() || '[]');
        let removedDates = removedDatesGlobal[workplaceId] || [];

        console.log('Before processing - Selected dates:', selectedDates, 'Removed dates:', removedDates);
        const isInitialAssign = initialAssigns[workplaceId] && initialAssigns[workplaceId].includes(dateStr);
        console.log('Is initial assign:', isInitialAssign);

        const existingAssign = existingEvents.find(event => event.extendedProps.type === 'existing-assign' || event.extendedProps.initialAssign);
        const removedAssign = existingEvents.find(event => event.extendedProps.type === 'removed-assign');
        const newAssign = existingEvents.find(event => event.extendedProps.type === 'new-assign');

        console.log('Event types:', {
            existingAssign: !!existingAssign,
            removedAssign: !!removedAssign,
            newAssign: !!newAssign
        });

        if (isInitialAssign || existingAssign) {
            // 既存のアサインを解除（赤く表示）
            existingEvents.forEach(event => event.remove());
            if (!removedDates.includes(dateStr)) {
                removedDates.push(dateStr);
            }
            selectedDates = selectedDates.filter(d => d !== dateStr);
            calendar.addEvent({
                start: dateStr,
                end: dateStr,
                display: 'background',
                backgroundColor: '#dc3545',
                classNames: ['removed-assign'],
                allDay: true,
                extendedProps: { type: 'removed-assign' }
            });
            console.log('Existing or initial assign removed and marked for removal');
        } else if (removedAssign) {
            // 解除をキャンセル（元に戻す）
            existingEvents.forEach(event => event.remove());
            removedDates = removedDates.filter(d => d !== dateStr);
            if (isInitialAssign && !selectedDates.includes(dateStr)) {
                selectedDates.push(dateStr);
            }
            calendar.addEvent({
                start: dateStr,
                end: dateStr,
                display: 'background',
                backgroundColor: '#28a745',
                classNames: ['existing-assign'],
                allDay: true,
                extendedProps: { type: 'existing-assign' }
            });
            console.log('Removed assign cancelled and restored');
        } else if (newAssign) {
            // 新しく選択した日付を解除
            existingEvents.forEach(event => event.remove());
            selectedDates = selectedDates.filter(d => d !== dateStr);
            console.log('New assign removed');
        } else {
            // 新しい日付を選択
            calendar.addEvent({
                start: dateStr,
                end: dateStr,
                display: 'background',
                backgroundColor: '#007bff',
                classNames: ['new-assign'],
                allDay: true,
                extendedProps: { type: 'new-assign' }
            });
            if (!selectedDates.includes(dateStr)) {
                selectedDates.push(dateStr);
            }
            console.log('New date selected');
        }

        // 重複を除去
        selectedDates = [...new Set(selectedDates)];
        removedDates = [...new Set(removedDates)];

        $(`#selectedDates-${workplaceId}`).val(JSON.stringify(selectedDates));
        $(`#removedDates-${workplaceId}`).val(JSON.stringify(removedDates));
        removedDatesGlobal[workplaceId] = removedDates;  // グローバル変数を更新

        console.log('After processing - Selected dates:', selectedDates, 'Removed dates:', removedDates);
        console.log('Current removedDates value:', $(`#removedDates-${workplaceId}`).val());
        console.log('Current removedDatesGlobal value:', JSON.stringify(removedDatesGlobal[workplaceId]));

        updateAssignButton(workplaceId);
    }
    /**
     * カレンダーの選択状態を更新する関数
     * @param {string} workplaceId 施工依頼ID
     */
    function updateCalendarSelection(workplaceId) {
        const calendar = calendars[workplaceId];
        if (!calendar) return;

        const selectedDates = JSON.parse($(`#selectedDates-${workplaceId}`).val() || '[]');

        calendar.removeAllEvents();
        selectedDates.forEach(date => {
            calendar.addEvent({
                start: date,
                end: date,
                display: 'background',
                color: '#3788d8'
            });
        });
    }

    /**
     * アサインの重複をチェックする関数
     * @param {string} workplaceId 施工依頼ID
     */
     function checkOverlap(workplaceId) {
        const form = $(`#assignForm-${workplaceId}`);
        const workerIds = form.find('input[name="worker_ids[]"]:checked').map(function() {
            return this.value;
        }).get();
        const selectedDates = $(`#selectedDates-${workplaceId}`).val();

        if (workerIds.length === 0 || !selectedDates) {
            return;
        }

        $.ajax({
            url: '{{ route("workplaces.check-overlap") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                workplace_id: workplaceId,
                worker_ids: workerIds,
                selected_dates: selectedDates
            },
            success: function(response) {
                updateWorkerAvailability(workplaceId, response);
                updateAssignButton(workplaceId);
            }
        });
    }
    
    /**
     * 職人の利用可能状況を更新する関数
     * @param {string} workplaceId 施工依頼ID
     * @param {Object} response サーバーからのレスポンス
     */
    function updateWorkerAvailability(workplaceId, response) {
        const form = $(`#assignForm-${workplaceId}`);
        const overlappingWorkerIds = response.overlappingWorkerIds || [];
        const assignedWorkerIds = response.assignedWorkerIds || [];

        form.find('input[name="worker_ids[]"]').each(function() {
            const workerId = parseInt($(this).data('worker-id'));
            const warningSpan = $(`#workerOverlapWarning${workerId}_${workplaceId}`);
            
            if (assignedWorkerIds.includes(workerId)) {
                $(this).prop('disabled', false);
                $(this).prop('checked', true);
                warningSpan.text('(割当済)').show();
            } else if (overlappingWorkerIds.includes(workerId)) {
                $(this).prop('disabled', true);
                $(this).prop('checked', false);
                warningSpan.text('(重複)').show();
            } else {
                $(this).prop('disabled', false);
                warningSpan.hide();
            }
        });

        updateOverlapWarning(workplaceId, overlappingWorkerIds.length > 0);
    }

    /**
     * 重複警告を更新する関数
     * @param {string} workplaceId 施工依頼ID
     * @param {boolean} hasOverlap 重複があるかどうか
     */
    function updateOverlapWarning(workplaceId, hasOverlap) {
        const warningEl = $(`#overlapWarning-${workplaceId}`);
        if (hasOverlap) {
            warningEl.text('一部の職人が他の施工依頼と重複しています。').show();
        } else {
            warningEl.hide();
        }
    }

    /**
     * カレンダーをリセットする関数
     * @param {string} workplaceId 施工依頼ID
     */
    function resetCalendar(workplaceId) {
        const calendar = calendars[workplaceId];
        if (!calendar) return;

        // 工期以外のイベントをすべて削除
        calendar.getEvents().forEach(event => {
            if (!event.classNames.includes('construction-period')) {
                event.remove();
            }
        });

        $(`#selectedDates-${workplaceId}`).val('[]');
        $(`#removedDates-${workplaceId}`).val('[]');
        updateAssignButton(workplaceId);
    }

    /**
     * アサインボタンを更新する関数
     * @param {string} workplaceId 施工依頼ID
     */
    function updateAssignButton(workplaceId) {
        const hasSelectedWorker = $(`#selectedWorker-${workplaceId}`).val() !== '';
        const selectedDates = JSON.parse($(`#selectedDates-${workplaceId}`).val() || '[]');
        const removedDates = JSON.parse($(`#removedDates-${workplaceId}`).val() || '[]');
        const hasChanges = selectedDates.length > 0 || removedDates.length > 0;
        $(`#assignButton-${workplaceId}`).prop('disabled', !(hasSelectedWorker && hasChanges));

        // ボタンのテキストを変更
        const $assignButton = $(`#assignButton-${workplaceId}`);
        if (removedDates.length > 0) {
            $assignButton.text('アサインを更新');
            $assignButton.removeClass('btn-primary').addClass('btn-warning');
        } else {
            $assignButton.text('アサインする');
            $assignButton.removeClass('btn-warning').addClass('btn-primary');
        }
        console.log(`Updating assign button for workplace ${workplaceId}`);
    }

    /**
     * 承認確認のダイアログを表示する関数
     * @returns {boolean} 確認結果
     */
    function confirmApproval() {
        return confirm('本当にこの施工依頼を承認しますか？');
    }

    /**
     * 否認確認のダイアログを表示する関数
     * @returns {boolean} 確認結果
     */
    function confirmRejection() {
        return confirm('本当にこの施工依頼を否認しますか？');
    }
</script>
@stop