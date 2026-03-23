<?php
// الملف برمجه المبرمج سميث ماتريكس كامل لا تغيير الحقوق لاني مش هسامح اي حدا ليوم الدين
// القناه @TJUI9
# المعرف @ypiu5
$update = json_decode(file_get_contents("php://input"));
$message = $update->message ?? null;
$chat_id = $message->chat->id ?? $update->callback_query->message->chat->id;
$message_id = $message->message_id ?? $update->callback_query->message->message_id;
$data = $update->callback_query->data ?? null;
$text = $message->text ?? null;
$from_id = $message->from->id ?? $update->callback_query->from->id;
$username = $message->from->username ?? $update->callback_query->from->username;
$bot_info = bot("getMe");
$userBot = $bot_info->result->username;
$bot_id  = $bot_info->result->id ?? "default_bot";

$dir  = "NAMERO/$bot_id";
$file = "$dir/SALEh.json";

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$SALEh = file_exists($file)
    ? json_decode(file_get_contents($file), true)
    : [];

function save($array){
    global $file;
    file_put_contents(
        $file,
        json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

$SALEh['main_buttons_status'] = $SALEh['main_buttons_status'] ?? "✅";
if ($data == "zrar") {
$rows = $SALEh['rows'] ?? [];
$reply_markup = [];
foreach ($rows as $i => $row) {
$currentRow = [];
foreach ($row as $btn_id) {
if (isset($SALEh['SALEhs'][$btn_id])) {
$currentRow[] = ['text' => $SALEh['SALEhs'][$btn_id]['name'], 'callback_data' => 'zh|' . $btn_id];
} elseif (isset($SALEh['links'][$btn_id])) {
$currentRow[] = ['text' => $SALEh['links'][$btn_id]['name'], 'callback_data' => 'zh|' . $btn_id];
}
}
$currentRow[] = ['text' => '+', 'callback_data' => 'addbtn|' . $i];
$reply_markup[] = $currentRow;
}

$reply_markup[] = [['text' => '➕', 'callback_data' => 'addbtn']];
$reply_markup[] = [['text' => "الأزرار الأساسية : {$SALEh['main_buttons_status']}", 'callback_data' => "toggle_main_buttons"]];
$reply_markup[] = [['text' => 'رجوع', 'callback_data' => '@ypiu5']];
$reply_markup = json_encode(['inline_keyboard' => $reply_markup]);
bot('EditMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "*• مرحبا بك في قسم الازرار الشفافة ✨*

- يمكنك اضافه ازرار شفافة او حذفها",
'parse_mode' => 'markdown',
'reply_markup' => $reply_markup,
]);
file_put_contents("set.txt", ".");
$SALEh['n'] = null;
$SALEh['mode'] = null;
save($SALEh);
exit;
}
if ($data == "toggle_main_buttons") {
$SALEh['main_buttons_status'] = ($SALEh['main_buttons_status'] == "✅") ? "❌" : "✅";
save($SALEh);

$rows = $SALEh['rows'] ?? [];
$reply_markup = [];
foreach ($rows as $i => $row) {
$currentRow = [];
foreach ($row as $btn_id) {
if (isset($SALEh['SALEhs'][$btn_id])) {
$currentRow[] = ['text' => $SALEh['SALEhs'][$btn_id]['name'], 'callback_data' => 'zh|' . $btn_id];
} elseif (isset($SALEh['links'][$btn_id])) {
$currentRow[] = ['text' => $SALEh['links'][$btn_id]['name'], 'callback_data' => 'zh|' . $btn_id];
}
}
$currentRow[] = ['text' => '+', 'callback_data' => 'addbtn|' . $i];
$reply_markup[] = $currentRow;
}

$reply_markup[] = [['text' => '➕', 'callback_data' => 'addbtn']];
$reply_markup[] = [['text' => "الأزرار الأساسية : {$SALEh['main_buttons_status']}", 'callback_data' => "toggle_main_buttons"]];
$reply_markup[] = [['text' => 'رجوع', 'callback_data' => '@ypiu5']];
$reply_markup = json_encode(['inline_keyboard' => $reply_markup]);
bot('editMessageReplyMarkup', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'reply_markup' => $reply_markup,
]);
exit;
}

if($text == "مشاهدة الازرار" or $text == 'مشاهده الازرار'){
foreach ($update->message->reply_to_message->reply_markup->inline_keyboard as $row) {
foreach ($row as $button) {
if (isset($button->text)) {
$r = $button->text;
$dat = $button->callback_data;
$dat = "`SALEH:". base64_encode($dat)."`";
$tm = $tm ."\n *$r* -> $dat";
}
}
}
bot("sendmessage",[
'chat_id' => $chat_id,
'text' => "".$tm."

• الكودات الخاصه بالزرار ⁉️",
'parse_mode' => 'markdown',
'reply_to_message_id' => $message_id,
]);
exit();
}
if (preg_match("/^addbtn(\\|(.+))?$/", $data, $m)) {
$SALEh['mode'] = 'add';
$SALEh['row_index'] = isset($m[2]) ? (int)$m[2] : count($SALEh['rows'] ?? []);
bot('EditMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "*• ارسل اسم الزر المراد اضافته*",
'parse_mode' => 'markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => '• رجوع •', 'callback_data' => 'zrar']]
]
])
]);
save($SALEh);
exit;
}

if ($text != '/start' && $text != null && $SALEh['mode'] == 'add') {
$SALEh['n'] = $text;
$SALEh['mode'] = 'addm';
save($SALEh);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "*• ارسل الان المحتوى المراد اضافته الى الزر *
- يمكنك ارسل كليشة نصية (يمكنك استخدام الماركداون) ",
'parse_mode' => 'MarkDown',
'reply_to_message_id' => $message->message_id,
]);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "•يمكنك وضع بعض الاضافات الى كليشه من خلال استخدام الاهاشتاكات التاليه :
1. `#name` : لوضع اسم شخص
2. `#username` : لوضع اسم مستخدم الشخص مع اضافه @ 
3. `#id` : لوضع ايدي الشخص ",
'parse_mode' => 'MarkDown',
'reply_to_message_id' => $message->message_id,
]);
exit;
}

if ($text != '/start' && $SALEh['mode'] == 'addm') {
$code = uniqid();
$row = $SALEh['row_index'] ?? 0;
$name = $SALEh['n'];
if (preg_match("#^https?://#", $text)) {
$SALEh['links'][$code] = [
'name' => $name,
'url' => $text
];
$SALEh['rows'][$row][] = $code;
$replyText = "*• تم حفظ الزر (رابط)*";
} elseif (preg_match("#^SALEH:#", $text)) {
$callback = base64_decode(str_replace("SALEH:", "", $text));
$SALEh['SALEhs'][$code] = [
'name' => $name,
'mo' => $callback,
'Type' => 'callback'
];
$SALEh['rows'][$row][] = $code;
$replyText = "*• تم حفظ الزر (كود كول باك)*";
} else {
$SALEh['SALEhs'][$code] = [
'name' => $name,
'mo' => $text,
'Type' => 'EditMessageText'
];
$SALEh['rows'][$row][] = $code;
$replyText = "*• تم حفظ الزر (محتوى نصي)*";
}
$SALEh['n'] = null;
$SALEh['mode'] = null;
unset($SALEh['row_index']);
save($SALEh);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => $replyText,
'parse_mode' => 'MarkDown',
'reply_markup' => json_encode([
'inline_keyboard' => [[['text' => '• رجوع •', 'callback_data' => 'zrar']]]
])
]);
exit;
}
$zhend = explode("|", $data);
if ($zhend[0] == "zh") {
$id = $zhend[1];
if (isset($SALEh['SALEhs'][$id])) {
$btn = $SALEh['SALEhs'][$id];
$name = $btn['name'];
$mo = $btn['mo'];
$type = $btn['Type'];
if ($type == "callback") {
$fro = "كود كول باك";
$buttons = [
[['text' => "مسح الزر ️", 'callback_data' => "delete|$id"]],
[['text' => "رجوع 🔙", 'callback_data' => "zrar"]]
];
} else {
$fro = "محتوى نصي";
$show = [
"EditMessageText" => "تعديل الرسالة ",
"sendMessage" => "إرسال الرسالة ",
"sendMessageSilent" => "همسة "
][$type] ?? "تعديل الرسالة ";
$buttons = [
[['text' => "طريقة عرض النص: $show", 'callback_data' => "showtype:$id"]],
[['text' => "مسح الزر ", 'callback_data' => "delete|$id"]],
[['text' => "رجوع 🔙", 'callback_data' => "zrar"]]
];
}
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "*• اسم الزر:* $name\n\n*• نوع الزر:* $fro\n\n`$mo`",
'parse_mode' => "markdown",
'disable_web_page_preview' => true,
'reply_markup' => json_encode(['inline_keyboard' => $buttons])
]);
exit;
}
if (isset($SALEh['links'][$id])) {
$name = $SALEh['links'][$id]['name'];
$url = $SALEh['links'][$id]['url'];
$buttons = [
[['text' => "تعديل الرابط ", 'callback_data' => "editlink|$id"]],
[['text' => "مسح الزر ", 'callback_data' => "delete|$id"]],
[['text' => "رجوع 🔙", 'callback_data' => "zrar"]]
];
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "*• اسم الزر:* $name\n\n*• نوع الزر:* رابط خارجي\n\n`$url`",
'parse_mode' => "markdown",
'disable_web_page_preview' => true,
'reply_markup' => json_encode(['inline_keyboard' => $buttons])
]);
exit;
}
}
if (preg_match("#^showtype:(.+)$#", $data, $m)) {
$id = $m[1];
if (!isset($SALEh['SALEhs'][$id])) return;
$curr = $SALEh['SALEhs'][$id]['Type'];
$options = [
['EditMessageText', 'تعديل الرسالة '],
['sendMessage', 'إرسال الرسالة '],
['sendMessageSilent', 'همسة ']
];
$markup = [];
foreach ($options as [$code, $label]) {
$check = ($code == $curr) ? "✅ " : "";
$markup[] = [['text' => $check . $label, 'callback_data' => "settype:$code:$id"]];
}
$markup[] = [['text' => "رجوع 🔙", 'callback_data' => "zh|$id"]];
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "• اختر طريقة عرض النص للزر:\n\n*{$SALEh['SALEhs'][$id]['name']}*",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => $markup])
]);
exit;
}
if (preg_match("#^settype:(EditMessageText|sendMessage|sendMessageSilent):(.+)$#", $data, $m)) {
$new = $m[1];
$id = $m[2];
if (!isset($SALEh['SALEhs'][$id])) return;
$SALEh['SALEhs'][$id]['Type'] = $new;
save($SALEh);
$show = [
"EditMessageText" => "تعديل الرسالة ",
"sendMessage" => "إرسال الرسالة ",
"sendMessageSilent" => "همسة "
][$new];
bot('answerCallbackQuery', [
'callback_query_id' => $update->callback_query->id,
'text' => "تم التغيير إلى: $show",
'show_alert' => false
]);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "• تم تغيير طريقة عرض الزر:\n*{$SALEh['SALEhs'][$id]['name']}*",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع 🔙", 'callback_data' => "zh|$id"]]
]
])
]);
exit;
}
$edit = explode("|", $data);
if ($edit[0] == "editlink") {
$id = $edit[1];
if (isset($SALEh['links'][$id])) {
$SALEh['mode'] = "editlink";
$SALEh['edit_id'] = $id;
save($SALEh);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "*• ارسل الرابط الجديد لهذا الزر 🔗*",
'parse_mode' => 'markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "zrar"]]
]
])
]);
exit;
}
}
if ($SALEh['mode'] == "editlink" && $text != null && $text != "/start") {
$id = $SALEh['edit_id'];
if (preg_match("#^https?://#", $text)) {
$SALEh['links'][$id]['url'] = $text;
$SALEh['mode'] = null;
unset($SALEh['edit_id']);
save($SALEh);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "*• تم تحديث الرابط بنجاح ✅*",
'parse_mode' => 'markdown',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "zrar"]]
]
])
]);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "*• الرابط غير صالح ❌*\nارسل رابط يبدأ بـ http أو https",
'parse_mode' => 'markdown'
]);
}
exit;
}
$zdelete = explode("|", $data);
if ($zdelete[0] == "delete") {
$id = $zdelete[1];
$btn_name = "هذا الزر";
if (isset($SALEh['SALEhs'][$id])) {
$btn_name = $SALEh['SALEhs'][$id]['name'];
unset($SALEh['SALEhs'][$id]);
} elseif (isset($SALEh['links'][$id])) {
$btn_name = $SALEh['links'][$id]['name'];
unset($SALEh['links'][$id]);
}
save($SALEh);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "*• اسم الزر:* $btn_name\n\n- تم مسح الزر بنجاح 🗑️",
'parse_mode' => "markdown",
'disable_web_page_preview' => true,
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع 🔙", 'callback_data' => "zrar"]]
]
])
]);
exit;
}


$price = $SALEh['SALEhs'][$data]['mo'];
$price = str_replace('#name', "[$name](tg://user?id=$from_id)",$price);
$price = str_replace('#username', "[$use]",$price);
$price = str_replace('#id', "$from_id",$price);
$price = str_replace('#coin', "$coin",$price);
$name = $SALEh['SALEhs'][$data]['name'];
$Type = $SALEh['SALEhs'][$data]['Type'];
if($Type == "EditMessageText"){
$reply_p[] = [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX"]];
$reply_p = json_encode(['inline_keyboard'=>$reply_p,]);
}
if($price != null){
bot($Type,[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>$price,
'reply_to_message_id'=>$message->message_id,
'parse_mode'=>"MarkDown",
 'reply_markup'=>$reply_p,
]);
}