@extends('adminlte::page')

@section('title', '取引先会社一覧')


@section('content_header')
    <h1>取引先会社一覧</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">登録済み得意先会社</h3>
            <div class="card-tools">
                <a href="{{ route('customer_companies.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> 新規得意先会社追加
                </a>
                <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus"></i> 新規ユーザー登録
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="customer-companies-table">
                    <thead>
                        <tr>
                            <th class="btn-icon">編集</th>
                            <th>ID</th>
                            <th>会社名</th>
                            <th>電話番号</th>
                            <th>メールアドレス</th>
                            <th>作成日</th>
                            <th>更新日</th>
                            <th class="btn-icon">削除</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customerCompanies as $company)
                            <tr>
                                <td>
                                    <a href="{{ route('customer_companies.edit', $company->id) }}" class="btn btn-info btn-sm btn-icon">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                                <td>{{ $company->id }}</td>
                                <td>{{ $company->name }}</td>
                                <td>{{ $company->tel }}</td>
                                <td>{{ $company->email }}</td>
                                <td>{{ $company->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $company->updated_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <form action="{{ route('customer_companies.destroy', $company->id) }}" method="POST" style="display: inline;">
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
    </div>
@stop
@section('css')
    <!-- DataTablesのCSSを読み込み -->
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        #customer-companies-table th, #customer-companies-table td {
            white-space: nowrap;  // テキストが折り返さないように設定
            padding-top: 8px;  // 上のパディングを調整
            padding-bottom: 8px;  // 下のパディングを調整
            vertical-align: middle;  // 垂直方向の配置を中央に設定
            line-height: 1.42857143;  // 標準の行高でBootstrapのテーブルと一致させる
        }
        .btn-icon {
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

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#customer-companies-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Japanese.json'
                },
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                scrollX: true,
                pagingType: "simple",
                columnDefs: [
                    { orderable: false, targets: [6, 7] }
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@stop