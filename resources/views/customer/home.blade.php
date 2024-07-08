@extends('adminlte::page')

@section('title', '得意先')

@section('css')
    <!-- FullCalendarのスタイルシートを追加 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css"/>
    <style>
        #calendar {
            max-width: 1100px;
            margin: 0 auto;
        }
    </style>
@stop

@section('content_header')
    <h1>得意先専用画面</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- カレンダーを表示するコンテナ -->
            <div id="calendar"></div>
        </div>
    </div>
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
            events: @json($events), // コントローラーから渡されたイベントデータ
            eventClick: function(event) {
                if (event.url) {
                    window.location.href = event.url;
                    return false; // クリックしたイベントのデフォルト動作をキャンセル
                }
            }
        });
    });
    </script>
@stop
