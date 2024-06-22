@extends('adminlte::page')

@section('title', '施工依頼一覧')

@section('css')
    <!-- DataTablesのCSSを読み込み -->
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

@section('content_header')
    <h1>施工依頼一覧</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('saler.workplaces.create') }}" class="btn btn-primary">新規作成</a>
    </div>
    <div class="card-body">
        <table id="workplaces-table" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="btn-icon">編集</th>
                    <th>ID</th>
                    <th>得意先名</th>
                    <th>施工依頼名</th>
                    <th>ステータス</th>
                    <th>作成日</th>
                    <th class="btn-icon">削除</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workplaces as $workplace)
                <tr>
                    <td>
                        <a href="{{ route('saler.workplaces.edit', $workplace->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </td>
                    <td>{{ $workplace->id }}</td>
                    <td>{{ $workplace->customer->name }}</td>
                    <td>{{ $workplace->name }}</td>
                    <td>{{ optional($workplace->status)->name_ja ?? 'ステータスなし' }}</td>
                    <td>{{ $workplace->created_at->format('Y-m-d') }}</td>
                    <td>
                        <form action="{{ route('saler.workplaces.destroy', $workplace->id) }}" method="POST" style="display:inline;">
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
</div>
@stop

@section('js')
    <!-- DataTablesのJSを読み込み -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#workplaces-table').DataTable();
        });
    </script>
@stop
