<!-- resources/views/workplaces/partials/instructions_tab.blade.php -->

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
                        <td class="editable">{{ $instruction->unit->name }}</td>
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
