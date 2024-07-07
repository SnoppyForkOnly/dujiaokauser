@extends('hyper.layouts.default')
@section('content')
<div class="row mt-3">
    <div class="col col-lg-9">
        <div class="card">
            <div class="card-header overview">
                <h3 class="card-title"> 账户总览 </h3>
            </div>
            <div class="card-body affiliate-overview">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-red mr-3">
                            <i class="uil uil-user-plus"></i>
                        </span>
                        <div>
                            <div class="h4 m-0">{{ $invite_count }}</div>
                            <small class="sm text-muted">返利订单数</small>
                        </div>
                    </div>
                </div>
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-success mr-3">
                            <i class="uil uil-card-atm"></i>
                        </span>
                        <div>
                            <div class="h4 m-0">{{ $invite_amount }}</div>
                            <small class="sm text-muted">可提现金额</small>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-primary affiliate-show-withdrowal-btn" data-toggle="modal"
                        data-target="#withdrowalModal">提现设置
                </button>
            </div>
        </div>
    </div>
    <div class="col col-lg-3">
        <div class="card">
            <div class="card-header overview"><h3 class="card-title">返现公告</h3></div>
            <div class="card-body contact"><p>{!! dujiaoka_config_get('gonggao_text') !!}</p></div>
        </div>
    </div>
</div>
<div class="row invite-card">
    <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-9">
        <div class="card">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link @if($type === 'invite') active @endif" href="{{ url('/user/invite') }}">返利记录</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if($type === 'withdraw') active @endif" href="{{ url('/user/invite/withdraw') }}">提现记录</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="table-responsive invite-table">
                        @if($type === 'invite')
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">返利用户</th>
                                <th scope="col">返利金额</th>
                                <th scope="col">状态</th>
                                <th scope="col">创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($orders as $order)
                            <tr>
                                <td scope="row">{{ $order->id }}</td>
                               <td>{{ $order->order ? \App\Service\Util::CryptoMail($order->order->email) : '无邮箱信息' }}</td>
                                <td class="text-primary">￥{{ $order->amount }}</td>
                                <td>
                                    @if($order->status === 1)
                                    <label class="badge badge-success">已提现</label>
                                    @else
                                    <label class="badge badge-primary">可提现</label>
                                    @endif
                                </td>
                                <td>{{ $order->created_at }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @else
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">提现金额</th>
                                <th scope="col">提现账号</th>
                                <th scope="col">提现地址</th>
                                <th scope="col">状态</th>
                                <th scope="col">创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($orders as $order)
                            <tr>
                                <td scope="row">{{ $order->id }}</td>
                                <td class="text-primary">￥{{ $order->amount }}</td>
                                @if($order->type === 2)
                                <td class="show-address">{{ \App\Service\Util::WithdrawAccount($order->account) }}</td>
                                @else
                                <td class="show-address">转余额</td>
                                @endif
                                <td class="show-address">{{ $order->address }}</td>
                                <td>
                                    @if($order->status === 1)
                                    <label class="badge badge-success">已完成</label>
                                    @else
                                    <label class="badge badge-primary">审核中</label>
                                    @endif
                                </td>
                                <td>{{ $order->created_at }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">获取推广链接</h3></div>
            <div class="card-body qa">
                <div class="malus-share">
                    <div class="form-group">
                        <label class="form-label">邀请链接</label>
                        <div class="input-group copy-input">
                            <input name="share-link" class="form-control" type="text" readonly=""
                                   value="{{ config('app.url') }}?aff={{ Auth::user()->invite_code }}"><span
                                class="input-group-append">
                                <button class="btn btn-primary"
                                        onclick="copyLink('{{ config('app.url') }}?aff={{ Auth::user()->invite_code }}')">
                                    <i class="uil uil-copy"></i>复制</button></span>
                        </div>
                    </div>
                    <div class="form-group"><label class="form-label">活动规则</label>
                        {!! dujiaoka_config_get('guize_text') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="withdrowalModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">返利提现</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="withdrowal-setting">
                    <div class="account">
                        <h4 class="label">提现金额</h4>
                        <div class="form-wrapper account-name">
                            <div class="mb-2"><span style="font-size: 18px;font-weight: bold;color: #000;">￥{{ $invite_amount }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="account-info">
                        <div class="label">提现类型</div>
                        <div class="payment-type">
                            <div onclick="changeType('balance')" class="balance active">
                                <span style="font-weight: 800;">转余额</span>
                            </div>
                            <div onclick="changeType('withdraw')" class="withdraw">
                                <span style="font-weight: 800;">提现账户</span>
                            </div>
                        </div>
                    </div>
                    <div class="account-info show-info">
                        <div class="label">账户类型</div>
                        <div class="payment-type">
                            <div class="USDT active" onclick="changeAccount('USDT')">
                                <svg t="1663682664904" class="icon" viewBox="0 0 1024 1024" version="1.1"
                                     xmlns="http://www.w3.org/2000/svg" p-id="1837" width="24" height="24">
                                    <path
                                        d="M1023.082985 511.821692c0 281.370746-228.08199 509.452736-509.452736 509.452736-281.360557 0-509.452736-228.08199-509.452737-509.452736 0-281.365652 228.092179-509.452736 509.452737-509.452737 281.370746 0 509.452736 228.087085 509.452736 509.452737"
                                        fill="#1BA27A" p-id="1838"></path>
                                    <path
                                        d="M752.731701 259.265592h-482.400796v116.460896h182.969951v171.176119h116.460895v-171.176119h182.96995z"
                                        fill="#FFFFFF" p-id="1839"></path>
                                    <path
                                        d="M512.636816 565.13592c-151.358408 0-274.070289-23.954468-274.070289-53.50782 0-29.548259 122.706786-53.507821 274.070289-53.507821 151.358408 0 274.065194 23.959562 274.065194 53.507821 0 29.553353-122.706786 53.507821-274.065194 53.50782m307.734925-44.587303c0-38.107065-137.776398-68.995184-307.734925-68.995184-169.953433 0-307.74002 30.888119-307.74002 68.995184 0 33.557652 106.837333 61.516418 248.409154 67.711363v245.729433h116.450707v-245.632637c142.66205-6.001353 250.615085-34.077294 250.615084-67.808159"
                                        fill="#FFFFFF" p-id="1840"></path>
                                </svg>
                                <span>USDT</span>
                            </div>
                            <div class="alipay" onclick="changeAccount('alipay')">
                                <svg t="1602939269695" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1127" width="32" height="32"><path d="M902.095 652.871l-250.96-84.392s19.287-28.87 39.874-85.472c20.59-56.606 23.539-87.689 23.539-87.689l-162.454-1.339v-55.487l196.739-1.387v-39.227H552.055v-89.29h-96.358v89.294H272.133v39.227l183.564-1.304v59.513h-147.24v31.079h303.064s-3.337 25.223-14.955 56.606c-11.615 31.38-23.58 58.862-23.58 58.862s-142.3-49.804-217.285-49.804c-74.985 0-166.182 30.123-175.024 117.55-8.8 87.383 42.481 134.716 114.728 152.139 72.256 17.513 138.962-0.173 197.04-28.607 58.087-28.391 115.081-92.933 115.081-92.933l292.486 142.041c-11.932 69.3-72.067 119.914-142.387 119.844H266.37c-79.714 0.078-144.392-64.483-144.466-144.194V266.374c-0.074-79.72 64.493-144.399 144.205-144.47h491.519c79.714-0.073 144.396 64.49 144.466 144.203v386.764z m-365.76-48.895s-91.302 115.262-198.879 115.262c-107.623 0-130.218-54.767-130.218-94.155 0-39.34 22.373-82.144 113.943-88.333 91.519-6.18 215.2 67.226 215.2 67.226h-0.047z" fill="#02A9F1" p-id="1128" data-spm-anchor-id="a313x.7781069.0.i1" class="selected"></path></svg>
                                <span>支付宝</span>
                            </div>
                            <div class="wechat" onclick="changeAccount('wechat')">
                                <svg t="1602939526328" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1368" width="32" height="32"><path d="M395.846 603.585c-3.921 1.98-7.936 2.925-12.81 2.925-10.9 0-19.791-5.85-24.764-14.625l-2.006-3.864-78.106-167.913c-0.956-1.98-0.956-3.865-0.956-5.845 0-7.83 5.928-13.68 13.863-13.68 2.965 0 5.928 0.944 8.893 2.924l91.965 64.43c6.884 3.864 14.82 6.79 23.708 6.79 4.972 0 9.85-0.945 14.822-2.926L861.71 282.479c-77.149-89.804-204.684-148.384-349.135-148.384-235.371 0-427.242 157.158-427.242 351.294 0 105.368 57.361 201.017 147.323 265.447 6.88 4.905 11.852 13.68 11.852 22.45 0 2.925-0.957 5.85-2.006 8.775-6.881 26.318-18.831 69.334-18.831 71.223-0.958 2.92-2.013 6.79-2.013 10.75 0 7.83 5.929 13.68 13.865 13.68 2.963 0 5.928-0.944 7.935-2.925l92.922-53.674c6.885-3.87 14.82-6.794 22.756-6.794 3.916 0 8.889 0.944 12.81 1.98 43.496 12.644 91.012 19.53 139.48 19.53 235.372 0 427.24-157.158 427.24-351.294 0-58.58-17.78-114.143-48.467-163.003l-491.39 280.07-2.963 1.98z" fill="#09BB07" p-id="1369"></path></svg>
                                <span>微信</span>
                            </div>
                        </div>
                        <div class="account">
                            <div class="form-wrapper account-name">
                                <div class="label">账号地址</div>
                                <input type="text" name="address" class="form-control" placeholder="请输入提现地址" value="">
                            </div>
                        </div>
                    </div>
                    <div class="desc"><h4>提现说明:</h4>
                        {!! dujiaoka_config_get('tixian_text') !!}
                        <button class="btn btn-primary btn-pill" onclick="onSubmit()">点击提现</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
    let param = {
        type: 'balance',
        address: '',
        account: 'USDT',
        _token: "{{ csrf_token() }}",
    };
    $(".show-info").hide();
    function copyLink(link) {
        var textField = document.createElement('textarea');
        $(textField).html(link)
        document.body.appendChild(textField);
        textField.select();
        document.execCommand('copy');
        $(textField).remove();
        $.NotificationApp.send("成功", "复制成功~", "top-center", "rgba(0,0,0,0.2)", "success");
    }
    function changeType(type){
        param.type = type;
        if (type === 'balance'){
            $(".show-info").hide();
        } else {
            $(".show-info").show();
        }
        $("."+type).addClass('active').siblings().removeClass('active');
    }
    function changeAccount(account){
        param.account = account;
        $("."+account).addClass('active').siblings().removeClass('active');
    }
    function onSubmit() {
        param.address = $("input[name='address']").val();
        if (param.type === 'withdraw' && param.address === ''){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","请输入提现账户地址！","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        $.post("/user/withdraw", param, function (res) {
            if (res.code) {
                $.NotificationApp.send("成功", res.message,"top-center","rgba(0,0,0,0.2)","success");
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                $.NotificationApp.send("{{ __('hyper.buy_warning') }}",res.message,"top-center","rgba(0,0,0,0.2)","info");
            }
        })
    }
</script>
@stop
