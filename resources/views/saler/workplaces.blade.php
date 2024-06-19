@extends('adminlte::page')

@section('title', '施工依頼一覧')

@section('content_header')
    <h1>施工依頼一覧</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>施工名</th>
                    <th>得意先</th>
                    <th>施工期間</th>
                    <th>ステータス</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workplaces as $workplace)
                    <tr>
                        <td>{{ $workplace->id }}</td>
                        <td>{{ $workplace->name }}</td>
                        <td>{{ $workplace->customer->name }}</td>
                        <td>{{ $workplace->construction_start }} ～ {{ $workplace->construction_end }}</td>
                        <td>{{ $workplace->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script> console.log('施工依頼一覧ページ'); </script>
@stop
