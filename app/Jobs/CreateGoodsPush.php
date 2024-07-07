<?php

namespace App\Jobs;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class CreateGoodsPush implements ShouldQueue
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
     * 商品服务层.
     * @var \App\Service\GoodsService
     */
    private $goodsService;

    private $goods_id;

    /**
     * Create a new job instance.
     *
     * @param int $goods_id
     */
    public function __construct(int $goods_id)
    {
        $this->goods_id = $goods_id;
        $this->goodsService = app('Service\GoodsService');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!dujiaoka_config_get('is_open_new_goods_notify')) {
            return;
        }
        $goodInfo = $this->goodsService->detail($this->goods_id);
        $formatText = '<b>上新通知</b>:' . PHP_EOL
            . '商品名称: <code>' . $goodInfo->gd_name . '</code>' . PHP_EOL
            . '商品描述: <code>' . $goodInfo->gd_description . '</code>' . PHP_EOL
            . '商品价格: <code>' . $goodInfo->actual_price . '</code>';
        
        $reply_markup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '前往购买',
                        'url' => url('buy', ['id' => $this->goods_id])
                    ]
                ]
            ]
        ];
        $params = [
            "chat_id" => dujiaoka_config_get('telegram_chat_id'),
            "parse_mode" => "HTML",
            "text" => $formatText,
            "reply_markup" => $reply_markup,
        ];
        $client = new Client([
            'timeout' => 30,
            'proxy' => dujiaoka_config_get('telegram_api_proxy')
        ]);
        $apiUrl = 'https://api.telegram.org/bot' . dujiaoka_config_get('telegram_bot_api_token')
            . '/sendMessage';
        Log::info($apiUrl);
        $client->post($apiUrl, ['json' => $params, 'verify' => false]);
    }
}
