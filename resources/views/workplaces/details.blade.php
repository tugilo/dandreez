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
            'workplace' => $workplace,
            'instructions' => $instructions,
            'photos' => $photos,
            'files' => $files,
            'units' => $units,
            'role' => $role,
            'storeRoute' => $storeRoute,
            'updateRoute' => $updateRoute,
            'destroyRoute' => $destroyRoute,
            'instructionsStoreRoute' => $instructionsStoreRoute,
            'photoStoreRoute' => $photoStoreRoute,
            'fileStoreRoute' => $fileStoreRoute,
        ])
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
                e.preventDefault();
                $(this).tab('show');
            });

            // 最初のタブを表示
            $('#detailTabs a:first').tab('show');

            // フォームを動的に追加
            let formCount = {{ count($instructions) }} + 10; // デフォルトで表示されるフォーム数
            $('#add-instruction-form').on('click', function() {
                formCount++;
                $('#instruction-forms').append(`
                    <tr>
                        <td class="text-right">${formCount}</td>
                        <td><input type="text" name="instructions[${formCount - 1}][construction_location]" class="form-control"></td>
                        <td><input type="text" name="instructions[${formCount - 1}][construction_location_detail]" class="form-control"></td>
                        <td><input type="text" name="instructions[${formCount - 1}][product_name]" class="form-control"></td>
                        <td><input type="text" name="instructions[${formCount - 1}][product_number]" class="form-control"></td>
                        <td><input type="text" name="instructions[${formCount - 1}][amount]" class="form-control"></td>
                        <td>
                            <select name="instructions[${formCount - 1}][unit_id]" class="form-control" style="width: auto; min-width: 100px;">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                `);
            });

            // 編集ボタンが押されたときの動作
            $(document).on('click', '.edit-instruction', function() {
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

                const unitName = row.find('td:eq(7)').text().trim();
                const select = $('<select>', {
                    class: 'form-control unit-select',
                    style: 'width: auto; min-width: 100px;',
                    html: `@foreach ($units as $unit)
                                <option value="{{ $unit->id }}" ${unitName === "{{ $unit->name }}" ? 'selected' : ''}>{{ $unit->name }}</option>
                           @endforeach`
                });
                row.find('td:eq(7)').html(select);

                row.find('.edit-instruction').hide();
                row.find('.save-instruction').show();
            });

            // 保存ボタンが押されたときの動作
            $(document).on('click', '.save-instruction', function() {
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
                    url: `/{{ $role }}/workplaces/{{ $role}}/${id}/instructions`,
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
            $(document).on('click', '.delete-instruction', function() {
                const id = $(this).data('id');
                if (confirm('本当に削除しますか？')) {
                    $.ajax({
                        url: `/{{ $role }}/workplaces/{{ $role}}/${id}/instructions`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert('指示内容が削除されました。');
                            location.reload(); // 更新が成功したらページをリロード
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
            $(document).on('click', '.save-instruction', function() {
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
            $('#photoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var src = button.data('src');
                var modal = $(this);
                modal.find('.modal-body #modalImage').attr('src', src);
            });

            // 写真の削除
            $(document).on('click', '.delete-photo', function() {
                const photoId = $(this).data('id');
                if (confirm('本当に削除しますか？')) {
                    $.ajax({
                        url: `/{{ $role }}/photos/${photoId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert('写真が削除されました。');
                            location.reload(); // 削除が成功したらページをリロード
                        },
                        error: function(response) {
                            alert('削除に失敗しました。');
                        }
                    });
                }
            });

            // 写真の編集
            $(document).on('click', '.edit-photo', function() {
                var photoId = $(this).data('id');
                $('#edit-photo-form-' + photoId).show();
                $(this).hide();
            });

            // キャンセルボタンの動作
            $(document).on('click', '.cancel-edit', function() {
                var photoId = $(this).data('id');
                $('#edit-photo-form-' + photoId).hide();
                $('.edit-photo[data-id="' + photoId + '"]').show();
            });

            // ファイルの削除
            $(document).on('click', '.delete-file', function() {
                const fileId = $(this).data('id');
                if (confirm('本当に削除しますか？')) {
                    $.ajax({
                        url: `/{{ $role }}/files/${fileId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert('ファイルが削除されました。');
                            location.reload(); // 削除が成功したらページをリロード
                        },
                        error: function(response) {
                            alert('削除に失敗しました。');
                        }
                    });
                }
            });
            // ファイルの削除
            $(document).on('click', '.delete-file', function() {
                const fileId = $(this).data('id');
                if (confirm('本当に削除しますか？')) {
                    $.ajax({
                        url: `/{{ $role }}/files/${fileId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert('ファイルが削除されました。');
                            location.reload(); // 削除が成功したらページをリロード
                        },
                        error: function(response) {
                            alert('削除に失敗しました。');
                        }
                    });
                }
            });
            // タブの選択状態を保存
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('activeTab', $(e.target).attr('href'));
            });

            // 保存されたタブを読み込む
            var activeTab = localStorage.getItem('activeTab');
            if(activeTab){
                $('#detailTabs a[href="' + activeTab + '"]').tab('show');
            }

        });
    </script>
@stop
