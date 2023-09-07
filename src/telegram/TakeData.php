<?php

namespace zhqing\telegram;

use zhqing\extend\Frame;

/**
 * 机器人接收到的信息(待完善)
 */
class TakeData {
    public array $data = [];

    /**
     * 获取telegram发送过来的数据
     * @return $this
     */
    public static function input(): static {
        return self::set(Frame::isJson(file_get_contents('php://input')));
    }

    /**
     * 使用webman获取telegram发送过来的数据
     * @return $this
     */
    public static function body(): static {
        return self::set(Frame::isJson(\request()->rawBody()));
    }

    /**
     * 保存数据
     * @param $data
     * @return $this
     */
    public static function set($data): static {
        $self = new self();
        $self->data = $data;
        return $self;
    }

    /**
     * 获取数据
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key = '', mixed $default = ''): mixed {
        return (!empty($key) ? Frame::getStrArr($this->data, $key, $default) : $this->data);
    }

    /**
     * 发送消息的人的ID
     * @return mixed
     */
    public function MessageFromId(): mixed {
        return $this->get('message.from.id');
    }

    /**
     * 消息ID，回复或者转发的时候可以用到
     * @return mixed
     */
    public function MessageMessageId(): mixed {
        return $this->get('message.message_id');
    }

    /**
     * 收到的信息
     * @return mixed
     */
    public function MessageText(): mixed {
        return $this->get('message.text');
    }

    /**
     * 用户名
     * @return mixed
     */
    public function MessageFromUserName(): mixed {
        return $this->get('message.from.username');
    }

    /**
     * 用户昵称
     * @return mixed
     */
    public function MessageFromFirstName(): mixed {
        return $this->get('message.from.first_name');
    }

    /**
     * 语言
     * @return mixed
     */
    public function MessageFromLangCode(): mixed {
        return $this->get('message.from.language_code');
    }

    /**
     *
     * 是否机器人,true=机器人
     * @return mixed
     */
    public function MessageFromIsBot(): mixed {
        return $this->get('message.from.is_bot');
    }

    /**
     * 所在群的ID，如果是个人发送给你的私信，则是个人ID  回复消息时候可以用到
     * @return mixed
     */
    public function MessageChatId(): mixed {
        return $this->get('message.chat.id');
    }

    /**
     * @return mixed
     */
    public function CallbackQuery(): mixed {
        return $this->get('callback_query');
    }

    /**
     * @return mixed
     */
    public function CallbackQueryId(): mixed {
        return $this->get('callback_query.id');
    }

    /**
     * @return mixed
     */
    public function CallbackQueryData(): mixed {
        return $this->get('callback_query.data');
    }

    /**
     * @return mixed
     */
    public function CallbackQueryMessage(): mixed {
        return $this->get('callback_query.message');
    }

    /**
     * @return mixed
     */
    public function CallbackQueryMessageChatId(): mixed {
        return $this->get('callback_query.message.chat.id');
    }

    /**
     * @return mixed
     */
    public function CallbackQueryMessageFromId(): mixed {
        return $this->get('callback_query.message.from.id');
    }

    /**
     *
     * 群新成员列表
     * @return mixed
     */
    public function MessageNewChatMembers(): mixed {
        return $this->get('message.new_chat_members');
    }

    /**
     *
     * 群新成员ID
     * @return mixed
     */
    public function MessageNewChatPartIcIpAntId(): mixed {
        return $this->get('message.new_chat_participant.id');
    }

    /**
     *
     * 群新成员用户名
     * @return mixed
     */
    public function MessageNewChatPartIcIpAntUserName(): mixed {
        return $this->get('message.new_chat_participant.username');
    }

    /**
     *
     * 群新成员昵
     * @return mixed
     */
    public function MessageNewChatPartIcIpAntFirstName(): mixed {
        return $this->get('message.new_chat_participant.first_name');
    }

    /**
     *
     * 群新成员称
     * @return mixed
     */
    public function MessageNewChatPartIcIpAntLastName(): mixed {
        return $this->get('message.new_chat_participant.last_name');
    }

    /**
     *
     * 群新成员ID
     * @return mixed
     */
    public function MessageNewChatMemberId(): mixed {
        return $this->get('message.new_chat_member.id');
    }

    /**
     *
     * 群新成员用户名
     * @return mixed
     */
    public function MessageNewChatMemberUserName(): mixed {
        return $this->get('message.new_chat_member.username');
    }

    /**
     *
     * 群新成员昵
     * @return mixed
     */
    public function MessageNewChatMemberFirstName(): mixed {
        return $this->get('message.new_chat_member.first_name');
    }

    /**
     *
     * 群新成员称
     * @return mixed
     */
    public function MessageNewChatMemberLastName(): mixed {
        return $this->get('message.new_chat_member.last_name');
    }

    /**
     *
     * 删除群成员ID
     * @return mixed
     */
    public function MessageLeftChatPartIcIpAntId(): mixed {
        return $this->get('message.left_chat_participant.id');
    }

    /**
     *
     * 删除群成员用户名
     * @return mixed
     */
    public function MessageLeftChatPartIcIpAntUsername(): mixed {
        return $this->get('message.left_chat_participant.username');
    }

    /**
     *
     * 删除群成员昵
     * @return mixed
     */
    public function MessageLeftChatPartIcIpAntFirstName(): mixed {
        return $this->get('message.left_chat_participant.first_name');
    }

    /**
     *
     * 删除群成员称
     * @return mixed
     */
    public function MessageLeftChatPartIcIpAntLastName(): mixed {
        return $this->get('message.left_chat_participant.last_name');
    }

    /**
     *
     * 删除群成员ID
     * @return mixed
     */
    public function MessageLeftChatMemberId(): mixed {
        return $this->get('message.left_chat_member.id');
    }

    /**
     *
     * 删除群成员用户名
     * @return mixed
     */
    public function MessageLeftChatMemberUsername(): mixed {
        return $this->get('message.left_chat_member.username');
    }

    /**
     *
     * 删除群成员昵
     * @return mixed
     */
    public function MessageLeftChatMemberFirstName(): mixed {
        return $this->get('message.left_chat_member.first_name');
    }

    /**
     *
     * 删除群成员称
     * @return mixed
     */
    public function MessageLeftChatMemberLastName(): mixed {
        return $this->get('message.left_chat_member.last_name');
    }
}