<?php

namespace App\Http\Controllers;

use App\Models\Morning;
use App\Services\WechatService;
use Cache;
use Houdunwang\Wechat\Message;
use Log;

class WechatController extends Controller
{
    protected $msg;

    public function __construct()
    {
        $this->msg = app(Message::class)->config(config('hd.wechat'));
    }

    public function handle()
    {
        if ($reply = $this->subscribe()) return $reply;
        if ($reply = $this->scanLogin()) return $reply;
        if ($content = $this->morning()) return $content;

        $morning = Morning::where('state', 0)->first();
        if ($morning) {
            return $this->msg->news([
                [
                    'title' => '本期【早起少年】活动礼品，等你拿',
                    'description' => '早睡早起，拥有好心情和好身体。家人开心，自己健康。',
                    'picurl' => $morning->image,
                    'url' => url('front/morning')
                ]
            ]);
        }
        return $this->msg->text('没明白你的意思，晚上八点来直播间聊天吧');
    }

    //订阅
    protected function subscribe()
    {
        if ($this->msg->isSubscribe()) {
            app(WechatService::class)->registerByOpenid($this->msg->FromUserName);
            return $this->msg->text(config('app.name') . '后盾人 欢迎你回家');
        }
    }

    //扫码登录
    protected function scanLogin()
    {
        if ($this->msg->isScan() && $this->msg->EventKey == 'bind') {
            Cache::put($this->msg->Ticket, $this->msg->message, now()->addMinutes(10));
            return $this->msg->text(config('app.name') . ' 欢迎你回家');
        }
    }

    //早起签到活动
    protected function morning()
    {
        if (preg_match('/^\s*签到/', $this->msg->Content)) {
            $user = app(WechatService::class)->registerByOpenid($this->msg->FromUserName);
            //签到判断
            $isSign = $user->signs()->whereDate('created_at', now())->exists();
            if ($isSign) return $this->msg->text('今天你已经签到过');
            $content = preg_replace(['/\s+/s', '/^\s*签到\s*/is'], '', $this->msg->Content);
            if (mb_strlen($content < 5)) return $this->msg->text('签到内容不能小于5个字');
            //创建签到
            $user->signs()->create(['content' => $content . '【微信快签】', 'mood' => 'kx']);
            return $this->msg->news([
                [
                    'title' => '签到成功',
                    'description' => '大叔祝你天天好心情',
                    'picurl' => url('assets/sign/xj.jpg'),
                    'url' => url('front/sign')
                ]
            ]);
        }
    }
}