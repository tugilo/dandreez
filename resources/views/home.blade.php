@extends('adminlte::page')

@section('title', 'Dandreez管理システム')

@section('content_header')
    <h1>Dandreez管理システム</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <!-- カレンダーコンテナ -->
        <div id="calendar" class="calendar"></div>
    </div>
</div>
@stop

@section('css')
<!-- FullCalendarのCSSを読み込み -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css"/>
@stop

@section('js')
<!-- 必要なJavaScriptライブラリの読み込み -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/ja.js"></script>
<script>
    $(document).ready(function() {
        // カレンダーを初期化
        $('#calendar').fullCalendar({
            locale: 'ja', // 日本語化設定
            defaultView: 'month', // デフォルトの表示は月ビュー
            editable: true, // イベントを編集可能にする
            eventLimit: true, // イベントが多い日はリンクで表示

        });
    });
</script>
@stop
