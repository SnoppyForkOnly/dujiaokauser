<?php

namespace App\Http\Controllers\Pay;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PayController;
use App\Service\OrderProcessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends PayController
{
    public function walletPay(Request $request, $orderSN)
    {
        $user = Auth::user();
        $order = $this->orderService->detailOrderSN($orderSN);
        if ($user->money < $order->actual_price){
            return $this->err("余额不足，请先充值");
        }
        if ($order->actual_price <= 0){
            return $this->err(__('dujiaoka.prompt.order_status_completed'));
        }
        $user->money = bcsub($user->money, $order->actual_price, 2);
        $user->save();

        $this->orderProcessService->completedOrder($orderSN, $order->actual_price, $order->pay_id);

        return redirect()->to('/detail-order-sn/'.$orderSN);
    }
}
