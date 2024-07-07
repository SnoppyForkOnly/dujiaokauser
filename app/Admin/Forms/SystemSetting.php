<?php

namespace App\Admin\Forms;

use App\Models\BaseModel;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemSetting extends Form
{

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        Cache::put('system-setting', $input);

           // 同时写入数据库
           DB::table('admin_settings')
           ->updateOrInsert(['slug' => 'system-setting'], ['value' => json_encode($input), 'updated_at' => date('Y-m-d H:i:s')]);
           
        return $this
				->response()
				->success(admin_trans('system-setting.rule_messages.save_system_setting_success'));
    }
    
    public function form()
    {
        $this->tab(admin_trans('system-setting.labels.base_setting'), function () {
            $this->text('title', admin_trans('system-setting.fields.title'))->required();
            $this->image('img_logo', admin_trans('system-setting.fields.img_logo'));
            $this->text('text_logo', admin_trans('system-setting.fields.text_logo'));
            $this->text('keywords', admin_trans('system-setting.fields.keywords'));
            $this->textarea('description', admin_trans('system-setting.fields.description'));
            $this->select('template', admin_trans('system-setting.fields.template'))
                ->options(config('dujiaoka.templates'))
                ->required();
            $this->select('language', admin_trans('system-setting.fields.language'))
                ->options(config('dujiaoka.language'))
                ->required();
            $this->text('manage_email', admin_trans('system-setting.fields.manage_email'));
            $this->switch('is_open_anti_red', admin_trans('system-setting.fields.is_open_anti_red'))
                ->default(BaseModel::STATUS_CLOSE);
           
            $this->switch('is_open_wenzhang', admin_trans('文章首页调用开关。默认关闭'))
                ->default(BaseModel::STATUS_CLOSE);
            $this->editor('notice', admin_trans('system-setting.fields.notice'));
            $this->textarea('footer', admin_trans('system-setting.fields.footer'));
        });
        $this->tab(admin_trans('system-setting.labels.order_push_setting'), function () {
            $this->switch('is_open_server_jiang', admin_trans('system-setting.fields.is_open_server_jiang'))
                ->default(BaseModel::STATUS_CLOSE);
            $this->text('server_jiang_token', admin_trans('system-setting.fields.server_jiang_token'));
            $this->switch('is_open_telegram_push', admin_trans('system-setting.fields.is_open_telegram_push'))
                ->default(BaseModel::STATUS_CLOSE);
            $this->text('telegram_bot_token', admin_trans('system-setting.fields.telegram_bot_token'));
            $this->text('telegram_userid', admin_trans('system-setting.fields.telegram_userid'));
            $this->switch('is_open_bark_push', admin_trans('system-setting.fields.is_open_bark_push'))
                ->default(BaseModel::STATUS_CLOSE);
            $this->switch('is_open_bark_push_url', admin_trans('system-setting.fields.is_open_bark_push_url'))
                ->default(BaseModel::STATUS_CLOSE);
            $this->text('bark_server', admin_trans('system-setting.fields.bark_server'));
            $this->text('bark_token', admin_trans('system-setting.fields.bark_token'));
            $this->switch('is_open_qywxbot_push', admin_trans('system-setting.fields.is_open_qywxbot_push'))
                ->default(BaseModel::STATUS_CLOSE);
            $this->text('qywxbot_key', admin_trans('system-setting.fields.qywxbot_key'));
        });
        $this->tab(admin_trans('system-setting.labels.mail_setting'), function () {
            $this->text('driver', admin_trans('system-setting.fields.driver'))->default('smtp')->required();
            $this->text('host', admin_trans('system-setting.fields.host'));
            $this->text('port', admin_trans('system-setting.fields.port'))->default(587);
            $this->text('username', admin_trans('system-setting.fields.username'));
            $this->text('password', admin_trans('system-setting.fields.password'));
            $this->text('encryption', admin_trans('system-setting.fields.encryption'));
            $this->text('from_address', admin_trans('system-setting.fields.from_address'));
            $this->text('from_name', admin_trans('system-setting.fields.from_name'));
        });
        $this->tab(admin_trans('访问/登录充值限制'), function () {
            
             $this->text('rjtitle', admin_trans('人机验证标题'))->default('人机验证')
            ->required();
            
            $this->switch('is_cn_allow', admin_trans('拒绝中国地区访问'))
                ->default(BaseModel::STATUS_CLOSE);
               $this->text('cntitle', admin_trans('拒绝访问提示'))->default('抱歉，暂时不对中国地区提供访问')
            ->required();
            $this->switch('is_cn_challenge', admin_trans('中国地区开启人机验证'))
                ->default(BaseModel::STATUS_OPEN); 
              $this->switch('is_open_pass', admin_trans('密码显示，默认开'))
                ->default(BaseModel::STATUS_OPEN); 
             $this->number('cnpass', admin_trans('设置访问密码')) 
                   ->default (8888) 
                   ->required();
            $this->divider();
            $this ->switch('is_open_login', admin_trans('登录默认为开放')) 
                    ->default (BaseModel::STATUS_OPEN);
            
                 
            $this->switch ('is_open_reg', admin_trans('注册默认为开放'))
                 -> default (BaseModel::STATUS_CLOSE);
                 
            $this->switch ('is_openreg_img_code', admin_trans('注册算学题默认位关闭'))
                 -> default (BaseModel::STATUS_CLOSE);
                 
            $this->switch ('is_openlogin_img_code', admin_trans('登陆数学题默认位关闭'))
                 -> default (BaseModel::STATUS_CLOSE); 
              
             $this->switch ('is_openregxianzhi', admin_trans('注册限制同IP'))
                 -> default (BaseModel::STATUS_OPEN);  
              
             $this ->number('reg_ip_limits', admin_trans('同IP注册多少个'))
                     ->default (1) ->required();
            
            $this->text('open_czid', admin_trans('充值支付方式ID从后台中寻，为0时不限制')) 
                   ->default (22)
                   ->required();
         
              
          });  
          
          $this->tab(admin_trans('商品推送配置'), function() {
                $this ->text('telegram_bot_api_token', admin_trans('TelegramBotToken'));
                $this ->text('telegram_api_proxy', admin_trans('Telegram API代理'));
                $this ->text('telegram_chat_id', admin_trans('TelegramChatId'));
                $this ->switch ('is_open_new_goods_notify', admin_trans('是否开启上新通知')) 
                ->default (BaseModel::STATUS_CLOSE);
                $this ->switch ('is_open_replenishment_notify', admin_trans('是否开启补货通知'))
                    ->default (BaseModel::STATUS_CLOSE);
                $this ->
                    switch ('is_open_price_reduce_notify', admin_trans('是否开启降价通知'))
                    ->default (BaseModel::STATUS_CLOSE);
            });
          $this  ->tab(admin_trans('下单设置'), function() {
                $this ->number('order_expire_time', admin_trans('订单过期时间')) 
                     ->default (5) ->required();
                $this ->switch ('is_open_img_code', admin_trans('下单图形验证码'))
                    -> default (BaseModel::STATUS_CLOSE);
                $this ->number('order_ip_limits', admin_trans('同IP支付时订单限制'))
                     ->default (1) ->required();
                 $this->switch('is_open_search_pwd', admin_trans('system-setting.fields.is_open_search_pwd'))
                ->default(BaseModel::STATUS_CLOSE);
                 $this->text('global_currency', admin_trans('网站货币单位'))->default('¥')
               ->required();
                 $this->switch('is_open_mail', admin_trans('开启下单邮箱任意填写'))
                ->default(BaseModel::STATUS_OPEN);
            });
            
            $this->tab(admin_trans('商品排序规则'), function() {
               $this->text('jg', admin_trans('如果设置price是价格排序，其他则是权重排序'))
             ->default('price') // 注意这里的 'price' 已被正确地放在引号内
              ->required();
            });


        $this->tab(admin_trans('system-setting.labels.geetest'), function () {
            $this->text('geetest_id', admin_trans('system-setting.fields.geetest_id'));
            $this->text('geetest_key', admin_trans('system-setting.fields.geetest_key'));
            $this->switch('is_open_geetest', admin_trans('system-setting.fields.is_open_geetest'))->default(BaseModel::STATUS_CLOSE);
        });
        $this->tab("充值/邀请/注册赠送/代理升级", function () {
            $this->table('recharge_promotion', '充值活动', function (NestedForm $table) {
                $table->text('amount','充值金额');
                $table->text('value', '赠送金额');
            });
                      
         $this->number('regmoney', admin_trans('注册时默认赠送金额')) 
                   ->default (0)
                   ->required();
         $this->textarea('daili_text', '代理升级规则')->default();

            $this->textarea('gonggao_text', '返现公告')->default();
            $this->textarea('guize_text', '活动规则')->default();
            $this->textarea('tixian_text', '提现说明')->default();
        });
        
          $this->tab(admin_trans('虚拟下单功能'), function () {
            $this->text('xn_products', admin_trans('虚拟商品设置，以---分割'));
            $this->text('xn_quantities', admin_trans('商品件数随机设置，以1~100设置件数'));
            $this->switch('is_open_xn', admin_trans('是否开启虚拟下单显示'))->default(BaseModel::STATUS_OPEN);
        });
  
        $this->confirm(
            admin_trans('dujiaoka.warning_title'),
            admin_trans('system-setting.rule_messages.change_reboot_php_worker')
        );
    }

   
   public function default()
    {
       
        return Cache::get('system-setting', function () {
            // 如果缓存没有则从数据库中读取
            $setting = DB::table('admin_settings')->where('slug', 'system-setting')->first();
            if ($setting && property_exists($setting, 'value')) {
                return json_decode($setting->value, true);
            }
        });
    }

}