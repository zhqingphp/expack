<?php

namespace zhqing\telegram\test;

use zhqing\telegram\Bot;
use support\Response;
use support\Request;
use zhqing\extend\Frame;
use zhqing\telegram\TakeData;
use zhqing\telegram\SendGame;
use zhqing\telegram\ReplyMarkup;
use zhqing\telegram\SendMessage;
use zhqing\telegram\AnswerCallbackQuery;

class demo {
    public Bot $bot;
    public static string $token = 'xxxxxxxxxxxxxxxxxxxx';

    public function __construct() {
        $this->bot = new Bot(self::$token);
    }

    public function bot(Request $req): Response {
        $self = TakeData::body();
        if (empty($self->get())) {
            return \response('success');
        }
        $message_chat_id = $self->message_chat_id();
        //回调游戏
        $callback_id = $self->callback_query_id();
        $callback_data = $self->callback_query_data();
        if (!empty($callback_id) && !empty($callback_data)) {
            switch ($callback_data) {
                case "pggame":
                    $data = AnswerCallbackQuery::callback_query_id($callback_id)->url('https://baidu.com')->get();
                    $this->bot->answerCallbackQuery($data);
                    break;
            }
            return \response('success');
        }
        if (empty($message_chat_id)) {
            return \response('success');
        }
        $message_text = $self->message_text();
        switch ($message_text) {
            case "/start":
                $data = SendMessage::chat_id($message_chat_id)
                    ->text('欢迎入群')
                    ->disable_web_page_preview(false)
                    ->protect_content(false)
                    ->reply_markup(ReplyMarkup::replyKeyBoardMarKup([
                        ['电子游戏', '个人中心', '测试按钮'],
                        ['电子游戏', '个人中心', '测试按钮'],
                        ['电子游戏', '个人中心', '测试按钮'],
                        ['电子游戏', '个人中心', '测试按钮']
                    ]))
                    ->get();
                $this->bot->sendMessage($data);
                break;
            case "电子游戏":
                $data = SendGame::chat_id($message_chat_id)
                    ->game_short_name('PgGame')
                    ->reply_markup(ReplyMarkup::InlineKeyboardMarkup([
                        [
                            ['text' => '开始游戏', 'callback_game' => 'pggame'],//此方法回调URL无效,可以通过 设置webHook来调试
                        ],
                        [
                            ['text' => '个人中心', 'url' => 'https://t.me/wwwrootdemo_bot']
                        ]
                    ]))
                    ->get();
                $this->bot->sendGame($data);
                break;
            case "个人中心":
                $data = SendMessage::chat_id($message_chat_id)
                    ->text('欢迎使用会员中心')
                    ->disable_web_page_preview(false)
                    ->protect_content(false)
                    ->reply_markup(ReplyMarkup::InlineKeyboardMarkup([
                        [
                            ['text' => '个人中心', 'url' => 'https://t.me/wwwrootdemo_bot']
                        ]
                    ]))
                    ->get();
                $this->bot->sendMessage($data);
                break;
            case "测试按钮":
                $data = SendMessage::chat_id($message_chat_id)
                    ->text('欢迎测试按钮')
                    ->disable_web_page_preview(false)
                    ->protect_content(false)
                    ->reply_markup(ReplyMarkup::InlineKeyboardMarkup([
                        [
                            ['text' => '按钮1', 'url' => 'https://baidu.com'],
                            ['text' => '按钮2', 'url' => 'https://taobao.com.com'],
                        ],
                        [
                            ['text' => '按钮3', 'url' => 'https://baidu.com'],
                            ['text' => '按钮4', 'url' => 'https://taobao.com.com']
                        ]
                    ]))
                    ->get();
                $this->bot->sendMessage($data);
                break;
            default:
                $data = SendMessage::chat_id($message_chat_id)
                    ->text('收到的信息:' . Frame::json($self->get()))
                    ->disable_web_page_preview(false)
                    ->protect_content(false)
                    ->get();
                $this->bot->sendMessage($data);
                break;
        }
        return \response('success');
    }

    public function set(Request $req): Response {
        return rs($this->bot->setWebHook('https://' . $req->host() . '/index/bot')->array());
    }

    public function get(Request $req): Response {
        return rs($this->bot->getWebHookInfo()->array());
    }

    public function del(Request $req): Response {
        return rs($this->bot->deleteWebHook()->array());
    }

    public function getMe(Request $req): Response {
        return rs($this->bot->getMe()->array());
    }

    public function getChat(Request $req): Response {
        return rs($this->bot->getUpdates()->array());
    }
}
