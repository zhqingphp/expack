<?php

namespace zhqing\telegram;

/**
 * 使用此方法发送短信。成功后，将返回发送的消息。
 * https://core.telegram.org/bots/api#sendmessage
 */
class SendMessage extends Common {
    /**
     * 参数说明
     * @return array
     */
    public static function demo(): array {
        return [
            'text' => '(string)(必选)待发送的消息文本，实体解析后1-4096个字符',
            'chat_id' => '(int|string)(必选)目标聊天的唯一标识符或目标频道的用户名',
            'message_thread_id' => '(int|string)论坛目标消息线程（主题）的唯一标识符；仅适用于论坛超级组',
            'parse_mode' => '(string)解析消息文本中的实体的模式。有关更多详细信息，请参阅格式选项。(HTML/MarkdownV2)',
            'entities' => '(array|string)出现在消息文本中的特殊实体的 JSON 序列化列表https://core.telegram.org/bots/api#messageentity',
            'disable_notification' => '(bool)默默地发送消息。用户将收到无声音的通知。',
            'reply_to_message_id' => '(int|string)如果消息是回复，则原始消息的 ID',
            'disable_web_page_preview' => '(bool)禁用此消息中链接的链接预览',
            'allow_sending_without_reply' => '(bool)如果即使未找到指定的回复消息也应发送消息，则传递 True',
            'protect_content' => '(bool)保护已发送消息的内容不被转发和保存',
            'reply_markup' => '(array|string)附加接口选项。用于内联键盘、自定义回复键盘、删除回复键盘或强制用户回复的说明的 JSON 序列化对象。',
        ];
    }

    /**
     * 待发送的消息文本，实体解析后1-4096个字符
     * @param string $data
     * @return static
     */
    public static function chat_id(string $data): static {
        $self = new self();
        $self->data = [];
        return $self->set('chat_id', $data);
    }

    /**
     * 目标聊天的唯一标识符或目标频道的用户名
     * @param int|string $data
     * @return $this
     */
    public function text(int|string $data): static {
        return $this->set('text', $data);
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
     * HTML/MarkdownV2
     * 解析消息文本中的实体的模式。有关更多详细信息，请参阅格式选项。
     * https://core.telegram.org/bots/api#formatting-options
     * @param string $data
     * @return $this
     */
    public function parse_mode(string $data): static {
        return $this->set('parse_mode', $data);
    }

    /**
     * 出现在消息文本中的特殊实体的 JSON 序列化列表，可以指定它而不是 parse_mode
     * @param array|string $data //    Array of MessageEntity  https://core.telegram.org/bots/api#messageentity
     * @return $this
     */
    public function entities(array|string $data): static {
        return $this->set('entities', $data);
    }

    /**
     * 默默地发送消息。用户将收到无声音的通知。
     * @param bool $data
     * @return $this
     */
    public function disable_notification(bool $data): static {
        return $this->set('disable_notification', $data);
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
     * 禁用此消息中链接的链接预览
     * @param bool $data
     * @return $this
     */
    public function disable_web_page_preview(bool $data): static {
        return $this->set('disable_web_page_preview', $data);
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
     * 保护已发送消息的内容不被转发和保存
     * @param bool $data
     * @return $this
     */
    public function protect_content(bool $data): static {
        return $this->set('protect_content', $data);
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