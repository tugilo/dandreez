@extends('adminlte::page')

@section('title', '通知内容一覧')

@section('content_header')
    <h1>通知内容一覧</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('notification_contents.create') }}" class="btn btn-primary">新規作成</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="notification-contents-table">
                <thead>
                    <tr>
                        <th>編集</th>
                        <th>ID</th>
                        <th>コード</th>
                        <th>メッセージ</th>
                        <th>表示フラグ</th>
                        <th>作成日</th>
                        <th>更新日</th>
                        <th>削除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notificationContents as $content)
                        <tr>
                            <td class="text-nowrap">
                                <a href="{{ route('notification_contents.edit', $content->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            </td>
                            <td>{{ $content->id }}</td>
                            <td>{{ $content->code }}</td>
                            <td>{{ $content->message }}</td>
                            <td>{{ $content->show_flg ? '表示' : '非表示' }}</td>
                            <td>{{ $content->created_at }}</td>
                            <td>{{ $content->updated_at }}</td>
                            <td class="text-nowrap">
                                <form action="{{ route('notification_contents.destroy', $content->id) }}" method="POST" style="display: inline;">
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

@section('css')
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        #notification-contents-table th, #notification-contents-table td {
            white-space: nowrap;  /* テキストが折り返さないように設定 */
            padding-top: 8px;  /* 上のパディングを調整 */
            padding-bottom: 8px;  /* 下のパディングを調整 */
            vertical-align: middle;  /* 垂直方向の配置を中央に設定 */
            line-height: 1.42857143;  /* 標準の行高でBootstrapのテーブルと一致させる */
        }
        .btn-icon {
            width: 30px;  /* アイコンボタンの幅を30pxに固定 */
            padding: 0;  /* アイコンボタン内のパディングを削除 */
        }
        .dataTables_wrapper .dataTables_filter {
            float: none;  /* フィルターボックスの位置調整 */
            text-align: left;  /* テキストの位置を左揃えに設定 */
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-top: 0.5rem;  /* マージントップを0.5remに設定 */
        }
        .card-body {
            overflow-x: auto;  /* コンテンツが幅を超えた場合にスクロールを可能にする */
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#notification-contents-table').DataTable({
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
@stop
