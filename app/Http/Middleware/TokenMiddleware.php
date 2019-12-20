<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class TokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $uid = $request->header('userId');
        $token = $request->header('token');
//        $token = $request->input('token');
        $currTime = time();
        if(!$uid || !$token){
            $result = [
               'code' => 0,
                'msg' => '您还没登录, 请先登录',
                'time' => $currTime,
                'data' => (object)[]

            ];
            return response()->json($result);
        }else{
            $tokenKey = 'uid:' . $uid;
            $savedTokenInfoStr = Redis::get($tokenKey);
//            echo 'savedTokenInfoStr = ' . $savedTokenInfoStr;
            if(!$savedTokenInfoStr){
                $result = [
                    'code' => 0,
                    'msg' => '您还没登录, 请先登录',
                    'time' => $currTime,
                    'data' => (object)[]

                ];
                return response()->json($result);
            }
            $savedTokenInfo = json_decode($savedTokenInfoStr);
            if(!$savedTokenInfo->token){
                $result = [
                    'code' => 0,
                    'msg' => '您还没登录, 请先登录',
                    'time' => $currTime,
                    'data' => (object)[]

                ];
                return response()->json($result);
            }
            if($savedTokenInfo->token != $token) {
                $result = [
                    'code' => 0,
                    'msg' => '账号失效, 请重新登录',
                    'time' => $currTime,
                    'data' => (object)[]

                ];
                return response()->json($result);
            }
            if($savedTokenInfo->expire < $currTime){
                $result = [
                    'code' => 0,
                    'msg' => '您长时间没使用, 请重新登录',
                    'time' => $currTime,
                    'data' => (object)[]

                ];
                return response()->json($result);
            }
            $refreshTime = 15 * 24 * 60 * 60;
            if(($savedTokenInfo->expire - $currTime) <= $refreshTime){
//                echo 'auto refresh token';
                $newExpire = $currTime * 15 * 24 * 60 * 60;
                $saveInfo = [
                    'token' => !$savedTokenInfo->token,
                    'expire' => $newExpire
                ];

                $saveJson = json_encode($saveInfo);
                Redis::set($tokenKey, $saveJson);
            }
        }
        return $next($request);
    }
}
