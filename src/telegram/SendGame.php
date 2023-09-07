<?php

namespace zhqing\telegram;

class SendGame extends Common {
    /**
     * 参数说明
     * @return array
     */
    public static function demo(): array {
        return [
            'chat_id' => '(int|string)(必选)目标聊天的唯一标识符',
            'game_short_name' => '(string)(必选)游戏简称，作为游戏的唯一标识符。通过@BotFather 设置您的游戏。',
            'message_thread_id' => '(int|string)论坛目标消息线程（主题）的唯一标识符；仅适用于论坛超级组',
            'disable_notification' => '(bool)默默地发送消息。用户将收到无声音的通知。',
            'protect_content' => '(bool)保护已发送消息的内容不被转发和保存',
            'reply_to_message_id' => '(int|string)如果消息是回复，则原始消息的 ID',
            'allow_sending_without_reply' => '(bool)如果即使未找到指定的回复消息也应发送消息，则传递 True',
            'reply_markup' => '(array|string)内联键盘的 JSON 序列化对象。如果为空，将显示一个“Play game_title”按钮。如果不为空，则第一个按钮必须启动游戏。',
        ];
    }

    /**
     * 目标聊天的唯一标识符
     * @param int|string $data
     * @return static
     */
    public static function chat_id(int|string $data): static {
        $self = new self();
        $self->data = [];
        return $self->set('chat_id', $data);
    }

    /**
     * 游戏简称
     * @param string $data
     * @return $this
     */
    public function game_short_name(string $data): static {
        return $this->set('game_short_name', $data);
    }

    /**
     * 论坛目标消息线程（主题）的唯一标识符；仅适用于论坛超级组
     * @param int|string $data
     * @return $this
     */
    public function message_thread_id(int|string $data): static {
        return $this->set('message_thread_id', $data);
    }

    /**
     * 默默地发送消息。用户将收到无声音的通知
     * @param bool $data
     * @return $this
     */
    public function disable_notification(bool $data): static {
        return $this->set('disable_notification', $data);
    }

    /**
     * 保护已发送消息的内容不被转发和保存
     * @param bool $data
     * @return $this
     */
    public function protect_content(bool $data): static {
        return $this->set('protect_content', $data);
    }

    /**
     * 如果消息是回复，则原始消息的 ID
     * @param int|string $data
     * @return $this
     */
    public function reply_to_message_id(int|string $data): static {
        return $this->set('reply_to_message_id', $data);
    }

    /**
     * 如果即使未找到指定的回复消息也应发送消息，则传递 True
     * @param bool $data
     * @return $this
     */
    public function allow_sending_without_reply(bool $data): static {
        return $this->set('allow_sending_without_reply', $data);
    }

    /**
     * 使用ReplyMarkup类
     * 附加接口选项。用于内联键盘、自定义回复键盘、删除回复键盘或强制用户回复的说明的 JSON 序列化对象。
     * @param array|string $data
     * @return $this
     */
    public function reply_markup(array|string $data): static {
        return $this->set('reply_markup', $data);
    }
}