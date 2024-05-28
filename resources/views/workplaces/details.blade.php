@extends('adminlte::page')

@section('title', '施工依頼詳細設定')

@section('content_header')
    <h1>施工依頼詳細設定</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-4">
            <h5>施工依頼情報</h5>
            <p><strong>得意先:</strong> {{ $workplace->customer->name }}</p>
            <p><strong>施工名:</strong> {{ $workplace->name }}</p>
            <p><strong>施工期間:</strong> {{ $workplace->construction_start }} 〜 {{ $workplace->construction_end }}</p>
        </div>
        <ul class="nav nav-tabs" id="detailTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="instructions-tab" data-toggle="tab" href="#instructions" role="tab" aria-controls="instructions" aria-selected="true">指示内容</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab" aria-controls="photos" aria-selected="false">写真</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="files-tab" data-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false">添付書類</a>
            </li>
        </ul>
        <div class="tab-content mt-4" id="detailTabsContent">
            <div class="tab-pane fade show active" id="instructions" role="tabpanel" aria-labelledby="instructions-tab">
                <!-- 指示内容を追加するフォーム -->
                <form action="{{ route('instructions.store', ['id' => $workplace->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                    <div id="instruction-forms">
                        @for ($i = 0; $i < 10; $i++)
                            <div class="form-group row">
                                <div class="col-1 text-right">
                                    <span>{{ $i + 1 }}</span>
                                </div>
                                <div class="col-2">
                                    <input type="text" name="instructions[{{ $i }}][construction_location]" placeholder="施工場所" class="form-control mb-2">
                                </div>
                                <div class="col-2">
                                    <input type="text" name="instructions[{{ $i }}][construction_location_detail]" placeholder="施工場所詳細" class="form-control mb-2">
                                </div>
                                <div class="col-2">
                                    <input type="text" name="instructions[{{ $i }}][product_name]" placeholder="製品名" class="form-control mb-2">
                                </div>
                                <div class="col-2">
                                    <input type="text" name="instructions[{{ $i }}][product_number]" placeholder="製品番号" class="form-control mb-2">
                                </div>
                                <div class="col-1">
                                    <input type="text" name="instructions[{{ $i }}][amount]" placeholder="数量" class="form-control mb-2">
                                </div>
                                <div class="col-2">
                                    <select name="instructions[{{ $i }}][unit_id]" class="form-control mb-2 unit-select">
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" id="add-instruction-form" class="btn btn-secondary">フォームを追加</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
                <!-- 既存の指示内容を表示 -->
                <div class="mt-4">
                    <h5>既存の指示内容</h5>
                    <ul class="list-group">
                        @foreach ($instructions as $instruction)
                            <li class="list-group-item">
                                {{ $instruction->construction_location }}
                                {{ $instruction->construction_location_detail }}
                                {{ $instruction->product_name }}
                                {{ $instruction->product_number }}
                                {{ $instruction->amount }}
                                {{ $instruction->unit->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                <!-- 写真をアップロードするフォーム -->
                <form action="{{ route('photos.store', ['id' => $workplace->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                    <!-- 写真アップロードフィールド -->
                    <div class="form-group">
                        <label for="photos">写真</label>
                        <input type="file" name="photos[]" id="photos" class="form-control" multiple>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
                <!-- 既存の写真を表示 -->
                <div class="mt-4">
                    <h5>既存の写真</h5>
                    <ul class="list-group">
                        @foreach ($photos as $photo)
                            <li class="list-group-item">{{ $photo->file_name }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                <!-- 添付書類をアップロードするフォーム -->
                <form action="{{ route('files.store', ['id' => $workplace->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                    <!-- 添付書類アップロードフィールド -->
                    <div class="form-group">
                        <label for="files">添付書類</label>
                        <input type="file" name="files[]" id="files" class="form-control" multiple>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
                <!-- 既存の添付書類を表示 -->
                <div class="mt-4">
                    <h5>既存の添付書類</h5>
                    <ul class="list-group">
                        @foreach ($files as $file)
                            <li class="list-group-item">{{ $file->file_name }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <!-- Bootstrap Datepicker CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        .unit-select {
            width: 60px;  /* 幅を60pxに設定 */
            display: inline-block;  /* インラインブロック要素に設定 */
        }
    </style>
@stop

@section('js')
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
    <script>
        $(function() {
            // Datepickerの初期化
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                language: 'ja',
                todayHighlight: true
            });
            
            // タブの表示を初期化
            $('#detailTabs a').on('click', function (e) {
                e.preventDefault()
                $(this).tab('show')
            });

            // フォームを動的に追加
            let formCount = 10;
            $('#add-instruction-form').on('click', function() {
                formCount++;
                $('#instruction-forms').append(`
                    <div class="form-group row">
                        <div class="col-1 text-right">
                            <span>${formCount}</span>
                        </div>
                        <div class="col-2">
                            <input type="text" name="instructions[${formCount}][construction_location]" placeholder="施工場所" class="form-control mb-2">
                        </div>
                        <div class="col-2">
                            <input type="text" name="instructions[${formCount}][construction_location_detail]" placeholder="施工場所詳細" class="form-control mb-2">
                        </div>
                        <div class="col-2">
                            <input type="text" name="instructions[${formCount}][product_name]" placeholder="製品名" class="form-control mb-2">
                        </div>
                        <div class="col-2">
                            <input type="text" name="instructions[${formCount}][product_number]" placeholder="製品番号" class="form-control mb-2">
                        </div>
                        <div class="col-1">
                            <input type="text" name="instructions[${formCount}][amount]" placeholder="数量" class="form-control mb-2">
                        </div>
                        <div class="col-2">
                            <select name="instructions[${formCount}][unit_id]" class="form-control mb-2 unit-select">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                `);
            });
        });
    </script>
@stop
