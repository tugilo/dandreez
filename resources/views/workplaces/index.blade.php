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
                                <a href="{{ route($editRoute, ['role' => $role, 'id' => $workplace->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="編集">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route($detailsRoute, ['role' => $role, 'id' => $workplace->id]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="詳細">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                @if($role === 'saler')
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="承認" data-toggle="modal" data-target="#approveModal-{{ $workplace->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="アサイン" data-toggle="modal" data-target="#assignModal-{{ $workplace->id }}">
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
                            <span class="badge {{ $workplace->status->color ?? 'secondary' }}">
                                {{ $workplace->status->name_ja ?? '未設定' }}
                            </span>
                        </td>
                        <td>{{ $workplace->created_at ? $workplace->created_at->format('Y/m/d H:i') : '未設定' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="削除" data-toggle="modal" data-target="#deleteModal-{{ $workplace->id }}">
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
                    <form action="{{ route($role . '.workplaces.approve', ['role' => $role, 'id' => $workplace->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">承認</button>
                    </form>
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
                    <h5 class="modal-title">施工依頼アサイン</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- アサインフォームの内容をここに配置 -->
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
<style>
    .btn-group .btn {
        margin-right: 5px;
    }
    .badge {
        font-size: 100%;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#workplaces-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Japanese.json"
        },
        "order": [[ 1, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 7] }
        ]
    });

    // ツールチップを有効化
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@stop