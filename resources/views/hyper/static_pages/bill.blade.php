@extends('hyper.layouts.default')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="page-title-box">
            {{-- 确认订单 --}}
            <h4 class="page-title">{{ __('hyper.bill_title') }}</h4>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card card-body">
        	<div class="mx-auto">
        	    {{-- 订单编号 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_order_number') }}：</label><span>{{ $order_sn }}</span></div>
                {{-- 商品名称 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_product_name') }}：</label><span>{{ $title }}</span></div>
                {{-- 商品单价 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_commodity_price') }}：</label><span>{{ $goods_price }}  {{(dujiaoka_config_get('global_currency')) }}</span></div>
                {{-- 购买数量 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_purchase_quantity') }}：</label><span>x {{ $buy_amount }}</span></div>
                 @if($preselection ?? '')
                                        <div class="mb-1"><label>{{ __('order.fields.preselection') }}：</label><span>{{ $preselection }}</span></div>
                                 <div class="mb-1"> <label>自选卡密价格:</label> {{ $goods['preselection'] }} {{(dujiaoka_config_get('global_currency')) }}</div>
                                        
                                    @endif
                @if(!empty($coupon))
                {{-- 优惠码 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_promo_code') }}：</label><span>{{ $coupon['coupon'] }}</span></div>
                {{-- 优惠金额 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_discounted_price') }}：</label><span>{{ $coupon_discount_price }}</span></div>
                @endif
            
                {{-- 电子邮箱 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_email') }}：</label><span>{{ $email }}</span></div>
                @if(!empty($info))
                {{-- 订单资料 --}}
                <div class="mb-1"><label>{{ __('hyper.bill_order_information') }}：</label><span>{{ $info }}</span></div>
                @endif
                {{-- 支付方式 --}}
                
              @if($pay_id > 0 && !empty(trim($pay['pay_name'])))
    <div class="mb-1">
        <label>{{ __('hyper.bill_payment_method') }}：</label>
        <span>{{ $pay['pay_name'] }}</span>
    </div>
    @if($pay['is_openfee'] > 0 && $pay['pay_fee'] > 0)
        <div class="mb-1">
            <label>{{ __('dujiaoka.payment_fee') }}：</label>
            <span>{{ $pay['pay_fee'] }}%</span>
        </div>
    @endif

       @if($pay['is_openhui'] > 0 && $pay['pay_qhuilv'] > 1)
        <div class="mb-1">
            <label>{{ __('当前汇率') }}：{{ $pay['pay_operation'] }}</label>
            <span>{{ $pay['pay_qhuilv'] }}</span>
        </div>
    @endif
   
                 @else
             <div class="mb-1"> <label>{{ __('hyper.bill_payment_method') }}：</label> <span>余额支付</span></div>
                @endif
            
        
                {{-- 商品总价 --}}
            <div class="mb-1"><label>{{ __('hyper.bill_actual_payment') }}：</label><span>{{ $actual_price }}</span></div>


            <div class="text-center">
                {{-- 立即支付 --}}
                @if($pay_id > 0)
                <a href="{{ url('pay-gateway', ['handle' => urlencode($pay['pay_handleroute']),'payway' => $pay['pay_check'], 'orderSN' => $order_sn]) }}"
                   class="btn btn-danger">
                    {{ __('hyper.bill_pay_immediately') }}
                </a>
                @else
                <a href="{{ url('pay/wallet/'.$order_sn) }}"
                   class="btn btn-danger">
                    {{ __('hyper.bill_pay_immediately') }}
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
@stop
