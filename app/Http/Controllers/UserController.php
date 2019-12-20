<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;


class UserController extends Controller
{
    //

    public function getUserById(Request $request){
        $usrs = DB::select('SELECT * FROM user WHERE id = :id', ['id' => 1]);
        return $usrs;
    }

    public function getUsers(Request $request){
        $usrs = DB::select('SELECT * FROM user');
        return $usrs;
    }

    public function insertUser(Request $request){
        $result = DB::insert('INSERT INTO user (user_name, user_pwd, gender) values(?, ?, ?)', ['jxiang120', '123456', '1']);

        return 'insert result ' . $result;
    }

    public function test(Request $request){
        $route = Route::current();

        $url = route('test');

        $routeName = Route::currentRouteName();
        return $routeName . '; url = ' . $url;
    }

    public function login(Request $request){
        $userName = $request->input('userName');
        $userPwd = $request->input('userPwd');

        /*$user = DB::select(
            'SELECT * FROM user WHERE user_name = :userName AND user_pwd = :userPwd',
            ['userName' => $userName, 'userPwd' => $userPwd])
        ;*/
        $user = DB::table('user')
            ->whereRaw('user_name = :userName AND user_pwd = :userPwd', ['userName' => $userName, 'userPwd' => $userPwd])
            ->first()
            ;
        $result = [
            'code' => 1,
            'msg' => '',
            'data' => $user ? $user : (object)[]
        ];
        if(!$user){
            $result['code'] = 0;
            $result['msg'] = 'loginFailure: username or pwd error';
        }else{
            $token = $this->updateToken($user->id);
            $user->token = $token;
        }
        return response()->json($result);
    }

    private function updateToken($userId){
        $timestamp = time();
//        echo 'timestamp = ' . date('Y-m-d H:i:s u', $timestamp);
        $expire = $timestamp + 15 * 24 * 60 * 60;
//        echo 'expire = ' . date('Y-m-d H:i:s u', $expire);
        $token = md5('{"uid":' . $userId . ',"timestamp":' . $timestamp . ',"expire":' . $expire . '}');

        $saveInfo = [
            'token' => $token,
            'expire' => $expire
        ];

        $saveJson = json_encode($saveInfo);

//        echo 'saveJson = ' . $saveJson;

        $tokenKey = 'uid:' . $userId;
        $tokenExpire = 'uidExpire:' . $userId;
        Redis::set($tokenKey, $saveJson);
//        Redis::set($tokenExpire, $expire);
//        $getTokenInfo = json_decode(Redis::get($tokenKey));

//        echo 'token = ' . Redis::get($tokenKey) . '; expire: ' . date('Y-m-d H:i:s u', Redis::get($tokenExpire));
//        echo 'token = ' . $getTokenInfo->token . '; expire: ' . date('Y-m-d H:i:s u', $getTokenInfo->expire);
        return $token;
    }
}
