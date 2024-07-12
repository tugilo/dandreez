@extends('adminlte::page')

@section('title', '現場詳細')

@section('content_header')
    <h1>{{ $workplace->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">基本情報</h3>
                </div>
                <div class="card-body">
                    <p><strong>顧客名:</strong> {{ $workplace->customer->name }}</p>
                    <p><strong>住所:</strong> {{ $workplace->address }}</p>
                    <p><strong>連絡先:</strong> {{ $workplace->customer->tel }}</p>
                    <p><strong>期間:</strong> 
                        @if($workplace->period_start && $workplace->period_end)
                            {{ $workplace->period_start->format('Y/m/d') }} - {{ $workplace->period_end->format('Y/m/d') }}
                        @else
                            未設定
                        @endif
                    </p>
                    <p><strong>作業内容:</strong> {{ $workplace->construction_outline }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">アサイン情報</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($assignments as $assignment)
                        <li class="list-group-item {{ $assignment->start_date && $assignment->start_date->isToday() ? 'bg-primary text-white' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $assignment->start_date ? $assignment->start_date->format('Y/m/d') : '日付未設定' }}</strong>
                                    <br>
                                    {{ $assignment->start_time ? $assignment->start_time->format('H:i') : '--:--' }} - 
                                    {{ $assignment->end_time ? $assignment->end_time->format('H:i') : '--:--' }}
                                </div>
                                <div>
                                    @if($assignment->start_date && !$assignment->dailyreports()->whereDate('report_day', $assignment->start_date)->exists())
                                        <a href="{{ route('worker.report.create', ['assign_id' => $assignment->id]) }}" class="btn btn-sm btn-primary">日報作成</a>
                                    @else
                                        <span class="badge badge-success">日報提出済み</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">必要な工具・材料</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @if(isset($workplace->tools) && count($workplace->tools) > 0)
                            @foreach($workplace->tools as $tool)
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="tool-{{ $tool->id }}">
                                        <label class="custom-control-label" for="tool-{{ $tool->id }}">{{ $tool->name }}</label>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li class="list-group-item">工具・材料情報なし</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">現場写真</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(isset($workplace->photos) && count($workplace->photos) > 0)
                            @foreach($workplace->photos as $photo)
                                <div class="col-6 col-md-4 mb-3">
                                    <img src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}" class="img-fluid rounded" alt="現場写真">
                                </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <p>現場写真はありません。</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .list-group-item {
        transition: all 0.3s;
    }
    .list-group-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .btn {
        font-size: 1rem;
        padding: 8px 16px;
    }
</style>
@stop