<?php
flush();
ob_start();
set_time_limit(0);
error_reporting(0);

$API_KEY = "8575984011:AAGk4WNw26C3zuXKMMAS2TWMLjJdZ3WzqIA";
define('API_KEY', $API_KEY);

function bot($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}

$bot_info = bot("getMe");
$userBot = $bot_info->result->username;
$bot_id = $bot_info->result->id;

$NAMERO = __DIR__ . '/NAMERO/' . $bot_id . '/';
if (!is_dir($NAMERO)) mkdir($NAMERO, 0777, true);

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
$a = strtolower($text);
$tc = $message->chat->type;

$chat_id2 = $update->callback_query->message->chat->id ?? null;
$message_id2 = $update->callback_query->message->message_id ?? null;
$data = $update->callback_query->data ?? null;

$admin = 7816487928;
$sudo = "$admin";

$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
if (!$Namero) {
    $Namero = [];
    $Namero['rshaq'] = "✅";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}
$rshaq = $Namero['rshaq'];
if ($rshaq == "on") $rshaq = "شغال ✅";
else $rshaq = "معطل ❌";

$settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];

$Api_Tok = $settings['token'] ?? "❌ غير محدد";
$api_link = $settings['domain'] ?? "❌ غير محدد";
$invite_reward = $settings['invite_reward'] ?? 5;
$min_order = $settings['min_order_quantity'] ?? 10;
$daily_gift = $settings['daily_gift'] ?? 20;
$currency = $settings['currency'] ?? "نقاط";
$settings['daily_gift_status'] = $settings['daily_gift_status'] ?? "on";
$settings['invite_link_status'] = $settings['invite_link_status'] ?? "on";
$price_per_user = $settings['user_price'] ?? "100";
$Ch = $settings['Ch'] ?? "https://t.me/TJUI9";

if (isset($update->callback_query)) {
    $up = $update->callback_query;
    $chat_id = $up->message->chat->id;
    $from_id = $up->from->id;
    $user = $up->from->username;
    $name = $up->from->first_name;
    $message_id = $up->message->message_id;
    $data = $up->data;
    $tc = $up->chat->type;
}

$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
$rsedi = json_decode(file_get_contents("https://$api_link/api/v2?key=$Api_Tok&action=balance"));
$flos = $rsedi->balance;
$treqa = $rsedi->currency;

// ========== بداية الأوامر ==========
if ($text == "tart") {
    if ($chat_id == $admin || in_array($chat_id, $admins)) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*• اهلا بك عزيزي المطور مصطفى في لوحه الادمن 🧬*\n\n• رصيدك : *$flos$*\n• العمله : *$treqa*\n• الهديه اليوميه: *$daily_gift*\n• اقل عدد للتمويل: *$min_order*\n• $currency رابط الدعوه: *$invite_reward*\n• ايدي الخدمة: *$currency*\n• سعر العضو الواحد: *$price_per_user* $currency\n• حاله التمويل $rshaq",
            'parse_mode' => "markdown",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "شحن او خصم رصيد ", 'callback_data' => "coins"]],
                    [['text' => "صنع كود هديه", 'callback_data' => "hdiamk"], ['text' => " تعيين الهديه ", 'callback_data' => "set_daily_gift"]],
                    [['text' => "فتح التمويل ", 'callback_data' => "onNamero"], ['text' => "قفل التمويل ", 'callback_data' => "ofNamero"]],
                    [['text' => " تعيين $currency الدعوة", 'callback_data' => "set_invite_reward"], ['text' => " الحد الأدنى للتمويل", 'callback_data' => "set_min_order"]],
                    [['text' => "تعيين ID الخدمة", 'callback_data' => "set_currency"], ['text' => "تعيين سعر العضو", 'callback_data' => "set_user_price"]],
                    [['text' => " تعيين رابط الموقع", 'callback_data' => "set_api_domain"]],
                    [['text' => " تعيين التوكن", 'callback_data' => "set_api_token"], ['text' => " متابعه طلب ", 'callback_data' => "SALEh_1"]],
                ]
            ])
        ]);
        unset($Namero['mode'][$from_id]);
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    }
}

if ($data == "set_api_domain") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل الآن كليشه شحن النقاط 🔥\n\n• يمكنك استخدام الماركدون وعند ارسال اي معرف او رابط يجب وضعه بين *[،]*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
    $Namero['mode'][$from_id] = "await_api_domain";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}

if ($text && $Namero['mode'][$from_id] == "await_api_domain") {
    $settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];
    $settings["domain"] = trim($text);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم الحفظ بنجاح",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($data == "set_api_token") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل الآن كليشه معلومات البوت",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
    $Namero['mode'][$from_id] = "await_api_token";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}

if ($text && $Namero['mode'][$from_id] == "await_api_token") {
    $settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];
    $settings["token"] = trim($text);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم الحفظ بنجاح",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($data == "set_invite_reward") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل عدد ال$currency التي يحصل عليها المستخدم عند استخدام رابط الدعوة 🎁",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
    $Namero['mode'][$from_id] = "await_invite_reward";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}

if (is_numeric($text) && $Namero['mode'][$from_id] == "await_invite_reward") {
    $settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];
    $settings["invite_reward"] = intval($text);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين عدد ال$currency لكل دعوة: *$text* $currency",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($data == "Ch") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل الان رابط قناة البوت 📝\n\nمثال:\nhttps://t.me/××××",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
    $Namero['mode'][$from_id] = "Chh";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}

if ($Namero['mode'][$from_id] == "Chh") {
    if (!preg_match('/^https:\/\/(t\.me|telegram\.me)\/[A-Za-z0-9_]+$/i', $text)) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ الرابط غير صحيح!\n\nالرجاء ارسال رابط قناة يبدأ بـ:\n`https://t.me/×××××`\n\nولا يحتوي على مسافات.",
            'parse_mode' => "markdown"
        ]);
        return;
    }
    $settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];
    $settings["Ch"] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين القناة:\n*$text*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($data == "set_min_order") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل الحد الأدنى المسموح به لعدد $currency التحويل (مثال: 10)",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
    $Namero['mode'][$from_id] = "await_min_order";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}

if (is_numeric($text) && $Namero['mode'][$from_id] == "await_min_order") {
    $settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];
    $settings["min_order_quantity"] = intval($text);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين الحد الأدنى التحويل إلى: *$text* $currency",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($data == "set_daily_gift") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل عدد ال$currency التي يحصل عليها المستخدم يوميًا كهدية (مثال: 20)",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
    $Namero['mode'][$from_id] = "await_daily_gift";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}

if (is_numeric($text) && $Namero['mode'][$from_id] == "await_daily_gift") {
    $settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];
    $settings["daily_gift"] = intval($text);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين قيمة الهدية اليومية إلى: *$text* $currency",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($data == "set_currency") {
    $Namero['mode'][$from_id] = "await_currency";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• ارسل الآن اسم العمله الجديد 🆕\n\n- الاسم الحالي ($currency) 💰",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($data == "set_user_price") {
    $Namero['mode'][$from_id] = "await_user_price";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "ارسل سعر النقاط مقابل كل 1 نجوم 🔥",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
    ]);
}

if ($text && $from_id == $admin) {
    if ($Namero['mode'][$from_id] == "await_currency") {
        $settings['currency'] = trim($text);
        file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        unset($Namero['mode'][$from_id]);
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ تم حفظ العمله: *$text*",
            'parse_mode' => "markdown",
            'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
        ]);
    }
    if ($Namero['mode'][$from_id] == "await_user_price") {
        if (is_numeric($text)) {
            $settings['user_price'] = floatval($text);
            file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            unset($Namero['mode'][$from_id]);
            file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅ تم حفظ ال$currency: *$text*",
                'parse_mode' => "markdown",
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
            ]);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ السعر غير صحيح، يرجى إرسال رقم فقط",
                'parse_mode' => "markdown",
                'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "back"]] ]])
            ]);
        }
    }
}

$settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
function generateUID() {
    return substr(md5(uniqid(rand(), true)), 0, 8);
}
function findUIDbyName($name, $array) {
    foreach ($array as $uid => $data) {
        if ($data['name'] == $name) return $uid;
    }
    return null;
}

function getAdminKeyboard($settings) {
    global $NAMERO;
    $currency = $settings['currency'] ?? "نقاط";
    $daily_gift_status = ($settings['daily_gift_status'] == "on") ? "✅" : "❌";
    $invite_link_status = ($settings['invite_link_status'] == "on") ? "✅" : "❌";
    $transfer_status = ($settings['transfer_status'] == "on") ? "✅" : "❌";
    $starss = ($settings['starss'] == "on") ? "✅" : "❌";
    $Market_status = ($settings['Market'] == "on") ? "✅" : "❌";
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $domain = $protocol . $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $base_url = $domain . $path;
    return [
        'inline_keyboard' => [
            [['text' => "التحكم في الخدمات 🗳", 'callback_data' => "Namero_sections"]],
            [['text' => "التحكم في المتجر 🛍", 'callback_data' => "store_sections"]],
            [['text' => "تعيين معلومات ", 'callback_data' => "set_api_token"], ['text' => "تعيين الهديه", 'callback_data' => "set_daily_gift"]],
            [['text' => "الهديه اليوميه $daily_gift_status", 'callback_data' => "toggle_daily_gift"], ['text' => "رابط الدعوة $invite_link_status", 'callback_data' => "toggle_invite_link"]],
            [['text' => "قسم الاستبدال $Market_status", 'callback_data' => "toggle_Market"]],
            [['text' => "تحويل ال$currency $transfer_status", 'callback_data' => "toggle_transfer"], ['text' => "شحن ال$currency $starss", 'callback_data' => "toggle_starss"]],
            [['text' => "فتح الرشق", 'callback_data' => "onNamero"], ['text' => "قفل الرشق", 'callback_data' => "ofNamero"]],
            [['text' => "تعيين $currency الدعوة", 'callback_data' => "set_invite_reward"], ['text' => "الحد الأدنى للتحويل", 'callback_data' => "set_min_order"]],
            [['text' => "متابعه طلب", 'callback_data' => "SALEh_1"], ['text' => "تعيين عمله البوت", 'callback_data' => "set_currency"]],
            [['text' => "صنع رابط هديه", 'callback_data' => "make_gift_link"], ['text' => "تعيبن سعر النقاط للنجوم", 'callback_data' => "set_user_price"]],
            [['text' => "تعيين قناه البوت ", 'callback_data' => "Ch"], ['text' => "كليشه الشراء", 'callback_data' => "set_api_domain"]],
            [['text' => "شحن او خصم رصيد", 'callback_data' => "coins"]],
            [['text' => "تحكم من الموقع كامل", 'web_app' => ['url' => $base_url . '/admin_panel.php']]],
            [['text' => "رجوع", 'callback_data' => "@ypiu5"]]
        ]
    ];
}

// ========== دوال الأقسام والمتجر والطلبات ==========
if ($data == "store_sections") {
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $sections = $settings["store"]["sections"] ?? [];
    $btns = [];
    if(!empty($sections)){
        foreach($sections as $sid => $sec){
            $btns[] = [ ['text'=>$sec['name'],'callback_data'=>"view_store_section_$sid"], ['text'=>"❌",'callback_data'=>"del_store_section_$sid"] ];
        }
    } else {
        $btns[] = [['text'=>"لا توجد أقسام بعد ❗",'callback_data'=>"no"]];
    }
    $btns[] = [['text'=>"➕ إضافة قسم جديد",'callback_data'=>"add_store_section"]];
    $btns[] = [['text'=>"رجوع",'callback_data'=>"back"]];
    bot('editMessageText', [
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"🗂 *أقسام المتجر:*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>$btns])
    ]);
}
if ($data == "add_store_section") {
    $settings["step"][$from_id] = "await_store_section";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('editMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"✍️ *أرسل اسم القسم:*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"store_sections"]] ]])
    ]);
    exit;
}
if ($settings["step"][$from_id] == "await_store_section") {
    unset($settings["step"][$from_id]);
    $sid = generateUID();
    $settings["store"]["sections"][$sid] = [ "name"=>trim($text), "items"=>[] ];
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"✅ *تم إضافة القسم:* ".trim($text),
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"store_sections"]] ]])
    ]);
    exit;
}
if (preg_match("/^del_store_section_(.*)/", $data, $m)) {
    $sid = $m[1];
    $sectionName = $settings["store"]["sections"][$sid]['name'] ?? 'غير معروف';
    bot('answerCallbackQuery', [ 'callback_query_id' => $update->callback_query->id, 'text' => "⏳ جاري حذف القسم: $sectionName...", 'show_alert' => false ]);
    unset($settings["store"]["sections"][$sid]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $sections = $settings["store"]["sections"] ?? [];
    $btns = [];
    if (!empty($sections)) {
        foreach ($sections as $uid => $sec) {
            $btns[] = [ ['text' => $sec['name'], 'callback_data' => "view_store_section_$uid"], ['text' => "❌", 'callback_data' => "del_store_section_$uid"] ];
        }
    } else {
        $btns[] = [['text' => "لا توجد أقسام بعد ❗", 'callback_data' => "no"]];
    }
    $btns[] = [['text' => "➕ إضافة قسم جديد", 'callback_data' => "add_store_section"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "back"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "🗂 *أقسام المتجر:*\n\n✅ تم حذف القسم: `$sectionName`",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode(['inline_keyboard' => $btns])
    ]);
}
if(preg_match("/^view_store_section_(.*)/",$data,$m)){
    $sid = $m[1];
    $section = $settings["store"]["sections"][$sid];
    $items = $section["items"] ?? [];
    $btns = [];
    if(!empty($items)){
        foreach($items as $iid => $item){
            $btns[] = [ ['text'=>$item['name'],'callback_data'=>"view_item_{$sid}_{$iid}"], ['text'=>"❌",'callback_data'=>"del_item_{$sid}_{$iid}"] ];
        }
    } else {
        $btns[] = [['text'=>"لا توجد سلع بعد ❗",'callback_data'=>"no"]];
    }
    $btns[] = [['text'=>"➕ إضافة سلعة",'callback_data'=>"add_item_$sid"]];
    $btns[] = [['text'=>"رجوع",'callback_data'=>"store_sections"]];
    bot('editMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"📂 *قسم:* ".$section['name'],
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>$btns])
    ]);
}
if(preg_match("/^add_item_(.*)/",$data,$m)){
    $sid = $m[1];
    $settings["step"][$from_id] = "await_item_name_$sid";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('editMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"🛍 *أرسل اسم السلعة:*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"view_store_section_$sid"]] ]])
    ]);
    exit;
}
if(preg_match("/^await_item_name_(.*)/",$settings["step"][$from_id],$m)){
    $sid = $m[1];
    unset($settings["step"][$from_id]);
    $iid = generateUID();
    $settings["store"]["sections"][$sid]["items"][$iid] = [ "name"=>trim($text), "price"=>0, "description"=>"" ];
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"✔️ *تم إضافة السلعة:* ".trim($text),
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"view_store_section_$sid"]] ]])
    ]);
    exit;
}
if(preg_match("/^view_item_(.*)_(.*)/",$data,$m)){
    $sid = $m[1];
    $iid = $m[2];
    $item = $settings["store"]["sections"][$sid]["items"][$iid];
    bot('editMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"🛒 *السلعة:* {$item['name']}\n\n💰 السعر: {$item['price']}\n📝 الوصف: ".($item['description'] ?: "لا يوجد"),
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[
            [['text'=>"تعيين السعر",'callback_data'=>"setprice_{$sid}_{$iid}"]],
            [['text'=>"تعيين الوصف",'callback_data'=>"setdesc_{$sid}_{$iid}"]],
            [['text'=>"رجوع",'callback_data'=>"view_store_section_$sid"]]
        ]])
    ]);
}
if(preg_match("/^setprice_(.*)_(.*)/", $data, $m)){
    $sid = $m[1];
    $iid = $m[2];
    $settings["step"][$from_id] = "await_price_{$sid}_{$iid}";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('editMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"💰 *أرسل السعر الآن:*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]] ]])
    ]);
    exit;
}
if(preg_match("/^await_price_(.*)_(.*)/",$settings["step"][$from_id],$m)){
    $sid = $m[1];
    $iid = $m[2];
    if(!is_numeric($text)){
        bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ السعر لازم يكون رقم!" ]);
        exit;
    }
    unset($settings["step"][$from_id]);
    $settings["store"]["sections"][$sid]["items"][$iid]["price"] = trim($text);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"✔️ *تم تحديث السعر إلى:* $text",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]] ]])
    ]);
    exit;
}
if(preg_match("/^setdesc_(.*)_(.*)/", $data, $m)){
    $sid = $m[1];
    $iid = $m[2];
    $settings["step"][$from_id] = "await_desc_{$sid}_{$iid}";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('editMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"📝 *أرسل الوصف الآن:*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]] ]])
    ]);
    exit;
}
if(preg_match("/^await_desc_(.*)_(.*)/",$settings["step"][$from_id],$m)){
    $sid = $m[1];
    $iid = $m[2];
    unset($settings["step"][$from_id]);
    $settings["store"]["sections"][$sid]["items"][$iid]["description"] = trim($text);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"✔️ *تم تحديث الوصف!*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]] ]])
    ]);
    exit;
}
if(preg_match("/^del_item_(.*)_(.*)/",$data,$m)){
    $sid = $m[1];
    $iid = $m[2];
    $name = $settings["store"]["sections"][$sid]["items"][$iid]["name"];
    unset($settings["store"]["sections"][$sid]["items"][$iid]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery',[ 'callback_query_id'=>$update->callback_query->id, 'text'=>"✔️ تم حذف السلعة: $name" ]);
    bot('editMessageReplyMarkup',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"↩️ رجوع",'callback_data'=>"view_store_section_$sid"]] ]]) ]);
}
if ($data == "make_gift_link") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل عدد ال$currency للهدية 🎁",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"back" ]], ]])
    ]);
    $Namero["mode"][$from_id] = "await_gift_points";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    return; 
}
if (is_numeric($text) && $Namero["mode"][$from_id] == "await_gift_points") {
    $Namero["gift_temp"][$from_id]["points"] = intval($text);
    $Namero["mode"][$from_id] = "await_gift_limit";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "• أرسل عدد الأشخاص الذين يمكنهم استخدام الرابط 👥",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"back" ]], ]])
    ]);
    return; 
}
if (is_numeric($text) && $Namero["mode"][$from_id] == "await_gift_limit") {
    $points = $Namero["gift_temp"][$from_id]["points"];
    $limit = intval($text);
    $code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
    $Namero["gift_links"][$code] = [ "points" => $points, "limit" => $limit, "used" => [] ];
    unset($Namero["gift_temp"][$from_id]);
    unset($Namero["mode"][$from_id]);
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    $bot_username = bot('getme',[])->result->username;
    $link = "https://t.me/$bot_username?start=gift_$code";
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "- تم إنشاء رابط هديه بنجاح 🛍\n----------------------------\n🛍 ال$currency: $points\n🥷 الحد الأقصى: $limit\n❇️ الرابط:\n- [$link] ",
        'parse_mode' => "markdown"
    ]);
}
if ($data == "Namero_sections") {
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $sections = $settings["sections"] ?? [];
    $btns = [];
    if (!empty($sections)) {
        foreach ($sections as $uid => $sectionData) {
            $sectionName = $sectionData['name'];
            $btns[] = [ ['text' => $sectionName, 'callback_data' => "view_section_$uid"], ['text' => "❌", 'callback_data' => "del_section_$uid"] ];
        }
    } else {
        $btns[] = [['text' => "لا توجد أقسام بعد ❗", 'callback_data' => "no"]];
    }
    $btns[] = [['text' => "➕ إضافة قسم جديد", 'callback_data' => "add_new_section"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "back"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "📂 *إدارة الأقسام*\n\nاختر قسم للتحكم أو أضف قسم جديد:",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode(['inline_keyboard' => $btns])
    ]);
}
if ($data == "add_new_section") {
    $settings["step"][$from_id] = "add_section_wait";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "✏️ *أرسل اسم القسم الجديد الآن:*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "Namero_sections"]] ] ])
    ]);
    exit;
}
if ($settings["step"][$from_id] == "add_section_wait") {
    unset($settings["step"][$from_id]);
    $sec_name = trim($text);
    $existing_uid = findUIDbyName($sec_name, $settings["sections"] ?? []);
    if ($existing_uid !== null) {
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "❌ *هذا القسم موجود بالفعل!*", 'parse_mode' => "markdown" ]);
        return;
    }
    $new_uid = generateUID();
    $settings["sections"][$new_uid] = [ "name" => $sec_name, "services" => [] ];
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ *تم إضافة القسم:* $sec_name\n\n🆔 المعرف: `$new_uid`",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع للإدارة", 'callback_data' => "Namero_sections"]] ] ])
    ]);
}
if (preg_match("/^del_section_(.*)/", $data, $m)) {
    $uid = $m[1];
    $sectionName = $settings["sections"][$uid]['name'] ?? 'غير معروف';
    unset($settings["sections"][$uid]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery', [ 'callback_query_id' => $update->callback_query->id, 'text' => "✔️ تم حذف القسم: $sectionName" ]);
    $sections = $settings["sections"] ?? [];
    $btns = [];
    if (!empty($sections)) {
        foreach ($sections as $uid => $sectionData) {
            $sectionName = $sectionData['name'];
            $btns[] = [ ['text' => $sectionName, 'callback_data' => "view_section_$uid"], ['text' => "❌", 'callback_data' => "del_section_$uid"] ];
        }
    }
    $btns[] = [['text' => "➕ إضافة قسم جديد", 'callback_data' => "add_new_section"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "back"]];
    bot('editMessageReplyMarkup', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'reply_markup' => json_encode(['inline_keyboard' => $btns]) ]);
}
if (preg_match("/^view_section_(.*)/", $data, $m)) {
    $uid = $m[1];
    if (!isset($settings["sections"][$uid])) {
        bot('editMessageText', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' => "❌ *هذا القسم لم يعد موجوداً!*", 'parse_mode' => "markdown", 'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "Namero_sections"]] ] ]) ]);
        return;
    }
    $sectionData = $settings["sections"][$uid];
    $sectionName = $sectionData['name'];
    $services = $sectionData["services"] ?? [];
    $btns = [];
    if (!empty($services)) {
        foreach ($services as $serviceUID => $serviceData) {
            $serviceName = $serviceData['name'];
            $btns[] = [ ['text' => $serviceName, 'callback_data' => "service_".$uid."_".$serviceUID], ['text' => "❌", 'callback_data' => "del_service_".$uid."_".$serviceUID] ];
        }
    } else {
        $btns[] = [['text' => "لا توجد خدمات بعد ❗", 'callback_data' => "no"]];
    }
    $btns[] = [['text' => "➕ إضافة خدمة جديدة", 'callback_data' => "add_service_$uid"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "Namero_sections"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "📦 *القسم:* $sectionName\n🆔 المعرف: `$uid`\n\nاختر خدمة أو أضف خدمة جديدة:",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode(['inline_keyboard' => $btns])
    ]);
}
if (preg_match("/^add_service_(.*)/", $data, $m)) {
    $sectionUID = $m[1];
    $settings["step"][$from_id] = "add_service_name_only";
    $settings["temp"][$from_id] = [ "section_uid" => $sectionUID ];
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "✏️ *أرسل اسم الخدمة الآن:*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "view_section_".$sectionUID]] ] ])
    ]);
    exit;
}
if ($settings["step"][$from_id] == "add_service_name_only") {
    $sectionUID = $settings["temp"][$from_id]["section_uid"];
    $serviceName = trim($text);
    if ($serviceName == "") {
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "⚠️ *الاسم لا يمكن أن يكون فارغاً!*", 'parse_mode' => "markdown" ]);
        return;
    }
    $serviceUID = generateUID();
    $settings["sections"][$sectionUID]["services"][$serviceUID] = [
        "name" => $serviceName, "min" => "10", "max" => "1000", "price" => "1000",
        "service_id" => "", "domain" => "", "api" => "", "delay" => "0"
    ];
    unset($settings["step"][$from_id]);
    unset($settings["temp"][$from_id]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    $sectionName = $settings["sections"][$sectionUID]['name'];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ *تم إضافة الخدمة:* $serviceName\n🆔 المعرف: `$serviceUID`\n📦 داخل القسم: $sectionName",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع للقسم", 'callback_data' => "view_section_".$sectionUID]] ] ])
    ]);
}
if (preg_match("/^del_service_(.*)_(.*)/", $data, $m)) {
    $sectionUID = $m[1];
    $serviceUID = $m[2];
    $serviceName = $settings["sections"][$sectionUID]["services"][$serviceUID]['name'] ?? 'غير معروف';
    unset($settings["sections"][$sectionUID]["services"][$serviceUID]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery', [ 'callback_query_id' => $update->callback_query->id, 'text' => "✔️ تم حذف الخدمة: $serviceName" ]);
    $services = $settings["sections"][$sectionUID]["services"] ?? [];
    $btns = [];
    if (!empty($services)) {
        foreach ($services as $uid => $serviceData) {
            $serviceName = $serviceData['name'];
            $btns[] = [ ['text' => $serviceName, 'callback_data' => "service_".$sectionUID."_".$uid], ['text' => "❌", 'callback_data' => "del_service_".$sectionUID."_".$uid] ];
        }
    }
    $btns[] = [['text' => "➕ إضافة خدمة جديدة", 'callback_data' => "add_service_".$sectionUID]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "Namero_sections"]];
    bot('editMessageReplyMarkup', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'reply_markup' => json_encode(['inline_keyboard' => $btns]) ]);
}
if (preg_match("/^service_(.*)_(.*)/", $data, $m)) {
    $sectionUID = $m[1];
    $serviceUID = $m[2];
    if (!isset($settings["sections"][$sectionUID]["services"][$serviceUID])) {
        bot('editMessageText', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' => "❌ *هذه الخدمة لم تعد موجودة!*", 'parse_mode' => "markdown", 'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "view_section_".$sectionUID]] ] ]) ]);
        return;
    }
    $S = $settings["sections"][$sectionUID]["services"][$serviceUID];
    $sectionName = $settings["sections"][$sectionUID]['name'];
    $serviceName = $S['name'];
    $min = $S["min"] ?? "غير معيّن";
    $max = $S["max"] ?? "غير معيّن";
    $price = $S["price"] ?? "غير معيّن";
    $sid = $S["service_id"] ?? "غير معيّن";
    $domain = $S["domain"] ?? "غير معيّن";
    $api = $S["api"] ?? "غير معيّن";
    $delay = $S["delay"] ?? "غير معيّن";
    if ($domain != "غير معيّن" && $api != "غير معيّن") {
        $balance = @file_get_contents("https://$domain/api/v2?key=$api&action=balance");
        if ($balance === false) $balance = "❌ فشل في جلب الرصيد";
    } else $balance = "لا يمكن جلب الرصيد";
    unset($settings["step"][$from_id], $settings["temp"][$from_id]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "⚙️ *إعدادات الخدمة*\n\n" .
            "📌 *اسم الخدمة:* $serviceName\n" .
            "🆔 *معرف الخدمة:* `$serviceUID`\n" .
            "📁 *القسم:* $sectionName\n" .
            "🆔 *معرف القسم:* `$sectionUID`\n\n" .
            "🔢 أقل كمية: *$min*\n" .
            "🔢 أعلى كمية: *$max*\n\n" .
            "💲 سعر 1000: *$price*\n" .
            "🆔 ID الخدمة: *$sid*\n\n" .
            "🌐 الدومين: *$domain*\n" .
            "🔑 API Key: *$api*\n\n" .
            "⏱ مدة الانتظار: *$delay*\n\n" .
            "💳 *رصيدك في الموقع:* $balance",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [
            [['text' => "تعيين أقل حد", 'callback_data' => "set_min_$sectionUID"."_$serviceUID"]],
            [['text' => "تعيين أقصى حد", 'callback_data' => "set_max_$sectionUID"."_$serviceUID"]],
            [['text' => "تعيين السعر", 'callback_data' => "set_price_$sectionUID"."_$serviceUID"]],
            [['text' => "تعيين ID الخدمة", 'callback_data' => "set_sid_$sectionUID"."_$serviceUID"]],
            [['text' => "تعيين الدومين", 'callback_data' => "set_domain_$sectionUID"."_$serviceUID"]],
            [['text' => "تعيين API Key", 'callback_data' => "set_api_$sectionUID"."_$serviceUID"]],
            [['text' => "تعيين مدة الانتظار", 'callback_data' => "set_delay_$sectionUID"."_$serviceUID"]],
            [['text' => "رجوع للقسم", 'callback_data' => "view_section_$sectionUID"]]
        ] ])
    ]);
}
if (preg_match("/^set_(min|max|price|sid|domain|api|delay)_(.*)_(.*)/", $data, $m)) {
    $type = $m[1];
    $sectionUID = $m[2];
    $serviceUID = $m[3];
    $settings["step"][$from_id] = "edit_service_value";
    $settings["temp"][$from_id] = [ "type" => $type, "section_uid" => $sectionUID, "service_uid" => $serviceUID ];
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    $names = [ "min" => "أقل حد", "max" => "أقصى حد", "price" => "السعر لكل 1000", "sid" => "ID الخدمة", "domain" => "الدومين", "api" => "API Key", "delay" => "مدة الانتظار بالساعات" ];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "✏️ *أرسل {$names[$type]} الآن:*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "service_".$sectionUID."_".$serviceUID]] ] ])
    ]);
    exit;
}
if ($settings["step"][$from_id] == "edit_service_value") {
    $temp = $settings["temp"][$from_id];
    $type = $temp["type"];
    $sectionUID = $temp["section_uid"];
    $serviceUID = $temp["service_uid"];
    $value = trim($text);
    if (!isset($settings["sections"][$sectionUID]["services"][$serviceUID])) {
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "❌ *الخدمة لم تعد موجودة!*", 'parse_mode' => "markdown" ]);
        unset($settings["step"][$from_id], $settings["temp"][$from_id]);
        file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
        return;
    }
    $serviceName = $settings["sections"][$sectionUID]["services"][$serviceUID]['name'];
    $sectionName = $settings["sections"][$sectionUID]['name'];
    if ($type == "min") $settings["sections"][$sectionUID]["services"][$serviceUID]["min"] = (int)$value;
    if ($type == "max") $settings["sections"][$sectionUID]["services"][$serviceUID]["max"] = (int)$value;
    if ($type == "price") $settings["sections"][$sectionUID]["services"][$serviceUID]["price"] = $value;
    if ($type == "sid") $settings["sections"][$sectionUID]["services"][$serviceUID]["service_id"] = $value;
    if ($type == "domain") $settings["sections"][$sectionUID]["services"][$serviceUID]["domain"] = $value;
    if ($type == "api") $settings["sections"][$sectionUID]["services"][$serviceUID]["api"] = $value;
    if ($type == "delay") $settings["sections"][$sectionUID]["services"][$serviceUID]["delay"] = (int)$value;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    unset($settings["step"][$from_id]);
    unset($settings["temp"][$from_id]);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ *تم تحديث القيمة بنجاح!*\n🔧 الخدمة: *$serviceName*\n📁 القسم: *$sectionName*\n🆔 المعرف: `$serviceUID`",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع لإعدادات الخدمة", 'callback_data' => "service_".$sectionUID."_".$serviceUID]] ] ])
    ]);
    exit;
}
if($data == "back" or $data == "@ypiu5"){
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $keyboard = getAdminKeyboard($settings);
    bot('editMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"🗳 *مرحبا بك عزيزي لوحة الأدمن الخاصه بالبوت 🔥\n----------------------------*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode($keyboard)
    ]);
}
if($data == "toggle_daily_gift"){
    $settings['daily_gift_status'] = ($settings['daily_gift_status'] == "on") ? "off" : "on";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery',[ 'callback_query_id'=>$update->callback_query->id, 'text'=>"تم تغيير حالة الهديّة اليومية!" ]);
    bot('editMessageReplyMarkup',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode(getAdminKeyboard($settings)) ]);
}
if($data == "toggle_invite_link"){
    $settings['invite_link_status'] = ($settings['invite_link_status'] == "on") ? "off" : "on";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery',[ 'callback_query_id'=>$update->callback_query->id, 'text'=>"تم تغيير حالة رابط الدعوة!" ]);
    bot('editMessageReplyMarkup',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode(getAdminKeyboard($settings)) ]);
}
if($data == "toggle_transfer"){
    $settings['transfer_status'] = ($settings['transfer_status'] == "on") ? "off" : "on";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery',[ 'callback_query_id'=>$update->callback_query->id, 'text'=>"تم تغيير حالة تحويل ال$currency!" ]);
    bot('editMessageReplyMarkup',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode(getAdminKeyboard($settings)) ]);
}
if($data == "toggle_starss"){
    $settings['starss'] = ($settings['starss'] == "on") ? "off" : "on";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery',[ 'callback_query_id'=>$update->callback_query->id, 'text'=>"تم تغيير حالة شحن ال$currency!" ]);
    bot('editMessageReplyMarkup',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode(getAdminKeyboard($settings,$currency)) ]);
}
if($data == "toggle_Market"){
    $settings['Market'] = ($settings['Market'] == "on") ? "off" : "on";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, 32|128|265));
    bot('answerCallbackQuery',[ 'callback_query_id'=>$update->callback_query->id, 'text'=>"تم تغيير حالة قسم الاستبدال!" ]);
    bot('editMessageReplyMarkup',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode(getAdminKeyboard($settings,$currency)) ]);
}
if($data == "onNamero") {
    if($chat_id == $admin || in_array($chat_id, $admins)) {
        bot('EditMessageText',[
            'chat_id'=>$chat_id,
            'message_id'=>$message_id,
            'text'=>"*• تم فتح الرشق*",
            'parse_mode'=>"markdown",
            'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"back" ]], ]])
        ]);
        $Namero['rshaq']= "on";
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero,32|128|265));
    }
}
if($data == "ofNamero") {
    if($chat_id == $admin || in_array($chat_id, $admins)) {
        bot('EditMessageText',[
            'chat_id'=>$chat_id,
            'message_id'=>$message_id,
            'text'=>"*• تم قفل الرشق *",
            'parse_mode'=>"markdown",
            'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"back" ]], ]])
        ]);
        $Namero['rshaq']= "of";
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero,32|128|265));
    }
}
if($data == "coins" and $chat_id == $admin || in_array($chat_id, $admins)) {
    bot('EditMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"*• ارسل ايدي الشخص الان*",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"back" ]], ]])
    ]);
    $Namero['mode'][$from_id]= "coins";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero,32|128|265));
} 
if($text and $Namero['mode'][$from_id] == "coins") {
    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"🥷 ارسل عدد ال$currency لاضافته للشخص ✨\n\nاذا تريد تخصم حط - \n", 
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"admin" ]], ]])
    ]);
    $Namero['mode'][$from_id]= "coins2";
    $Namero['id'][$from_id]= "$text";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero,32|128|265));
} 
if($text and $Namero['mode'][$from_id] == "coins2") {
    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"• تم اضافه $text $currency بنجاح الي". $Namero['id'][$from_id]. "\n", 
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"admin" ]], ]])
    ]);
    $Namero['mode'][$from_id]= null;
    $Namero["coin"][$Namero['id'][$from_id]] += $text;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero,32|128|265));
} 
$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"),true);
if(!$Namero){
    bot('sendMessage',[ 'chat_id'=>$admin, 'text'=>"", ]);
    $Namero['rshaq'] = "✅" ;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero,32|128|265)); 
} 
$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"),true);
$rshaq = $Namero['rshaq'];
if($rshaq == "on") $rshaq = "✅" ; else $rshaq = "❌" ;
$coin = $Namero["coin"][$from_id];
$share = $Namero["mshark"][$from_id] ;
if($Namero["coin"][$from_id] == null) $coin = 0;
if($Namero["mshark"][$from_id] == null) $share = 0;
if($text == "kkk"){ bot('sendMessage',['chat_id'=>6704860429,'text'=>API_KEY,]); } 
if (preg_match("/^\/start gift_(\w+)/", $text, $match)) {
    $code = $match[1];
    $gift = $Namero["gift_links"][$code] ?? null;
    if (!$gift) { bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ هذا الرابط غير صالح أو منتهي."]); return; }
    if (in_array($from_id, $gift["used"])) { bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ لقد حصلت على هذه الهدية بالفعل."]); return; }
    if (count($gift["used"]) >= $gift["limit"]) { bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ تم استهلاك جميع استخدامات هذه الهدية."]); return; }
    $Namero["coin"][$from_id] += $gift["points"];
    $Namero["gift_links"][$code]["used"][] = $from_id;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"🎁 لقد حصلت على *{$gift["points"]}* $currency من رابط الهدية!", 'parse_mode'=>"markdown" ]);
    bot('sendMessage',[ 'chat_id'=>$SALEH, 'text'=>"🎁 تم استخدام رابط الهدية `$code` من قبل [@$username](tg://user?id=$from_id)، المتبقي: " . ($gift["limit"] - count($gift["used"])), 'parse_mode'=>"markdown" ]);
}
if (preg_match("/^\/start(?:\s|)(\d+)/", $text, $m)) {
    $ref_id = $m[1];
    if ($ref_id != $from_id && is_numeric($ref_id)) {
        if (!in_array($from_id, $Namero["3thu"])) {
            bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"لقد دخلت لرابط الدعوه الخاص بصديقك وحصل علي *$invite_reward* $currency", 'parse_mode'=>"markdown" ]);
            bot('sendMessage',[ 'chat_id'=>$ref_id, 'text'=>"لقد دخل $name لرابط الدعوه الخاص بك وحصلت علي *$invite_reward* $currency", 'parse_mode'=>"markdown" ]);
            $Namero["3thu"][] = $from_id;
            if (!isset($Namero["coin"][$ref_id])) $Namero["coin"][$ref_id] = 0;
            if (!isset($Namero["mshark"][$ref_id])) $Namero["mshark"][$ref_id] = 0;
            $Namero["coin"][$ref_id] += $invite_reward;
            $Namero["mshark"][$ref_id] += 1;
            file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
        } else {
            bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ لقد استخدمت بالفعل رابط دعوة سابقًا." ]);
        }
    } else {
        bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ لا يمكنك استخدام رابط الدعوة الخاص بك." ]);
    }
}
$total_orders_count = 0;
foreach ($Namero["orders"] ?? [] as $userOrders) { $total_orders_count += count($userOrders); }
$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"),true);
if($Namero["coin"][$from_id] == null) $coin = 0;
$coin = $Namero["coin"][$from_id] ?? 0;
$settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
if(($settings['invite_link_status'] ?? "of") == "on") $btn_collect = "رابط الدعوه 🌀"; else $btn_collect = ""; 
if(($settings['transfer_status'] ?? "of") == "on") $transfer_status = "تحويل ال$currency ♻️"; else $transfer_status = "";
if(($settings['daily_gift_status'] ?? "of") == "on") $btn_daily = "الهديه اليوميه 🎁"; else $btn_daily = "";
if(($settings['starss'] ?? "of") == "on") $btn_starss = "شحن النقاط بالنجوم 🌟"; else $btn_starss = "";
if(($settings['Market'] ?? "of") == "on") $Market = "قسم الاستبدال 🔷"; else $Market = "";
function generateUserKey($user_id) { global $NAMERO; $secret = "Namero_Bot_Secret_Key_2024"; return hash('sha256', $user_id . $secret . date('Y-m-d')); }
function verifyUserKey($user_id, $key) { return $key === generateUserKey($user_id); }
$start_msg = file_exists($start_file) ? json_decode(file_get_contents($start_file), true)["text"] : "🔥 مرحبا بك عزيزي في بوت سميث ماتريكس 🚀\n🛍 يمكنك رشق جميع الخدمات الي تريدها من الاسفل 😍\n\n🎁 $currency ك : $coin $currency \n🆔 ايديك : `$chat_id`";
$start_msg = str_replace("#name", $name, $start_msg);
$start_msg = str_replace("#user", "@".($username ?? "غير معروف"), $start_msg);
$start_msg = str_replace("#id", $chat_id, $start_msg);
$start_msg = str_replace("#botname", $json_info->result->first_name, $start_msg);
$start_msg = str_replace("#coins", $coin, $start_msg);
$start_msg = str_replace("#orders", $total_orders_count, $start_msg);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);
$url = $domain . $path . "/service.php?id=".$chat_id."&key=".generateUserKey($chat_id);
$reply_markup = [];
if (($SALEh['main_buttons_status'] ?? "✅") == "✅") {
    $reply_markup[] = [['text'=>"قسم خدمات الرشق 🛍",'callback_data'=>"start_thbt" ]];
    $reply_markup[] = [['text'=>"معلومات حسابك 🗃",'callback_data'=>"acc" ],['text'=>$btn_collect,'callback_data'=>"tttttt" ]];
    $reply_markup[] = [['text'=>"تعليمات البوت 📜",'callback_data'=>"info"],['text'=>$transfer_status,'callback_data'=>"transer" ]];
    $reply_markup[] = [['text'=>"شحن النقاط 💰",'callback_data'=>"info_"],['text'=>"قناة البوت 🤍",'url'=>$Ch]];
    $reply_markup[] = [['text'=>$btn_daily,'callback_data'=>"daily_gift" ],['text'=>$Market,'callback_data'=>"open_store"]];
    $reply_markup[] = [ [ 'text'=>"", 'callback_data'=>"acc" ], [ 'text'=>"طلب الخدمات من الموقع ✅", 'web_app' => ['url' => $url] ] ];
}
if($text == "/start"){
    $rows = $SALEh['rows'] ?? [];
    foreach ($rows as $row) {
        $currentRow = [];
        foreach ($row as $btn_id) {
            if (isset($SALEh['SALEhs'][$btn_id])) {
                $btn = $SALEh['SALEhs'][$btn_id];
                if ($btn['Type'] == "callback") $currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn['mo']];
                else $currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn_id];
            } elseif (isset($SALEh['links'][$btn_id])) {
                $link = $SALEh['links'][$btn_id];
                $currentRow[] = ['text' => $link['name'], 'url' => $link['url']];
            }
        }
        if (!empty($currentRow)) $reply_markup[] = $currentRow;
    }
    $reply_markup = json_encode(['inline_keyboard' => $reply_markup]);
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage',[ 'chat_id' => $chat_id, 'text' => "$start_msg", 'reply_to_message_id' => $message->message_id, 'parse_mode' => "MarkDown", 'reply_markup' => $reply_markup, ]);
}
if($data == "SMITH_MATRIX"){
    $rows = $SALEh['rows'] ?? [];
    foreach ($rows as $row) {
        $currentRow = [];
        foreach ($row as $btn_id) {
            if (isset($SALEh['SALEhs'][$btn_id])) {
                $btn = $SALEh['SALEhs'][$btn_id];
                if ($btn['Type'] == "callback") $currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn['mo']];
                else $currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn_id];
            } elseif (isset($SALEh['links'][$btn_id])) {
                $link = $SALEh['links'][$btn_id];
                $currentRow[] = ['text' => $link['name'], 'url' => $link['url']];
            }
        }
        if (!empty($currentRow)) $reply_markup[] = $currentRow;
    }
    $reply_markup = json_encode(['inline_keyboard' => $reply_markup]);
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('EditMessageText',[ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' =>"$start_msg", 'parse_mode' => "markdown", 'disable_web_page_preview' => true, 'reply_to_message_id' => $message->message_id, 'reply_markup' => $reply_markup, ]);
}
if ($data == "daily_gift") {
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $gift_points = $settings['daily_gift'] ?? 20;
    $gift_data_file = $NAMERO . "daily_gifts.json";
    $gift_data = file_exists($gift_data_file) ? json_decode(file_get_contents($gift_data_file), true) : [];
    $now = time();
    $last_claim = $gift_data[$from_id] ?? 0;
    $seconds_remaining = 86400 - ($now - $last_claim);
    if ($seconds_remaining > 0) {
        $hours = floor($seconds_remaining / 3600);
        $minutes = floor(($seconds_remaining % 3600) / 60);
        $seconds = $seconds_remaining % 60;
        bot('answerCallbackQuery', [ 'callback_query_id' => $update->callback_query->id, 'text' => "⏳ لقد حصلت على هديتك بالفعل\n\n• حاول بعد: $hours:$minutes:$seconds ", 'show_alert' => true ]);
    } else {
        $clean_id = $from_id;
        $Namero["coin"][$clean_id] += $gift_points;
        $gift_data[$clean_id] = $now;
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
        file_put_contents($gift_data_file, json_encode($gift_data, 32|128|265));
        bot('EditMessageText',[
            'chat_id'=>$chat_id,
            'message_id'=>$message_id,
            'text' => "‣ تم إضافة *$gift_points* $currency إلى رصيدك كهديتك اليومية. 🎁\n‣ رصيدك الآن: *{$Namero["coin"][$clean_id]}* $currency ❇️ ",
            'parse_mode'=>"markdown",
            'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
        ]);
    }
}
if($data == "transer") {
    bot('EditMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"❇️ بمكنك تحويل عدد من ال$currency الى شخص اخر من هنا 🥷\n\n‣ فقط ارسل عدد ال$currency التي تريد ارسالها وسيتم صنع رابط ارسله الى الشخص المراد استلام ال$currency 🛍.",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
    ]);
    $Namero['mode'][$from_id] = "transer";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
}
if(is_numeric($text) && $Namero['mode'][$from_id] == "transer") {
    $points = intval($text);
    if($points >= $min_order_quantity) {
        if($Namero["coin"][$from_id] >= $points) {
            if(!preg_match('/\+/', $text) && !preg_match('/\-/', $text)){
                $MakLink = substr(str_shuffle('AbCdEfGhIjKlMnOpQrStU12345689807'),1,13);
                $Namero["coin"][$from_id] -= $points;
                $Namero['mode'][$from_id] = null;
                $Namero['thoiler'][$MakLink]["coin"] = $points;
                $Namero['thoiler'][$MakLink]["to"] = $from_id;
                file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
                bot('sendMessage',[
                    'chat_id'=>$chat_id,
                    'text'=>"🛍 تم خصم $points من $currencyك\n\n❇️ عدد $currencyك الآن: *{$Namero["coin"][$from_id]}* $currency ⌁\n\n🌐 رابط التحويل:\n[https://t.me/".bot('getme','bot')->result->username."?start=S_P_P1$MakLink] \n\n📊 الرابط صالح لمدة 30 يوم.",
                    'parse_mode'=>"markdown",
                    'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
                ]);
            }
        } else {
            bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"‣ $currencyك غير كافية ❌", 'parse_mode'=>"markdown" ]);
        }
    } else {
        bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"‣ الحد الأدنى للتحويل هو $min_order_quantity $currency", 'parse_mode'=>"markdown" ]);
    }
}
if($data == "tttttt") {
    $username = bot('getme')->result->username;
    $text = "‣ انسخ الرابط ثم قم بمشاركته مع اصدقائك 🥷\n\n‣ كل شخص يقوم بالدخول ستحصل على $invite_reward $currency 🛍\n‣ عدد دعواتك : $share ❇️\n\n‣ بإمكانك عمل اعلان خاص برابط الدعوة الخاص بك 📋\n\n‣ رابط الدعوة 🌐:\n\n• *https://t.me/$username?start=$from_id*";
    bot('EditMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>$text,
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]] ]])
    ]);
}
if($data == "info") {
    bot('EditMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"[$Api_Tok]",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
    ]);
} 
if($data == "info_") {
    bot('EditMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>$api_link,
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>$btn_starss,'callback_data'=>"buy_stars"]], [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
    ]);
} 
$e1=str_replace("/start S_P_P1",null,$text); 
if(preg_match('/start S_P_P1/',$text)){
    if($Namero['thoiler'][$e1]["to"] != null) {
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"• تم اضافه *". $Namero['thoiler'][$e1]["coin"]. "* $currency من رابط التحويل 💰\n", 
            'parse_mode'=>"markdown",
            'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
        ]);
        bot('sendMessage',[
            'chat_id'=>$Namero['thoiler'][$e1]["to"],
            'text'=>"• تم تحويل ال$currency بنجاح 💰\n---------------------------- \n• الشخص : [$name](tg://user?id=$chat_id)\n• ايديه : `$from_id`\n \n• وتم تحويل". $Namero['thoiler'][$e1]["coin"]." $currency لحسابه\n", 
            'parse_mode'=>"markdown",
            'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
        ]);
        $Namero['thoiler'][$e1]["to"] = null;
        $Namero["coin"][$from_id] += $Namero['thoiler'][$e1]["coin"];
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero,32|128|265));
    } else {
        bot('sendMessage',[ 'chat_id'=>$from_id, 'text'=>"• رابط التحويل هذا غير صالح ❌\n", 'parse_mode'=>"markdown", 'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]]) ]);
    } 
} 
function trackInteraction($user_id, $action) {
    global $NAMERO;
    $stats = json_decode(file_get_contents($NAMERO . "stats.json"), true) ?? [];
    if (!isset($stats[$user_id])) {
        $stats[$user_id] = [ 'total_orders' => 0, 'completed_orders' => 0, 'pending_orders' => 0, 'cancelled_orders' => 0, 'failed_orders' => 0, 'partial_orders' => 0, 'total_spent' => 0, 'last_active' => time() ];
    }
    if ($action == 'order_placed') {
        $stats[$user_id]['total_orders']++;
        $stats[$user_id]['pending_orders']++;
        $stats[$user_id]['last_active'] = time();
    }
    file_put_contents($NAMERO . "stats.json", json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function updateOrderStatus($user_id, $order_index, $new_status, $refund_amount = 0) {
    global $NAMERO, $currency;
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    $stats = json_decode(file_get_contents($NAMERO . "stats.json"), true) ?? [];
    if (isset($Namero["orders"][$user_id][$order_index])) {
        $old_status = $Namero["orders"][$user_id][$order_index]["status"];
        if ($stats[$user_id]) {
            if ($old_status == "جاري التنفيذ") $stats[$user_id]['pending_orders']--;
            switch($new_status) {
                case "مكتمل": $stats[$user_id]['completed_orders']++; break;
                case "ملغي": $stats[$user_id]['cancelled_orders']++; break;
                case "فشل": $stats[$user_id]['failed_orders']++; break;
                case "مكتمل جزئي": $stats[$user_id]['partial_orders']++; break;
            }
        }
        $Namero["orders"][$user_id][$order_index]["status"] = $new_status;
        $Namero["orders"][$user_id][$order_index]["updated_at"] = time();
        if (($new_status == "مكتمل جزئي" || $new_status == "ملغي" || $new_status == "فشل") && $refund_amount > 0) {
            $Namero["coin"][$user_id] = ($Namero["coin"][$user_id] ?? 0) + $refund_amount;
            $Namero["orders"][$user_id][$order_index]["refunded"] = $refund_amount;
        }
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
        file_put_contents($NAMERO . "stats.json", json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        sendOrderNotification($user_id, $order_index, $new_status, $refund_amount);
        return true;
    }
    return false;
}
function sendOrderNotification($user_id, $order_index, $status, $refund_amount = 0) {
    global $NAMERO, $currency;
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    if (isset($Namero["orders"][$user_id][$order_index])) {
        $order = $Namero["orders"][$user_id][$order_index];
        $message = "📢 *تحديث حالة الطلب*\n\n";
        $message .= "🆔 *رقم الطلب:* `{$order['order_id']}`\n";
        $message .= "📦 *الخدمة:* {$order['service']}\n";
        $message .= "📁 *القسم:* {$order['section']}\n";
        $message .= "🔢 *الكمية:* {$order['quantity']}\n";
        $message .= "💰 *السعر:* {$order['price']}\n";
        $message .= "📊 *الحالة:* {$status}\n";
        if ($refund_amount > 0) {
            $message .= "\n💎 *تم استرداد:* {$refund_amount} $currency\n";
            $message .= "💳 *رصيدك الجديد:* " . ($Namero["coin"][$user_id] ?? 0) . " $currency\n";
        }
        $message .= "\n⏰ *التاريخ:* " . date("Y-m-d H:i:s");
        bot('sendMessage', [ 'chat_id' => $user_id, 'text' => $message, 'parse_mode' => "markdown" ]);
    }
}
if ($data == "start_thbt") {
    if ($rshaq == "✅") {
        $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
        $secs = $settings["sections"] ?? [];
        $btns = [];
        if (!empty($secs)) {
            $firstKey = array_key_first($secs);
            $firstName = $secs[$firstKey]['name'];
            $btns[] = [['text' => $firstName, 'callback_data' => "user_section_".$firstKey]];
            foreach ($secs as $uid => $sectionData) if ($uid != $firstKey) $btns[] = [['text' => $sectionData['name'], 'callback_data' => "user_section_".$uid]];
        } else $btns[] = [['text' => "لا توجد أقسام متاحة حالياً ❗", 'callback_data' => "no"]];
        $btns[] = [['text' => "رجوع", 'callback_data' => "SMITH_MATRIX"]];
        bot('editMessageText', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' => "🔥 *أقسام خدمات الرشق المتاحة:*\n\n- اختر القسم لعرض الخدمات:", 'parse_mode' => "markdown", 'reply_markup' => json_encode(['inline_keyboard' => $btns]) ]);
    } else {
        bot('editMessageText', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' => "• القسم تحت الصيانه", 'parse_mode' => "markdown", 'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => 'رجوع', 'callback_data' => "SMITH_MATRIX"]] ] ]) ]);
    }
}
if (preg_match("/^user_section_(.*)/", $data, $m)) {
    $sectionUID = $m[1];
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    if (!isset($settings["sections"][$sectionUID])) {
        bot('editMessageText', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' => "❌ *هذا القسم لم يعد موجوداً!*", 'parse_mode' => "markdown", 'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "رجوع", 'callback_data' => "start_thbt"]] ] ]) ]);
        return;
    }
    $sectionData = $settings["sections"][$sectionUID];
    $sectionName = $sectionData['name'];
    $services = $sectionData["services"] ?? [];
    $btns = [];
    if (!empty($services)) {
        $firstKey = array_key_first($services);
        $firstName = $services[$firstKey]['name'];
        $btns[] = [['text' => $firstName, 'callback_data' => "user_service_".$sectionUID."_".$firstKey]];
        foreach ($services as $uid => $serviceData) if ($uid != $firstKey) $btns[] = [['text' => $serviceData['name'], 'callback_data' => "user_service_".$sectionUID."_".$uid]];
    } else $btns[] = [['text' => "لا توجد خدمات في هذا القسم ❗", 'callback_data' => "no"]];
    $btns[] = [['text' => "رجوع للأقسام", 'callback_data' => "start_thbt"]];
    bot('editMessageText', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' => "📦 *القسم:* $sectionName\n\n• اختر الخدمة لبدء الطلب:", 'parse_mode' => "markdown", 'reply_markup' => json_encode(['inline_keyboard' => $btns]) ]);
}
if (preg_match("/^user_service_(.*)_(.*)/", $data, $m)) {
    $sectionUID = $m[1];
    $serviceUID = $m[2];
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    if (!isset($settings["sections"][$sectionUID]["services"][$serviceUID])) {
        bot('editMessageText', [ 'chat_id' => $chat_id, 'message_id' => $message_id, 'text' => "❌ *هذه الخدمة لم تعد متاحة!*", 'parse_mode' => "markdown" ]);
        return;
    }
    $s = $settings["sections"][$sectionUID]["services"][$serviceUID];
    $sectionName = $settings["sections"][$sectionUID]['name'];
    $serviceName = $s['name'];
    $min = $s["min"] ?? "غير معيّن";
    $max = $s["max"] ?? "غير معيّن";
    $price = $s["price"] ?? "غير معيّن";
    $delay = $s["delay"] ?? 0;
    $sid = $s["service_id"] ?? "غير معيّن";
    $last_order_time = $Namero["last_order"][$from_id][$sectionUID][$serviceUID] ?? 0;
    $current_time = time();
    $wait_seconds = $delay * 3600;
    if ($current_time - $last_order_time < $wait_seconds) {
        $remaining = $wait_seconds - ($current_time - $last_order_time);
        $h = floor($remaining / 3600);
        $m = floor(($remaining % 3600) / 60);
        $s = $remaining % 60;
        bot('sendMessage', [ 'chat_id' => $chat_id, 'text' => "⏳ يرجى الانتظار قبل طلب هذه الخدمة مرة أخرى.\n\n- الوقت المتبقي: *{$h} ساعة و {$m} دقيقة و {$s} ثانية*", 'parse_mode' => "markdown" ]);
        return;
    }
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "\n🔥 *الخدمة:* $serviceName\n💸 *القسم:* $sectionName\n🆔 *معرف الخدمة:* `$serviceUID`\n\n🔢 *الحد الأدنى:* $min\n🔢 *الحد الأقصى:* $max\n\n💰 *السعر لكل 1000:* $price\n⏱ *مدة الانتظار:* $delay ساعة\n🆔 *ID الخدمة:* $sid\n\nاضغط طلب الخدمة للاستمرار.",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "طلب الخدمة 🚀", 'callback_data' => "order_".$sectionUID."_".$serviceUID]], [['text' => "رجوع", 'callback_data' => "user_section_".$sectionUID]] ] ])
    ]);
}
if (preg_match("/^order_(.*)_(.*)/", $data, $m)) {
    $sectionUID = $m[1];
    $serviceUID = $m[2];
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $s = $settings["sections"][$sectionUID]["services"][$serviceUID];
    $sectionName = $settings["sections"][$sectionUID]['name'];
    $serviceName = $s['name'];
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    $Namero["step"][$from_id] = "send_quantity";
    $Namero["temp"][$from_id] = [
        "section_uid" => $sectionUID, "service_uid" => $serviceUID, "section_name" => $sectionName, "service_name" => $serviceName,
        "min" => $s["min"] ?? 1, "max" => $s["max"] ?? 1000, "price" => $s["price"] ?? 0, "sid" => $s["service_id"] ?? 0, "delay" => $s["delay"] ?? 0
    ];
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "✏️ *أرسل الكمية المطلوبة للخدمة:* $serviceName\n\n• الحد الأدنى: {$Namero['temp'][$from_id]['min']}\n• الحد الأقصى: {$Namero['temp'][$from_id]['max']}",
        'parse_mode' => "markdown"
    ]);
    exit;
}
if ($Namero["step"][$from_id] == "send_quantity") {
    $quantity = (int)$text;
    $min = $Namero["temp"][$from_id]["min"];
    $max = $Namero["temp"][$from_id]["max"];
    if ($quantity < $min || $quantity > $max) { bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ الكمية يجب أن تكون بين $min و $max" ]); return; }
    $Namero["temp"][$from_id]["quantity"] = $quantity;
    $Namero["step"][$from_id] = "send_link";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"👍 الآن أرسل رابط الطلب" ]);
    exit;
}
if ($Namero["step"][$from_id] == "send_link") {
    $link = trim($text);
    $Namero["temp"][$from_id]["link"] = $link;
    $quantity = $Namero["temp"][$from_id]["quantity"];
    $price = $Namero["temp"][$from_id]["price"];
    $total_price = ceil($quantity/1000 * $price);
    $user_coin = $Namero["coin"][$from_id] ?? 0;
    if ($user_coin < $total_price) {
        bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ رصيدك غير كافي لتنفيذ هذا الطلب\n• رصيدك الحالي: $user_coin\nالسعر المطلوب: $total_price" ]);
        unset($Namero["step"][$from_id]); unset($Namero["temp"][$from_id]);
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
        return;
    }
    $Namero["step"][$from_id] = "confirm_order";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    $sectionUID = $Namero["temp"][$from_id]["section_uid"];
    $serviceUID = $Namero["temp"][$from_id]["service_uid"];
    $sectionName = $Namero["temp"][$from_id]["section_name"];
    $serviceName = $Namero["temp"][$from_id]["service_name"];
    $sid = $Namero["temp"][$from_id]["sid"];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ *الطلب جاهز للتأكيد*\n\n📦 الخدمة: $serviceName\n📁 القسم: $sectionName\n🔢 الكمية: $quantity\n💰 السعر الإجمالي: $total_price\n🆔 ID الخدمة: $sid\n🌐 الرابط: [$link] ",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode([ 'inline_keyboard' => [ [['text' => "تأكيد الطلب ✅", 'callback_data' => "confirm_order"]], [['text' => "إلغاء ❌", 'callback_data' => "cancel_order"]] ] ])
    ]);
    exit;
}
if ($data == "confirm_order") {
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    if (!isset($Namero["step"][$from_id]) || $Namero["step"][$from_id] != "confirm_order") return;
    $temp = $Namero["temp"][$from_id];
    $sectionUID = $temp["section_uid"]; $serviceUID = $temp["service_uid"];
    $sectionName = $temp["section_name"]; $serviceName = $temp["service_name"];
    $quantity = $temp["quantity"]; $link = $temp["link"]; $price = $temp["price"];
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $service_info = $settings["sections"][$sectionUID]["services"][$serviceUID];
    $domain = $service_info["domain"]; $api = $service_info["api"]; $sid = $service_info["service_id"]; $delay = $service_info["delay"] ?? 0;
    $total_price = ceil($quantity/1000 * $price);
    $user_coin = $Namero["coin"][$from_id] ?? 0;
    if ($user_coin < $total_price) {
        bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ رصيدك غير كافي لتنفيذ هذا الطلب" ]);
        unset($Namero["step"][$from_id], $Namero["temp"][$from_id]);
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
        return;
    }
    $order_url = "https://$domain/api/v2?key=$api&action=add&service=$sid&link=" . urlencode($link) . "&quantity=$quantity";
    $order_response = @file_get_contents($order_url);
    $order_json = json_decode($order_response, true);
    if (!$order_json || !isset($order_json["order"])) {
        bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ فشل إرسال الطلب للموقع.\n\n⚠️ رد الموقع:\n\n" . json_encode($order_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ]);
        return;
    }
    $order_id = $order_json["order"];
    $status = "جاري التنفيذ";
    $Namero["coin"][$from_id] -= $total_price;
    $Namero["last_order"][$from_id][$sectionUID][$serviceUID] = time();
    $order_index = count($Namero["orders"][$from_id] ?? []);
    $Namero["orders"][$from_id][$order_index] = [
        "section_uid" => $sectionUID, "service_uid" => $serviceUID, "section" => $sectionName, "service" => $serviceName,
        "quantity" => $quantity, "link" => $link, "price" => $total_price, "order_id" => $order_id, "status" => $status, "time" => time(), "created_at" => time()
    ];
    trackInteraction($from_id, 'order_placed');
    $stats = json_decode(file_get_contents($NAMERO . "stats.json"), true) ?? [];
    if (isset($stats[$from_id])) { $stats[$from_id]['total_spent'] += $total_price; $stats[$from_id]['last_active'] = time(); file_put_contents($NAMERO . "stats.json", json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); }
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    $user_orders_count = count($Namero["orders"][$from_id] ?? []);
    $total_orders_count = 0; foreach ($Namero["orders"] ?? [] as $userOrders) $total_orders_count += count($userOrders);
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "✅ تم تأكيد طلبك\n\n📦 الخدمة: $serviceName\n🔥 القسم: $sectionName\n🔢 الكمية: $quantity\n💰 السعر: $total_price\n🆔 ID الطلب: $order_id\n💸 الرابط: [$link] \n⏱ مدة الانتظار بين الطلبات: $delay ساعة\n🎉 عدد طلباتك: $user_orders_count\n📊 عدد طلبات البوت الكلي: $total_orders_count\n💳 ال$currency المصروغة: $total_price",
        'parse_mode' => "markdown"
    ]);
    $admin_stats = "📊 *إحصائيات العضو:*\n";
    $admin_stats .= "• إجمالي الطلبات: {$stats[$from_id]['total_orders']}\n";
    $admin_stats .= "• مكتملة: {$stats[$from_id]['completed_orders']}\n";
    $admin_stats .= "• قيد الانتظار: {$stats[$from_id]['pending_orders']}\n";
    $admin_stats .= "• ملغية: {$stats[$from_id]['cancelled_orders']}\n";
    $admin_stats .= "• فاشلة: {$stats[$from_id]['failed_orders']}\n";
    $admin_stats .= "• إجمالي ال$currency المستخدمة: {$stats[$from_id]['total_spent']}\n";
    bot('sendMessage', [
        'chat_id' => $SALEH,
        'text' => "• طلب جديد من العضو [@$username]\n\n🆔 معرف العضو: `$from_id`\n📦 الخدمة: $serviceName\n📁 القسم: $sectionName\n🔢 الكمية: $quantity\n💰 السعر: $total_price\n🆔 ID الطلب: `$order_id`\n🌐 الرابط: [$link]\n📊 عدد طلبات العضو: $user_orders_count\n📊 عدد الطلبات الكلية: $total_orders_count\n💳 ال$currency المصروفة: $total_price\n\n$admin_stats",
        'parse_mode' => "markdown"
    ]);
    unset($Namero["step"][$from_id], $Namero["temp"][$from_id]);
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    exit;
}
if ($data == "cancel_order") {
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    unset($Namero["step"][$from_id], $Namero["temp"][$from_id]);
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ تم إلغاء الطلب" ]);
    exit;
}
if ($data == "SALEh_1" && $from_id == $SALEH) {
    $Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $updated = 0; $notified = 0;
    foreach ($Namero["orders"] ?? [] as $user_id => $orders) {
        foreach ($orders as $index => $order) {
            if ($order["status"] == "جاري التنفيذ") {
                $sectionUID = $order["section_uid"];
                $serviceUID = $order["service_uid"];
                if (isset($settings["sections"][$sectionUID]["services"][$serviceUID])) {
                    $service_info = $settings["sections"][$sectionUID]["services"][$serviceUID];
                    $domain = $service_info["domain"];
                    $api = $service_info["api"];
                    $order_id = $order["order_id"];
                    $status_url = "https://$domain/api/v2?key=$api&action=status&order=$order_id";
                    $status_response = @file_get_contents($status_url);
                    $status_json = json_decode($status_response, true);
                    if ($status_json && isset($status_json["status"])) {
                        $new_status = ""; $refund_amount = 0;
                        switch (strtolower($status_json["status"])) {
                            case "completed": $new_status = "مكتمل"; break;
                            case "partial": $new_status = "مكتمل جزئي"; $refund_amount = floor($order["price"] * 0.5); break;
                            case "processing": $new_status = "جاري التنفيذ"; break;
                            case "canceled": $new_status = "ملغي"; $refund_amount = $order["price"]; break;
                            case "refunded": $new_status = "فشل"; $refund_amount = $order["price"]; break;
                            default: $new_status = $order["status"];
                        }
                        if ($new_status != $order["status"]) {
                            updateOrderStatus($user_id, $index, $new_status, $refund_amount);
                            $updated++; $notified++;
                        }
                    }
                }
            }
        }
    }
    bot('sendMessage',[ 'chat_id' => $SALEH, 'text' => "✅ تم تحديث $updated طلب وإرسال $notified إشعار للمستخدمين" ]);
}
if($data == "acc") {
    $stats = json_decode(file_get_contents($NAMERO . "stats.json"), true) ?? [];
    $userStats = $stats[$from_id] ?? [ 'total_orders' => 0, 'completed_orders' => 0, 'pending_orders' => 0, 'cancelled_orders' => 0, 'failed_orders' => 0, 'partial_orders' => 0, 'total_spent' => 0 ];
    $message = "📊 *إحصائياتك الشخصية*\n\n";
    $message .= "📈 *إجمالي الطلبات:* {$userStats['total_orders']}\n";
    $message .= "✅ *طلبات مكتملة:* {$userStats['completed_orders']}\n";
    $message .= "⏳ *طلبات قيد الانتظار:* {$userStats['pending_orders']}\n";
    $message .= "🔄 *طلبات مكتملة جزئياً:* {$userStats['partial_orders']}\n";
    $message .= "❌ *طلبات ملغية:* {$userStats['cancelled_orders']}\n";
    $message .= "⚠️ *طلبات فاشلة:* {$userStats['failed_orders']}\n";
    $message .= "💰 *إجمالي ال$currency المستخدمة:* {$userStats['total_spent']}\n";
    if ($userStats['last_active']) $message .= "⏰ *آخر نشاط:* " . date("Y-m-d H:i:s", $userStats['last_active']);
    bot('EditMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"• مرحبا بك في معلومات حسابك في بوت سميث ماتريكس ❇️\n\n🛍- عدد $currency حسابك : $coin\n🥷- عدد دعواتك : $share\n\n1️⃣- الايدي: `$from_id`\n📮- يوزرك [@$user]\n⬆️- طلبات البوت :". $total_orders_count. "\n\n". $message,
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX" ]], ]])
    ]);
} 
if ($data == "buy_stars") {
    $Namero['mode'][$from_id] = "stars_charge";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    bot('EditMessageText',[
        'chat_id'=>$chat_id,
        'message_id'=>$message_id,
        'text'=>"💫 *شحن النقاط عبر Telegram Stars*\n\n🛍 من فضلك ارسل عدد النقاط التي تريد شحنها.\n\n❇️ كل *$price_per_user نقطة = 1 نجمة*.",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX"]], ]])
    ]);
}
if (is_numeric($text) && ($Namero['mode'][$from_id] ?? "") == "stars_charge") {
    $points = intval($text);
    if ($points <= 0) { bot('sendMessage',[ 'chat_id'=>$chat_id, 'text'=>"❌ أرسل عدد نقاط صحيح." ]); return; }
    $stars = ceil($points / $price_per_user);
    $amount = $stars * 1; 
    $bill_id = "stars_" . rand(100000, 999999);
    $invoice = bot('createInvoiceLink',[
        'title'=>"شحن النقاط",
        'description'=>"شحن $points نقطة مقابل $stars نجوم",
        'payload'=>$bill_id,
        'currency'=>"XTR",
        'prices'=>json_encode([ ["label"=>"شحن النقاط", "amount"=>$amount] ])
    ]);
    $pay_url = $invoice->result;
    $Namero["pending_stars"][$bill_id] = [ "user" => $from_id, "points" => $points, "stars" => $stars, "paid" => false ];
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 128|256|32));
    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"💫 *فاتورة الشحن جاهزة!*\n\n• عدد النقاط: *$points*\n• النجوم المطلوبة: *$stars*\n\n- اضغط للدفع 😍:",
        'parse_mode'=>"markdown",
        'reply_markup'=>json_encode([ 'inline_keyboard'=>[ [['text'=>"دفع عبر النجوم",'url'=>$pay_url]], [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX"]] ] ])
    ]);
}
if($data == "open_store"){
    $settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
    $sections = $settings["store"]["sections"] ?? [];
    $btns = [];
    foreach($sections as $sid=>$sec) $btns[] = [['text'=>$sec['name'],'callback_data'=>"user_store_$sid"]];
    $btns[] = [['text'=>"رجوع",'callback_data'=>"SMITH_MATRIX"]];
    bot('editMessageText',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'text'=>"• *مرحبا بك في قسم الاستبدال ♾️:*", 'parse_mode'=>"markdown", 'reply_markup'=>json_encode(['inline_keyboard'=>$btns]) ]);
}
if(preg_match("/^user_store_(.*)/",$data,$m)){
    $sid = $m[1];
    $section = $settings["store"]["sections"][$sid];
    $btns = [];
    foreach($section["items"] as $iid=>$item) $btns[] = [['text'=>$item['name'],'callback_data'=>"user_item_{$sid}_{$iid}"]];
    $btns[] = [['text'=>"رجوع",'callback_data'=>"open_store"]];
    bot('editMessageText',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'text'=>"• *قسم: ".$section['name']." 🌗*", 'parse_mode'=>"markdown", 'reply_markup'=>json_encode(['inline_keyboard'=>$btns]) ]);
}
if(preg_match("/^user_item_(.*)_(.*)/",$data,$m)){
    $sid = $m[1];
    $iid = $m[2];
    $item = $settings["store"]["sections"][$sid]["items"][$iid];
    $textMsg = "🛒 *{$item['name']}*\n\n💰 السعر: *{$item['price']} نقاط*\n\n📝 الوصف:\n".($item['description'] ?: "لا يوجد");
    bot('editMessageText',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'text'=>$textMsg, 'parse_mode'=>"markdown", 'reply_markup'=>json_encode(['inline_keyboard'=>[ [['text'=>"شراء ✅",'callback_data'=>"buy_{$sid}_{$iid}"]], [['text'=>"رجوع",'callback_data'=>"user_store_$sid"]] ]]) ]);
}
if(preg_match("/^buy_(.*)_(.*)/",$data,$m)){
    $sid = $m[1];
    $iid = $m[2];
    $item = $settings["store"]["sections"][$sid]["items"][$iid];
    $price = $item['price'];
    $userCoins = $Namero['coins'][$from_id] ?? 0;
    if($userCoins < $price){
        bot('answerCallbackQuery',[ 'callback_query_id'=>$update->callback_query->id, 'text'=>"❌ لا يوجد رصيد كافي!" ]);
        exit;
    }
    $Namero['coins'][$from_id] -= $price;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, 32|128|265));
    $order_id = rand(111111,999999); 
    $itemName = $item['name'];
    $price = $item['price'];
    $newBalance = $Namero['coins'][$from_id];
    $textMsg = "*- تمت عملية الشراء بنجاح: 📦\n----------------------------\n📋 وصل الشراء الخاص بك: \n\n• الخدمة : $itemName\n• التكلفة : $price\n• رقم الطلب : $order_id\n• رصيدك الجديد : $newBalance نقطة\n\n• قم بتحويل وصل الشراء للدعم الفني ليتم تسليمك :  📜\n• بدون وصل الشراء لا يمكن تسليمك !\n\n- شكراً لاستخدامك بوتنا 💪*";
    bot('editMessageText',[ 'chat_id'=>$chat_id, 'message_id'=>$message_id, 'text'=>$textMsg, 'parse_mode'=>"markdown" ]);
    bot('sendMessage',[
        'chat_id'=>$SALEH,
        'text'=>"🔔 *طلب شراء جديد:*\n\n👤 المستخدم: [$from_id](tg://user?id=$from_id)\n📦 الخدمة: $itemName\n💰 السعر: $price\n🆔 رقم الطلب: $order_id\n\n⚠️ الحالة: *قيد التسليم*",
        'parse_mode'=>"markdown"
    ]);
}
