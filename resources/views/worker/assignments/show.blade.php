@extends('adminlte::page')

@section('title', 'アサイン詳細')

@section('content_header')
    <h1>アサイン詳細</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <h2>{{ $assign->workplace->name }}</h2>
            <p><strong>日時:</strong> {{ $assign->start_date->format('Y/m/d H:i') }} - {{ $assign->end_date->format('Y/m/d H:i') }}</p>
            <p><strong>住所:</strong> {{ $assign->workplace->address }}</p>
            
            @if($assign->workplace->instructions->isNotEmpty())
                <h3>作業内容:</h3>
                <ul>
                    @foreach($assign->workplace->instructions as $instruction)
                        <li>{{ $instruction->construction_location }}: {{ $instruction->product_name }}</li>
                    @endforeach
                </ul>
            @endif

            @if($assign->workplace->files->isNotEmpty())
                <h3>関連ファイル:</h3>
                <ul>
                    @foreach($assign->workplace->files as $file)
                        <li><a href="{{ route('worker.file.download', $file->id) }}">{{ $file->title }}</a></li>
                    @endforeach
                </ul>
            @endif

            @if($assign->workplace->photos->isNotEmpty())
                <h3>現場写真:</h3>
                <div class="row">
                    @foreach($assign->workplace->photos as $photo)
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <img src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}" class="img-fluid" alt="{{ $photo->title }}">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@stop