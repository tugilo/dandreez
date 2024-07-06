<div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
    <!-- 写真をアップロードするフォーム -->
    <form action="{{ route($photoStoreRoute, ['role' => $role, 'workplaceId' => $workplace->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
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
            @foreach($photos as $photo)
                <div class="p-2 text-center">
                    <img src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}" alt="{{ $photo->file_name }}" class="img-thumbnail" style="width: 100px; height: 100px;" data-toggle="modal" data-target="#photoModal" data-src="{{ asset('storage/instructions/photos/' . $photo->directory . $photo->file_name) }}">
                    <form action="{{ route($photoUpdateRoute, ['role' => $role, 'workplaceId' => $workplace->id, 'id' => $photo->id]) }}" method="POST" class="mt-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="title" value="{{ $photo->title }}" class="form-control mb-1" placeholder="タイトル">
                        <textarea name="comment" class="form-control mb-1" placeholder="コメント">{{ $photo->comment }}</textarea>
                        <button type="submit" class="btn btn-primary btn-sm">更新</button>
                    </form>
                    <form action="{{ route($photoDestroyRoute, ['role' => $role, 'workplaceId' => $workplace->id, 'id' => $photo->id]) }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">削除</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- モーダル -->
<div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">写真の表示</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="写真" style="max-width: 100%;">
            </div>
        </div>
    </div>
</div>
