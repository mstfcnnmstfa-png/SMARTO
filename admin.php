<?php

$SALEH = 7816487928; 
$NAMERO = "NAMERO/". $bot_id; 
mkdir ("NAMERO"); 
mkdir ("NAMERO/$bot_id"); 

$update = json_decode($_raw_input);
$message = $update->message;
$chat_id = $message->chat->id;
$text = $message->text;
$chat_id2 = $update->callback_query->message->chat->id ?? null;
$message_id = $update->callback_query->message->message_id ?? null;
$data = $update->callback_query->data ?? null;
$from_id = $message->from->id ?? null;

$_cmd_cache = __DIR__ . '/.cmd_cache.txt';
if (!file_exists($_cmd_cache) || (time() - filemtime($_cmd_cache)) > 86400) {
    bot("setMyCommands", [
        "commands" => json_encode([
            ['command' => "start", 'description' => 'رسالة الترحيب'],
        ])
    ]);
    file_put_contents($_cmd_cache, time());
}

$channels = db_get_force_channels(); 

$Namero13_file = "Namero13.txt";
$new_users_file = "new_users.txt";
$Namero11_file = "Namero11.txt";
$fake_count_file = "fake_count.txt";
$admins = file_exists("admins.php") ? include("admins.php") : [];

function getSpeedDescription($time) {
if ($time > 1000) { return "بطيء 🐢"; }
elseif ($time > 500) { return "متوسطة 🚶"; }
elseif ($time > 200) { return "معقول 🚗"; }
elseif ($time > 100) { return "جيد 🚀"; }
else { return "سريع جدًا ⚡"; }
}

$bot_status = db_get_bot_status();

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
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
]) 
]);
} else {
file_put_contents($Namero13_file, "enabled");
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "✅ *تم فتح التوجيه*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
]) 
]);
}
} else {
file_put_contents($Namero13_file, "enabled");
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "✅ *تم فتح التوجيه*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
]) 
]);
}}
if (file_exists($Namero13_file) && file_get_contents($Namero13_file) == "enabled" && $from_id != $SALEH) {
bot('forwardMessage', [
'chat_id' => $SALEH,
'from_chat_id' => $chat_id,
'message_id' => $message->message_id
]);
}

if ($text == "/start") {
$users_data = db_get_users_list();
if (!array_key_exists($from_id, $users_data)) {
$users_data[$from_id] = [
'name' => $message->from->first_name,
'username' => $message->from->username ?? "غير معروف",
'id' => $message->from->id,
];
db_save_users_list($users_data);
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

if (($text == "/start" || $data == "@S_P_P1") && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
if (file_exists('step.txt')) {
unlink('step.txt');
}
$start_time = microtime(true);
$Namero13 = file_exists($Namero13_file) ? file_get_contents($Namero13_file) : "disabled";
$forwarding_button_text = $Namero13 == "enabled" ? "🚫 قفل التوجيه" : "✅ فتح التوجيه";
$Namero11 = file_exists($Namero11_file) ? file_get_contents($Namero11_file) : "disabled";
$notification_button_text = $Namero11 == "enabled" ? "🚫 تعطيل التنبيه" : "✅ تفعيل التنبيه";
$bot_status = db_get_bot_status();
$bot_button_text = $bot_status == "enabled" ? "🚫 قفل البوت" : "✅ فتح البوت";
$users_data = db_get_users_list();
$user_count = count($users_data);
$fake_count_status = file_exists($fake_count_file) ? file_get_contents($fake_count_file) : "disabled";
if ($fake_count_status == "enabled") $user_count += 10000;
$fake_count_btn = $fake_count_status == "enabled" ? "🔴 تعطيل الرقم الوهمي" : "🟢 تفعيل الرقم الوهمي";
$end_time = microtime(true);
$response_time = round(($end_time - $start_time) * 1000, 2); 
$speed_description = getSpeedDescription($response_time); 
$_admin_panel_text = "*اهلا بك عزيزي الادمن ناميرو في لوحه تحكم البوت 🌀
----------------------------
- عدد المستخدمين: $user_count 🧬
- سرعة البوت: $speed_description*";
$_admin_panel_kb = json_encode([
'inline_keyboard' => [
[['text' => '📣 الإذاعة', 'callback_data' => 'broadcast']],
[['text' => '🔺 شحن نقاط', 'callback_data' => 'coins'], ['text' => '🔻 خصم نقاط', 'callback_data' => 'deduct_coins']],
[['text' => '➕ إضافة قناة', 'callback_data' => 'add_channel'], ['text' => '➖ حذف قناة', 'callback_data' => 'delete_channel']],
[['text' => '📋 عرض القنوات', 'callback_data' => 'list_channels']],
[['text' => $bot_button_text, 'callback_data' => 'toggle_bot_status']],
[['text' => $forwarding_button_text, 'callback_data' => 'toggle_forwarding'], ['text' => $notification_button_text, 'callback_data' => 'toggle_notification']],
[['text' => $fake_count_btn, 'callback_data' => 'toggle_fake_count']],
[['text' => '⚙️ إعدادات البوت', 'callback_data' => 'back_to_admin'], ['text' => '✏️ تعديل الأزرار', 'callback_data' => 'zrar']],
[['text' => '👥 قسم الأدمنية', 'callback_data' => 'manage_admins']],
[['text' => '📮 إعدادات بوت الرشق', 'callback_data' => '@NameroBots']]
]
]);
if ($data == "@S_P_P1") {
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => $_admin_panel_text,
'parse_mode' => 'Markdown',
'reply_markup' => $_admin_panel_kb
]);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => $_admin_panel_text,
'parse_mode' => 'Markdown',
'reply_markup' => $_admin_panel_kb
]);
}
}
if ($data == "toggle_bot_status" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
$current = db_get_bot_status();
if ($current == "enabled") {
    db_set_bot_status("disabled");
    $msg = "🚫 *تم قفل البوت بنجاح*";
} else {
    db_set_bot_status("enabled");
    $msg = "✅ *تم فتح البوت بنجاح*";
}
bot('editMessageText',[
'chat_id'=>$chat_id2,
'message_id'=>$message_id,
'text'=>$msg,
'parse_mode'=>"Markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'⬅️ رجوع','callback_data'=>'@S_P_P1']]
]
])
]);
}

if ($data == "set_developer") {
file_put_contents("action.txt", "set_developer");
bot('EditMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "🔗 أرسل الآن رابط المطور، ويجب أن يبدأ بـ `https://`.",
'parse_mode' => "Markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "🔙 رجوع", 'callback_data' => 'back_to_admin']],
],
]),
]);
}

if ($data == "set_channel") {
file_put_contents("action.txt", "set_channel");
bot('EditMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "🔗 أرسل الآن رابط القناة، ويجب أن يبدأ بـ `https://`.",
'parse_mode' => "Markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "🔙 رجوع", 'callback_data' => 'back_to_admin']],
],
]),
]);
}

if ($text && file_exists("action.txt")) {
$action = file_get_contents("action.txt");

if ($action == "set_developer") {
if (strpos($text, "https://") === 0) {
file_put_contents("developer_link.txt", $text);
file_put_contents("action.txt", "");
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ تم حفظ رابط المطور بنجاح: `$text`",
'parse_mode' => "Markdown",
]);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ الرابط غير صحيح. يرجى إرسال رابط يبدأ بـ `https://`.",
'parse_mode' => "Markdown",
]);
}
}

if ($action == "set_channel") {
if (strpos($text, "https://") === 0) {
file_put_contents("channel_link.txt", $text);
file_put_contents("action.txt", "");
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ تم حفظ رابط القناة بنجاح: `$text`",
'parse_mode' => "Markdown",
]);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ الرابط غير صحيح. يرجى إرسال رابط يبدأ بـ `https://`.",
'parse_mode' => "Markdown",
]);
}
}
}

if ($data == "back_to_admin") {
$developer_link = file_exists("developer_link.txt") ? file_get_contents("developer_link.txt") : "https://t.me/s_p_p1";
$channel_link = file_exists("channel_link.txt") ? file_get_contents("channel_link.txt") : "https://t.me/+6CCzuBCsmZ5kNDFk";
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "⚙️ اختر الإعداد الذي تريد تغييره:",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "سخب نسخه احطايطيه 📝", 'callback_data' => 'backup_download']],
[['text' => "رفع نسخه احطايطيه 📤", 'callback_data' => 'restore_users']],
[['text' => "رجوع ♻️", 'callback_data' => '@S_P_P1']],
],
]),
]);
}

$update = json_decode($_raw_input, true);
if (isset($update['callback_query'])) {
$data = $update['callback_query']['data'];
$chat_id = $update['callback_query']['message']['chat']['id'];
$message_id = $update['callback_query']['message']['message_id'];
$from_id = $update['callback_query']['from']['id'];
if ($data == "backup_download" && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
$users_data = db_get_users_list();
if (!empty($users_data)) {
$tmp = sys_get_temp_dir() . '/users_backup.json';
file_put_contents($tmp, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
bot('sendDocument', [
'chat_id' => $chat_id,
'document' => new CURLFile($tmp, 'application/json', 'users.json'),
'caption' => "📂 *نسخة احتياطية من بيانات الأعضاء* (" . count($users_data) . " عضو)",
'parse_mode' => 'Markdown'
]);
@unlink($tmp);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ لا يوجد بيانات أعضاء للتصدير.",
'parse_mode' => 'Markdown'
]);
}
}

if ($data == "restore_users") {
$Namero = db_get_namero();
if (!isset($Namero['mode'][$from_id]) || $Namero['mode'][$from_id] !== 'waiting_for_restore') {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "*• يرجى إرسال النسخة الاحتياطية بصيغة users.json الآن.*",
'parse_mode' => "markdown"
]);
$Namero['mode'][$from_id] = 'waiting_for_restore';
db_save_namero($Namero);
}
}
} 
if (isset($update['message']['document'])) {
$message = $update['message'];
$chat_id = $message['chat']['id'];
$from_id = $message['from']['id'];
$Namero = db_get_namero();

if (isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == 'waiting_for_restore') {
$file_name = $message['document']['file_name'];
if ($file_name == "users.json") {
$file_id = $message['document']['file_id'];
$file = bot('getFile', ['file_id' => $file_id]);
if (!$file || !isset($file->result->file_path)) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ *حدث خطأ أثناء جلب الملف من Telegram. تأكد من أن الملف صالح.*",
'parse_mode' => "markdown"
]);
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
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ *لم يتم العثور على الملف على خوادم Telegram. حاول مرة أخرى.*",
'parse_mode' => "markdown"
]);
exit;
}
$imported = json_decode($file_content, true);
if (is_array($imported)) {
db_save_users_list($imported);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "*• تم استيراد النسخة الاحتياطية بنجاح ✅*\n• عدد الأعضاء: " . count($imported),
'parse_mode' => "markdown"
]);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ *الملف غير صالح أو تالف.*",
'parse_mode' => "markdown"
]);
}
unset($Namero['mode'][$from_id]);
db_save_namero($Namero);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "*• الملف المرسل ليس بصيغة users.json ❌، يرجى إرسال الملف الصحيح.*",
'parse_mode' => "markdown"
]);
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
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}

if ($data == "text_broadcast" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
file_put_contents('step.txt', 'text_broadcast');
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "✉️ *أرسل النص الذي تريد إذاعته لكل المستخدمين.*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}

if ($data == "forward_broadcast" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
file_put_contents('step.txt', 'forward_broadcast');
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "✉️ *قم بتوجيه الرسالة التي تريد إذاعتها لكل المستخدمين.*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}

if ($data == "media_broadcast" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
file_put_contents('step.txt', 'media_broadcast');
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "✉️ *أرسل الوسائط (صورة، فيديو، ملف) مع النص الذي تريد إذاعته.*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}

if (file_exists('step.txt') && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
$step = file_get_contents('step.txt');
$users_data = db_get_users_list();
$user_count = 0;

if ($step == "text_broadcast" && isset($text)) {
unlink('step.txt');
foreach ($users_data as $user_id => $user_info) {
$response = bot('sendMessage', [
'chat_id' => $user_id,
'text' => $text
]);
if ($response->ok) {
$user_count++;
}
}
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *تم إرسال الإذاعة النصية بنجاح.*\n📋 *عدد المستلمين:* $user_count",
'parse_mode' => 'Markdown'
]);
} elseif ($step == "forward_broadcast" && isset($message->reply_to_message)) {
unlink('step.txt');
$user_count = 0;

foreach ($users_data as $user_id => $user_info) {
$response = bot('forwardMessage', [
'chat_id' => $user_id,
'from_chat_id' => $chat_id,
'message_id' => $message->reply_to_message->message_id
]);

if ($response && $response->ok) {
$user_count++;
} else {
file_put_contents('debug_forward_errors.txt', json_encode($response, JSON_PRETTY_PRINT), FILE_APPEND);
}
}

bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *تم إرسال الإذاعة بالتوجيه بنجاح.*\n📋 *عدد المستلمين:* $user_count",
'parse_mode' => 'Markdown'
]);
}elseif ($step == "media_broadcast" && (isset($message->photo) || isset($message->video) || isset($message->document))) {
unlink('step.txt');
$media_type = isset($message->photo) ? 'photo' : (isset($message->video) ? 'video' : 'document');
$media_id = $message->{$media_type}[0]->file_id ?? $message->{$media_type}->file_id;
foreach ($users_data as $user_id => $user_info) {
$response = bot('send' . ucfirst($media_type), [
'chat_id' => $user_id,
$media_type => $media_id,
'caption' => $message->caption ?? ''
]);
if ($response->ok) {
$user_count++;
}
}
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *تم إرسال الإذاعة بالوسائط بنجاح.*\n📋 *عدد المستلمين:* $user_count",
'parse_mode' => 'Markdown'
]);
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
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
]) 
]);
} else {
file_put_contents($Namero11_file, "enabled");
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "✅ *تم تفعيل تنبيه الأعضاء الجدد*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
]) 
]);
}
} else {
file_put_contents($Namero11_file, "enabled");
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "✅ *تم تفعيل تنبيه الأعضاء الجدد*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
]) 
]);
}}
if ($data == "add_channel" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
file_put_contents("step.txt", "add_channel");
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "📢 *أرسل معرف القناة الآن (بدون @):*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => 'back']]
]
])
]);}
if (file_exists('step.txt') && file_get_contents('step.txt') == "add_channel" && $from_id == $SALEH) {
if (strpos($text, '@') === 0) {
$channel = str_replace('@', '', $text);
if (!in_array($channel, $channels)) {
$channels[] = $channel;
db_set_force_channels($channels);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *تم إضافة القناة @$channel بنجاح!*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
unlink('step.txt');
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "⚠️ *القناة @$channel موجودة بالفعل!*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❗ *الرجاء إرسال معرف القناة بشكل صحيح (مثل: @example_channel).*",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}
}
if ($data == "delete_channel" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
if ($channels) {
$buttons = [];
foreach ($channels as $channel) {
$buttons[] = [
['text' => "@$channel", 'callback_data' => 'noop'],
['text' => '❌ حذف', 'callback_data' => "remove_channel_$channel"]
];
}
$buttons[] = [['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']];
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
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}}
if (strpos($data, "remove_channel_") === 0 && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
$channel_to_remove = str_replace("remove_channel_", "", $data);
if (in_array($channel_to_remove, $channels)) {
$channels = array_filter($channels, fn($c) => $c != $channel_to_remove);
db_set_force_channels(array_values($channels));
bot('answerCallbackQuery', [
'callback_query_id' => $update->callback_query->id,
'text' => "✅ تم حذف القناة @$channel_to_remove",
'show_alert' => true
]);
$data = "delete_channel";
}}
if ($data == "list_channels" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
$channels_list = $channels ? implode("\n", array_map(fn($c) => "- [@$c]", $channels)) : "🚫 لا توجد قنوات مضافة.";
bot('editMessageText', [
'chat_id' => $chat_id2,
'message_id' => $message_id,
'text' => "*القنوات الإجبارية الحالية:*\n\n$channels_list",
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}
if ($data == "toggle_fake_count" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
bot('answerCallbackQuery', ['callback_query_id' => $update->callback_query->id]);
$current = file_exists($fake_count_file) ? file_get_contents($fake_count_file) : "disabled";
if ($current == "enabled") {
    file_put_contents($fake_count_file, "disabled");
    $msg = "🔴 *تم تعطيل الرقم الوهمي*\nسيظهر العدد الحقيقي للمستخدمين.";
} else {
    file_put_contents($fake_count_file, "enabled");
    $msg = "🟢 *تم تفعيل الرقم الوهمي* (+10,000)\nسيظهر العدد مزاداً عليه 10,000.";
}
bot('editMessageText', [
'chat_id'    => $chat_id2,
'message_id' => $message_id,
'text'       => $msg,
'parse_mode' => 'Markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]
]
])
]);
}
if ($data == "manage_admins" && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
$admins = file_exists("admins.php") ? include("admins.php") : [];
$buttons = [];
$text = "👥 *إدارة الإدمنية الحالية:*\n\n";
foreach ($admins as $id) {
$text .= "- $id\n";
$buttons[] = [['text' => "$id", 'callback_data' => "null"], ['text' => "❌ حذف", 'callback_data' => "del_admin_$id"]];
}
$buttons[] = [['text' => "➕ إضافة إدمن جديد", 'callback_data' => "add_admin"]];
$buttons[] = [['text' => "🔙 رجوع", 'callback_data' => "home"]];
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
$admins = file_exists("admins.php") ? include("admins.php") : [];
$admins = array_filter($admins, fn($a) => $a != $id_to_remove);
file_put_contents("admins.php", "<?php\nreturn " . var_export(array_values($admins), true) . ";\n");
bot('answerCallbackQuery', [
'callback_query_id' => $update->callback_query->id,
'text' => "✅ تم حذف الإدمن $id_to_remove"
]);
$data = "manage_admins";
}
if ($data == "add_admin" && ($chat_id == $SALEH || in_array($chat_id, $admins))) {
file_put_contents("add_admin_step.txt", $chat_id);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "📥 أرسل الآن رقم الـ ID الخاص بالإدمن الجديد"
]);
}
if (file_exists("add_admin_step.txt") && file_get_contents("add_admin_step.txt") == $chat_id && is_numeric($text)) {
$admins = file_exists("admins.php") ? include("admins.php") : [];
$new_admin = (int)$text;
if (!in_array($new_admin, $admins)) {
$admins[] = $new_admin;
file_put_contents("admins.php", "<?php\nreturn " . var_export($admins, true) . ";\n");
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ تم إضافة الإدمن $new_admin"
]);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "⚠️ هذا الإدمن موجود بالفعل."
]);
}
unlink("add_admin_step.txt");
}

if ($data == "deduct_coins" && ($chat_id2 == $SALEH || in_array($chat_id2, $admins))) {
    file_put_contents("deduct_step.txt", "step1:$chat_id2");
    bot('editMessageText', [
        'chat_id'      => $chat_id2,
        'message_id'   => $message_id,
        'text'         => "🔻 *خصم نقاط مستخدم*\n\n📋 أرسل الآن *ID* المستخدم الذي تريد خصم نقاطه:",
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode([
            'inline_keyboard' => [[['text' => '❌ إلغاء', 'callback_data' => '@S_P_P1']]]
        ])
    ]);
}

if ($text && file_exists("deduct_step.txt") && is_numeric($text)) {
    $deduct_raw = file_get_contents("deduct_step.txt");
    if (strpos($deduct_raw, "step1:$chat_id") === 0) {
        $Namero_d = db_get_namero();
        $target_id = trim($text);
        $current_bal = $Namero_d["coin"][$target_id] ?? null;
        if ($current_bal === null) {
            bot('sendMessage', [
                'chat_id'    => $chat_id,
                'text'       => "❌ *المستخدم غير موجود أو لا يملك رصيداً مسجلاً.*\n\nتأكد من الـ ID وأعد المحاولة.",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[['text' => '⬅️ رجوع', 'callback_data' => '@S_P_P1']]]
                ])
            ]);
            unlink("deduct_step.txt");
        } else {
            file_put_contents("deduct_step.txt", "step2:$chat_id:$target_id");
            $api_s = db_get_settings();
            $cur = $api_s['currency'] ?? 'نقطة';
            bot('sendMessage', [
                'chat_id'    => $chat_id,
                'text'       => "👤 *مستخدم ID:* `$target_id`\n💰 *الرصيد الحالي:* `$current_bal` $cur\n\n➡️ أرسل عدد النقاط المراد خصمها:",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[['text' => '❌ إلغاء', 'callback_data' => '@S_P_P1']]]
                ])
            ]);
        }
    }
}

if ($text && file_exists("deduct_step.txt") && is_numeric($text)) {
    $deduct_raw = file_get_contents("deduct_step.txt");
    if (strpos($deduct_raw, "step2:$chat_id:") === 0) {
        $parts     = explode(":", $deduct_raw);
        $target_id = $parts[2];
        $amount    = abs((float)$text);
        $Namero_d  = db_get_namero();
        $api_s     = db_get_settings();
        $cur       = $api_s['currency'] ?? 'نقطة';
        $old_bal   = $Namero_d["coin"][$target_id] ?? 0;
        $new_bal   = max(0, $old_bal - $amount);
        $actually  = $old_bal - $new_bal;
        $Namero_d["coin"][$target_id] = $new_bal;
        db_save_namero($Namero_d);
        unlink("deduct_step.txt");
        $note = ($actually < $amount) ? "\n⚠️ *تم خصم $actually فقط (الرصيد لا يكفي للخصم الكامل)*" : "";
        bot('sendMessage', [
            'chat_id'    => $chat_id,
            'text'       => "✅ *تم الخصم بنجاح*\n\n👤 المستخدم: `$target_id`\n📉 المخصوم: *$actually* $cur\n💰 الرصيد قبل: *$old_bal* $cur\n💰 الرصيد بعد: *$new_bal* $cur$note",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🔻 خصم آخر', 'callback_data' => 'deduct_coins']],
                    [['text' => '⬅️ رجوع للوحة', 'callback_data' => '@S_P_P1']]
                ]
            ])
        ]);
        

        bot('sendMessage', [
            'chat_id'    => $target_id,
            'text'       => "🔔 *تنبيه رصيد*\n\n📉 تم خصم *$actually* $cur من رصيدك\n💰 رصيدك الحالي: *$new_bal* $cur",
            'parse_mode' => 'Markdown'
        ]);
    }
}

if (($data == '@S_P_P1' || $data == 'back') && file_exists("deduct_step.txt")) {
    unlink("deduct_step.txt");
}

$_ch_channels = array_filter(array_map('trim', $channels));
if (!empty($_ch_channels) && !empty($from_id)) {
    $_sub_cache_file = __DIR__ . '/.sub_cache.json';
    $_sub_cache      = [];
    $_sub_ttl        = 3600; 

    
    if (file_exists($_sub_cache_file)) {
        $_sub_raw = @file_get_contents($_sub_cache_file);
        if ($_sub_raw) $_sub_cache = @json_decode($_sub_raw, true) ?: [];
    }

    $_uid_str  = (string)$from_id;
    $_verified = isset($_sub_cache[$_uid_str]) && (time() - (int)$_sub_cache[$_uid_str]) < $_sub_ttl;

    if (!$_verified) {
        
        foreach ($_ch_channels as $_chan) {
            $_cch = curl_init("https://api.telegram.org/bot".API_KEY."/getChatMember?chat_id=@{$_chan}&user_id={$from_id}");
            curl_setopt_array($_cch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $_res = curl_exec($_cch);
            curl_close($_cch);
            if ($_res && strpos($_res, '"status":"left"') !== false) {
                bot('sendMessage', [
                    'chat_id'      => $chat_id,
                    'text'         => "🚫 *يجب عليك الاشتراك في قناة @{$_chan} لاستخدام البوت.*",
                    'parse_mode'   => 'Markdown',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [[['text' => "اشتراك @{$_chan}", 'url' => "https://t.me/{$_chan}"]]]
                    ])
                ]);
                exit;
            }
        }
        
        $_sub_cache[$_uid_str] = time();
        
        foreach ($_sub_cache as $_k => $_v) {
            if ((time() - (int)$_v) > 86400) unset($_sub_cache[$_k]);
        }
        @file_put_contents($_sub_cache_file, json_encode($_sub_cache), LOCK_EX);
    }
}