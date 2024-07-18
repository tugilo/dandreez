@extends('adminlte::page')

@section('title', 'ステータス一覧')

@section('content_header')
    <h1>
        <i class="fas fa-tasks"></i> ステータス一覧
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">登録済みステータス</h3>
            <div class="card-tools">
                <a href="{{ route('statuses.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> 新規ステータス追加
                </a>
            </div>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="statusTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab" data-toggle="tab" href="#active" role="tab" aria-controls="active" aria-selected="true">アクティブ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="deleted-tab" data-toggle="tab" href="#deleted" role="tab" aria-controls="deleted" aria-selected="false">削除済み</a>
                </li>
            </ul>
            <div class="tab-content" id="statusTabContent">
                <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                    <div class="table-responsive">
                        <table id="statuses-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="col-edit">編集</th>
                                    <th class="col-id">ID</th>
                                    <th class="col-name">名前</th>
                                    <th class="col-description">説明</th>
                                    <th class="col-name-ja">日本語表記</th>
                                    <th class="col-color">色</th>
                                    <th class="col-sort">表示順</th>
                                    <th class="col-flag">表示フラグ</th>
                                    <th class="col-delete">削除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statuses as $status)
                                <tr>
                                    <td class="col-edit">
                                        <a href="{{ route('statuses.edit', $status->id) }}" class="btn btn-info btn-sm btn-icon">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                    <td class="col-id">{{ $status->id }}</td>
                                    <td class="col-name">{{ $status->name }}</td>
                                    <td class="col-description">{{ $status->description }}</td>
                                    <td class="col-name-ja">{{ $status->name_ja }}</td>
                                    <td class="col-color">
                                        <span class="badge {{ $status->color }} status-badge">{{ $status->name_ja }}</span>
                                    </td>
                                    <td class="col-sort">{{ $status->sort_order }}</td>
                                    <td class="col-flag">{!! $status->show_flg ? '<span class="badge badge-success">表示</span>' : '<span class="badge badge-danger">非表示</span>' !!}</td>
                                    <td class="col-delete">
                                        <form action="{{ route('statuses.destroy', $status->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('本当に削除しますか？')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="deleted" role="tabpanel" aria-labelledby="deleted-tab">
                    <div class="table-responsive">
                        <table id="deleted-statuses-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="col-restore">復活</th>
                                    <th class="col-id">ID</th>
                                    <th class="col-name">名前</th>
                                    <th class="col-description">説明</th>
                                    <th class="col-name-ja">日本語表記</th>
                                    <th class="col-color">色</th>
                                    <th class="col-sort">表示順</th>
                                    <th class="col-flag">表示フラグ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deletedStatuses as $status)
                                <tr>
                                    <td class="col-restore">
                                        <form action="{{ route('statuses.restore', $status->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" onclick="return confirm('本当に復活しますか？')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="col-id">{{ $status->id }}</td>
                                    <td class="col-name">{{ $status->name }}</td>
                                    <td class="col-description">{{ $status->description }}</td>
                                    <td class="col-name-ja">{{ $status->name_ja }}</td>
                                    <td class="col-color">
                                        <span class="badge {{ $status->color }} status-badge">{{ $status->name_ja }}</span>
                                    </td>
                                    <td class="col-sort">{{ $status->sort_order }}</td>
                                    <td class="col-flag">{!! $status->show_flg ? '<span class="badge badge-success">表示</span>' : '<span class="badge badge-danger">非表示</span>' !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            width: 100%;
            table-layout: fixed;
        }
        .col-edit, .col-delete, .col-restore { width: 60px; }
        .col-id { width: 60px; }
        .col-name, .col-name-ja { width: 120px; }
        .col-description { width: 200px; }
        .col-color { width: 100px; }
        .col-sort { width: 80px; }
        .col-flag { width: 100px; }
        .status-badge {
            display: inline-block;
            width: 80px;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        th, td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .btn-icon {
            width: 30px;
            padding: 0;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#statuses-table, #deleted-statuses-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Japanese.json'
                },
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                scrollX: true,
                pagingType: "simple",
                columnDefs: [
                    { orderable: false, targets: [0, 8] }
                ],
                order: [[1, 'asc']]
            });
        });
    </script>
@stop