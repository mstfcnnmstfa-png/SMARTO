<?php
// admin.php - لوحة تحكم الأدمن (لا يوجد فيه تعريف دالة bot)
$SALEH = 7816487928;

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$chat_id = $message->chat->id;
$text = $message->text;
$chat_id2 = $update->callback_query->message->chat->id ?? null;
$message_id = $update->callback_query->message->message_id ?? null;
$data = $update->callback_query->data ?? null;
$from_id = $message->from->id ?? null;

if ($update) {
    bot("setMyCommands", [
        "commands" => json_encode([
            ['command' => "start", 'description' => 'رساله الترحيب'],
        ])
    ]);
}

global $NAMERO;
if (!isset($NAMERO) || !is_dir($NAMERO)) {
    $bot_info = bot("getMe");
    $bot_id = $bot_info->result->id;
    $NAMERO = __DIR__ . '/NAMERO/' . $bot_id . '/';
    if (!is_dir($NAMERO)) mkdir($NAMERO, 0777, true);
}

$channels_file = $NAMERO . "Namero12.txt";
$channels = file_exists($channels_file) ? explode("\n", file_get_contents($channels_file)) : [];
$new_users_file = $NAMERO . "new_users.txt";
$Namero11_file = $NAMERO . "Namero11.txt";
$admins = file_exists($NAMERO . "admins.php") ? include($NAMERO . "admins.php") : [];

$bot_status_file = $NAMERO . "bot_status.txt";
$bot_status = file_exists($bot_status_file) ? file_get_contents($bot_status_file) : "enabled";

if ($text == "/start" && $from_id != $SALEH && !in_array($from_id, $admins)) {
    if ($bot_status == "disabled") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ *البوت مقفول مؤقتًا*\n\n⏳ الرجاء المحاولة لاحقًا",
            'parse_mode' => "Markdown"
        ]);
        exit;
    }
}

$Namero13_file = $NAMERO . "Namero13.txt";
if ($data == "toggle_forwarding" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    if (file_exists($Namero13_file)) {
        $status = file_get_contents($Namero13_file);
        if ($status == "enabled") {
            file_put_contents($Namero13_file, "disabled");
            bot('editMessageText', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id,
                'text' => "🚫 *تم قفل التوجيه*",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
            ]);
        } else {
            file_put_contents($Namero13_file, "enabled");
            bot('editMessageText', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id,
                'text' => "✅ *تم فتح التوجيه*",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
            ]);
        }
    } else {
        file_put_contents($Namero13_file, "enabled");
        bot('editMessageText', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id,
            'text' => "✅ *تم فتح التوجيه*",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
        ]);
    }
}
if (file_exists($Namero13_file) && file_get_contents($Namero13_file) == "enabled" && $from_id != $SALEH) {
    bot('forwardMessage', [
        'chat_id' => $SALEH,
        'from_chat_id' => $chat_id,
        'message_id' => $message->message_id
    ]);
}
$users_file = $NAMERO . "users.json";
if ($text == "/start") {
    $users_data = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
    if (!array_key_exists($from_id, $users_data)) {
        $users_data[$from_id] = [
            'name' => $message->from->first_name,
            'username' => $message->from->username ?? "غير معروف",
            'id' => $message->from->id,
        ];
        file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($from_id != $SALEH && file_exists($Namero11_file) && file_get_contents($Namero11_file) == "enabled") {
            $user_count = count($users_data);
            bot('sendMessage', [
                'chat_id' => $SALEH,
                'text' => "📝 *تم دخول شخص جديد إلى البوت* 📝\n\n" .
                    "*الاسم:*[" . $message->from->first_name . "] \n" .
                    "*المعرف:*[@" . ($message->from->username ?? "غير معروف") . "] \n" .
                    "*الايدي:* " . $message->from->id . "\n" .
                    "*عدد الأعضاء:* " . $user_count,
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}
if (($text == "/start" || $data == "@ypui5" || $data == "back") && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
    if (file_exists($NAMERO . 'step.txt')) unlink($NAMERO . 'step.txt');
    $start_time = microtime(true);
    $Namero13 = file_exists($Namero13_file) ? file_get_contents($Namero13_file) : "disabled";
    $forwarding_button_text = $Namero13 == "enabled" ? "🚫 قفل التوجيه" : "✅ فتح التوجيه";
    $Namero11 = file_exists($Namero11_file) ? file_get_contents($Namero11_file) : "disabled";
    $notification_button_text = $Namero11 == "enabled" ? "🚫 تعطيل التنبيه" : "✅ تفعيل التنبيه";
    $bot_status = file_exists($bot_status_file) ? file_get_contents($bot_status_file) : "enabled";
    $bot_button_text = $bot_status == "enabled" ? "🚫 قفل البوت" : "✅ فتح البوت";
    $users_data = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
    $user_count = count($users_data);
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2);
    function getSpeedDescription($time) {
        if ($time > 1000) return "بطيء 🐢";
        elseif ($time > 500) return "متوسطة 🚶";
        elseif ($time > 200) return "معقول 🚗";
        elseif ($time > 100) return "جيد 🚀";
        else return "سريع جدًا ⚡";
    }
    $speed_description = getSpeedDescription($response_time);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "*اهلا بك عزيزي الادمن مصطفى في لوحه تحكم البوت 🌀\n----------------------------\n- عدد المستخدمين: $user_count 🧬\n- سرعة البوت: $speed_description*\n\n✨ تم التطوير بواسطة [@ypui5](https://t.me/ypui5)",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text'=>'أخر تحديثات البوت ⚙️','url'=>"https://t.me/TJUI9"]],
                [['text'=>'مطور البوت☠️','url'=>"https://t.me/ypui5"], ['text' => '🛠 الإذاعة', 'callback_data' => 'broadcast']],
                [['text' => '➕ إضافة قناة', 'callback_data' => 'add_channel'], ['text' => '➖ حذف قناة', 'callback_data' => 'delete_channel']],
                [['text' => '📝 عرض القنوات', 'callback_data' => 'list_channels']],
                [['text' => $bot_button_text, 'callback_data' => 'toggle_bot_status']],
                [['text' => $forwarding_button_text, 'callback_data' => 'toggle_forwarding'], ['text' => $notification_button_text, 'callback_data' => 'toggle_notification']],
                [['text' => 'اعدادات البوت ', 'callback_data' => 'back_to_admin'], ['text' => 'تعديل الازرار ', 'callback_data' => 'zrar']],
                [['text' => '👥 قسم الأدمنية', 'callback_data' => 'manage_admins']],
                [['text' => 'اعدادات بوت الرشق 📮 ', 'callback_data' => '@ypui5']]
            ]
        ])
    ]);
}
if ($data == "back_to_admin") {
    $developer_link = file_exists($NAMERO . "developer_link.txt") ? file_get_contents($NAMERO . "developer_link.txt") : "https://t.me/ypui5";
    $channel_link = file_exists($NAMERO . "channel_link.txt") ? file_get_contents($NAMERO . "channel_link.txt") : "https://t.me/TJUI9";
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "⚙️ اختر الإعداد الذي تريد تغييره:",
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "سخب نسخه احطايطيه 📝", 'callback_data' => 'backup_download']],
                [['text' => "رفع نسخه احطايطيه 📤", 'callback_data' => 'restore_users']],
                [['text' => "رجوع ♻️", 'callback_data' => '@ypui5']],
            ],
        ]),
    ]);
}
$update = json_decode(file_get_contents("php://input"), true);
if (isset($update['callback_query'])) {
    $data = $update['callback_query']['data'];
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $message_id = $update['callback_query']['message']['message_id'];
    $from_id = $update['callback_query']['from']['id'];
    if ($data == "backup_download" && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
        if (file_exists($users_file)) {
            bot('sendDocument', [
                'chat_id' => $chat_id,
                'document' => new CURLFile($users_file),
                'caption' => "📂 *نسخة احتياطية من بيانات الأعضاء*",
                'parse_mode' => 'Markdown'
            ]);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "❌ لم يتم العثور على ملف النسخة الاحتياطية.",
                'parse_mode' => 'Markdown'
            ]);
        }
    }
    if ($data == "restore_users") {
        $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
        if (!isset($Namero['mode'][$from_id]) || $Namero['mode'][$from_id] !== 'waiting_for_restore') {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*• يرجى إرسال النسخة الاحتياطية بصيغة users.json الآن.*",
                'parse_mode' => "markdown"
            ]);
            $Namero['mode'][$from_id] = 'waiting_for_restore';
            file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        }
    }
}
if (isset($update['message']['document'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $from_id = $message['from']['id'];
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    if (isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == 'waiting_for_restore') {
        $file_name = $message['document']['file_name'];
        if ($file_name == "users.json") {
            if (file_exists($users_file)) unlink($users_file);
            $file_id = $message['document']['file_id'];
            $file = bot('getFile', ['file_id' => $file_id]);
            if (!$file || !isset($file->result->file_path)) {
                bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "❌ *حدث خطأ أثناء جلب الملف من Telegram. تأكد من أن الملف صالح.*", 'parse_mode' => "markdown" ]);
                exit;
            }
            $file_path = $file->result->file_path;
            $file_url = "https://api.telegram.org/file/bot" . API_KEY . "/" . $file_path;
            $ch = curl_init($file_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            $file_content = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_status != 200 || !$file_content) {
                bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "❌ *لم يتم العثور على الملف على خوادم Telegram. حاول مرة أخرى.*", 'parse_mode' => "markdown" ]);
                exit;
            }
            file_put_contents($users_file, $file_content);
            bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "*• تم استبدال النسخة الاحتياطية بنجاح ✅*", 'parse_mode' => "markdown" ]);
            unset($Namero['mode'][$from_id]);
            file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        } else {
            bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "*• الملف المرسل ليس بصيغة users.json ❌، يرجى إرسال الملف الصحيح.*", 'parse_mode' => "markdown" ]);
        }
    }
}
if ($data == "broadcast" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "✉️ *اختر نوع الإذاعة التي تريد استخدامها *",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => 'إذاعة نصية', 'callback_data' => 'text_broadcast']],
                [['text' => 'إذاعة بالتوجيه', 'callback_data' => 'forward_broadcast']],
                [['text' => 'إذاعة بالوسائط', 'callback_data' => 'media_broadcast']],
                [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']]
            ]
        ])
    ]);
}
$step_file = $NAMERO . 'step.txt';
if ($data == "text_broadcast" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    file_put_contents($step_file, 'text_broadcast');
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "✉️ *أرسل النص الذي تريد إذاعته لكل المستخدمين.*",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
    ]);
}
if ($data == "forward_broadcast" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    file_put_contents($step_file, 'forward_broadcast');
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "✉️ *قم بتوجيه الرسالة التي تريد إذاعتها لكل المستخدمين.*",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
    ]);
}
if ($data == "media_broadcast" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    file_put_contents($step_file, 'media_broadcast');
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "✉️ *أرسل الوسائط (صورة، فيديو، ملف) مع النص الذي تريد إذاعته.*",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
    ]);
}
if (file_exists($step_file) && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
    $step = file_get_contents($step_file);
    $users_data = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
    $user_count = 0;
    if ($step == "text_broadcast" && isset($text)) {
        unlink($step_file);
        foreach ($users_data as $user_id => $user_info) {
            $response = bot('sendMessage', [ 'chat_id' => $user_id, 'text' => $text ]);
            if ($response && $response->ok) $user_count++;
        }
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "✅ *تم إرسال الإذاعة النصية بنجاح.*\n📋 *عدد المستلمين:* $user_count", 'parse_mode' => 'Markdown' ]);
    } elseif ($step == "forward_broadcast" && isset($message->reply_to_message)) {
        unlink($step_file);
        $user_count = 0;
        foreach ($users_data as $user_id => $user_info) {
            $response = bot('forwardMessage', [ 'chat_id' => $user_id, 'from_chat_id' => $chat_id, 'message_id' => $message->reply_to_message->message_id ]);
            if ($response && $response->ok) $user_count++;
        }
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "✅ *تم إرسال الإذاعة بالتوجيه بنجاح.*\n📋 *عدد المستلمين:* $user_count", 'parse_mode' => 'Markdown' ]);
    } elseif ($step == "media_broadcast" && (isset($message->photo) || isset($message->video) || isset($message->document))) {
        unlink($step_file);
        $media_type = isset($message->photo) ? 'photo' : (isset($message->video) ? 'video' : 'document');
        $media_id = $message->{$media_type}[0]->file_id ?? $message->{$media_type}->file_id;
        foreach ($users_data as $user_id => $user_info) {
            $response = bot('send' . ucfirst($media_type), [ 'chat_id' => $user_id, $media_type => $media_id, 'caption' => $message->caption ?? '' ]);
            if ($response && $response->ok) $user_count++;
        }
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "✅ *تم إرسال الإذاعة بالوسائط بنجاح.*\n📋 *عدد المستلمين:* $user_count", 'parse_mode' => 'Markdown' ]);
    }
}
if ($data == "toggle_notification" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    if (file_exists($Namero11_file)) {
        $status = file_get_contents($Namero11_file);
        if ($status == "enabled") {
            file_put_contents($Namero11_file, "disabled");
            bot('editMessageText', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id,
                'text' => "🚫 *تم تعطيل تنبيه الأعضاء الجدد*",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
            ]);
        } else {
            file_put_contents($Namero11_file, "enabled");
            bot('editMessageText', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id,
                'text' => "✅ *تم تفعيل تنبيه الأعضاء الجدد*",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
            ]);
        }
    } else {
        file_put_contents($Namero11_file, "enabled");
        bot('editMessageText', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id,
            'text' => "✅ *تم تفعيل تنبيه الأعضاء الجدد*",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
        ]);
    }
}
if ($data == "add_channel" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    file_put_contents($step_file, "add_channel");
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "📢 *أرسل معرف القناة الآن (بدون @):*",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => 'back']] ] ])
    ]);
}
if (file_exists($step_file) && file_get_contents($step_file) == "add_channel" && $from_id == $SALEH) {
    if (strpos($text, '@') === 0) {
        $channel = str_replace('@', '', $text);
        if (!in_array($channel, $channels)) {
            $channels[] = $channel;
            file_put_contents($channels_file, implode("\n", $channels));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅ *تم إضافة القناة @$channel بنجاح!*",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
            ]);
            unlink($step_file);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ *القناة @$channel موجودة بالفعل!*",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
            ]);
        }
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❗ *الرجاء إرسال معرف القناة بشكل صحيح (مثل: @example_channel).*",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
        ]);
    }
}
if ($data == "delete_channel" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    if ($channels) {
        $buttons = [];
        foreach ($channels as $channel) {
            $buttons[] = [ ['text' => "@$channel", 'callback_data' => 'noop'], ['text' => '❌ حذف', 'callback_data' => "remove_channel_$channel"] ];
        }
        $buttons[] = [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']];
        bot('editMessageText', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id,
            'text' => "🗑️ *اختر القناة لحذفها:*",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    } else {
        bot('editMessageText', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id,
            'text' => "🚫 *لا توجد قنوات لحذفها.*",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
        ]);
    }
}
if (strpos($data, "remove_channel_") === 0 && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    $channel_to_remove = str_replace("remove_channel_", "", $data);
    if (in_array($channel_to_remove, $channels)) {
        $channels = array_filter($channels, fn($c) => $c != $channel_to_remove);
        file_put_contents($channels_file, implode("\n", $channels));
        bot('answerCallbackQuery', [ 'callback_query_id' => $update->callback_query->id, 'text' => "✅ تم حذف القناة @$channel_to_remove", 'show_alert' => true ]);
        $data = "delete_channel";
    }
}
if ($data == "list_channels" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    $channels_list = $channels ? implode("\n", array_map(fn($c) => "- [@$c]", $channels)) : "🚫 لا توجد قنوات مضافة.";
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "*القنوات الإجبارية الحالية:*\n\n$channels_list",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => '⬅️ رجوع', 'callback_data' => '@ypui5']] ] ])
    ]);
}
if ($data == "@ypui5" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    $start_time = microtime(true);
    $Namero13 = file_exists($Namero13_file) ? file_get_contents($Namero13_file) : "disabled";
    $forwarding_button_text = $Namero13 == "enabled" ? "🚫 قفل التوجيه" : "✅ فتح التوجيه";
    $Namero11 = file_exists($Namero11_file) ? file_get_contents($Namero11_file) : "disabled";
    $notification_button_text = $Namero11 == "enabled" ? "🚫 تعطيل التنبيه" : "✅ تفعيل التنبيه";
    $bot_status = file_exists($bot_status_file) ? file_get_contents($bot_status_file) : "enabled";
    $bot_button_text = $bot_status == "enabled" ? "🚫 قفل البوت" : "✅ فتح البوت";
    $users_data = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
    $user_count = count($users_data);
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2);
    function getSpeedDescription($time) {
        if ($time > 1000) return "بطيء 🐢";
        elseif ($time > 500) return "متوسطة 🚶";
        elseif ($time > 200) return "معقول 🚗";
        elseif ($time > 100) return "جيد 🚀";
        else return "سريع جدًا ⚡";
    }
    $speed_description = getSpeedDescription($response_time);
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id,
        'text' => "*اهلا بك عزيزي الادمن مصطفى في لوحه تحكم البوت 🌀\n----------------------------\n- عدد المستخدمين: $user_count 🧬\n- سرعة البوت: $speed_description*\n\n✨ تم التطوير بواسطة [@ypui5](https://t.me/ypui5)",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text'=>'أخر تحديثات البوت ⚙️','url'=>"https://t.me/TJUI9"]],
                [['text'=>'مطور البوت ☠️','url'=>"https://t.me/ypui5"], ['text' => '🛠 الإذاعة', 'callback_data' => 'broadcast']],
                [['text' => '➕ إضافة قناة', 'callback_data' => 'add_channel'], ['text' => '➖ حذف قناة', 'callback_data' => 'delete_channel']],
                [['text' => '📝 عرض القنوات', 'callback_data' => 'list_channels']],
                [['text' => $bot_button_text, 'callback_data' => 'toggle_bot_status']],
                [['text' => $forwarding_button_text, 'callback_data' => 'toggle_forwarding'], ['text' => $notification_button_text, 'callback_data' => 'toggle_notification']],
                [['text' => 'اعدادات البوت ', 'callback_data' => 'back_to_admin'], ['text' => 'تعديل الازرار ', 'callback_data' => 'zrar']],
                [['text' => '👥 قسم الأدمنية', 'callback_data' => 'manage_admins']],
                [['text' => 'اعدادات بوت الرشق 📮 ', 'callback_data' => '@ypui5']]
            ]
        ])
    ]);
}
if ($data == "manage_admins" && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
    $admins = file_exists($NAMERO . "admins.php") ? include($NAMERO . "admins.php") : [];
    $buttons = [];
    $text = "👥 *إدارة الإدمنية الحالية:*\n\n";
    foreach ($admins as $id) {
        $text .= "- $id\n";
        $buttons[] = [ ['text' => "$id", 'callback_data' => "null"], ['text' => "❌ حذف", 'callback_data' => "del_admin_$id"] ];
    }
    $buttons[] = [ ['text' => "➕ إضافة إدمن جديد", 'callback_data' => "add_admin"] ];
    $buttons[] = [ ['text' => "🔙 رجوع", 'callback_data' => "home"] ];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode(['inline_keyboard' => $buttons])
    ]);
}
if (preg_match("/^del_admin_(\d+)$/", $data, $m) && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
    $id_to_remove = (int)$m[1];
    $admins = file_exists($NAMERO . "admins.php") ? include($NAMERO . "admins.php") : [];
    $admins = array_filter($admins, fn($a) => $a != $id_to_remove);
    file_put_contents($NAMERO . "admins.php", "<?php\nreturn " . var_export(array_values($admins), true) . ";\n");
    bot('answerCallbackQuery', [ 'callback_query_id' => $update->callback_query->id, 'text' => "✅ تم حذف الإدمن $id_to_remove" ]);
    $data = "manage_admins";
}
$add_admin_step_file = $NAMERO . "add_admin_step.txt";
if ($data == "add_admin" && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
    file_put_contents($add_admin_step_file, $chat_id);
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "📥 أرسل الآن رقم الـ ID الخاص بالإدمن الجديد"
    ]);
}
if (file_exists($add_admin_step_file) && file_get_contents($add_admin_step_file) == $chat_id && is_numeric($text)) {
    $admins = file_exists($NAMERO . "admins.php") ? include($NAMERO . "admins.php") : [];
    $new_admin = (int)$text;
    if (!in_array($new_admin, $admins)) {
        $admins[] = $new_admin;
        file_put_contents($NAMERO . "admins.php", "<?php\nreturn " . var_export($admins, true) . ";\n");
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "✅ تم إضافة الإدمن $new_admin" ]);
    } else {
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "⚠️ هذا الإدمن موجود بالفعل." ]);
    }
    unlink($add_admin_step_file);
}
foreach ($channels as $channel) {
    $check = file_get_contents("https://api.telegram.org/bot".API_KEY."/getChatMember?chat_id=@$channel&user_id=$from_id");
    if (strpos($check, '"status":"left"') !== false) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "🚫 *يجب عليك الاشتراك في قناة @$channel لاستخدام البوت.*",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "اشتراك @$channel", 'url' => "https://t.me/$channel"]] ] ])
        ]);
        exit;
    }
}
