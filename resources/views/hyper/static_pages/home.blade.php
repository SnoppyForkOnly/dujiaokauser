@extends('hyper.layouts.default')
@section('content')
<style>
    .purchase-info-container {
    overflow: hidden;
    white-space: nowrap;
}

.purchase-info {
    display: inline-block;
    animation: slideLeft 20s linear infinite;
}
.purchase-info div {
    color: red;
    font-weight: bold;
    font-size: 18px;
}


@keyframes slideLeft {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-100%);
    }
}

</style>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="app-search">
                    <div class="position-relative">
                        <input type="text" class="form-control" id="search" placeholder="{{ __('hyper.home_search_box') }}">
                        <span class="uil-search"></span>
                    </div>
                </div>
            </div>
            <h4 class="page-title d-none d-md-block">{{ __('hyper.home_title') }}</h4>
        </div>
    </div>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
            	<h4 class="header-title mb-3">{{ __('hyper.notice_announcement') }}</h4>
                <div class="notice">{!! dujiaoka_config_get('notice') !!}</div>
            </div>
        </div>
    </div>
</div>
  @if(dujiaoka_config_get('is_open_xn') == \App\Models\BaseModel::STATUS_OPEN)  
<div class="purchase-info-container">
    <div class="purchase-info">
        @foreach ($purchaseInfos as $info)
            <div>{{ $info['email'] }} 在 {{ $info['time'] }} 购买了 {{ $info['quantity'] }} 件 {{ $info['product'] }}</div>
        @endforeach
    </div>
</div>
    @endif
<div class="nav nav-list">
    <a href="#group-all" class="tab-link active" data-bs-toggle="tab" aria-expanded="false" role="tab" data-toggle="tab">
        <span class="tab-title">
        {{-- 全部 --}}
        {{ __('hyper.home_whole') }}
        </span>
        <div class="img-checkmark">
            <img src="/assets/hyper/images/check.png">
        </div>
    </a>
    @foreach($data as  $index => $group)
    <a href="#group-{{ $group['id'] }}" class="tab-link" data-bs-toggle="tab" aria-expanded="false" role="tab" data-toggle="tab">
        <span class="tab-title">
            {{ $group['gp_name'] }}
        </span>
        <div class="img-checkmark">
            <img src="/assets/hyper/images/check.png">
        </div>
    </a>
    @endforeach
</div>
<div class="tab-content">
    <div class="tab-pane active" id="group-all">
        <div class="hyper-wrapper">
            @foreach($data as $group)
                @foreach($group['goods'] as $goods)
                    @if($goods['in_stock'] > 0)
                    <a href="{{ url("/buy/{$goods['id']}") }}" class="home-card category">
                    @else
                    <a href="javascript:void(0);" onclick="sell_out_tip()" class="home-card category ribbon-box">
                        <div class="ribbon-two ribbon-two-danger">
                            {{-- 缺货 --}}
                            <span>{{ __('hyper.home_out_of_stock') }}</span>
                        </div>
                    @endif
                        <img class="home-img" src="/assets/hyper/images/loading.gif" data-src="{{ picture_ulr($goods['picture']) }}">
                        <div class="flex">
                            <p class="name">
                                {{ $goods['gd_name'] }}
                            </p>
                          <div class="price">
                                <p>
                                <b>{{ $goods['actual_price'] }}</b> {{(dujiaoka_config_get('global_currency')) }}</b>
                                </p>
                                             @if ($goods['open_rebate'] > 0 && $goods['rebate_rate'] > 0)
    <small>
        {{-- 返利 --}}
        返利{{ $goods['rebate_rate'] }}%
    </small>
@endif
                            </div>
                        </div>
                    </a>
                @endforeach
            @endforeach
        </div>
    </div>
    @foreach($data as  $index => $group)
        <div class="tab-pane" id="group-{{ $group['id'] }}">
            <div class="hyper-wrapper">
                @foreach($group['goods'] as $goods)
                    @if($goods['in_stock'] > 0)
                    <a href="{{ url("/buy/{$goods['id']}") }}" class="home-card category">
                    @else
                    <a href="javascript:void(0);" onclick="sell_out_tip()" class="home-card category ribbon-box">
                        <div class="ribbon-two ribbon-two-danger">
                            {{-- 缺货 --}}
                            <span>{{ __('hyper.home_out_of_stock') }}</span>
                        </div>
                    @endif
                        <img class="home-img" src="/assets/hyper/images/loading.gif" data-src="{{ picture_ulr($goods['picture']) }}">
                        <div class="flex">
                            <p class="name">
                                {{ $goods['gd_name'] }}
                            </p>
                             <div class="price">
                                <p>
                                <b>{{ $goods['actual_price'] }}</b> {{(dujiaoka_config_get('global_currency')) }}</b>
                                </p>
                             @if ($goods['open_rebate'] > 0 && $goods['rebate_rate'] > 0)
    <small>
        {{-- 返利 --}}
        返利{{ $goods['rebate_rate'] }}%
    </small>
@endif

                            </div>
                        </div>
                    
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

 
@if(dujiaoka_config_get('is_open_wenzhang') == \App\Models\BaseModel::STATUS_OPEN)    
  <div class="row">
    <div class="col-md-12">
        <div class="card">
               <div class="card-header">
               <a href="/article" style="text-decoration: none;">
  <span class="btn badge-info" style="display: block; width: 100%;">文章教程</span>
</a>

            </div>
            <div class="card-body p-0">
                <table class="table table-centered mb-0">
                    <thead>
                        <tr>
                          
                        </tr>
                    </thead>
                    <tbody>
                         @foreach ($articles->shuffle()->take(6) as $article)
                        <tr>
                            <td>
                                <a href="article/{{ !empty($article['link']) ? $article['link'] : $article['id'] }}" class="text-body">
                                    <span>{{ $article['title'] }}</span>
                                </a>
                            </td>
                     <td class="article-updated-at">{{ $article['updated_at'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>
@endif
@stop 
@section('js')
<script>
    $("#search").on("input",function(e){
        var txt = $("#search").val();
        if($.trim(txt)!="") {
            $(".category").hide().filter(":contains('"+txt+"')").show();
        } else {
            $(".category").show();
        }
    });
    function sell_out_tip() {
        $.NotificationApp.send("{{ __('hyper.home_tip') }}","{{ __('hyper.home_sell_out_tip') }}","top-center","rgba(0,0,0,0.2)","info");
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const purchaseInfoContainer = document.querySelector('.purchase-info-container');
    const purchaseInfo = document.querySelector('.purchase-info');
    const purchaseInfoItems = document.querySelectorAll('.purchase-info div');
    let currentIndex = 0;
    let animationDuration = 0;

    function calculateAnimationDuration() {
        const containerWidth = purchaseInfoContainer.offsetWidth;
        const currentItemWidth = purchaseInfoItems[currentIndex].offsetWidth;
        const distanceToTravel = currentItemWidth + containerWidth;
        const pixelsPerSecond = distanceToTravel / 20; // 假设每秒移动的像素数为容器和当前项的宽度之和的20分之一
        animationDuration = distanceToTravel / pixelsPerSecond * 1000; // 将动画持续时间转换为毫秒
    }

    function showNextInfo() {
        // 计算当前购买信息的动画持续时间
        calculateAnimationDuration();

        // 隐藏所有购买信息
        purchaseInfoItems.forEach((item) => {
            item.style.display = 'none';
        });

        // 显示当前购买信息
        purchaseInfoItems[currentIndex].style.display = 'block';

        // 更新索引，循环显示购买信息
        currentIndex = (currentIndex + 1) % purchaseInfoItems.length;

        // 设置定时器，在动画完成后再滚动到下一条信息
        setTimeout(showNextInfo, animationDuration);
    }

    // 初始化
    showNextInfo();
});

</script>
@stop