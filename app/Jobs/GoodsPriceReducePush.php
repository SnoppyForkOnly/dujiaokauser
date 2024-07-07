<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;  //


class GoodsPriceReducePush implements ShouldQueue
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

    private $old_price;

    private $new_price;

    /**
     * Create a new job instance.
     *
     * @param int $goods_id
     */
    public function __construct(int $goods_id, float $old_price, float $new_price)
    {
        $this->goods_id = $goods_id;
        $this->old_price = $old_price;
        $this->new_price = $new_price;
        $this->goodsService = app('Service\GoodsService');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!dujiaoka_config_get('is_open_price_reduce_notify')) {
            return;
        }
        if ($this->new_price >= $this->old_price) {
            return;
        }
        $diff_price = $this->old_price - $this->new_price;
        $diff_price_str = Number_format($diff_price, 2, '.', '');;
        $goodInfo = $this->goodsService->detail($this->goods_id);
        $old_price_str = Number_format($this->old_price, 2, '.', '');;
        $new_price_str = Number_format($this->new_price, 2, '.', '');;
        $formatText = '<b>降价通知</b>:' . PHP_EOL
            . '商品名称: <code>' . $goodInfo->gd_name . '</code>' . PHP_EOL
            . '商品现价: <code>' . $old_price_str . '</code>' . PHP_EOL
            . '商品原价: <code>' . $new_price_str . '</code>' . PHP_EOL
            . '降价额度: <code>' . $diff_price_str . '</code>';

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
