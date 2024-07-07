<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\BaseController;
use App\Jobs\MailSend;
use App\Models\BaseModel;
use App\Models\Emailtpl;
use App\Models\User;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function login()
    {
        return $this->render('static_pages/login');
    }

    public function loginHandler(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::query()->where('email', $credentials['email'])->first();
        if (! $user){
            return $this->err('邮箱不存在');
        }
        if ($user->status === 0){
            return $this->err('账号已被封禁，请联系管理员');
        }

        if (Auth::attempt($credentials, true)) {
            $user->last_ip = $request->ip();
            $user->last_login = now()->toDateTimeString();
            $user->save();

            return redirect()->intended('/user');
        }

        return $this->err('账号密码错误');
    }

    public function register()
    {
        return $this->render('static_pages/register');
    }

  

    public function registerHandler(Request $request)
    {
       $data = $request->all();
    $ip = $request->ip();

    // 检查是否开启了注册IP限制
    $isIpLimitEnabled = dujiaoka_config_get('is_openregxianzhi', BaseModel::STATUS_OPEN);
    $maxAccountsPerIp = dujiaoka_config_get('reg_ip_limits', 1);

    if ($isIpLimitEnabled == BaseModel::STATUS_OPEN) {
        // 检查这个IP是否已经注册了过多账号
        $accountsFromIp = User::where('last_ip', $ip)->count();
        if ($accountsFromIp >= $maxAccountsPerIp) {
            $errMsg = "此IP（最多允许注册 {$maxAccountsPerIp} 个账号）";
            return $this->err($errMsg);
        }
    }
     
        if (strlen($data['password']) < 6){
            return $this->err('密码不能小于6位数');
        }
        if (User::query()->where('email', $data['email'])->exists()){
            return $this->err('邮箱已存在');
        }
        if ($data['invite_code'] && User::query()->where('invite_code', $data['invite_code'])->doesntExist()){
            return $this->err('邀请码不存在');
        }
        $user = new User();
 
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->last_ip = $request->ip();
        $user->last_login = now()->toDateTimeString();
        $user->register_at = now()->toDateTimeString();
        if ($data['invite_code']){
            $user->pid = User::query()->where('invite_code', $data['invite_code'])->value('id');
        }
        $user->invite_code = Str::random(8);
        
        $user->save();
        // 屏蔽注册码逻辑 验证码已使用
        //$verifyCode->status = 1;
       // $verifyCode->save();
       $configAmount = dujiaoka_config_get('regmoney');



       // 更新注册用户金额，默认配置文件里的数据为0
        $this->updateUserMoney($user, $configAmount);


       // 重新获取用户并排序
         $users = User::orderBy('money', 'asc')->get();
        Auth::login($user, true);

        return redirect()->to('/');
    }
    
    // 更新注册用户金额的方法
    private function updateUserMoney($user, $amount)
    {
    // 直接更新数据库字段
     $user->update(['money' => $user->money + $amount]);
      }

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->to('/');
    }
    
    
}
