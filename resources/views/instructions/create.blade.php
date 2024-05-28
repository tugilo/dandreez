
@extends('adminlte::page')

@section('title', '指示内容追加')

@section('content_header')
    <h1>指示内容追加 - {{ $workplace->name }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('instructions.store', ['workplace_id' => $workplace->id]) }}" method="POST">
            @csrf
            <div id="instruction-forms">
                @for ($i = 0; $i < 10; $i++)
                    <div class="form-group">
                        <label for="construction_location_{{ $i }}">施工場所</label>
                        <input type="text" name="instructions[{{ $i }}][construction_location]" id="construction_location_{{ $i }}" class="form-control">
                        
                        <label for="construction_location_detail_{{ $i }}">施工場所詳細</label>
                        <input type="text" name="instructions[{{ $i }}][construction_location_detail]" id="construction_location_detail_{{ $i }}" class="form-control">
                        
                        <label for="product_name_{{ $i }}">製品名</label>
                        <input type="text" name="instructions[{{ $i }}][product_name]" id="product_name_{{ $i }}" class="form-control">
                        
                        <label for="product_number_{{ $i }}">製品番号</label>
                        <input type="text" name="instructions[{{ $i }}][product_number]" id="product_number_{{ $i }}" class="form-control">
                        
                        <label for="amount_{{ $i }}">数量</label>
                        <input type="text" name="instructions[{{ $i }}][amount]" id="amount_{{ $i }}" class="form-control">
                        
                        <label for="unit_id_{{ $i }}">単位</label>
                        <input type="text" name="instructions[{{ $i }}][unit_id]" id="unit_id_{{ $i }}" class="form-control">
                    </div>
                @endfor
            </div>
            <button type="button" id="add-instruction-form" class="btn btn-secondary">フォームを追加</button>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>
@stop

@section('js')
    <script>
        $(function() {
            let formCount = 10;
            $('#add-instruction-form').on('click', function() {
                formCount++;
                $('#instruction-forms').append(`
                    <div class="form-group">
                        <label for="construction_location_${formCount}">施工場所</label>
                        <input type="text" name="instructions[${formCount}][construction_location]" id="construction_location_${formCount}" class="form-control">
                        
                        <label for="construction_location_detail_${formCount}">施工場所詳細</label>
                        <input type="text" name="instructions[${formCount}][construction_location_detail]" id="construction_location_detail_${formCount}" class="form-control">
                        
                        <label for="product_name_${formCount}">製品名</label>
                        <input type="text" name="instructions[${formCount}][product_name]" id="product_name_${formCount}" class="form-control">
                        
                        <label for="product_number_${formCount}">製品番号</label>
                        <input type="text" name="instructions[${formCount}][product_number]" id="product_number_${formCount}" class="form-control">
                        
                        <label for="amount_${formCount}">数量</label>
                        <input type="text" name="instructions[${formCount}][amount]" id="amount_${formCount}" class="form-control">
                        
                        <label for="unit_id_${formCount}">単位</label>
                        <input type="text" name="instructions[${formCount}][unit_id]" id="unit_id_${formCount}" class="form-control">
                    </div>
                `);
            });
        });
    </script>
@stop
