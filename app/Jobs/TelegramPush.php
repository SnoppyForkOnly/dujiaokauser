<?php

namespace App\Jobs;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; // 添加 Log facade

class TelegramPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数。
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 任务运行的超时时间。
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * @var Order
     */
    private $order;

    /**
     * 商品服务层.
     * @var \App\Service\PayService
     */
    private $goodsService;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->goodsService = app('Service\GoodsService');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $goodInfo = $this->goodsService->detail($this->order->goods_id);
            $payName = $this->order->pay_id == 0 ? "余额支付" : $this->order->pay->pay_name;
            $formatText = '*'. __('dujiaoka.prompt.new_order_push').'('.$this->order->actual_price.'元)*%0A'
                . __('order.fields.order_id') .': `'.$this->order->id.'`%0A'
                . __('order.fields.order_sn') .': `'.$this->order->order_sn.'`%0A'
                . __('order.fields.pay_id') .': `'.$payName.'`%0A'
                . __('order.fields.title') .': '.$this->order->title.'%0A'
                . __('order.fields.actual_price') .': '.$this->order->actual_price.'%0A'
                . __('order.fields.email') .': `'.$this->order->email.'`%0A'
                . __('goods.fields.gd_name') .': `'.$goodInfo->gd_name.'`%0A'
                . __('goods.fields.in_stock') .': `'.$goodInfo->in_stock.'`%0A'
                . __('order.fields.order_created') .': '.$this->order->created_at;

            $client = new Client([
                'timeout' => 30,
                'proxy'=> ''
            ]);
            $apiUrl = 'https://api.telegram.org/bot' . dujiaoka_config_get('telegram_bot_token') .
                '/sendMessage?chat_id=' . dujiaoka_config_get('telegram_userid') . '&parse_mode=Markdown&text='.$formatText;
            $client->post($apiUrl);

            // 记录成功日志
            Log::info('TelegramPush job executed successfully.', ['order_id' => $this->order->id]);
        } catch (\Exception $exception) {
            // 记录失败日志
            Log::error('TelegramPush job failed: ' . $exception->getMessage(), ['order_id' => $this->order->id]);
        }
    }
}
