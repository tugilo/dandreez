@extends('adminlte::page')

@section('title', 'ユーザー一覧')

@section('content_header')
    <h1><i class="fas fa-users"></i> ユーザー一覧</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">登録ユーザー</h3>
            <div class="card-tools">
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus"></i> 新規ユーザー追加
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="users-table">
                    <thead>
                        <tr>
                            <th class="btn-icon">編集</th>
                            <th>ユーザー種別</th>
                            <th>名前</th>
                            <th>メールアドレス</th>
                            <th>ログインID</th>
                            <th>ロール名</th>
                            <th>会社名</th>
                            <th>作成日</th>
                            <th>更新日</th>
                            <th class="btn-icon">削除</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <a href="{{ route('users.edit', $user['id']) }}" class="btn btn-info btn-sm btn-icon">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                                <td>{{ $user['userType'] }}</td>
                                <td>{{ $user['name'] }}</td>
                                <td>{{ $user['email'] }}</td>
                                <td>{{ $user['login_id'] }}</td>
                                <td>{{ $user['roleName'] }}</td>
                                <td>{{ $user['companyName'] }}</td>
                                <td>{{ $user['createdAt'] }}</td>
                                <td>{{ $user['updatedAt'] }}</td>
                                <td>
                                    <form action="{{ route('users.destroy', $user['id']) }}" method="POST" style="display: inline;">
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <style>
        #users-table th, #users-table td {
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

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Japanese.json'
                },
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                scrollX: true,
                pagingType: "simple",
                columnDefs: [
                    { orderable: false, targets: [0, 9] }
                ],
                order: [[7, 'desc']]
            });
        });
    </script>
@stop