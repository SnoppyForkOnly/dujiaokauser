<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\BaseModel;
use GeoIp2\Database\Reader;

class Challenge
{
    /**过白模块部分开始 */
     private $whiteClass = [
        "App\Http\Controllers\Pay",
         "App\Admin",
         "App\Service",
          "App\Jobs",
        "App\Models\Order"
        ];

     /**过白模块部分结束 */
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      


         if (dujiaoka_config_get('is_cn_allow') == BaseModel::STATUS_OPEN) {
            $country = $request->server->get('HTTP_CF_IPCOUNTRY');
            if ('cn' == strtolower($country)) {
                // return response('不允许大陆访问');
                return response()->view('common/bancn', [
                    'clientip'  => $request->server->get('HTTP_CF_CONNECTING_IP'),
                    'country'   => $request->server->get('HTTP_CF_IPCOUNTRY')
                ], 403);
            }
        }
        
        // 之前的代码
// if(in_array($request->route()->getAction()["namespace"],$this->whiteClass))
//    return $next($request);

// 修改后的代码
$actionNamespace = $request->route()->getAction()["namespace"] ?? '';
if(in_array($actionNamespace, $this->whiteClass)) {
    return $next($request);
}
        
      
              // 获取请求来源国家代码
 $country = $request->server->get('HTTP_CF_IPCOUNTRY');

// 检查是否开启挑战配置并且请求来自中国
if (dujiaoka_config_get('is_cn_challenge') == BaseModel::STATUS_OPEN && strtolower($country) == 'cn') {
    // 挑战逻辑
    $status = session('challenge');
    if ($status === "pass") {
        return $next($request);
    }

    if (isset($_REQUEST['_challenge'])) {
        if (substr(sha1($_REQUEST['_challenge']), -4) == $status) {
            session(['challenge' => 'pass']);
            return $next($request);
        }
    }

    // 如果没有通过挑战，可以在这里添加额外的逻辑，例如返回一个错误响应或重定向到挑战页面
} else {
    // 如果挑战配置未开启或请求不来自中国，直接允许请求通过
    return $next($request);
}
      
  // 对Cloudflare站点的支持优化
 
        if(isset($_SERVER["HTTP_CF_IPCOUNTRY"]))
            $isoCode = $_SERVER["HTTP_CF_IPCOUNTRY"];
        else{
            $reader = new Reader(storage_path('app/library/GeoLite2-Country.mmdb'));
            $ip = $request->ip();
            // 对局域网进行特殊处理
            if (ip2long($ip) >= ip2long('10.0.0.0') && ip2long($ip) <= ip2long('10.255.255.255') ||
                ip2long($ip) >= ip2long('172.16.0.0') && ip2long($ip) <= ip2long('172.31.255.255') ||
                ip2long($ip) >= ip2long('192.168.0.0') && ip2long($ip) <= ip2long('192.168.255.255')) {
                session(['challenge' => 'pass']);
                return $next($request);
            } else {
                $isoCode = $reader->country($ip)->country->isoCode;
            }
        }
        if($isoCode != 'CN'){
            session(['challenge' => 'pass']);
            return $next($request);
        }
        $challenge = substr(sha1(rand()), -4);
        session(['challenge' => $challenge]);
        return response()->view('common/challenge',['code' => $challenge]);
    }
}