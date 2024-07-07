<?php

namespace App\Service;

use App\Models\Carmis;
use App\Models\Goods as GoodsModel;
use Illuminate\Support\Facades\Auth;

class Util
{
    public static function CryptoMail($email)
    {
        $email = explode('@', $email);
        return substr_replace($email[0], '****', 1, strlen($email[0])) .'@'. $email[1];
    }

    public static function WithdrawAccount($account)
    {
        switch ($account){
            case "alipay":
                return "支付宝";
            case "wechat":
                return "微信";
            default:
                return "USDT";
        }
    }

    public static function getStock($goods)
    {
        // 如果为自动发货，则加载库存卡密
        if ($goods->type == GoodsModel::AUTOMATIC_DELIVERY) {
            return Carmis::query()->where('goods_id', $goods->id)
                ->where('status', Carmis::STATUS_UNSOLD)
                ->count();
        } else {
            return $goods->in_stock;
        }
    }

    public static function getGradePrice($goods)
    {
        $user = Auth::user();
        $grade = 'grade_'.$user->grade;

        return $goods->$grade;
    }
}
