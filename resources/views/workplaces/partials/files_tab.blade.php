<!-- resources/views/workplaces/partials/files_tab.blade.php -->

<div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
    <!-- 添付書類をアップロードするフォーム -->
    <form action="{{ route('files.store', ['id' => $workplace->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
        <!-- 添付書類アップロードフィールド -->
        <div class="d-flex flex-wrap">
            <div class="form-group mr-3">
                <label for="files">添付書類</label>
                <input type="file" name="files[0][file]" class="form-control-file" required>
                <input type="text" name="files[0][title]" class="form-control mt-2" placeholder="タイトル">
                <textarea name="files[0][comment]" class="form-control mt-2" placeholder="コメント"></textarea>
            </div>
            <div class="form-group mr-3">
                <label for="files">添付書類</label>
                <input type="file" name="files[1][file]" class="form-control-file">
                <input type="text" name="files[1][title]" class="form-control mt-2" placeholder="タイトル">
                <textarea name="files[1][comment]" class="form-control mt-2" placeholder="コメント"></textarea>
            </div>
            <div class="form-group">
                <label for="files">添付書類</label>
                <input type="file" name="files[2][file]" class="form-control-file">
                <input type="text" name="files[2][title]" class="form-control mt-2" placeholder="タイトル">
                <textarea name="files[2][comment]" class="form-control mt-2" placeholder="コメント"></textarea>
            </div>
        </div>
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary">保存</button>
        </div>
    </form>
    <!-- 既存の添付書類を表示 -->
    <div class="mt-4">
        <h5>既存の添付書類</h5>
        <div class="file-icons d-flex flex-wrap">
            @foreach ($files as $file)
                <div class="file-icon text-center m-2">
                    <a href="{{ asset('storage/' . $file->directory . $file->file_name) }}" target="_blank">
                        <i class="fas fa-file fa-3x"></i>
                        <div class="file-title mt-2">{{ $file->title ?? '無題' }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
