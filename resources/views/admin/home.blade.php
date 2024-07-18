@extends('adminlte::page')

@section('title', '管理者ダッシュボード')

@section('content_header')
    <h1>管理者ダッシュボード</h1>
@stop

@section('content')
    <div class="row">
        <!-- ユーザー統計 -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <!-- 総ユーザー数を表示 -->
                <div class="inner">
                    <h3>{{ $userStats['totalUsers'] }}</h3>
                    <p>総ユーザー数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('users.index') }}" class="small-box-footer">
                    詳細 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <!-- アクティブユーザー -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <!-- アクティブユーザーを表示 -->
                <div class="inner">
                    <h3>{{ $userStats['activeUsers'] }}</h3>
                    <p>アクティブユーザー</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="#" class="small-box-footer">
                    詳細 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <!-- 総施工依頼数 -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <!-- 総施工依頼数を表示 -->
                <div class="inner">
                    <h3>{{ $workplaceStats['totalWorkplaces'] }}</h3>
                    <p>総施工依頼数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <a href="{{ route('saler.workplaces.index') }}" class="small-box-footer">
                    詳細 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <!-- 新規ユーザー -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <!-- 新規ユーザー（過去7日間）を表示 -->
                <div class="inner">
                    <h3>{{ $userStats['newUsers'] }}</h3>
                    <p>新規ユーザー（過去7日間）</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <a href="#" class="small-box-footer">
                    詳細 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ユーザータイプ別グラフ -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ユーザータイプ別内訳</h3>
                </div>
                <div class="card-body">
                    <canvas id="userTypeChart"></canvas>
                </div>
            </div>
        </div>
        <!-- 施工依頼ステータス別グラフ -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">施工依頼ステータス別内訳</h3>
                </div>
                <div class="card-body">
                    <canvas id="workplaceStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($recentActivities))
    <!-- アクティビティログ -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">最近のアクティビティ</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @foreach($recentActivities as $activity)
                            <li class="item">
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{ $activity->user->name ?? 'Unknown User' }}
                                        <span class="badge badge-warning float-right">{{ $activity->created_at->diffForHumans() }}</span>
                                    </a>
                                    <span class="product-description">
                                        {{ $activity->description ?? 'No description available' }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- アクティビティログ機能は将来の拡張のために予約されています -->
@endif

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ユーザータイプ別グラフ
        var userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
        var userTypeChart = new Chart(userTypeCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($userStats['usersByType'])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($userStats['usersByType'])) !!},
                    backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // 施工依頼ステータス別グラフ
        var workplaceStatusCtx = document.getElementById('workplaceStatusChart').getContext('2d');
        var workplaceStatusChart = new Chart(workplaceStatusCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($workplaceStats['workplacesByStatus'])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($workplaceStats['workplacesByStatus'])) !!},
                    backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    </script>
@stop