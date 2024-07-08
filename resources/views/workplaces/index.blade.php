@extends('adminlte::page')

@section('title', '施工依頼一覧')

@section('content_header')
    <h1>施工依頼一覧</h1>
@stop

@section('css')
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
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
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignModalLabel-{{ $workplace->id }}">施工依頼アサイン</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>現在のアサイン</label>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>施工会社</th>
                                        <th>職人</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($workplace->assignedWorkers as $assign)
                                        <tr>
                                            <td>{{ $assign->constructionCompany->name }}</td>
                                            <td>{{ $assign->worker->name }}</td>
                                            <td>
                                                <form action="{{ route('saler.workplaces.unassign', ['id' => $workplace->id, 'role' => $role]) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="assign_id" value="{{ $assign->id }}">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('このアサインを解除してもよろしいですか？')">解除</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">現在アサインされている職人はいません。</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <form id="assignForm-{{ $workplace->id }}" action="{{ route($role . '.workplaces.assign.store', ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="construction_company_{{ $workplace->id }}">施工会社</label>
                                <select name="construction_company_id" id="construction_company_{{ $workplace->id }}" class="form-control">
                                    @foreach($constructionCompanies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>職人</label>
                                @foreach($workers as $worker)
                                    <div class="form-check">
                                        <input class="form-check-input worker-checkbox" type="checkbox" name="worker_ids[]" value="{{ $worker->id }}" id="worker{{ $worker->id }}_{{ $workplace->id }}" data-workplace-id="{{ $workplace->id }}" data-worker-id="{{ $worker->id }}">
                                        <label class="form-check-label" for="worker{{ $worker->id }}_{{ $workplace->id }}">
                                            {{ $worker->name }}
                                        </label>
                                        <span class="overlap-warning" id="workerOverlapWarning{{ $worker->id }}_{{ $workplace->id }}" style="display:none; color: red;">
                                            (重複)
                                        </span>
                                    </div>
                                @endforeach
                            </div>

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

@section('js')
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#workplaces-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/ja.json"
                }
            });

            $('.worker-checkbox').change(function() {
                const workplaceId = $(this).data('workplace-id');
                checkOverlap(workplaceId);
            });

            function checkOverlap(workplaceId) {
                const form = $(`#assignForm-${workplaceId}`);
                const workerIds = form.find('input[name="worker_ids[]"]').map(function() {
                    return this.value;
                }).get();

                const workplace = {!! json_encode($workplaces) !!}.find(w => w.id == workplaceId);

                $.ajax({
                    url: '{{ route("workplaces.check-overlap") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        workplace_id: workplaceId,
                        worker_ids: workerIds,
                        start_date: workplace.construction_start,
                        end_date: workplace.construction_end
                    },
                    success: function(response) {
                        let hasAvailableWorker = false;
                        let hasOverlappingWorker = false;
                        const overlappingWorkerIds = response.overlappingWorkerIds || [];
                        const assignedWorkerIds = response.assignedWorkerIds || [];

                        form.find('input[name="worker_ids[]"]').each(function() {
                            const workerId = parseInt($(this).data('worker-id'));
                            const isOverlapping = overlappingWorkerIds.includes(workerId);
                            const isAssigned = assignedWorkerIds.includes(workerId);
                            const warningSpan = $(`#workerOverlapWarning${workerId}_${workplaceId}`);
                            
                            if (isAssigned) {
                                $(this).prop('disabled', false);
                                $(this).prop('checked', true);
                                warningSpan.text('(割当済)').show();
                                hasAvailableWorker = true;
                            } else if (isOverlapping) {
                                $(this).prop('disabled', true);
                                $(this).prop('checked', false);
                                warningSpan.text('(重複)').show();
                                hasOverlappingWorker = true;
                            } else {
                                $(this).prop('disabled', false);
                                warningSpan.hide();
                                hasAvailableWorker = true;
                            }
                        });

                        if (!hasAvailableWorker && hasOverlappingWorker) {
                            $(`#overlapWarning-${workplaceId}`).text('全ての未割当の職人が他の施工依頼と重複しています。').show();
                        } else if (hasOverlappingWorker) {
                            $(`#overlapWarning-${workplaceId}`).text('一部の未割当の職人が他の施工依頼と重複しています。').show();
                        } else {
                            $(`#overlapWarning-${workplaceId}`).hide();
                        }

                        updateAssignButton(workplaceId);
                    }
                });
            }

            // 各モーダルが開かれたときに重複チェックを実行
            $('.modal').on('shown.bs.modal', function () {
                const workplaceId = $(this).attr('id').split('-')[1];
                checkOverlap(workplaceId);
            });

            // チェックボックスの状態が変更されたときにアサインボタンの状態を更新
            $('.worker-checkbox').change(function() {
                const workplaceId = $(this).data('workplace-id');
                updateAssignButton(workplaceId);
            });

            function updateAssignButton(workplaceId) {
                const form = $(`#assignForm-${workplaceId}`);
                const hasCheckedWorker = form.find('input[name="worker_ids[]"]:checked:not(:disabled)').length > 0;
                $(`#assignButton-${workplaceId}`).prop('disabled', !hasCheckedWorker);
            }
        });

        function confirmApproval() {
            return confirm('本当にこの施工依頼を承認しますか？');
        }

        function confirmRejection() {
            return confirm('本当にこの施工依頼を否認しますか？');
        }
    </script>
@stop