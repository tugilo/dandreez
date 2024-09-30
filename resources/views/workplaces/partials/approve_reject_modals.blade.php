<!-- 承認モーダル -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">施工依頼の承認</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>本当にこの施工依頼を承認しますか？</p>
                <p><strong>ID:</strong> {{ $workplace->id }}</p>
                <p><strong>得意先:</strong> {{ $workplace->customer->name }}</p>
                <p><strong>施工名:</strong> {{ $workplace->name }}</p>
                <p><strong>施工期間:</strong> {{ $workplace->construction_start }} ～ {{ $workplace->construction_end }}</p>
                <p><strong>施工場所:</strong> {{ $workplace->zip }} {{ $workplace->prefecture }} {{ $workplace->city }} {{ $workplace->address }} {{ $workplace->building }}</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('saler.workplaces.approve', ['role' => 'saler', 'id' => $workplace->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">承認</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
            </div>
        </div>
    </div>
</div>

<!-- 否認モーダル -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">施工依頼の否認</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>本当にこの施工依頼を否認しますか？</p>
                <p><strong>ID:</strong> {{ $workplace->id }}</p>
                <p><strong>得意先:</strong> {{ $workplace->customer->name }}</p>
                <p><strong>施工名:</strong> {{ $workplace->name }}</p>
                <p><strong>施工期間:</strong> {{ $workplace->construction_start }} ～ {{ $workplace->construction_end }}</p>
                <p><strong>施工場所:</strong> {{ $workplace->zip }} {{ $workplace->prefecture }} {{ $workplace->city }} {{ $workplace->address }} {{ $workplace->building }}</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('saler.workplaces.reject', ['role' => 'saler', 'id' => $workplace->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">否認</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
            </div>
        </div>
    </div>
</div>