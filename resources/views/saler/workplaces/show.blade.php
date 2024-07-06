@extends('adminlte::page')

@section('title', '施工依頼詳細設定')

@section('content_header')
    <h1>施工依頼詳細設定</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4">
            <!-- 施工依頼情報の表示 -->
            <h5>施工依頼情報</h5>
            <p><strong>ID:</strong> {{ $workplace->id }}</p>
            <p><strong>得意先:</strong> {{ $workplace->customer->name }}</p>
            <p><strong>施工名:</strong> {{ $workplace->name }}</p>
            <p><strong>施工期間:</strong> {{ $workplace->construction_start }} ～ {{ $workplace->construction_end }}</p>
        </div>

        @include('workplaces.partials.details_tabs', [
            'instructions' => $instructions,
            'photos' => $photos,
            'files' => $files,
            'units' => $units,
            'workplace' => $workplace
        ])
    </div>
</div>
@stop
