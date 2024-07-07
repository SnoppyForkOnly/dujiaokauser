<?php

namespace App\Http\Controllers\Home;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Jobs\MailSend;
use App\Jobs\OrderExpired;
use App\Models\Carmis;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\InviteUser;
use App\Models\Order;
use App\Models\Pay;
use App\Models\User;
use App\Models\Withdraw;
use App\Service\OrderProcessService;
use App\Service\PayService;
use App\Service\Util;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;




class UserController extends BaseController
{
    /**
     * 卡密服务层
     * @var \App\Service\CarmisService
     */
    private $carmisService;

    /**
     * 邮件服务层
     * @var \App\Service\EmailtplService
     */
    private $emailtplService;
    
    /**
     * 订单处理服务层
     * @var \App\Service\OrderProcessService
     */
    private $orderProcessService;

    public function __construct(OrderProcessService $orderProcessService)
    {  
        $this->orderProcessService = $orderProcessService;
        $this->orderService = app('Service\OrderService');
        $this->carmisService = app('Service\CarmisService');
        $this->emailtplService = app('Service\EmailtplService');
        $this->goodsService = app('Service\GoodsService');
    }

    /**
     * 计算手续费
     * @param float $price
     * @param int $payID
     * @return float
     */
    public function calculatePayFee(float $price, int $payID): float
    {
        if ($payID > 0) {
            $payDetail = app('Service\PayService')->detail($payID);

            // 检查 is_openfee 字段是否为 1
            if ($payDetail->is_openfee == 1) {
                $fee = $payDetail->pay_fee;

                // 如果价格为 0 或手续费率为 0，则手续费为 0
                if (!$price || $fee == 0) {
                    return 0.00;
                }

                // 计算手续费
                $fee = ceil($fee * $price) / 100;
                return $fee;
            }
        }

        // 如果 payID 小于等于 0，或 is_openfee 不为 1，则手续费为 0
        return 0.00;
    }


public function calculatePriceWithExchangeRate(float $price, int $payID): float
{
    if ($payID > 0) {
        $payDetail = app('Service\PayService')->detail($payID);

        // 检查 is_openhui 字段是否为 1 以及 pay_qhuilv 是否有效
        if ($payDetail->is_openhui == 1 && !empty($payDetail->pay_qhuilv) && $payDetail->pay_qhuilv != 0) {
            $exchangeRate = $payDetail->pay_qhuilv;
            $operation = $payDetail->pay_operation ?? '*'; // 默认为乘法

            // 如果价格为0，直接返回0，避免不必要的计算
            if ($price == 0) {
                return 0.00;
            }

            // 根据运算符号计算新价格
            switch ($operation) {
                case '*':
                    $price *= $exchangeRate;
                    break;
                case '/':
                    // 防止除以0
                    if ($exchangeRate != 0) {
                        $price /= $exchangeRate;
                    } else {
                        // 如果汇率为0，避免除法操作，直接返回原价
                        return $price;
                    }
                    break;
                default:
                    // 如果运算符不是乘或除，不调整价格
                    return $price;
            }

            return round($price, 2); // 返回四舍五入到小数点后两位的结果
        }
    }

    // 如果 payID 小于等于 0，或 is_openhui 不为 1，或汇率值无效，则返回原价
    return $price;
}


   public function getUserInfo(Request $request)
{    
  

    return response()->json($request->user());
  
   
}
   public function index(Request $request)
    {
        
      
    // 加载支付方式
    $client = Pay::PAY_CLIENT_PC;
    if (app('Jenssegers\Agent')->isMobile()) {
        $client = Pay::PAY_CLIENT_MOBILE;
    }
    
    $payways = (new PayService())->pays($client);
    
    // 获取配置值
    $configValue = dujiaoka_config_get('open_czid');;   //充值支付方式ID

    
    // 如果配置值不为0，应用过滤
    if ($configValue !== '0') {
        $allowedPayways = explode(',', $configValue); // 将配置值转换为数组
        $payways = array_filter($payways, function($way) use ($allowedPayways) {
            return in_array($way['id'], $allowedPayways);
        });
    }

   

    // 其他代码
    $orders = Order::query()->where('email', Auth::user()->email)->orderByDesc('id')->paginate(10);
    
    $recharge_promotion = dujiaoka_config_get('recharge_promotion');
    $invite_count = User::query()->where('pid', Auth::id())->count();

    return $this->render('static_pages/user', compact('payways', 'orders', 'recharge_promotion', 'invite_count'));
    
   
}

    public function recharge(Request $request)
    {
      
        $user = Auth::user();
        DB::beginTransaction();
        try {
            // 创建订单
            $order = new Order();
            // 生成订单号
            $order->order_sn = Str::random(16);
            // 设置商品
            $order->goods_id = 0;
            // 标题
            $order->title = "余额充值";
            // 订单类型
            $order->type = 1;
            // 查询密码
            $order->search_pwd = "";
            // 邮箱
            $order->email = $user->email;
            // 支付方式.
            $order->pay_id = $request->input('payway');
            // 商品单价
            // 商品单价 - 使用用户输入的充值金额
        $order->goods_price = $request->input('amount');
            // 购买数量
            $order->buy_amount = 1;
            // 订单详情
            $order->info = "用户余额充值";
            // ip地址
            $order->buy_ip = $request->getClientIp();
            // 订单总价
           // 获取订单总价
$totalPrice = $request->input('amount');
$order->total_price = $totalPrice;

// 计算手续费
$fee = $this->calculatePayFee($totalPrice, $request->input('payway'));

// 应用手续费到总价
$priceWithFee = $totalPrice + $fee;

// 应用汇率调整到已经包含手续费的价格
$actualPrice = $this->calculatePriceWithExchangeRate($priceWithFee, $request->input('payway'));

$order->actual_price = $actualPrice;
            $order->save();
            // 将订单加入队列 x分钟后过期
            $expiredOrderDate = dujiaoka_config_get('order_expire_time', 5);
            OrderExpired::dispatch($order->order_sn)->delay(Carbon::now()->addMinutes($expiredOrderDate));
        
            DB::commit();
      
            // 设置订单cookie
            $this->queueCookie($order->order_sn);
            return redirect(url('/bill', ['orderSN' => $order->order_sn]));


        } catch (Exception $exception) {
        DB::rollBack();
        return $this->err($exception->getMessage());
    }
}

    /**
     * 设置订单cookie.
     * @param string $orderSN 订单号.
     */
    private function queueCookie(string $orderSN): void
    {
        // 设置订单cookie
        $cookies = Cookie::get('dujiaoka_orders');
        if (empty($cookies)) {
            Cookie::queue('dujiaoka_orders', json_encode([$orderSN]));
        } else {
            $cookies = json_decode($cookies, true);
            array_push($cookies, $orderSN);
            Cookie::queue('dujiaoka_orders', json_encode($cookies));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function invite(Request $request, $type = "invite")
    {
        if ($type === "invite"){
            $orders = InviteUser::with(['order:id,email'])->where('user_id', Auth::id())->orderByDesc('id')->paginate(10);
        } else {
            $orders = Withdraw::where('user_id', Auth::id())->orderByDesc('id')->paginate(10);
        }
        $invite_count = InviteUser::where('user_id', Auth::id())->count('id');
        $invite_amount = InviteUser::where('user_id', Auth::id())->where('status', 0)->sum('amount');

        return $this->render('static_pages/invite',
            compact('orders', 'invite_count', 'invite_amount', 'type')
        );
    }

    public function withdraw(Request $request)
    {
        $user_id = Auth::id();
        $key = 'withdrawing_'.$user_id;
        if (Cache::has($key)) {
            return response()->json(['code' => 0, 'message' => '请不要频繁点击！']);
        }
        Cache::forever($key, 'yes');
        $data = $request->all();
        if ($data['type'] === 'withdraw' && empty($data['address'])) {
            Cache::forget($key);
            return response()->json(['code' => 0, 'message' => '请输入提现账户!']);
        }
        $invite_amount = InviteUser::where('user_id', $user_id)->where('status', 0)->sum('amount');
        if ($invite_amount <= 0) {
            Cache::forget($key);
            return response()->json(['code' => 0, 'message' => '没有可提现的金额']);
        }
        DB::beginTransaction();
        try {
            $model = new Withdraw();
            $model->user_id = $user_id;
            $model->amount = $invite_amount;
            $model->type = $data['type'] === 'withdraw' ? 2 : 1;
            $model->status = $data['type'] === 'withdraw' ? 0 : 1;
            $model->address = $data['address'];
            $model->account = $data['type'] === 'withdraw' ? $data['account'] : null;
            $model->save();
            InviteUser::query()
                ->where(['user_id' => $user_id, 'status' => 0])
                ->update(['status' => 1, 'withdraw_id' => $model->id]);
            if ($model->type === 1){
                User::query()->where('id', $user_id)->increment('money', $invite_amount);
            }
 
            DB::commit();
             
 

       
        } catch (\Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            return response()->json(['code' => 0, 'message' => $exception->getMessage()]);
        }
        Cache::forget($key);
        return response()->json(['code' => 1, 'message' => '提交成功!']);
    }

    public function wholesale(Request $request)
    {
        $user = $request->user();
        $goods = Goods::query()
            ->where('grade_' .$user->grade, '>', 0)
            ->where('is_open', GoodsGroup::STATUS_OPEN)
            ->orderBy('ord', 'DESC')
            ->paginate(10);
        // 加载支付方式.
        $client = Pay::PAY_CLIENT_PC;
        if (app('Jenssegers\Agent')->isMobile()) {
            $client = Pay::PAY_CLIENT_MOBILE;
        }
        $payways = (new PayService())->pays($client);

        return $this->render('static_pages/wholesale', compact('goods', 'payways'));
    }
     

/**
 * 修改密码
 * @param Request $request
 * @return \Illuminate\Http\Response
 */


public function changePassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'current_password' => 'required',
        'new_password' => 'required|min:6|confirmed',
    ], [
        'current_password.required' => '当前密码不能为空。',
        'new_password.required' => '新密码不能为空。',
        'new_password.min' => '新密码至少需要6个字符。',
        'new_password.confirmed' => '两次输入的新密码不一致。',
    ]);

    if ($validator->fails()) {
        // 验证失败，重定向到 /user 并带上错误消息
        return redirect('/user')->withErrors($validator)->withInput();
    }

    $user = Auth::user();

    if (!Hash::check($request->input('current_password'), $user->password)) {
        // 当前密码不正确，重定向到 /user 并带上错误消息
        return redirect('/user')->withErrors(['current_password' => '当前密码不正确']);
    }

    // 更新用户密码
    $user->password = bcrypt($request->input('new_password'));
    $user->save();

    // 密码更新成功，重定向到 /user 并带上成功消息
    return redirect('/user')->with('success', '密码修改成功！');
}




     
    public function wholesaleSubmit(Request $request)
    {
        $user = $request->user();
        $count = (int)$request->input('count', 0);
        $goods = Goods::query()->find($request->input('goods_id'));
        if ($count > $goods->max_buy_count){
            return $this->err("请不要大于最大批发数");
        }
        if ($count < $goods->min_buy_count){
            return $this->err("请不要小于最小批发数");
        }
        $grade = 'grade_'.$user->grade;
        $amount = $goods->$grade * $count;
        if ($amount > $user->money) {
            return $this->err("余额不足，请先前往【个人中心】充值!");
        }
        if ($count > Util::getStock($goods)) {
            return $this->err(__('dujiaoka.prompt.inventory_shortage'));
        }
        // 创建订单
        $order = new Order();
        // 生成订单号
        $order->order_sn = Str::random(16);
        // 设置商品
        $order->goods_id = $goods->id;
        // 标题
        $order->title = "{$goods->gd_name} - 批发";
        // 订单类型
        $order->type = 1;
        // 查询密码
        $order->search_pwd = "";
        // 邮箱
        $order->email = $user->email;
        // 支付方式.
        $order->pay_id = 0;
        // 商品单价
        $order->goods_price = $goods->$grade;
        // 购买数量
        $order->buy_amount = $count;
        // 订单详情
        $order->info = "用户【{$user->email}】批发商品 {$user}";
        // ip地址
        $order->buy_ip = $request->getClientIp();
        // 订单总价
        $order->total_price = $amount;
        // 订单实际需要支付价格
        $order->actual_price = $amount;
        // 保存订单
        $order->save();

        // 获得卡密
        $carmis = $this->carmisService->withGoodsByAmountAndStatusUnsold($order->goods_id, $order->buy_amount);
        // 实际可使用的库存已经少于购买数量了
        if (count($carmis) != $order->buy_amount) {
            $order->info = __('dujiaoka.prompt.order_carmis_insufficient_quantity_available');
            $order->status = Order::STATUS_ABNORMAL;
            $order->save();
        }
        $carmisInfo = array_column($carmis, 'carmi');
        $ids = array_column($carmis, 'id');
        $order->info = implode(PHP_EOL, $carmisInfo);
        $order->status = Order::STATUS_COMPLETED;
        $order->save();
        $user->money = bcsub($user->money, $amount, 2);
        $user->save();
        // 将卡密设置为已售出
        $this->carmisService->soldByIDS($ids);
        
       
     
        // 邮件数据
        $mailData = [
            'created_at' => $order->create_at,
            'product_name' => $order->goods->gd_name,
            'webname' => dujiaoka_config_get('title'),
            'ord_info' => implode('<br/>', $carmisInfo),
            'ord_title' => $order->title,
            'order_id' => $order->order_sn,
            'buy_amount' => $order->buy_amount,
            'ord_price' => $order->actual_price,
        ];
        $tpl = $this->emailtplService->detailByToken('card_send_user_email');
        $mailBody = replace_mail_tpl($tpl, $mailData);
        
        // 邮件发送
        MailSend::dispatch($order->email, $mailBody['tpl_name'], $mailBody['tpl_content']);

        return $this->render('static_pages/orderinfo', ['orders' => [$order]], __('dujiaoka.page-title.order-detail'));
    }
    
    



      


}