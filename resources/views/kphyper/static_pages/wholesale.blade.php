@extends('hyper.layouts.default')
@section('content')
<div class="row invite-card mt-3">
    <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive invite-table">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">图片</th>
                                <th scope="col">商品名称</th>
                                <th scope="col">原价</th>
                                <th scope="col">{{ Auth::user()->grade }}级代理价</th>
                                <th scope="col">库存</th>
                                <th scope="col">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($goods as $item)
                            <tr>
                                <td scope="row" style="vertical-align: middle;">{{ $item->id }}</td>
                                <td style="vertical-align: middle;">
                                    <img width="30" class="home-img" src="/assets/hyper/images/loading.gif" data-src="{{ picture_ulr($goods['picture']) }}">
                                </td>
                                <td class="text-primary" style="vertical-align: middle;">{{ $item->gd_name }}</td>
                                <td style="vertical-align: middle;">￥{{ $item->actual_price }}{{(dujiaoka_config_get('global_currency')) }}</td>
                                <td style="vertical-align: middle;">￥{{ \App\Service\Util::getGradePrice($item) }}{{(dujiaoka_config_get('global_currency')) }}</td>
                                <td style="vertical-align: middle;">{{ \App\Service\Util::getStock($item) }}</td>
                                <td style="vertical-align: middle;">
                                    <button class="btn btn-primary" onclick="onWholesale({{$item}})">批发</button>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $goods->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="wholesaleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">商品批发</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="buy-form" action="{{ url('/user/wholesale') }}" method="post">
                    @csrf
                    <input type="hidden" name="goods_id" value="">
                    <div class="form-group buy-group">
                        <div class="buy-title">最小批发数：</div>
                        <span id="min_count"></span>
                    </div>
                    <div class="form-group buy-group">
                        <div class="buy-title">最大批发数：</div>
                        <span id="max_count"></span>
                    </div>
                    <div class="form-group buy-group">
                        <div class="buy-title">批发数：</div>
                        <input type="number" name="count" min="1" class="form-control" placeholder="请输入需要批发的数量">
                    </div>
                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-danger" id="onSubmit">
                            <i class="mdi mdi-truck-fast mr-1"></i>
                            点击批发
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
    var goods = {};
   function onWholesale(goods) {
       goods = goods;
       $("#max_count").html(goods.max_buy_count);
       $("#min_count").html(goods.min_buy_count);
       $("input[name='goods_id']").val(goods.id);
       $("#wholesaleModal").modal();
   }
    $('#onSubmit').click(function () {
        var count = $("input[name='count']").val();
        var max_buy_count = $("#max_count").html();
        var min_buy_count = $("#min_count").html();
        if (count > parseInt(max_buy_count)){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}", "请不要大于最大批发数", "top-center", "rgba(0,0,0,0.2)", "info");
            return false;
        }
        if (count < parseInt(min_buy_count)){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}", "请不要小于最小批发数", "top-center", "rgba(0,0,0,0.2)", "info");
            return false;
        }
    })
</script>
@stop
