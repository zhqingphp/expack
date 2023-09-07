<?php

namespace zhqing\telegram;

use zhqing\extend\Curl;

/**
 * 机器人接口
 * https://core.telegram.org/bots/api
 * BotFather 的对话框：https://t.me/botfather
 * 创建机器人：/newbot
 * 设置漫游器隐私：/setprivacy. 选择：Disable
 * 创建游戏：/newgame
 * 内联模式： /setinline
 */
class Bot {
    public string $url = 'https://api.telegram.org/';
    public string $token = '';
    public array $proxy = [];
    public Curl $curl;

    /**
     * 发送短信
     * 使用此方法发送短信。成功后，将返回发送的消息。
     * https://core.telegram.org/bots/api#sendmessage
     * @param array $data
     * @return $this
     */
    public function sendMessage(array $data): static {
        return $this->send('sendmessage', $data);
    }

    /**
     * 发送游戏
     * 使用此方法发送游戏。成功后，将返回发送的消息。
     * https://core.telegram.org/bots/api#sendgame
     * @param array $data
     * @return $this
     */
    public function sendGame(array $data): static {
        return $this->send('sendGame', $data);
    }

    /**
     * 键盘回调
     * 使用此方法可发送对从内联键盘发送的回调查询的答案。答案将作为聊天屏幕顶部的通知或警报显示给用户。成功时，返回 True。
     * https://core.telegram.org/bots/api#answercallbackquery
     * @param array $data
     * @return $this
     */
    public function answerCallbackQuery(array $data): static {
        return $this->send('answerCallbackQuery', $data);
    }

    /**
     * 接收数据,使用前要删除WebHook
     * 使用此方法通过长轮询接收传入的更新 (wiki)。返回更新对象的数组。
     * https://core.telegram.org/bots/api#getupdates
     * @param array $data
     * @return $this
     */
    public function getUpdates(array $data = []): static {
        return $this->send('getUpdates', $data);
    }

    /**
     * 设置WebHook
     * 使用此方法指定 URL 并通过传出 Webhook 接收传入更新。每当机器人有更新时，我们都会向指定的 URL 发送 HTTPS POST 请求，其中包含 JSON 序列化的更新。如果请求不成功，我们将在合理尝试后放弃。成功则返回 True。
     * https://core.telegram.org/bots/api#setwebhook
     * @param string $url
     * @param array $data
     * @return $this
     */
    public function setWebHook(string $url, array $data = []): static {
        return $this->send('setWebhook', array_merge(['url' => $url], $data));
    }

    /**
     * 获取当前的WebHook状态
     * 使用此方法获取当前的 webhook 状态。不需要参数。成功时，返回 WebhookInfo 对象。如果机器人使用 getUpdates，将返回一个 url 字段为空的对象。
     * https://core.telegram.org/bots/api#getwebhookinfo
     * @return $this
     */
    public function getWebHookInfo(): static {
        return $this->send('getWebhookInfo');
    }

    /**
     * 删除WebHook,切换回getUpdates
     * 使用此方法删除 Webhook 集成。成功则返回 True。
     * https://core.telegram.org/bots/api#deletewebhook
     * @return $this
     */
    public function deleteWebHook(): static {
        return $this->send('deleteWebhook');
    }

    /**
     * 获取机器人信息
     * 测试机器人身份验证令牌的简单方法。不需要参数。以 User 对象的形式返回有关机器人的基本信息。
     * https://core.telegram.org/bots/api#getme
     * @return $this
     */
    public function getMe(): static {
        return $this->send('getMe');
    }

    /**
     * 获取返回的body
     * @return string
     */
    public function body(): string {
        return $this->curl->body();
    }

    /**
     * 获取返回的array
     * @return array
     */
    public function array(): array {
        return $this->curl->array();
    }

    /**
     * @param $token
     * @param string $url
     */
    public function __construct($token, string $url = '') {
        $this->token = $token;
        if (!empty($url)) {
            $this->url = $url;
        }
    }

    /**
     * 设置代理IP
     * @param string $ip 代理ip
     * @param string $port 代理ip端口
     * @param string $user 帐号:密码
     * @param string $type 代理模式(http|socks5或者自定)
     * @param string $auth 认证模式(basic|ntlm或者自定)
     * @return $this
     */
    public function proxy(string $ip, string $port, string $user, string $type = 'http', string $auth = 'basic'): static {
        $this->proxy = [
            'ip' => $ip,
            'port' => $port,
            'userPass' => $user,
            'type' => $type,
            'auth' => $auth
        ];
        return $this;
    }

    /**
     * 发送
     * @param string $path
     * @param array $data
     * @param mixed $curl
     * @return $this
     */
    public function send(string $path, array $data = [], mixed $curl = null): static {
        $this->curl = Curl::post(rtrim($this->url, '/') . '/bot' . $this->token . '/' . $path, $data)->proxy($this->proxy, !empty($this->proxy));
        $this->curl = ((!empty($curl) && is_callable($curl)) ? $this->curl->curl($curl) : $this->curl)->exec();
        return $this;
    }
}