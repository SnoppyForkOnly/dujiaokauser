<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */

namespace App\Service;

use App\Exceptions\RuleValidationException;
use App\Jobs\ApiHook;
use App\Jobs\ApiWebhookPush;
use App\Jobs\MailSend;
use App\Jobs\OrderExpired;
use App\Jobs\ServerJiang;
use App\Jobs\TelegramPush;
use App\Jobs\BarkPush;
use App\Jobs\WorkWeiXinPush;
use App\Models\BaseModel;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\InviteUser;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * 订单处理层
 *
 * Class OrderProcessService
 * @package App\Service
 * @author: Assimon
 * @email: Ashang@utf8.hk
 * @blog: https://utf8.hk
 * Date: 2021/5/30
 */
class OrderProcessService
{

    const PENDING_CACHE_KEY = 'PENDING_ORDERS_LIST';

    /**
     * 优惠码服务层
     * @var \App\Service\CouponService
     */
    private $couponService;

    /**
     * 订单服务层
     * @var \App\Service\OrderService
     */
    private $orderService;

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
     * 商品服务层.
     * @var \App\Service\GoodsService
     */
    private $goodsService;

    /**
     * 商品
     * @var Goods
     */
    private $goods;

    /**
     * 优惠码
     * @var Coupon;
     */
    private $coupon;

    /**
     * 其他输入框
     * @var string
     */
    private $otherIpt;

    /**
     * 购买数量
     * @var int
     */
    private $buyAmount;

    /**
     * 购买邮箱
     * @var string
     */
    private $email;

    /**
     * 查询密码
     * @var string
     */
    private $searchPwd;

    /**
     * 邀请码
     * @var string
     */
    private $aff;

    /**
     * 下单id
     * @var string
     */
    private $buyIP;

    /**
     * 支付方式
     * @var int
     */
    private $payID;

      /**
     * 预选的卡密ID
     * @var int
     */
    private $carmiID;


    public function __construct()
    {
        $this->couponService = app('Service\CouponService');
        $this->orderService = app('Service\OrderService');
        $this->carmisService = app('Service\CarmisService');
        $this->emailtplService = app('Service\EmailtplService');
        $this->goodsService = app('Service\GoodsService');
        $this->payService = app('Service\PayService');//添加支付服务层，没添加报错
    }

    /**
     * 设置支付方式
     * @param int $payID
     */
    public function setPayID(int $payID): void
    {
        $this->payID = $payID;
    }



    /**
     * 下单ip
     * @param mixed $buyIP
     */
    public function setBuyIP($buyIP): void
    {
        $this->buyIP = $buyIP;
    }

    /**
     * 设置查询密码
     * @param mixed $searchPwd
     */
    public function setSearchPwd($searchPwd): void
    {
        $this->searchPwd = $searchPwd;
    }

    /**
     * 设置邀请码
     * @param mixed $aff
     */
    public function setAff($aff): void
    {
        if (Auth::check() && Auth::user()->pid > 0){
            $this->aff = User::query()->where('id', Auth::user()->pid)->value('invite_code');
        } else {
            $this->aff = $aff;
        }
    }

    /**
     * 设置购买数量
     * @param mixed $buyAmount
     */
    public function setBuyAmount($buyAmount): void
    {
        $this->buyAmount = $buyAmount;
    }

    /**
     * 设置下单邮箱
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }
    
       /**
     * 设置预选卡密ID
     * @param int $id
     */
    public function setCarmi(int $id): void
    {
        $this->carmiID = $id;
    }

    /**
     * 设置商品
     *
     * @param Goods $goods
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function setGoods(Goods $goods)
    {
        $this->goods = $goods;
    }

    /**
     * 设置优惠码.
     *
     * @param ?Coupon $coupon
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function setCoupon(?Coupon $coupon)
    {
        $this->coupon = $coupon;
    }

    /**
     * 其他输入框设置.
     *
     * @param ?string $otherIpt
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function setOtherIpt(?string $otherIpt)
    {
        $this->otherIpt = $otherIpt;
    }

    /**
     * 计算优惠码价格
     *
     * @return float
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    private function calculateTheCouponPrice(): float //多打了一个f 造成的问题
    {
        $couponPrice = 0;
        // 优惠码优惠价格
        if ($this->coupon) {
            switch($this->coupon->type){
                case Coupon::TYPE_FIXED:
                    $couponPrice =  $this->coupon->discount;
                    break;
                case Coupon::TYPE_PERCENT:
                    $totalPrice = $this->calculateTheTotalPrice(); // 总价
                    $couponPrice = $totalPrice - bcmul($totalPrice, $this->coupon->discount, 2); //计算折扣
                    break;
                case Coupon::TYPE_EACH:
                    $couponPrice = bcmul($this->coupon->discount, $this->buyAmount, 2);
                    break;
            }
        }
        return $couponPrice;
    }

    /**
     * 计算批发优惠
     * @return float
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    private function calculateTheWholesalePrice(): float
    {
        // 优惠码与批发价不叠加
        if($this->coupon)
            return 0;
        $wholesalePrice = 0; // 优惠单价
        $wholesaleTotalPrice = 0; // 优惠总价
        if ($this->goods->wholesale_price_cnf) {
            $formatWholesalePrice = format_wholesale_price($this->goods->wholesale_price_cnf);
            foreach ($formatWholesalePrice as $item) {
                if ($this->buyAmount >= $item['number']) {
                    $wholesalePrice = $item['price'];
                }
            }
        }
        if ($wholesalePrice > 0 ) {
            $totalPrice = $this->calculateTheTotalPrice(); // 实际原总价
            $newTotalPrice = bcmul($wholesalePrice, $this->buyAmount, 2); // 批发价优惠后的总价
            $wholesaleTotalPrice = bcsub($totalPrice, $newTotalPrice, 2); // 批发总优惠
        }
        return $wholesaleTotalPrice;
    }

    /**
     * 订单总价
     * @return float
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
/**
 * 订单总价
 * @return float
 */
private function calculateTheTotalPrice(): float
{
    $price = $this->goods->actual_price;
    
    // 如果预选了卡密，则加上预选加价
    if($this->carmiID) {
        $price += $this->goods->preselection;
    }

    $totalPrice = bcmul($price, $this->buyAmount, 2);

    // 记录日志
 
    return $totalPrice;
}


    /**
     * 计算支付通道手续费
     * @return float
     *fakaboy优化改良
  *fakaboy优化改良
     */
 
private function calculateThePayFee(float $price): float
{
    // 尝试获取支付详情
    $payDetail = $this->payService->detail($this->payID);

    // 确保 payDetail 是一个对象
    if (is_object($payDetail)) {
        // 检查 pay_name 不为空且 is_openfee 字段是否为 1
        if (!empty($payDetail->pay_name) && $payDetail->is_openfee == 1) {
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

    // 如果不满足计算手续费的条件，则手续费为 0
    return 0.00;
}
//汇率计算
//fakaboy原创
private function calculatePriceWithExchangeRate(float $price): float
{
    // 尝试获取支付详情
    $payDetail = $this->payService->detail($this->payID);

    // 确保 payDetail 是一个对象
    if (is_object($payDetail)) {
        // 检查 pay_name 不为空且 is_openhui 字段是否为 1
        if (!empty($payDetail->pay_name) && $payDetail->is_openhui == 1) {
            // 设置默认汇率为1，如果 pay_qhuilv 未设置或为0
            $exchangeRate = !empty($payDetail->pay_qhuilv) ? $payDetail->pay_qhuilv : 1.00;

            // 确保运算符号是乘或除
            if (in_array($payDetail->pay_operation, ['*', '/'])) {
                // 如果价格为0，直接返回0，避免不必要的计算
                if ($price == 0) {
                    return 0.00;
                }

                // 根据汇率运算符号计算新价格
                if ($payDetail->pay_operation == '*') {
                    $price *= $exchangeRate;
                } else if ($payDetail->pay_operation == '/') {
                    // 除法操作，防止除以0
                    if ($exchangeRate != 0) {
                        $price /= $exchangeRate;
                    }
                }

                return round($price, 2); // 返回四舍五入到小数点后两位的结果
            }
        }
    }

    // 如果不满足条件，则返回原价
    return $price;
}








    /**
     * 计算实际需要支付的价格
     *
     * @param float $totalPrice 总价
     * @param float $couponPrice 优惠码优惠价
     * @param float $wholesalePrice 批发优惠
     * @return float
     * @fakagege汇率功能
    
     */
   private function calculateTheActualPrice(float $totalPrice, float $couponPrice, float $wholesalePrice): float
{
    // 首先从calculateTheTotalPrice获取包含预选卡密价格的总价
    $totalPriceWithPreselection = $this->calculateTheTotalPrice();

    // 从总价中减去优惠码优惠价和批发优惠
    $actualPrice = bcsub($totalPriceWithPreselection, $couponPrice, 2);
    $actualPrice = bcsub($actualPrice, $wholesalePrice, 2);

    // 确保价格不为负数
    if ($actualPrice <= 0) {
        $actualPrice = 0;
    }

    // 加上支付通道手续费
    $actualPrice += $this->calculateThePayFee($actualPrice);
        // 如果启用了汇率计算，根据汇率调整实际价格
        $actualPrice = $this->calculatePriceWithExchangeRate($actualPrice);

    return $actualPrice;
}


    /**
     * 创建订单.
     * @return Order
     * @throws RuleValidationException
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function createOrder(): Order
    {
        try {
            $order = new Order();
            // 生成订单号
            $order->order_sn = strtoupper(Str::random(16));
            // 设置商品
            $order->goods_id = $this->goods->id;
            // 标题
            $order->title = $this->goods->gd_name . ' x ' . $this->buyAmount;
            // 订单类型
            $order->type = $this->goods->type;
            // 查询密码
            $order->search_pwd = $this->searchPwd;
            // 邀请码
            $order->aff = $this->aff;
            // 邮箱
            $order->email = $this->email;
            // 支付方式.
            $order->pay_id = $this->payID;
            // 商品单价
            $order->goods_price = $this->goods->actual_price;
            // 购买数量
            $order->buy_amount = $this->buyAmount;
            // 预选卡密
            $order->carmi_id = $this->carmiID;
            // 订单详情
            $order->info = $this->otherIpt;
            // ip地址
            $order->buy_ip = $this->buyIP;
            // 优惠码优惠价格
            $order->coupon_discount_price = $this->calculateTheCouponPrice();
            if ($this->coupon) {
                $order->coupon_id = $this->coupon->id;
            }
            // 批发价
            $order->wholesale_discount_price = $this->calculateTheWholesalePrice();
            // 订单总价
            $order->total_price = $this->calculateTheTotalPrice();
            // 订单实际需要支付价格
            $order->actual_price = $this->calculateTheActualPrice(
                $this->calculateTheTotalPrice(),
                $this->calculateTheCouponPrice(),
                $this->calculateTheWholesalePrice()
            );
            
            
            // 保存订单
            $order->save();
            // 如果有用到优惠券
            if ($this->coupon) 
            $this->couponService->retDecr($this->coupon->coupon);// 使用次数-1
            // 将订单加入队列 x分钟后过期
            $expiredOrderDate = dujiaoka_config_get('order_expire_time', 5);
            OrderExpired::dispatch($order->order_sn)->delay(Carbon::now()->addMinutes($expiredOrderDate));
            return $order;
        } catch (\Exception $exception) {
            throw new RuleValidationException($exception->getMessage());
        }

    }


    /**
     * 订单成功方法
     *
     * @param string $orderSN 订单号
     * @param float $actualPrice 实际支付金额
     * @param string $tradeNo 第三方订单号
     * @return Order
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function completedOrder(string $orderSN, float $actualPrice, string $tradeNo = '')
    {
        DB::beginTransaction();
        try {
            // 得到订单详情
            $order = $this->orderService->detailOrderSN($orderSN);
            if (!$order) {
                throw new \Exception(__('dujiaoka.prompt.order_does_not_exist'));
            }
            // 订单已经处理
            if ($order->status == Order::STATUS_COMPLETED) {
                throw new \Exception(__('dujiaoka.prompt.order_status_completed'));
            }
            $bccomp = bccomp($order->actual_price, $actualPrice, 2);
            // 金额不一致
            if ($bccomp != 0) {
                throw new \Exception(__('dujiaoka.prompt.order_inconsistent_amounts'));
            }
            $order->actual_price = $actualPrice;
            $order->trade_no = $tradeNo;
            // 区分订单类型
            // 自动发货
            if ($order->goods_id === 0){ // 余额充值
                $completedOrder = $this->processMoney($order);
            }  elseif ($order->type == Order::AUTOMATIC_DELIVERY) {
                $completedOrder = $this->processAuto($order);
            } else {
                $completedOrder = $this->processManual($order);
            }
            if ($order->goods_id){
                // 销量加上
                $this->goodsService->salesVolumeIncr($order->goods_id, $order->buy_amount);
                $goods = $this->goodsService->detail($order->goods_id);
                // 如果有回调事件
                if ($goods->api_hook){
                    ApiHook::dispatch($order);
                }
                if ($order->aff && $goods->open_rebate){
                    $this->rebateAmount($order);
                }
            }
            DB::commit();
            // 如果开启了server酱
            if (dujiaoka_config_get('is_open_server_jiang', 0) == BaseModel::STATUS_OPEN) {
                ServerJiang::dispatch($order);
            }
            // 如果开启了TG推送
            if (dujiaoka_config_get('is_open_telegram_push', 0) == BaseModel::STATUS_OPEN) {
                TelegramPush::dispatch($order);
            }
            // 如果开启了Bark推送
            if (dujiaoka_config_get('is_open_bark_push', 0) == BaseModel::STATUS_OPEN) {
                BarkPush::dispatch($order);
            }
            // 如果开启了企业微信Bot推送
            if (dujiaoka_config_get('is_open_qywxbot_push', 0) == BaseModel::STATUS_OPEN) {
                WorkWeiXinPush::dispatch($order);
            }
             // 回调事件
             ApiHook::dispatch($order);
            return $completedOrder;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new RuleValidationException($exception->getMessage());
        }
    }



    public function processMoney(Order $order)
{
    Log::info('processMoney started', ['order_id' => $order->order_sn]);

    $email = $order->email;
    $amount = $order->goods_price;
    $order->status = Order::STATUS_COMPLETED;
    $order->save();

    Log::info('Order status updated to completed', ['order_id' => $order->order_sn]);

    // 获取充值促销配置
    $recharge_promotion = dujiaoka_config_get('recharge_promotion', []);
    if (!is_array($recharge_promotion)) {
        $recharge_promotion = [];
    }

    // 对促销配置进行排序
    if ($recharge_promotion) {
        $sort = array_column($recharge_promotion, 'amount');
        array_multisort($sort, SORT_DESC, $recharge_promotion);

        // 根据促销配置计算最终金额
        foreach ($recharge_promotion as $item) {
            if (isset($item['amount']) && isset($item['value']) && $amount >= $item['amount']) {
                $amount = bcadd($amount, $item['value'], 2);
                Log::info('Recharge promotion applied', ['amount' => $amount, 'promotion' => $item]);
                break;
            }
        }
    }

    // 更新用户余额
    $user = User::query()->where('email', $email)->first();
    if ($user) {
        $oldBalance = $user->money;
        $user->money = bcadd($user->money, $amount, 2);
        $user->save();
        Log::info('User balance updated', [
            'user_id' => $user->id,
            'old_balance' => $oldBalance,
            'new_balance' => $user->money,
            'amount_added' => $amount
        ]);
    } else {
        Log::warning('User not found', ['email' => $email]);
    }

    // 准备邮件数据
    $mailData = [
        'product_name' => "余额充值",
        'webname' => dujiaoka_config_get('text_logo', '独角数卡'),
        'weburl' => config('app.url') ?? 'http://dujiaoka.com',
        'ord_info' => str_replace(PHP_EOL, '<br/>', $order->info),
        'ord_title' => $order->title,
        'order_id' => $order->order_sn,
        'buy_amount' => $order->buy_amount,
        'ord_price' => $order->actual_price,
        'created_at' => $order->created_at,
    ];

    // 获取邮件模板并替换内容
    $tpl = $this->emailtplService->detailByToken('manual_send_manage_mail');
    $mailBody = replace_mail_tpl($tpl, $mailData);
    $manageMail = dujiaoka_config_get('manage_email', '');

    // 发送邮件
    MailSend::dispatch($manageMail, $mailBody['tpl_name'], $mailBody['tpl_content']);
    Log::info('Email dispatched', ['order_id' => $order->order_sn]);

    return $order;
}

    /**
     * 手动处理的订单.
     *
     * @param Order $order 订单
     * @return Order 订单
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function processManual(Order $order)
    {
        // 设置订单为待处理
        $order->status = Order::STATUS_PENDING;
        // 保存订单
        $order->save();
        // 商品库存减去
        $this->goodsService->inStockDecr($order->goods_id, $order->buy_amount);
        // 邮件数据
        $mailData = [
            'created_at' => $order->create_at,
            'product_name' => $order->goods->gd_name,
            'webname' => dujiaoka_config_get('text_logo', '独角数卡'),
            'weburl' => config('app.url') ?? 'http://dujiaoka.com',
            'ord_info' => str_replace(PHP_EOL, '<br/>', $order->info),
            'ord_title' => $order->title,
            'order_id' => $order->order_sn,
            'buy_amount' => $order->buy_amount,
            'ord_price' => $order->actual_price,
            'created_at' => $order->created_at,
        ];
        $tpl = $this->emailtplService->detailByToken('manual_send_manage_mail');
        $mailBody = replace_mail_tpl($tpl, $mailData);
        $manageMail = dujiaoka_config_get('manage_email', '');
        // 邮件发送
        MailSend::dispatch($manageMail, $mailBody['tpl_name'], $mailBody['tpl_content']);
        return $order;
    }

    /**
     * 处理自动发货.
     *
     * @param Order $order 订单
     * @return Order 订单
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function processAuto(Order $order): Order
    {
        if($order->carmi_id){
            $carmis = $this->carmisService->getCarmiById($order->carmi_id);
            $carmisInfo = [$carmis['carmi']];
            $ids = [$carmis['id']];
        }else{
            // 批量获得卡密
            $carmis = $this->carmisService->withGoodsByAmountAndStatusUnsold($order->goods_id, $order->buy_amount);
            // 实际可使用的库存已经少于购买数量了
            if (count($carmis) != $order->buy_amount) {
                $order->info = __('dujiaoka.prompt.order_carmis_insufficient_quantity_available');
                $order->status = Order::STATUS_ABNORMAL;
                $order->save();
                return $order;
            }
            $carmisInfo = array_column($carmis, 'carmi');
            $ids = array_column($carmis, 'id');
        }
        $order->info = implode(PHP_EOL, $carmisInfo);
        $order->status = Order::STATUS_COMPLETED;
        $order->save();
        // 将卡密设置为已售出
        $this->carmisService->soldByIDS($ids);
        // 邮件数据
        $mailData = [
            'created_at' => $order->create_at,
            'product_name' => $order->goods->gd_name,
            'webname' => dujiaoka_config_get('text_logo', '独角数卡'),
            'weburl' => config('app.url') ?? 'http://dujiaoka.com',
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
        return $order;
    }

  public function rebateAmount($order)
{
    // 获取订单中商品的返利比率
    $goods = Goods::find($order->goods_id);
    $rate = $goods->rebate_rate;

    if ($rate <= 0) {
        return false;
    }

    // 计算返利金额
    $amount = bcmul($order->actual_price, $rate / 100, 2);

    // 查找用户
    $user = User::query()->where('invite_code', $order->aff)->first();
    if ($user) {
        $invite = new InviteUser();
        $invite->user_id = $user->id;
        $invite->order_id = $order->id;
        $invite->amount = $amount;
        $invite->status = 0;
        $invite->save();
    }
}

}