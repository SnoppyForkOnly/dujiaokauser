<div class="header-navbar">
    <div class="container header-flex">
        <!-- LOGO -->
        <a href="/" class="topnav-logo" style="float: none;">
            <img src="{{ picture_ulr(dujiaoka_config_get('img_logo')) }}" height="36">
            <div class="logo-title">{{ dujiaoka_config_get('text_logo') }}</div>
        </a>
        
        <div class="header-right">
   
            <a class="btn btn-outline-primary" href="{{ url('order-search') }}">
                <i class="noti-icon uil-file-search-alt search-icon"></i>
                查询订单
            </a>
            @if(Auth::check())
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-expanded="false">
                <i class="uil uil-user"></i>
                {{ Auth::user()->email }}
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="{{ url('user') }}">个人中心</a>
                @if(Auth::user()->grade > 0)
                <a class="dropdown-item" href="{{ url('/user/wholesale') }}">商品批发</a>
                @endif
                <a class="dropdown-item" href="{{ url('logout') }}">退出登录</a>
            </div>
            @else
            <a class="btn btn-outline-primary" href="{{ url('login') }}">
                <i class="uil uil-user"></i>
                登录
            </a>
  
            @endif
          
        </div>
    </div>
</div>
