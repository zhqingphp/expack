<?php

namespace zhqing\telegram;

use zhqing\extend\Curl;

class AnswerCallbackQuery extends Common {
    /**
     * 参数说明
     * @return array
     */
    public static function demo(): array {
        return [
            'callback_query_id' => '(string)(必选)要回答的查询的唯一标识符',
            'text' => '(string)通知的文本。如果未指定，则不会向用户显示任何内容，0-200 个字符',
            'show_alert' => '(bool)如果为 True，客户端将显示警报，而不是聊天屏幕顶部的通知。默认为 false。',
            'url' => '(string)将由用户客户端打开的 URL。如果您已创建游戏并通过 @BotFather 接受条件，请指定打开游戏的 URL - 请注意，这仅在查询来自 callback_game 按钮时才有效。否则，您可以使用 t.me/your_bot?start=XXXX 之类的链接，通过参数打开您的机器人。',
            'cache_time' => '(int|string)回调查询结果可以在客户端缓存的最长时间（以秒为单位）。 Telegram 应用程序将从 3.14 版本开始支持缓存。默认为 0。',
        ];
    }

    /**
     * 目标聊天的唯一标识符
     * @param string $data
     * @return static
     */
    public static function callback_query_id(string $data): static {
        $self = new self();
        $self->data = [];
        return $self->set('callback_query_id', $data);
    }

    /**
     * 通知的文本
     * @param string $data
     * @return $this
     */
    public function text(string $data): static {
        return $this->set('text', $data);
    }

    /**
     * 客户端将显示警报
     * @param bool $data
     * @return $this
     */
    public function show_alert(bool $data): static {
        return $this->set('show_alert', $data);
    }

    /**
     * 客户端打开的 URL
     * @param string $data
     * @return $this
     */
    public function url(string $data): static {
        return $this->set('url', $data);
    }

    /**
     * 客户端缓存的最长时间
     * @param int|string $data
     * @return $this
     */
    public function cache_time(int|string $data): static {
        return $this->set('cache_time', $data);
    }
}