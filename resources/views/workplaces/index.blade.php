@extends('adminlte::page')

@section('title', '施工依頼一覧')
@section('css')
    <!-- DataTablesのCSSを読み込み -->
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        #workplaces-table th, #users-table td {
            white-space: nowrap;  // テキストが折り返さないように設定
            padding-top: 8px;  // 上のパディングを調整
            padding-bottom: 8px;  // 下のパディングを調整
            vertical-align: middle;  // 垂直方向の配置を中央に設定
            line-height: 1.42857143;  // 標準の行高でBootstrapのテーブルと一致させる
        }
        .btn-icon {
            white-space: nowrap;  // テキストが折り返さないように設定
            width: 30px;  // アイコンボタンの幅を30pxに固定
            padding: 0;  // アイコンボタン内のパディングを削除
        }
        .dataTables_wrapper .dataTables_filter {
            float: none;  // フィルターボックスの位置調整
            text-align: left;  // テキストの位置を左揃えに設定
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-top: 0.5rem;  // マージントップを0.5remに設定
        }
        .card-body {
            overflow-x: auto;  // コンテンツが幅を超えた場合にスクロールを可能にする
        }
    </style>
@stop

@section('content')
<div class="container">
    <h1>施工依頼一覧</h1>
    <table id="workplaces-table" class="table table-bordered">
        <thead>
            <tr>
                <th class="btn-icon">編集</th>
                <th>ID</th>
                <th>得意先</th>
                <th>施工名</th>
                <th>施工開始日</th>
                <th>施工終了日</th>
                <th class="btn-icon">指示</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workplaces as $workplace)
                <tr>
                    <td>
                        <a href="{{ route('workplaces.edit', $workplace->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </td>
                    <td>{{ $workplace->id }}</td>
                    <td>{{ $workplace->customer->name }}</td>
                    <td>{{ $workplace->name }}</td>
                    <td>{{ $workplace->construction_start }}</td>
                    <td>{{ $workplace->construction_end }}</td>
                    <td>
                        <a href="{{ route('instructions.create', $workplace->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-hand-point-up"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
@endsection

@section('js')
    <!-- DataTablesのJavaScriptを読み込み -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#workplaces-table').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/ja.json"
                },
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                scrollX: true,
                pagingType: "simple",
                dom: 'Bfrtip',
                buttons: []
            });
        });
    </script>
</script>
@endsection
