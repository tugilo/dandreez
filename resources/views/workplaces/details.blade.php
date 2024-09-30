@extends('adminlte::page')

@section('title', '施工依頼詳細')

@section('content_header')
    <h1>施工依頼詳細: {{ $workplace->name }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">基本情報</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> {{ $workplace->id }}</p>
                            <p><strong>得意先名:</strong> {{ $workplace->customer->name }}</p>
                            <p><strong>施工期間:</strong> {{ $workplace->construction_start->format('Y/m/d') }} ～ {{ $workplace->construction_end->format('Y/m/d') }}</p>
                            <p><strong>床面積:</strong> {{ $workplace->floor_space }} m²</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>ステータス:</strong> 
                                <span class="badge badge-{{ $workplace->status->color }} p-2">{{ $workplace->status->name_ja }}</span>
                            </p>
                            <p><strong>施工場所:</strong> {{ $workplace->prefecture }}{{ $workplace->city }}{{ $workplace->address }}{{ $workplace->building }}</p>
                            <p><strong>作成日:</strong> {{ $workplace->created_at->format('Y/m/d H:i') }}</p>
                            <p><strong>更新日:</strong> {{ $workplace->updated_at->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>
                    @if($role === 'saler')
                    <div class="text-center mt-3">
                        @if($workplace->status_id == 1 || $workplace->status_id == 2)
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">承認</button>
                            <button type="button" class="btn btn-danger" id="rejectButton" data-toggle="modal" data-target="#rejectModal">否認</button>
                        @elseif($workplace->status_id == 3)
                            <button type="button" class="btn btn-success active" disabled>承認済み</button>
                        @elseif($workplace->status_id == 4)
                            <button type="button" class="btn btn-danger active" disabled>否認済み</button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">施工指示</h3>
                </div>
                <div class="card-body">
                    @include('workplaces.partials.instructions_tab', ['workplace' => $workplace, 'instructions' => $instructions, 'units' => $units])
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">現場地図</h3>
                </div>
                <div class="card-body p-0">
                    <div class="map-container">
                        <iframe 
                            width="100%" 
                            height="300" 
                            frameborder="0" 
                            scrolling="no" 
                            marginheight="0" 
                            marginwidth="0" 
                            src="https://maps.google.co.jp/maps?f=q&amp;source=s_q&amp;aq=&amp;ie=UTF8&amp;output=embed&amp;iwloc=b&amp;zoom=15&amp;view=text&amp;q={{ urlencode($workplace->prefecture . ' ' . $workplace->city . ' ' . $workplace->address . ' ' . $workplace->building) }}">
                        </iframe>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">写真・ファイル</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="photo-tab" data-toggle="tab" href="#photos" role="tab">写真</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="file-tab" data-toggle="tab" href="#files" role="tab">ファイル</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="photos" role="tabpanel">
                            @include('workplaces.partials.photos_tab', ['workplace' => $workplace, 'photos' => $photos])
                        </div>
                        <div class="tab-pane fade" id="files" role="tabpanel">
                            @include('workplaces.partials.files_tab', ['workplace' => $workplace, 'files' => $files])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">アサイン情報</h3>
        </div>
        <div class="card-body">
            @include('workplaces.partials.assigns_tab', ['workplace' => $workplace])
        </div>
    </div>
</div>


@if($role === 'saler')
    @include('workplaces.partials.approve_reject_modals', ['workplace' => $workplace])
@endif


@stop

@section('css')
    <!-- Bootstrap Datepicker CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .badge {
            font-size: 100%;
        }
        .table-scrollable {
            max-height: 300px;
            overflow-y: auto;
        }
        .camera-icon {
            cursor: pointer;
            width: 200px;
            height: 200px;
        }
        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .file-icon {
            display: inline-block;
            width: 100px;
            height: 100px;
            margin: 10px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .file-icon img {
            width: 50px;
            height: 50px;
        }
        .file-title {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .map-container {
            position: relative;
            padding-bottom: 75%;
            height: 0;
            overflow: hidden;
        }
        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #007bff;
        }
    </style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
<script>
    $(function() {
        // タブの制御
        $('#myTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        var activeTab = localStorage.getItem('activeDetailTab');
        if(activeTab){
            $('#myTab a[href="' + activeTab + '"]').tab('show');
        }

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('activeDetailTab', $(e.target).attr('href'));
        });

        // DatepickerとTimepickerの初期化
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            language: 'ja'
        });

        $('.timepicker').timepicker({
            timeFormat: 'HH:mm',
            interval: 30,
            minTime: '00:00',
            maxTime: '23:30',
            defaultTime: '09:00',
            startTime: '00:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
        });

        // 承認・否認ボタンの制御
        var hasAssigns = {{ $workplace->assigns->count() > 0 ? 'true' : 'false' }};
        if (hasAssigns) {
            $('#rejectModal').modal('hide');
            $('#rejectButton').prop('disabled', true).attr('title', 'アサインが完了しているため否認できません');
        }
    });
</script>
@stop