@php
    Log::info('Files Tab Data:', ['files' => $files, 'role' => $role, 'storeRoute' => $storeRoute, 'updateRoute' => $updateRoute, 'destroyRoute' => $destroyRoute]);
@endphp

<div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
<!-- ファイルをアップロードするフォーム -->
<form action="{{ route($fileStoreRoute, ['role' => $role, 'workplaceId' => $workplace->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="workplace_id" value="{{ $workplace->id }}">
    <!-- ファイルアップロードフィールド -->
    <div class="d-flex justify-content-center">
        <div class="form-group text-center mr-2">
            <label for="files1" class="d-block">ファイル1</label>
            <input type="file" name="files[0][file]" id="files1" class="form-control mb-2">
            <input type="text" name="files[0][title]" class="form-control mb-1" placeholder="タイトル">
            <textarea name="files[0][comment]" class="form-control" placeholder="コメント"></textarea>
        </div>
        <div class="form-group text-center mr-2">
            <label for="files2" class="d-block">ファイル2</label>
            <input type="file" name="files[1][file]" id="files2" class="form-control mb-2">
            <input type="text" name="files[1][title]" class="form-control mb-1" placeholder="タイトル">
            <textarea name="files[1][comment]" class="form-control" placeholder="コメント"></textarea>
        </div>
        <div class="form-group text-center">
            <label for="files3" class="d-block">ファイル3</label>
            <input type="file" name="files[2][file]" id="files3" class="form-control mb-2">
            <input type="text" name="files[2][title]" class="form-control mb-1" placeholder="タイトル">
            <textarea name="files[2][comment]" class="form-control" placeholder="コメント"></textarea>
        </div>
    </div>
    
    <div class="text-center">
        <button type="submit" class="btn btn-primary">保存</button>
    </div>
</form>
<!-- 既存のファイルを表示 -->
<div class="mt-4 text-center">
    <h5>既存のファイル</h5>
    <div class="d-flex flex-wrap justify-content-center">
        @foreach ($files as $file)
            <div class="p-2 text-center">
                <a href="{{ asset('storage/instructions/files/' . $file->directory . $file->file_name) }}" target="_blank">{{ $file->file_name }}</a>
                <form action="{{ route($updateRoute, ['role' => $role, 'workplaceId' => $workplace->id, 'id' => $file->id]) }}" method="POST" class="mt-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="title" value="{{ $file->title }}" class="form-control mb-1" placeholder="タイトル">
                    <textarea name="comment" class="form-control mb-1" placeholder="コメント">{{ $file->comment }}</textarea>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary btn-sm mr-1">更新</button>
                        <button type="submit" class="btn btn-danger btn-sm" formaction="{{ route($destroyRoute, ['role' => $role, 'workplaceId' => $workplace->id, 'id' => $file->id]) }}" formmethod="POST">削除</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
