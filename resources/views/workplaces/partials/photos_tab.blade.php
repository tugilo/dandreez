<div class="card">
    <div class="card-header">
        <h3 class="card-title">写真</h3>
        <button type="button" class="btn btn-primary float-right" id="addPhotoBtn">追加</button>
    </div>
    <div class="card-body">
        <form id="photoForm" action="{{ route($photoStoreRoute, ['role' => $role, 'workplaceId' => $workplace->id]) }}" method="POST" enctype="multipart/form-data" style="display: none;">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
            <input type="hidden" name="photo_id" id="photo_id" value="">
                            
            <div class="form-group">
                <label for="photo">写真</label>
                <input type="file" name="photo" class="form-control-file" required>
            </div>
            <div class="form-group">
                <label for="title">タイトル</label>
                <input type="text" name="title" class="form-control">
            </div>
            <div class="form-group">
                <label for="comment">コメント</label>
                <textarea name="comment" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">アップロード</button>
            <button type="button" class="btn btn-secondary" id="cancelPhotoBtn">キャンセル</button>
        </form>

        <div class="row mt-3">
            @foreach($photos as $photo)
            <div class="col-md-4 mb-3">
                <img src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}" alt="{{ $photo->title }}" class="img-fluid" data-toggle="modal" data-target="#photoModal" data-src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}">
                <h5>{{ $photo->title }}</h5>
                <p>{{ $photo->comment }}</p>
                <button class="btn btn-warning btn-sm edit-photo" data-id="{{ $photo->id }}" data-title="{{ $photo->title }}" data-comment="{{ $photo->comment }}">編集</button>
                <button class="btn btn-danger btn-sm delete-photo" data-id="{{ $photo->id }}">削除</button>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- 写真表示モーダル -->
<div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">写真</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="" class="img-fluid" id="modalImage">
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
$(function() {
    $('#addPhotoBtn').click(function() {
        $('#photoForm').show();
    });

    // キャンセルボタンのクリックイベント
    $('#cancelPhotoBtn').click(function() {
        resetPhotoForm();
    });


    $('.delete-photo').click(function() {
        var id = $(this).data('id');
        var workplaceId = '{{ $workplace->id }}';
        if (confirm('本当に削除しますか？')) {
            $.ajax({
                url: '{{ route($role . ".workplaces.photos.destroy", ["role" => $role, "workplaceId" => ":workplaceId", "id" => ":id"]) }}'
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
    
    $('#photoModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var src = button.data('src');
        var modal = $(this);
        modal.find('.modal-body #modalImage').attr('src', src);
    });
    $('.edit-photo').click(function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var comment = $(this).data('comment');
        
        // フォームに現在の値をセット
        var updateUrl = '{{ route($photoUpdateRoute, ["role" => $role, "workplaceId" => $workplace->id, "id" => "__id__"]) }}';
        updateUrl = updateUrl.replace('__id__', id);
        $('#photoForm').attr('action', updateUrl);
        $('#photoForm input[name="title"]').val(title);
        $('#photoForm textarea[name="comment"]').val(comment);
        $('#photoForm input[name="_method"]').val('PUT');
        $('#photoForm input[name="photo_id"]').val(id);
        
        // ファイル入力フィールドを非表示にし、任意にする
        $('#photoForm input[name="photo"]').hide().prop('required', false);
        
        // フォームを表示
        $('#photoForm').show();
    });    
});
</script>
@endpush