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
                    <th>ID</th>
                    <th>得意先名</th>
                    <th>施工依頼名</th>
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
                        <td>{{ $workplace->id }}</td>
                        <td>{{ $workplace->customer->name }}</td>
                        <td>{{ $workplace->name }}</td>
                        <td>{{ $workplace->status->name_ja }}</td>
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
        });
    </script>
@stop
