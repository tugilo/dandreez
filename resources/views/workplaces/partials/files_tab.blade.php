<!-- ファイルタブ -->
<div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
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
        <hr>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">アップロード</button>
        </div>
    </form>
    <hr>
    <ul class="list-group">
        @foreach($files as $file)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ asset('storage/'.$file->directory.$file->file_name) }}" download>
                        <i class="fa fa-file"></i> {{ $file->file_name }}
                    </a>
                    <div>タイトル: {{ $file->title ?? 'なし' }}</div>
                    <div>コメント: {{ $file->comment ?? 'なし' }}</div>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#editFileModal-{{ $file->id }}">
                        編集
                    </button>
                    <form action="{{ route($fileDeleteRoute, ['role' => $role, 'workplaceId' => $workplace->id, 'id' => $file->id]) }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">削除</button>
                    </form>
                </div>
            </li>

            <!-- 編集モーダル -->
            <div class="modal fade" id="editFileModal-{{ $file->id }}" tabindex="-1" role="dialog" aria-labelledby="editFileModalLabel-{{ $file->id }}" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editFileModalLabel-{{ $file->id }}">ファイル情報の編集</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route($fileUpdateRoute, ['role' => $role, 'workplaceId' => $workplace->id, 'id' => $file->id]) }}" method="POST" class="mt-2">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="title">タイトル</label>
                                    <input type="text" name="title" class="form-control" value="{{ $file->title }}">
                                </div>
                                <div class="form-group">
                                    <label for="comment">コメント</label>
                                    <textarea name="comment" class="form-control">{{ $file->comment }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                                <button type="submit" class="btn btn-primary">保存</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </ul>
</div>
