<?php
namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use Illuminate\Http\Request;

class AlphaController extends PayController
{
    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);
            //构造要请求的参数数组，无需改动
            $params = [
                'app_id' => $this->payGateway->merchant_id,
                'out_trade_no' => $this->order->order_sn,
                'total_amount' => (int)($this->order->actual_price * 100),
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                'return_url' => url('detail-order-sn', ['orderSN' => $this->order->order_sn]),
            ];
            ksort($params);
            $sign = http_build_query($params);
            $params['sign'] = strtolower(md5($sign . $this->payGateway->merchant_pem));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->payGateway->merchant_key . "/api/v1/tron");
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['User-Agent: Alpha']);
            $res = curl_exec($curl);
            curl_close($curl);
            $res2 = json_decode($res, true);
            if ($res2['code'] === 0) {
                return $this->err($res2['msg']);
            }
            return redirect()->away($res2['url']);
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }

    public function notifyUrl(Request $request)
    {
        $data = $request->all();
        $order = $this->orderService->detailOrderSN($data['out_trade_no']);
        if (!$order) {
            return 'fail';
        }
        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            return 'fail';
        }
        $params = $data;
        unset($params['sign']);
        ksort($params);
        $http_key = http_build_query($params);
        $sign = strtolower(md5($http_key. $payGateway->merchant_pem));

        if ($sign != $data['sign']) { //不合法的数据
            return 'fail';  //返回失败 继续补单
        } else { //合法的数据
            //业务处理
            $this->orderProcessService->completedOrder($data['out_trade_no'], $order->actual_price, $order->pay_id);
            return 'success';
        }
    }
}
