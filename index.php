<?php
flush();
ob_start();
set_time_limit(0);
error_reporting(0);

$API_KEY = "8575984011:AAGk4WNw26C3zuXKMMAS2TWMLjJdZ3WzqIA";
define('API_KEY', $API_KEY);

function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res);
}

$bot_info = bot("getMe");
$userBot = $bot_info->result->username;
$bot_id = $bot_info->result->id;

$NAMERO = __DIR__ . '/NAMERO/' . $bot_id . '/';
if (!is_dir($NAMERO)) mkdir($NAMERO, 0777, true);

// تضمين الملفات المساعدة
include __DIR__ . "/admin.php";
include __DIR__ . "/azrar.php";

$update = json_decode(file_get_contents('php://input'));
if (!$update) exit;

$message = $update->message;
$text = $message->text;
$chat_id = $message->chat->id;
$name = $message->from->first_name;
$user = $message->from->username;
$message_id = $update->message->message_id;
$from_id = $update->message->from->id;

$chat_id2 = $update->callback_query->message->chat->id ?? null;
$message_id2 = $update->callback_query->message->message_id ?? null;
$data = $update->callback_query->data ?? null;

$admin = 7816487928;
$SALEH = 7816487928;

// تحميل البيانات
$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
if (!$Namero) $Namero = [];

$settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];

$rshaq = $Namero['rshaq'] ?? "on";
$currency = $settings['currency'] ?? "نقاط";
$invite_reward = $settings['invite_reward'] ?? 5;
$min_order = $settings['min_order_quantity'] ?? 10;
$daily_gift = $settings['daily_gift'] ?? 20;
$price_per_user = $settings['user_price'] ?? "100";
$Ch = $settings['Ch'] ?? "https://t.me/TJUI9";

// إعدادات القنوات الإجبارية
$channels_file = $NAMERO . "Namero12.txt";
$channels = file_exists($channels_file) ? explode("\n", file_get_contents($channels_file)) : [];

// التحقق من الاشتراك
function check_channels($from_id, $channels) {
    foreach ($channels as $channel) {
        if (empty(trim($channel))) continue;
        $check = @file_get_contents("https://api.telegram.org/bot" . API_KEY . "/getChatMember?chat_id=@$channel&user_id=$from_id");
        if (strpos($check, '"status":"left"') !== false) return false;
    }
    return true;
}

// ================== معالجة الرسائل ==================

// 1. أمر /start
if ($text == "/start") {
    // التحقق من الاشتراك
    if (!check_channels($from_id, $channels)) {
        $keyboard = ['inline_keyboard' => []];
        foreach ($channels as $ch) {
            if (empty(trim($ch))) continue;
            $keyboard['inline_keyboard'][] = [['text' => "اشتراك @$ch", 'url' => "https://t.me/$ch"]];
        }
        $keyboard['inline_keyboard'][] = [['text' => "✅ تم الاشتراك", 'callback_data' => "check_sub"]];
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "🚫 *يجب عليك الاشتراك في القنوات التالية لاستخدام البوت*",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
        exit;
    }

    // إذا كان المستخدم هو المطور
    if ($from_id == $admin) {
        // عرض لوحة المطور
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📊 لوحة التحكم', 'callback_data' => 'admin_panel']],
                [['text' => '🔧 إعدادات البوت', 'callback_data' => 'back_to_admin']],
                [['text' => '👥 إدارة الأدمنية', 'callback_data' => 'manage_admins']],
                [['text' => '📢 الإذاعة', 'callback_data' => 'broadcast']],
                [['text' => '➕ إضافة قناة', 'callback_data' => 'add_channel'], ['text' => '➖ حذف قناة', 'callback_data' => 'delete_channel']],
                [['text' => '📝 عرض القنوات', 'callback_data' => 'list_channels']],
                [['text' => '🚫 قفل البوت', 'callback_data' => 'toggle_bot_status'], ['text' => '🔓 فتح البوت', 'callback_data' => 'toggle_bot_status']],
                [['text' => '⚙️ إعدادات الرشق', 'callback_data' => 'rshqG']]
            ]
        ];
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*مرحباً أيها المطور*\nاختر الإعدادات المناسبة:",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
        exit;
    }

    // المستخدم العادي: عرض الأزرار وزر Mini App
    $user_coin = $Namero["coin"][$from_id] ?? 0;
    $bot_username = bot('getMe')->result->username;
    $webapp_url = "https://" . $_SERVER['HTTP_HOST'] . "/service.php?id=$from_id&key=" . hash('sha256', $from_id . "Namero_Bot_Secret_Key_2024" . date('Y-m-d'));
    
    $start_msg = "🔥 مرحبا بك في بوت سميث ماتريكس 🚀\n🛍 يمكنك رشق جميع الخدمات الي تريدها من الاسفل 😍\n\n🎁 $currency : $user_coin $currency \n🆔 ايديك : `$from_id`\n\n✨ تم التطوير بواسطة [@ypui5](https://t.me/ypui5)";
    
    $reply_markup = [
        'inline_keyboard' => [
            [['text' => '🚀 فتح المتجر', 'web_app' => ['url' => $webapp_url]]],
            [['text' => '💰 شحن النقاط', 'callback_data' => 'buy'], ['text' => '📜 الشروط', 'callback_data' => 'termss']],
            [['text' => '🎁 الهدية اليومية', 'callback_data' => 'daily_gift'], ['text' => '🔗 رابط الدعوة', 'callback_data' => 'linkme']],
            [['text' => '📊 معلومات حسابي', 'callback_data' => 'acc']],
            [['text' => '📢 قناة البوت', 'url' => $Ch]]
        ]
    ];
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $start_msg,
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode($reply_markup)
    ]);
    exit;
}

// 2. معالجة الأزرار (callback_query)
if ($data) {
    $chat_id = $chat_id2;
    $from_id = $update->callback_query->from->id;
    $message_id = $message_id2;
    
    // التحقق من الاشتراك
    if (!check_channels($from_id, $channels)) {
        bot('answerCallbackQuery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => 'يرجى الاشتراك في القنوات أولاً',
            'show_alert' => true
        ]);
        exit;
    }
    
    // تمرير البيانات إلى admin.php أو azrar.php
    $GLOBALS['from_id'] = $from_id;
    $GLOBALS['chat_id'] = $chat_id;
    $GLOBALS['message_id'] = $message_id;
    
    // إذا كان المطور وطلب لوحة التحكم
    if ($from_id == $admin && in_array($data, ['admin_panel', 'back_to_admin', 'manage_admins', 'broadcast', 'add_channel', 'delete_channel', 'list_channels', 'toggle_bot_status', 'rshqG'])) {
        include __DIR__ . "/admin.php";
        exit;
    }
    
    // باقي الأزرار (للمستخدم العادي والمطور)
    include __DIR__ . "/azrar.php";
    exit;
}

// 3. معالجة الرسائل النصية الأخرى
if ($text) {
    $GLOBALS['from_id'] = $from_id;
    $GLOBALS['chat_id'] = $chat_id;
    $GLOBALS['message_id'] = $message_id;
    $GLOBALS['text'] = $text;
    include __DIR__ . "/azrar.php";
    exit;
}

exit;
