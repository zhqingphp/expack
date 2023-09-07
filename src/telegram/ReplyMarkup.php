<?php

namespace zhqing\telegram;

use zhqing\extend\Frame;

/**
 * 用于内联键盘、自定义回复键盘
 * 删除回复键盘或强制用户回复(待完善)
 */
class ReplyMarkup extends Common {
    /**
     * 参数说明
     * @return array
     */
    public static function demo(): array {
        //消息下方按钮
        $data['InlineKeyboardMarkup'] = [
            'inline_keyboard' => [
                'text' => '(string)(必选)按钮上的标签文本',
                'url' => '(string)选修的。按下按钮时要打开的 HTTP 或 tg:// URL。如果隐私设置允许，链接 tg://user?id=<user_id> 可用于通过 ID 提及用户，而无需使用用户名。',
                'callback_data' => '(string)按下按钮时要在回调查询中发送到机器人的数据，1-64 字节',
                'web_app' => '(WebAppInfo)用户按下按钮时将启动的 Web 应用程序的描述。 Web 应用程序将能够使用answerWebAppQuery 方法代表用户发送任意消息。仅在用户和机器人之间的私人聊天中可用。',
                'login_url' => '(LoginUrl)用于自动授权用户的 HTTPS URL。可用作 Telegram 登录小部件的替代品。',
                'switch_inline_query' => '(string)如果设置，按下该按钮将提示用户选择其中一个聊天，打开该聊天并在输入字段中插入机器人的用户名和指定的内联查询。可能为空，在这种情况下仅插入机器人的用户名。',
                'switch_inline_query_current_chat' => '(string)如果设置，按下按钮将在当前聊天的输入字段中插入机器人的用户名和指定的内联查询。可能为空，在这种情况下仅插入机器人的用户名。',
                'switch_inline_query_chosen_chat' => '(SwitchInlineQueryChosenChat)如果设置，按下按钮将提示用户选择指定类型的聊天之一，打开该聊天并在输入字段中插入机器人的用户名和指定的内联查询',
                'callback_game' => '(CallbackGame)选修的。用户按下按钮时将启动的游戏的描述。注意：此类按钮必须始终是第一行中的第一个按钮。',
                'pay' => '(bool)选修的。指定 True，以发送支付按钮。注意：此类按钮必须始终是第一行中的第一个按钮，并且只能在发票消息中使用。',
            ]
        ];
        //键盘下方输入按钮
        $data['replyKeyBoardMarKup'] = [
            'keyboard' => [
                'text' => '(string)(必选)按钮上的标签文本',
                'request_user' => '(KeyboardButtonRequestUser)如果指定，按下该按钮将打开合适用户的列表。点击任何用户都会将其标识符通过“user_shared”服务消息发送给机器人。仅在私人聊天中可用。',
                'request_chat' => '(KeyboardButtonRequestChat)如果指定，按下该按钮将打开合适的聊天列表。点击聊天将通过“chat_shared”服务消息将其标识符发送给机器人。仅在私人聊天中可用。',
                'request_contact' => '(bool)如果为 True，则按下按钮时，用户的电话号码将作为联系人发送。仅在私人聊天中可用。',
                'request_location' => '(bool)如果为 True，则按下按钮时将发送用户的当前位置。仅在私人聊天中可用。',
                'request_poll' => '(KeyboardButtonPollType)如果指定，系统将要求用户创建民意调查并在按下按钮时将其发送给机器人。仅在私人聊天中可用。',
                'web_app' => '(WebAppInfo)如果指定，则按下按钮时将启动所描述的 Web 应用程序。 Web 应用程序将能够发送“web_app_data”服务消息。仅在私人聊天中可用。'
            ],
            'is_persistent' => '(bool)请求客户端在隐藏常规键盘时始终显示键盘。默认为 false，在这种情况下，可以隐藏自定义键盘并使用键盘图标打开。',
            'resize_keyboard' => '(bool)要求客户垂直调整键盘大小以获得最佳配合（例如，如果只有两行按钮，则缩小键盘）。默认为 false，在这种情况下，自定义键盘始终与应用程序的标准键盘具有相同的高度。',
            'one_time_keyboard' => '(bool)要求客户在使用键盘后立即隐藏键盘。键盘仍然可用，但客户端将自动在聊天中显示常用的字母键盘 - 用户可以按输入字段中的特殊按钮再次看到自定义键盘。默认为 false。',
            'input_field_placeholder' => '(string)当键盘处于活动状态时，在输入字段中显示的占位符； 1-64 个字符',
            'selective' => '(bool)如果您只想向特定用户显示键盘，请使用此参数。目标：1）在Message对象的文本中@提及的用户； 2) 如果机器人的消息是回复（具有reply_to_message_id），则为原始消息的发送者。',
        ];
        return $data;
    }

    /**
     * 消息下方按钮
     * 该对象代表一个内嵌键盘，它出现在它所属的消息旁边。
     * https://core.telegram.org/bots/api#inlinekeyboardmarkup
     * https://core.telegram.org/bots/api#inlinekeyboardbutton
     * @param $data
     * @param array $array
     * @return string
     */
    public static function InlineKeyboardMarkup($data, array $array = []): string {
        if (!empty($data)) {
            $arr['inline_keyboard'] = [];
            foreach ($data as $k => $v) {
                foreach ($v as $key => $val) {
                    if (is_array($val)) {
                        $arr['inline_keyboard'][$k][$key] = $val;
                    } else {
                        $arr['inline_keyboard'][$k][$key] = ['text' => $val, 'callback_data' => $val];
                    }
                }
            }
        }
        return Frame::json(array_merge((!empty($arr) ? $arr : []), $array));
    }

    /**
     * 键盘下方输入按钮
     * 该对象代表带有回复选项的自定义键盘（有关详细信息和示例，请参阅机器人简介）。
     * https://core.telegram.org/bots/api#replykeyboardmarkup
     * https://core.telegram.org/bots/api#keyboardbutton
     * @param array $data
     * @param array $array
     * @return string
     */
    public static function replyKeyBoardMarKup(array $data, array $array = []): string {
        if (!empty($data)) {
            $arr['keyboard'] = [];
            foreach ($data as $k => $v) {
                foreach ($v as $key => $val) {
                    if (is_array($val)) {
                        $arr['keyboard'][$k][$key] = $val;
                    } else {
                        $arr['keyboard'][$k][$key] = ['text' => $val, 'request_contact' => false, 'request_location' => false];
                    }
                }
            }
            $arr['one_time_keyboard'] = false;
            $arr['resize_keyboard'] = false;
            $arr['is_persistent'] = true;
        }
        return Frame::json(array_merge((!empty($arr) ? $arr : []), $array));
    }
}