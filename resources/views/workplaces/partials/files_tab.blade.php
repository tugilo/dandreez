<div class="card">
    <div class="card-header">
        <h3 class="card-title">ファイル</h3>
        <button type="button" class="btn btn-primary float-right" id="addFileBtn">追加</button>
    </div>
    <div class="card-body">
        <form id="fileForm" action="{{ route($fileStoreRoute, ['role' => $role, 'workplaceId' => $workplace->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
            <div class="form-group">
                <label for="file">ファイル</label>
                <input type="file" name="files[0][file]" class="form-control-file" required>
            </div>
            <div class="form-group">
                <label for="title">タイトル</label>
                <input type="text" name="files[0][title]" class="form-control">
            </div>
            <div class="form-group">
                <label for="comment">コメント</label>
                <textarea name="files[0][comment]" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">アップロード</button>
            <button type="button" class="btn btn-secondary" id="cancelFileBtn">キャンセル</button>
        </form>

        <ul class="list-group mt-3">
            @foreach($files as $file)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ asset('storage/'.$file->directory.$file->file_name) }}" download>
                            <i class="fa fa-file"></i> {{ $file->file_name }}
                        </a>
                        <div>タイトル: {{ $file->title ?? 'なし' }}</div>
                        <div>コメント: {{ $file->comment ?? 'なし' }}</div>
                    </div>
                    <div>
                        <button class="btn btn-warning btn-sm edit-file" data-id="{{ $file->id }}">編集</button>
                        <button class="btn btn-danger btn-sm delete-file" data-id="{{ $file->id }}">削除</button>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@push('js')
<script>
$(function() {
    $('#addFileBtn').click(function() {
        $('#fileForm').show();
    });

    $('#cancelFileBtn').click(function() {
        $('#fileForm').hide();
    });

    $('.edit-file').click(function() {
        var id = $(this).data('id');
        // 編集処理を実装
    });
    $('#fileForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('File uploaded successfully', response);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error uploading file:', xhr.responseJSON);
                alert('ファイルのアップロードに失敗しました。エラー: ' + JSON.stringify(xhr.responseJSON));
            }
        });
    });

    $('.delete-file').click(function() {
        var id = $(this).data('id');
        var workplaceId = '{{ $workplace->id }}';
        if (confirm('本当に削除しますか？')) {
            $.ajax({
                url: '{{ route($role . ".workplaces.files.destroy", ["role" => $role, "workplaceId" => ":workplaceId", "id" => ":id"]) }}'
                    .replace(':workplaceId', workplaceId)
                    .replace(':id', id),
                method: 'DELETE',
                data: {_token: '{{ csrf_token() }}'},
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('エラーが発生しました');
                }
            });
        }
    });

});
</script>
@endpush