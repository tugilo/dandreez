@extends('adminlte::page')

@section('title', 'ステータス一覧')

@section('css')
    <!-- DataTablesのCSSを読み込み -->
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        #statuses-table th, #statuses-table td,
        #deleted-statuses-table th, #deleted-statuses-table td {
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
        .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>
@stop

@section('content_header')
    <h1>ステータス一覧</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('statuses.create') }}" class="btn btn-primary">新規作成</a>
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
                <table id="statuses-table" class="table table-bordered table-hover mt-3">
                    <thead>
                        <tr>
                            <th class="btn-icon">編集</th>
                            <th>ID</th>
                            <th>名前</th>
                            <th>説明</th>
                            <th>日本語表記</th>
                            <th>表示順</th>
                            <th>表示フラグ</th>
                            <th class="btn-icon">削除</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statuses as $status)
                        <tr>
                            <td>
                                <a href="{{ route('statuses.edit', $status->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            </td>
                            <td>{{ $status->id }}</td>
                            <td>{{ $status->name }}</td>
                            <td>{{ $status->description }}</td>
                            <td>{{ $status->name_ja }}</td>
                            <td>{{ $status->sort_order }}</td>
                            <td>{{ $status->show_flg ? '表示' : '非表示' }}</td>
                            <td>
                                <form action="{{ route('statuses.destroy', $status->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('本当に削除しますか？')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="deleted" role="tabpanel" aria-labelledby="deleted-tab">
                <table id="deleted-statuses-table" class="table table-bordered table-hover mt-3">
                    <thead>
                        <tr>
                            <th class="btn-icon">復活</th>
                            <th>ID</th>
                            <th>名前</th>
                            <th>説明</th>
                            <th>日本語表記</th>
                            <th>表示順</th>
                            <th>表示フラグ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deletedStatuses as $status)
                        <tr>
                            <td>
                                <form action="{{ route('statuses.restore', $status->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('本当に復活しますか？')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                            </td>
                            <td>{{ $status->id }}</td>
                            <td>{{ $status->name }}</td>
                            <td>{{ $status->description }}</td>
                            <td>{{ $status->name_ja }}</td>
                            <td>{{ $status->sort_order }}</td>
                            <td>{{ $status->show_flg ? '表示' : '非表示' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
    <!-- DataTablesのJSを読み込み -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#statuses-table').DataTable();
            $('#deleted-statuses-table').DataTable();
        });
    </script>
@stop
