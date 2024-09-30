@php
    Log::info('Instructions Tab Data:', ['instructions' => $instructions, 'role' => $role, 'storeRoute' => $storeRoute, 'updateRoute' => $updateRoute, 'destroyRoute' => $destroyRoute]);
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="card-title">施工指示</h3>
        <button type="button" class="btn btn-primary float-right" id="addInstructionBtn">追加</button>
    </div>
    <div class="card-body">
        <form id="instructionForm" action="{{ route($instructionsStoreRoute, ['role' => $role, 'id' => $workplace->id]) }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">
            <input type="hidden" name="id" value="{{ $workplace->id }}">

            <div class="form-group">
                <label for="construction_location">施工場所</label>
                <input type="text" name="construction_location" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="construction_location_detail">箇所詳細</label>
                <input type="text" name="construction_location_detail" class="form-control">
            </div>
            <div class="form-group">
                <label for="product_name">品名</label>
                <input type="text" name="product_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="product_number">品番</label>
                <input type="text" name="product_number" class="form-control">
            </div>
            <div class="form-group">
                <label for="amount">数量</label>
                <input type="number" name="amount" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="unit_id">単位</label>
                <select name="unit_id" class="form-control" required>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
            <button type="button" class="btn btn-secondary" id="cancelInstructionBtn">キャンセル</button>
        </form>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>施工場所</th>
                    <th>箇所詳細</th>
                    <th>品名</th>
                    <th>品番</th>
                    <th>数量</th>
                    <th>単位</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($instructions as $index => $instruction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $instruction->construction_location }}</td>
                        <td>{{ $instruction->construction_location_detail }}</td>
                        <td>{{ $instruction->product_name }}</td>
                        <td>{{ $instruction->product_number }}</td>
                        <td>{{ $instruction->amount }}</td>
                        <td data-unit-id="{{ $instruction->unit_id }}">{{ $instruction->unit->name }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm edit-instruction" data-id="{{ $instruction->id }}">編集</button>
                            <button type="button" class="btn btn-danger btn-sm delete-instruction" data-id="{{ $instruction->id }}">削除</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">クロス合計m数</th>
                    <th colspan="2">{{ $totalAmount }}ｍ</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('js')
<script>
$(function() {
    $('#addInstructionBtn').click(function() {
        $('#instructionForm').show();
    });

    $('#cancelInstructionBtn').click(function() {
        $('#instructionForm').hide();
    });

    $('.edit-instruction').click(function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        // フォームに現在の値をセット
        $('#instructionForm input[name="construction_location"]').val(row.find('td:eq(1)').text());
        $('#instructionForm input[name="construction_location_detail"]').val(row.find('td:eq(2)').text());
        $('#instructionForm input[name="product_name"]').val(row.find('td:eq(3)').text());
        $('#instructionForm input[name="product_number"]').val(row.find('td:eq(4)').text());
        $('#instructionForm input[name="amount"]').val(row.find('td:eq(5)').text());
        $('#instructionForm select[name="unit_id"]').val(row.find('td:eq(6)').data('unit-id'));
        
        // フォームの送信先を更新用に変更
        $('#instructionForm').attr('action', '{{ route($instructionsUpdateRoute, ["role" => $role, "id" => $workplace->id]) }}');
        $('#instructionForm').append('<input type="hidden" name="_method" value="PUT">');
        $('#instructionForm').append('<input type="hidden" name="instruction_id" value="' + id + '">');
        
        $('#instructionForm').show();
    });

    $('.delete-instruction').click(function() {
        var id = $(this).data('id');
        var workplaceId = '{{ $workplace->id }}';
        var role = '{{ $role }}';
        if (confirm('本当に削除しますか？')) {
            $.ajax({
                url: '/saler/workplaces/' + workplaceId + '/instructions/' + id,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    role: role
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('削除に失敗しました: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert('エラーが発生しました: ' + error);
                }
            });
        }
    });

});
</script>
@endpush