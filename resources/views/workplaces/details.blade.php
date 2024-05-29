@extends('adminlte::page')

@section('title', '施工依頼詳細設定')

@section('content_header')
    <h1>施工依頼詳細設定</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-4">
            <!-- 施工依頼情報の表示 -->
            <h5>施工依頼情報</h5>
            <p><strong>ID:</strong> {{ $workplace->id }}</p>
            <p><strong>得意先:</strong> {{ $workplace->customer->name }}</p>
            <p><strong>施工名:</strong> {{ $workplace->name }}</p>
            <p><strong>施工期間:</strong> {{ $workplace->construction_start }} ～ {{ $workplace->construction_end }}</p>
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

        <div class="tab-content" id="detailTabsContent">
            <div class="tab-pane fade show active" id="instructions" role="tabpanel" aria-labelledby="instructions-tab">
                <!-- 指示内容を追加するフォーム -->
                <form action="{{ route('instructions.store', ['id' => $workplace->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>施工場所</th>
                                    <th>箇所詳細</th>
                                    <th>品名</th>
                                    <th>品番</th>
                                    <th>数量</th>
                                    <th>単位</th>
                                </tr>
                            </thead>
                            <tbody id="instruction-forms">
                                @for ($i = 0; $i < 10; $i++)
                                    <tr>
                                        <td class="text-right">{{ $i + 1 }}</td>
                                        <td><input type="text" name="instructions[{{ $i }}][construction_location]" class="form-control"></td>
                                        <td><input type="text" name="instructions[{{ $i }}][construction_location_detail]" class="form-control"></td>
                                        <td><input type="text" name="instructions[{{ $i }}][product_name]" class="form-control"></td>
                                        <td><input type="text" name="instructions[{{ $i }}][product_number]" class="form-control"></td>
                                        <td><input type="text" name="instructions[{{ $i }}][amount]" class="form-control"></td>
                                        <td>
                                            <select name="instructions[{{ $i }}][unit_id]" class="form-control" style="width: auto; min-width: 100px;">
                                                @foreach ($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" id="add-instruction-form" class="btn btn-secondary">フォームを追加</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>

                <!-- 既存の指示内容を表示 -->
                <div class="mt-4">
                    <h5>指示内容</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>編集</th>
                                <th>#</th>
                                <th>施工場所</th>
                                <th>箇所詳細</th>
                                <th>品名</th>
                                <th>品番</th>
                                <th>数量</th>
                                <th>単位</th>
                                <th>削除</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($instructions as $index => $instruction)
                                <tr data-id="{{ $instruction->id }}">
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm edit-instruction" data-id="{{ $instruction->id }}">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm save-instruction" data-id="{{ $instruction->id }}" style="display: none;">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="editable">{{ $instruction->construction_location }}</td>
                                    <td class="editable">{{ $instruction->construction_location_detail }}</td>
                                    <td class="editable">{{ $instruction->product_name }}</td>
                                    <td class="editable">{{ $instruction->product_number }}</td>
                                    <td class="editable">{{ $instruction->amount }}</td>
                                    <td class="editable">{{ $instruction->unit->name }} m</td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm delete-instruction" data-id="{{ $instruction->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">クロス合計m数</th>
                                <th class="text-right" id="total-amount"></th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                <!-- 写真をアップロードするフォーム -->
                <form action="{{ route('photos.store', ['workplaceId' => $workplace->id]) }}" method="POST" enctype="multipart/form-data"> @csrf
                    <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
                    <!-- 写真アップロードフィールド -->
                    <div class="d-flex justify-content-center">
                        <div class="form-group text-center mr-2">
                            <label for="photos1" class="d-block">写真1</label>
                            <input type="file" name="photos[0][file]" id="photos1" class="form-control mb-2">
                            <input type="text" name="photos[0][title]" class="form-control mb-1" placeholder="タイトル">
                            <textarea name="photos[0][comment]" class="form-control" placeholder="コメント"></textarea>
                        </div>
                        <div class="form-group text-center mr-2">
                            <label for="photos2" class="d-block">写真2</label>
                            <input type="file" name="photos[1][file]" id="photos2" class="form-control mb-2">
                            <input type="text" name="photos[1][title]" class="form-control mb-1" placeholder="タイトル">
                            <textarea name="photos[1][comment]" class="form-control" placeholder="コメント"></textarea>
                        </div>
                        <div class="form-group text-center">
                            <label for="photos3" class="d-block">写真3</label>
                            <input type="file" name="photos[2][file]" id="photos3" class="form-control mb-2">
                            <input type="text" name="photos[2][title]" class="form-control mb-1" placeholder="タイトル">
                            <textarea name="photos[2][comment]" class="form-control" placeholder="コメント"></textarea>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
                <!-- 既存の写真を表示 -->
                <div class="mt-4 text-center">
                    <h5>既存の写真</h5>
                    <div class="d-flex flex-wrap justify-content-center">
                        @foreach ($photos as $photo)
                            <div class="p-2 text-center">
                                <img src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}" alt="{{ $photo->file_name }}" class="img-thumbnail" style="width: 100px; height: 100px;" data-toggle="modal" data-target="#photoModal" data-src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}">
                                <form action="{{ route('photos.update', ['workplaceId' => $workplace->id, 'id' => $photo->id]) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="title" value="{{ $photo->title }}" class="form-control mb-1" placeholder="タイトル">
                                    <textarea name="comment" class="form-control mb-1" placeholder="コメント">{{ $photo->comment }}</textarea>
                                    <div class="d-flex justify-content-center">
                                        <button type="submit" class="btn btn-primary btn-sm mr-1">更新</button>
                                        <form action="{{ route('photos.destroy', ['workplaceId' => $workplace->id, 'id' => $photo->id]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">削除</button>
                                        </form>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- モーダル -->
            <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="photoModalLabel">画像表示</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="modalImage" src="" alt="拡大画像" class="img-fluid">
                        </div>
                    </div>
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
                    <div class="text-center">
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
        .table-scrollable {
            max-height: 150px;
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

            // モーダルの画像表示
            $('#photoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget)
                var src = button.data('src')
                var modal = $(this)
                modal.find('.modal-body #modalImage').attr('src', src)
            });

            // フォームを動的に追加
            let formCount = {{ count($instructions) }} + 1;
            $('#add-instruction-form').on('click', function() {
                $('#instruction-forms').append(`
                    <tr>
                        <td class="text-right">${formCount}</td>
                        <td>
                            <input type="text" name="instructions[${formCount}][construction_location]" class="form-control mb-1">
                            <input type="text" name="instructions[${formCount}][construction_location_detail]" class="form-control">
                        </td>
                        <td><input type="text" name="instructions[${formCount}][product_name]" class="form-control"></td>
                        <td><input type="text" name="instructions[${formCount}][product_number]" class="form-control"></td>
                        <td><input type="text" name="instructions[${formCount}][amount]" class="form-control"></td>
                        <td>
                            <select name="instructions[${formCount}][unit_id]" class="form-control" style="width: auto; min-width: 100px;">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} m</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                `);
                formCount++;
            });

            // 編集ボタンが押されたときの動作
            $('.edit-instruction').on('click', function() {
                const row = $(this).closest('tr');
                row.find('td.editable').each(function(index) {
                    let value = $(this).text().trim();
                    const input = $('<input>', {
                        type: 'text',
                        class: 'form-control',
                        value: value
                    });
                    $(this).html(input);
                });

                const unitName = row.find('td:eq(7)').text().trim().replace(' m', '');
                const select = $('<select>', {
                    class: 'form-control unit-select',
                    style: 'width: auto; min-width: 100px;',
                    html: `@foreach ($units as $unit)
                                <option value="{{ $unit->id }}" ${unitName === "{{ $unit->name }}" ? 'selected' : ''}>{{ $unit->name }} m</option>
                           @endforeach`
                });
                row.find('td:eq(7)').html(select);

                row.find('.edit-instruction').hide();
                row.find('.save-instruction').show();
            });

            // 保存ボタンが押されたときの動作
            $('.save-instruction').on('click', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                const data = {
                    construction_location: row.find('td:eq(2) input').val(),
                    construction_location_detail: row.find('td:eq(3) input').val(),
                    product_name: row.find('td:eq(4) input').val(),
                    product_number: row.find('td:eq(5) input').val(),
                    amount: row.find('td:eq(6) input').val(),
                    unit_id: row.find('select.unit-select').val(),
                    _token: '{{ csrf_token() }}'
                };
                $.ajax({
                    url: `/instructions/${id}`,
                    type: 'PUT',
                    data: data,
                    success: function(response) {
                        alert('指示内容が更新されました。');
                        location.reload(); // 更新が成功したらページをリロード
                    },
                    error: function(response) {
                        alert('更新に失敗しました。');
                    }
                });
            });

            // 指示内容の削除
            $('.delete-instruction').on('click', function() {
                const id = $(this).data('id');
                if (confirm('本当に削除しますか？')) {
                    $.ajax({
                        url: `/instructions/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $(`tr[data-id="${id}"]`).remove();
                            updateTotalAmount(); // 削除後に合計を更新
                            alert('指示内容が削除されました。');
                        },
                        error: function(response) {
                            alert('削除に失敗しました。');
                        }
                    });
                }
            });

            // 合計数量を計算して表示
            function updateTotalAmount() {
                let total = 0;
                $('tbody tr').each(function() {
                    const amount = parseFloat($(this).find('td:eq(6)').text().trim());
                    if (!isNaN(amount)) {
                        total += amount;
                    }
                });
                $('#total-amount').text(total.toFixed(2) + ' m');
            }

            // 初期表示
            updateTotalAmount();

            // 編集完了時に合計を更新
            $('.save-instruction').on('click', function() {
                updateTotalAmount();
            });

            // カメラアイコンをクリックしてファイル選択をトリガー
            $('.camera-icon').on('click', function() {
                $('#photos').click();
            });

            // ファイル選択時にアイコンが変更されるように設定
            $('#photos').on('change', function(event) {
                const files = event.target.files;
                if (files && files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('.camera-icon').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(files[0]);
                }
            });
        });
    </script>
@stop
