<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Cache;
use Illuminate\Http\Request;
use Log;
use Houdunwang\Wechat\User as WechatUser;

//微信绑定
class WechatBindController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    //微信绑定
    public function bind(string $ticket)
    {
        if ($info = Cache::get($ticket)) {
            $userWechat = app(WechatUser::class)->config(config('hd.wechat'));
            $info = $userWechat->getByOpenid($info['FromUserName']);
            $registerUser = User::orWhere('openid', $info['openid'])->orWhere('unionid', $info['unionid'] ?? "")->first();
            if ($registerUser) {
                if ($registerUser->id == Auth::id()) return $this->error('你已经绑定了这个微信号');
                return $this->error('微信已经被其他用户绑定');
            }

            $user = Auth::user();
            $user->name = $user->name ?? $info['nickname'];
            $user->avatar = $user->avatar ?? $info['headimgurl'];
            $user->openid = $info['openid'];
            $user->unionid = $info['unionid'];
            $user->save();
            return $this->success('绑定成功', ['token' => $user->createToken('auth')->plainTextToken, 'user' => $user]);
        }
        return $this->error('缺少 ticket');
    }

    //解绑微信
    public function unbind()
    {
        $user = Auth::user();
        if (!$user->mobile) return $this->error('请绑定手机号后操作');
        $user->openid = null;
        $user->unionid = null;
        $user->save();
        return $this->success('解绑成功', ['user' => $user]);
    }
}