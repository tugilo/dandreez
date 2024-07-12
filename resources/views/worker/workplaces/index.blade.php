@extends('adminlte::page')

@section('title', '現場一覧')

@section('content_header')
    <h1>現場一覧</h1>
@stop

@section('content')
    <div class="row">
        @foreach($workplaces as $workplace)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card h-100 {{ $workplace->assigns->contains('start_date', today()) ? 'border-primary' : '' }}">
                    <div class="card-header {{ $workplace->assigns->contains('start_date', today()) ? 'bg-primary text-white' : '' }}">
                        <h5 class="mb-0">{{ $workplace->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>顧客:</strong> {{ $workplace->customer->name }}</p>
                        <p><strong>期間:</strong> {{ $workplace->period_start->format('Y/m/d') }} - {{ $workplace->period_end->format('Y/m/d') }}</p>
                        <p><strong>次回予定:</strong> 
                            @if($workplace->assigns->isNotEmpty())
                                {{ $workplace->assigns->first()->start_date->format('Y/m/d') }}
                            @else
                                未定
                            @endif
                        </p>
                        <p><strong>作業内容:</strong> {{ Str::limit($workplace->construction_outline, 50) }}</p>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('worker.workplaces.show', $workplace->id) }}" class="btn btn-info btn-block">詳細</a>
                        @if($workplace->assigns->isNotEmpty() && !$workplace->assigns->first()->dailyreports()->whereDate('report_day', $workplace->assigns->first()->start_date)->exists())
                            <a href="{{ route('worker.report.create', ['workplace_id' => $workplace->id]) }}" class="btn btn-primary btn-block mt-2">日報作成</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop

@section('css')
<style>
    .card {
        transition: all 0.3s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .btn {
        font-size: 1.1rem;
        padding: 10px;
    }
</style>
@stop