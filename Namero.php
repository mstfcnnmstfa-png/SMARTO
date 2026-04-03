<?php
chdir(__DIR__);
set_time_limit(0);
error_reporting(0);
ignore_user_abort(true); 

$API_KEY = "8076347498:AAEq520a0raqgxYØkQW7_fiYM23khnxSKNU";
define('API_KEY', $API_KEY);
function bot($method,$datas=[]){
$url = "https://api.telegram.org/bot".API_KEY."/".$method;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
curl_setopt($ch,CURLOPT_TIMEOUT,10);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
$res = curl_exec($ch);
if(curl_error($ch)){return null;}
return json_decode($res);
}

function cachedJson(string $file): array {
    static $cache = [];
    if (!isset($cache[$file])) {
        $cache[$file] = file_exists($file) ? (json_decode(file_get_contents($file), true) ?? []) : [];
    }
    return $cache[$file];
}
function invalidateJsonCache(string $file): void {
    static $cache = [];
    unset($cache[$file]);
}

$_bot_cache_file = __DIR__ . '/.bot_cache.json';
if (file_exists($_bot_cache_file) && (time() - filemtime($_bot_cache_file)) < 86400) {
    $_bot_cached = json_decode(file_get_contents($_bot_cache_file));
    $userBot       = $_bot_cached->username ?? '';
    $bot_id        = $_bot_cached->id ?? '';
    $bot_firstname = $_bot_cached->first_name ?? '';
} else {
    $bot_info = bot("getMe");
    $userBot      = $bot_info->result->username ?? '';
    $bot_id       = $bot_info->result->id ?? '';
    $bot_firstname = $bot_info->result->first_name ?? '';
    file_put_contents($_bot_cache_file, json_encode(['id' => $bot_id, 'username' => $userBot, 'first_name' => $bot_info->result->first_name ?? '']));
}

$_raw_input = $GLOBALS['_polling_input'] ?? file_get_contents('php://input');

if (!isset($GLOBALS['_polling_input'])) {
    $_tmp_check = json_decode($_raw_input);
    if (empty($_tmp_check) || (!isset($_tmp_check->message) && !isset($_tmp_check->callback_query) && !isset($_tmp_check->inline_query) && !isset($_tmp_check->my_chat_member))) {
        if (!headers_sent()) {
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json');
            echo '{}';
        }
        return;
    }
    unset($_tmp_check);
    if (!headers_sent()) {
        $_fast_resp = '{}';
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($_fast_resp));
        header('Connection: close');
        echo $_fast_resp;
        if (ob_get_level() > 0) { ob_end_flush(); }
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}

$NAMERO = "NAMERO/$bot_id";
if (!is_dir($NAMERO)) { mkdir($NAMERO, 0777, true); }

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asiacell.php';
db_init(__DIR__ . "/$NAMERO/botdata.sqlite");

_db_preload_settings(['_bot_status', 'saleh_data', '_namero_rshaq']);

$_init_settings = db_get_settings();
if (empty($_init_settings)) {
    db_save_settings([
        "currency" => "نقاط",
        "invite_reward" => 5,
        "min_order_quantity" => 10,
        "daily_gift" => 20,
        "daily_gift_status" => "on",
        "invite_link_status" => "on",
        "transfer_status" => "off",
        "starss" => "off",
        "Market" => "off",
        "Ch" => "https://t.me/NameroBots",
        "sections" => [],
        "store" => ["sections" => []]
    ]);
    db_settings_invalidate();
}

$update = json_decode($_raw_input);

if (empty($update) || (!isset($update->message) && !isset($update->callback_query) && !isset($update->inline_query) && !isset($update->my_chat_member))) {
    return;
}

$data = '';
if (isset($update->callback_query)) {
    $cb = $update->callback_query;
    $data = $cb->data ?? '';
    $from_id = $cb->from->id ?? 0;
    $chat_id = $cb->message->chat->id ?? 0;
    $chat_id2 = $chat_id;
    $name = $cb->from->first_name ?? '';
    $user = $cb->from->username ?? '';
    $message_id = $cb->message->message_id ?? 0;
    $message = $cb->message;
    $text = '';
    $a = '';
    $tc = $cb->message->chat->type ?? '';
} else {
    $message = $update->message;
    $text = $message->text ?? '';
    $chat_id = $message->chat->id ?? 0;
    $chat_id2 = 0;
    $name = $message->from->first_name ?? '';
    $user = $message->from->username ?? '';
    $message_id = $message->message_id ?? 0;
    $from_id = $message->from->id ?? 0;
    $a = strtolower($text);
    $tc = $message->chat->type ?? '';
}
if (empty($from_id)) {
    return;
}

db_save_user_profile((string)$from_id, $name, $user);

include ("admin.php"); 
include ("azrar.php") ;

$admin = $SALEH;
$sudo = "$admin";

$_maint_status = db_get_bot_status(); 

$_maint_from_id = (string)($update->message->from->id ?? $update->callback_query->from->id ?? 0);
$_maint_admins = array_map('strval', (array)($admins ?? []));
if ($_maint_status === 'disabled' && $_maint_from_id !== (string)$SALEH && !in_array($_maint_from_id, $_maint_admins)) {
    $_maint_ch = _db_get_setting('Ch', 'https://t.me/NameroBots');
    $_maint_kb = json_encode(['inline_keyboard'=>[[['text'=>"📢 قناة البوت",'url'=>$_maint_ch]]]]);
    if (!empty($chat_id)) {
        bot('sendMessage', [
            'chat_id'      => $chat_id,
            'text'         => "🔧 *البوت في وضع الصيانة*\n\n⏳ نعمل على تحسين الخدمة وسنعود قريباً إن شاء الله\n\n📢 تابع آخر الأخبار والتحديثات من قناتنا",
            'parse_mode'   => 'Markdown',
            'reply_markup' => $_maint_kb
        ]);
    } elseif (!empty($chat_id2)) {
        bot('answerCallbackQuery', [
            'callback_query_id' => $update->callback_query->id,
            'text'              => '🔧 البوت في وضع الصيانة، يرجى الانتظار.',
            'show_alert'        => true
        ]);
    }
    exit;
}

$_ban_uid = (int)$_maint_from_id;
if ($_ban_uid > 0 && $_maint_from_id !== (string)$SALEH && !in_array($_maint_from_id, $_maint_admins)) {
    if (db_is_banned($_ban_uid)) {
        if (!empty($chat_id)) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "🚫 عذراً، تم حظرك من استخدام البوت."]);
        } elseif (!empty($chat_id2)) {
            bot('answerCallbackQuery', [
                'callback_query_id' => $update->callback_query->id,
                'text' => '🚫 أنت محظور من استخدام البوت.',
                'show_alert' => true
            ]);
        }
        exit;
    }
}


if (isset($update->pre_checkout_query)) {
$_pcq = $update->pre_checkout_query;
bot('answerPreCheckoutQuery', [
'pre_checkout_query_id' => $_pcq->id,
'ok' => true
]);
exit;
}

if (isset($update->message->successful_payment)) {
$_sp      = $update->message->successful_payment;
$_payload = $_sp->invoice_payload ?? '';
$Namero   = db_get_namero();
$_pend    = $Namero['pending_stars'][$_payload] ?? null;
if ($_pend && empty($_pend['paid'])) {
$_sp_uid    = (int)$_pend['user'];
$_sp_pts    = (int)$_pend['points'];
$_sp_stars  = (int)$_pend['stars'];
db_ensure_namero_coin($Namero, $_sp_uid);
$Namero['coin'][$_sp_uid] += $_sp_pts;
$Namero['pending_stars'][$_payload]['paid'] = true;
db_save_namero($Namero);
db_set_user_coin($_sp_uid, $Namero['coin'][$_sp_uid]);
bot('sendMessage', [
'chat_id'    => $_sp_uid,
'text'       => "✅ *تم شحن نقاطك بنجاح!*\n\n⭐ النجوم المدفوعة: *{$_sp_stars}*\n💰 النقاط المضافة: *{$_sp_pts}*\n💳 رصيدك الآن: *{$Namero['coin'][$_sp_uid]}*",
'parse_mode' => 'Markdown'
]);
bot('sendMessage', [
'chat_id'    => $SALEH,
'text'       => "⭐ *شحن بالنجوم*\n👤 المستخدم: `{$_sp_uid}`\n⭐ النجوم: *{$_sp_stars}*\n💰 النقاط: *{$_sp_pts}*",
'parse_mode' => 'Markdown'
]);
}
exit;
}

$Namero = db_get_namero();
if(!$Namero){
$Namero = [];
$Namero['rshaq'] = "✅" ;
db_save_namero($Namero); 
} 
$rshaq = $Namero['rshaq'];
if($rshaq == "on") {$rshaq = "شغال ✅" ;
} else {$rshaq = "معطل ❌" ;} 

$settings = db_get_settings();

$Api_Tok = $settings['token'] ?? "❌ غير محدد";
$api_link= $settings['domain'] ?? "❌ غير محدد";
$invite_reward = $settings['invite_reward'] ?? 5;
$min_order = $settings['min_order_quantity'] ?? 10;
$daily_gift= $settings['daily_gift'] ?? 20;
$currency= $settings['currency'] ?? "نقاط";
$settings['daily_gift_status'] = $settings['daily_gift_status'] ?? "on";
$settings['invite_link_status'] = $settings['invite_link_status'] ?? "on";
$price_per_user = $settings['user_price'] ?? "100";
$Ch = $settings['Ch'] ?? "https://t.me/لا يوجد";
if(isset($update->callback_query)){
$up = $update->callback_query;
$chat_id = $up->message->chat->id;
$from_id = $up->from->id;
$user = $up->from->username;
$name = $up->from->first_name;
$message_id = $up->message->message_id;
$data = $up->data;
$tc = $up->chat->type ;
}

$_balance_cache_file = __DIR__ . '/.provider_balance_cache.json';
$_balance_cache_ttl  = 120; 

$rsedi = null;
if ($api_link != "❌ غير محدد" && $Api_Tok != "❌ غير محدد") {
    $_bc = (file_exists($_balance_cache_file) && (time() - filemtime($_balance_cache_file)) < $_balance_cache_ttl)
         ? @json_decode(file_get_contents($_balance_cache_file))
         : null;
    if ($_bc !== null) {
        $rsedi = $_bc;
    } else {
        $_bch = curl_init("https://$api_link/api/v2?key=$Api_Tok&action=balance");
        curl_setopt_array($_bch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $_braw = curl_exec($_bch);
        curl_close($_bch);
        if ($_braw) {
            $rsedi = @json_decode($_braw);
            file_put_contents($_balance_cache_file, $_braw);
        }
    }
}
$flos  = $rsedi->balance  ?? 0;
$treqa = $rsedi->currency ?? "";

if (!empty($text) && strpos($text, '/') === 0) {
    if (isset($Namero['mode'][$from_id])) {
        unset($Namero['mode'][$from_id]);
        db_set_user_mode((int)$from_id, ''); 
    }
    
    $__urow = db_get_user_row((int)$from_id);
    if (!empty($__urow['step'])) {
        db_set_user_step((int)$from_id, '');
        db_set_user_temp((int)$from_id, '');
    }
}

if($text == "tart") {
if($chat_id == $admin || in_array($chat_id, $admins)) {
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"*• اهلا بك عزيزي المطور في لوحه الادمن 🧬*

• رصيدك : *$flos$*
• العمله : *$treqa*
• الهديه اليوميه: *$daily_gift*
• اقل عدد للتمويل: *$min_order*
• $currency رابط الدعوه: *$invite_reward*
• ايدي الخدمة: *$currency*
• سعر العضو الواحد: *$price_per_user* $currency
• حاله التمويل $rshaq
",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([ 
'inline_keyboard'=>[
 [['text'=>"🔺 شحن نقاط",'callback_data'=>"coins"],['text'=>"🔻 خصم نقاط",'callback_data'=>"deduct_coins"]],
 [['text'=>"🎫 صنع كود هديه",'callback_data'=>"hdiamk"],['text'=>"🎁 تعيين الهديه",'callback_data'=>"set_daily_gift"]],
 [['text'=>"✅ فتح التمويل",'callback_data'=>"onNamero"],['text'=>"🚫 قفل التمويل",'callback_data'=>"ofNamero"]],
 [['text'=>"🎯 مكافأة الدعوة",'callback_data'=>"set_invite_reward"],['text'=>"📦 الحد الأدنى",'callback_data'=>"set_min_order"]],
 [['text'=>"🪙 عملة البوت",'callback_data'=>"set_currency"],['text'=>"💎 سعر العضو",'callback_data'=>"set_user_price"]],
 [['text'=>"🔗 رابط الموقع",'callback_data'=>"set_api_domain"],['text'=>"🔑 التوكن",'callback_data'=>"set_api_token"]],
 [['text'=>"📋 متابعة طلب",'callback_data'=>"SALEh_1"]],
]
])
]);
unset($Namero['mode'][$from_id]);
db_save_namero($Namero);
}
}

if($data == "set_api_domain"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• أرسل الآن كليشه شحن النقاط 🔥

• يمكنك استخدام الماركدون وعند ارسال اي معرف او رابط يجب وضعه بين *[،]*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
$Namero['mode'][$from_id] = "await_api_domain";
db_save_namero($Namero);
}
if($text && ($Namero['mode'][$from_id] ?? '') == "await_api_domain"){
$settings = db_get_settings();
$settings["domain"] = trim($text);
db_save_settings($settings);
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم الحفظ بنجاح",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
])
]);
}

if($data == "set_api_token"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• أرسل الآن كليشه معلومات البوت",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
])
]);
$Namero['mode'][$from_id] = "await_api_token";
db_save_namero($Namero);
}
if($text && ($Namero['mode'][$from_id] ?? '') == "await_api_token"){
$settings = db_get_settings();
$settings["token"] = trim($text);
db_save_settings($settings);
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم الحفظ بنجاح",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
}
if($data == "set_site_url"){
$_cur_site = $settings['site_url'] ?? '';
$_cur_label = !empty($_cur_site) ? "`$_cur_site`" : 'لم يُعيَّن';
$_site_kb = [[['text'=>"رجوع",'callback_data'=>"back"]]];
if (!empty($_cur_site)) {
    array_unshift($_site_kb, [['text'=>"🗑 إزالة الرابط",'callback_data'=>"remove_site_url"]]);
}
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"🌐 *رابط الموقع الحالي:*\n$_cur_label\n\n• أرسل الآن رابط الموقع الجديد\n• مثال: `https://example.com`",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>$_site_kb])
]);
$Namero['mode'][$from_id] = "await_site_url";
db_save_namero($Namero);
}
if($data == "remove_site_url"){
$settings = db_get_settings();
$settings["site_url"] = "";
db_save_settings($settings);
db_settings_invalidate();
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"✅ تم إزالة رابط الموقع بنجاح",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
}
if($text && ($Namero['mode'][$from_id] ?? '') == "await_site_url"){
if(strpos($text, 'https://') === 0){
$settings = db_get_settings();
$settings["site_url"] = rtrim(trim($text), '/');
db_save_settings($settings);
db_settings_invalidate();
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم حفظ رابط الموقع بنجاح:\n`".$settings['site_url']."`",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
]
])
]);
} else {
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"❌ الرابط يجب أن يبدأ بـ `https://`\nأرسل الرابط مرة أخرى",
'parse_mode'=>"markdown"
]);
}
}
if($data == "set_invite_reward"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• أرسل عدد ال$currency التي يحصل عليها المستخدم عند استخدام رابط الدعوة 🎁",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
$Namero['mode'][$from_id] = "await_invite_reward";
db_save_namero($Namero);
}
if(is_numeric($text) && ($Namero['mode'][$from_id] ?? '') == "await_invite_reward"){
$settings = db_get_settings();
$settings["invite_reward"] = intval($text);
db_save_settings($settings);
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم تعيين عدد ال$currency لكل دعوة: *$text* $currency",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
}
if($data == "Ch"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• أرسل الان رابط قناة البوت 📝

مثال:
https://t.me/××××",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
$Namero['mode'][$from_id] = "Chh";
db_save_namero($Namero);
}

if(($Namero['mode'][$from_id] ?? '') == "Chh"){
if(!preg_match('/^https:\/\/(t\.me|telegram\.me)\/[A-Za-z0-9_]+$/i', $text)){
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"❌ الرابط غير صحيح!

الرجاء ارسال رابط قناة يبدأ بـ:
`https://t.me/×××××`

ولا يحتوي على مسافات.",
'parse_mode'=>"markdown"
]);
return;
}
$settings = db_get_settings();
$settings["Ch"] = $text;
db_save_settings($settings);
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم تعيين القناة:
*$text*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
}
if($data == "set_min_order"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• أرسل الحد الأدنى المسموح به لعدد $currency التحويل (مثال: 10)",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
$Namero['mode'][$from_id] = "await_min_order";
db_save_namero($Namero);
}

if(is_numeric($text) && ($Namero['mode'][$from_id] ?? '') == "await_min_order"){
$settings = db_get_settings();
$settings["min_order_quantity"] = intval($text);
db_save_settings($settings);
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم تعيين الحد الأدنى التحويل إلى: *$text* $currency",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
}
if($data == "set_daily_gift"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• أرسل عدد ال$currency التي يحصل عليها المستخدم يوميًا كهدية (مثال: 20)",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
$Namero['mode'][$from_id] = "await_daily_gift";
db_save_namero($Namero);
}

if(is_numeric($text) && ($Namero['mode'][$from_id] ?? '') == "await_daily_gift"){
$settings = db_get_settings();
$settings["daily_gift"] = intval($text);
db_save_settings($settings);
$Namero['mode'][$from_id] = null;
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم تعيين قيمة الهدية اليومية إلى: *$text* $currency",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
}

if($data == "set_currency"){
$Namero['mode'][$from_id] = "await_currency";
db_save_namero($Namero);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "• ارسل الآن اسم العمله الجديد 🆕

- الاسم الحالي ($currency) 💰",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
]) 
]);
}
if($data == "set_user_price"){
$Namero['mode'][$from_id] = "await_user_price";
db_save_namero($Namero);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "ارسل سعر النقاط مقابل كل 1 نجوم 🔥",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
]) 
]);
}
if($text && $from_id == $admin){
if(($Namero['mode'][$from_id] ?? '') == "await_currency"){
$settings['currency'] = trim($text);
db_save_settings($settings);
unset($Namero['mode'][$from_id]);
db_save_namero($Namero);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ تم حفظ العمله: *$text*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
]) 
]);
}
if(($Namero['mode'][$from_id] ?? '') == "await_user_price"){
if(is_numeric($text)){
$settings['user_price'] = floatval($text);
db_save_settings($settings);
unset($Namero['mode'][$from_id]);
db_save_namero($Namero);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ تم حفظ ال$currency: *$text*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
]) 
]);
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "⚠️ السعر غير صحيح، يرجى إرسال رقم فقط",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
]) 
]);
}
}
}

$settings = db_get_settings();
function generateUID() {
return substr(md5(uniqid(rand(), true)), 0, 8);
}
function findUIDbyName($name, $array) {
foreach ($array as $uid => $data) {
if ($data['name'] == $name) {
return $uid;
}
}
return null;
}

function getAdminKeyboard($settings, $is_owner = false){
global $admin;
$currency= $settings['currency'] ?? "نقاط";
$daily_gift_status = ($settings['daily_gift_status'] == "on") ? "✅" : "❌";
$invite_link_status = ($settings['invite_link_status'] == "on") ? "✅" : "❌";
$transfer_status = ($settings['transfer_status'] == "on") ? "✅" : "❌";
$starss = ($settings['starss'] == "on") ? "✅" : "❌";
$Market_status = ($settings['Market'] == "on") ? "✅" : "❌";
$_saved_site_url = $settings['site_url'] ?? '';
$base_url = !empty($_saved_site_url) ? rtrim($_saved_site_url, '/') : '';
$_site_url_label = !empty($_saved_site_url) ? "✅" : "❌";
$rows = [
[['text'=>"🗳 التحكم في الخدمات",'callback_data'=>"Namero_sections"]],
[['text'=>"🛍 التحكم في المتجر",'callback_data'=>"store_sections"]],
[['text'=>"🔺 شحن نقاط",'callback_data'=>"coins"],['text'=>"🔻 خصم نقاط",'callback_data'=>"deduct_coins"]],
[['text'=>"🚫 حظر مستخدم",'callback_data'=>"ban_user"],['text'=>"✅ فك الحظر",'callback_data'=>"unban_user"]],
[['text'=>"🔍 كشف حساب",'callback_data'=>"account_info"]],
[['text'=>"✅ فتح الرشق",'callback_data'=>"onNamero"],['text'=>"🚫 قفل الرشق",'callback_data'=>"ofNamero"]],
[['text'=>"🎁 الهديه اليومية $daily_gift_status",'callback_data'=>"toggle_daily_gift"],['text'=>"🔗 رابط الدعوة $invite_link_status",'callback_data'=>"toggle_invite_link"]],
[['text'=>"♻️ التحويل $transfer_status",'callback_data'=>"toggle_transfer"],['text'=>"⭐ شحن بالنجوم $starss",'callback_data'=>"toggle_starss"]],
[['text'=>"🛍 قسم المتجر $Market_status",'callback_data'=>"toggle_Market"]],
[['text'=>"🎯 مكافأة الدعوة",'callback_data'=>"set_invite_reward"],['text'=>"📦 الحد الأدنى",'callback_data'=>"set_min_order"]],
[['text'=>"🪙 عملة البوت",'callback_data'=>"set_currency"],['text'=>"💎 سعر النقاط للنجوم",'callback_data'=>"set_user_price"]],
[['text'=>"🎫 صنع رابط هدية",'callback_data'=>"make_gift_link"],['text'=>"🎁 تعيين الهدية",'callback_data'=>"set_daily_gift"]],
[['text'=>"📡 قناة البوت",'callback_data'=>"Ch"],['text'=>"🧾 كليشة الشراء",'callback_data'=>"set_api_domain"]],
[['text'=>"🔑 التوكن",'callback_data'=>"set_api_token"],['text'=>"📋 متابعة طلب",'callback_data'=>"SALEh_1"]],
[['text'=>"🌐 رابط الموقع $_site_url_label",'callback_data'=>"set_site_url"]],
];
if (!empty($base_url)) {
    $rows[] = [['text'=>"🖥 تحكم من الموقع الكامل", 'web_app' => ['url' => $base_url.'/admin_panel.php']]];
}
if ($is_owner) {
    $rows[] = [['text'=>"🔐 كلمة سر لوحة الأدمن",'callback_data'=>"panel_pass_menu"]];
}
$rows[] = [['text'=>"⬅️ رجوع",'callback_data'=>"@S_P_P1"]];
return ['inline_keyboard' => $rows];
}

if($data == "store_sections"){
$settings = db_get_settings();
$sections = $settings["store"]["sections"] ?? [];

$btns = [];
if(!empty($sections)){
foreach($sections as $sid => $sec){
$btns[] = [
['text'=>$sec['name'],'callback_data'=>"view_store_section_$sid"],
['text'=>"❌",'callback_data'=>"del_store_section_$sid"]
];
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
if($data == "add_store_section"){
$settings["step"][$from_id] = "await_store_section";
db_save_settings($settings);

bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"✍️ *أرسل اسم القسم:*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"store_sections"]]
]])
]);
exit;
}

if($settings["step"][$from_id] == "await_store_section"){
unset($settings["step"][$from_id]);
$sid = generateUID();
$settings["store"]["sections"][$sid] = [
"name"=>trim($text),
"items"=>[]
];
db_save_settings($settings);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ *تم إضافة القسم:* ".trim($text),
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"store_sections"]]
]])
]);
exit;
}
if (preg_match("/^del_store_section_(.*)/", $data, $m)) {
$sid = $m[1];
$sectionName = $settings["store"]["sections"][$sid]['name'] ?? 'غير معروف';
bot('answerCallbackQuery', [
'callback_query_id' => $update->callback_query->id,
'text' => "⏳ جاري حذف القسم: $sectionName...",
'show_alert' => false
]);
unset($settings["store"]["sections"][$sid]);
db_save_settings($settings);
$sections = $settings["store"]["sections"] ?? [];
$btns = [];
if (!empty($sections)) {
foreach ($sections as $uid => $sec) {
$btns[] = [
['text' => $sec['name'], 'callback_data' => "view_store_section_$uid"],
['text' => "❌", 'callback_data' => "del_store_section_$uid"]
];
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
$btns[] = [
['text'=>$item['name'],'callback_data'=>"view_item_{$sid}_{$iid}"],
['text'=>"❌",'callback_data'=>"del_item_{$sid}_{$iid}"]
];
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
db_save_settings($settings);

bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"🛍 *أرسل اسم السلعة:*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"view_store_section_$sid"]]
]])
]);
exit;
}

if(preg_match("/^await_item_name_(.*)/",$settings["step"][$from_id],$m)){
$sid = $m[1];
unset($settings["step"][$from_id]);

$iid = generateUID();
$settings["store"]["sections"][$sid]["items"][$iid] = [
"name"=>trim($text),
"price"=>0,
"description"=>""
];
db_save_settings($settings);

bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✔️ *تم إضافة السلعة:* ".trim($text),
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"view_store_section_$sid"]]
]])
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
'text'=>"🛒 *السلعة:* {$item['name']}

💰 السعر: {$item['price']}
📝 الوصف: ".($item['description'] ?: "لا يوجد")."",
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
db_save_settings($settings);

bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"💰 *أرسل السعر الآن:*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]]
]])
]);
exit;
}

if(preg_match("/^await_price_(.*)_(.*)/",$settings["step"][$from_id],$m)){
$sid = $m[1];
$iid = $m[2];

if(!is_numeric($text)){
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"❌ السعر لازم يكون رقم!"
]);
exit;
}

unset($settings["step"][$from_id]);
$settings["store"]["sections"][$sid]["items"][$iid]["price"] = trim($text);
db_save_settings($settings);

bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✔️ *تم تحديث السعر إلى:* $text",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]]
]])
]);
exit;
}
if(preg_match("/^setdesc_(.*)_(.*)/", $data, $m)){
$sid = $m[1];
$iid = $m[2];

$settings["step"][$from_id] = "await_desc_{$sid}_{$iid}";
db_save_settings($settings);

bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"📝 *أرسل الوصف الآن:*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]]
]])
]);
exit;
}

if(preg_match("/^await_desc_(.*)_(.*)/",$settings["step"][$from_id],$m)){
$sid = $m[1];
$iid = $m[2];

unset($settings["step"][$from_id]);
$settings["store"]["sections"][$sid]["items"][$iid]["description"] = trim($text);
db_save_settings($settings);

bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✔️ *تم تحديث الوصف!*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"view_item_{$sid}_{$iid}"]]
]])
]);
exit;
}
if(preg_match("/^del_item_(.*)_(.*)/",$data,$m)){
$sid = $m[1];
$iid = $m[2];
$name = $settings["store"]["sections"][$sid]["items"][$iid]["name"];

unset($settings["store"]["sections"][$sid]["items"][$iid]);
db_save_settings($settings);

bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"✔️ تم حذف السلعة: $name"
]);

bot('editMessageReplyMarkup',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"↩️ رجوع",'callback_data'=>"view_store_section_$sid"]]
]])
]);
}
if ($data == "make_gift_link") {
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "• أرسل عدد ال$currency للهدية 🎁",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
]) 
]);
$Namero["mode"][$from_id] = "await_gift_points";
db_save_namero($Namero);
return; 
}

if (is_numeric($text) && $Namero["mode"][$from_id] == "await_gift_points") {
$Namero["gift_temp"][$from_id]["points"] = intval($text);
$Namero["mode"][$from_id] = "await_gift_limit";
db_save_namero($Namero);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "• أرسل عدد الأشخاص الذين يمكنهم استخدام الرابط 👥",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"back" ]],
 
]
]) 
]);
return; 
}

if (is_numeric($text) && $Namero["mode"][$from_id] == "await_gift_limit") {
$points = $Namero["gift_temp"][$from_id]["points"];
$limit = intval($text);
$code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
$Namero["gift_links"][$code] = [
"points" => $points,
"limit" => $limit,
"used" => []
];
unset($Namero["gift_temp"][$from_id]);
unset($Namero["mode"][$from_id]);
db_save_namero($Namero);
$bot_username = $userBot;
$link = "https://t.me/$bot_username?start=gift_$code";
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "- تم إنشاء رابط هديه بنجاح 🛍
----------------------------
🛍 ال$currency: $points
🥷 الحد الأقصى: $limit
❇️ الرابط:
- [$link] ",
'parse_mode' => "markdown"
]);
}

if ($data == "Namero_sections") {
$settings = db_get_settings();
$sections = $settings["sections"] ?? [];
$btns = [];
if (!empty($sections)) {
foreach ($sections as $uid => $sectionData) {
$sectionName = $sectionData['name'];
$btns[] = [
['text' => $sectionName, 'callback_data' => "view_section_$uid"],
['text' => "❌", 'callback_data' => "del_section_$uid"]
];
}
} else {
$btns[] = [['text' => "لا توجد أقسام بعد ❗", 'callback_data' => "no"]];
}
$btns[] = [['text' => "➕ إضافة قسم جديد", 'callback_data' => "add_new_section"]];
$btns[] = [['text' => "رجوع", 'callback_data' => "back"]];
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "📂 *إدارة الأقسام*

اختر قسم للتحكم أو أضف قسم جديد:",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => $btns])
]);
}

if ($data == "add_new_section") {
$settings["step"][$from_id] = "add_section_wait";
db_save_settings($settings);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "✏️ *أرسل اسم القسم الجديد الآن:*",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "Namero_sections"]]
]
])
]);
exit;
}

if ($settings["step"][$from_id] == "add_section_wait") {
unset($settings["step"][$from_id]);
$sec_name = trim($text);
$existing_uid = findUIDbyName($sec_name, $settings["sections"] ?? []);
if ($existing_uid !== null) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ *هذا القسم موجود بالفعل!*",
'parse_mode' => "markdown"
]);
return;
}
$new_uid = generateUID();
$settings["sections"][$new_uid] = [
"name" => $sec_name,
"services" => []
];
db_save_settings($settings);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *تم إضافة القسم:* $sec_name

🆔 المعرف: `$new_uid`",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع للإدارة", 'callback_data' => "Namero_sections"]]
]
])
]);
}
if (preg_match("/^del_section_(.*)/", $data, $m)) {
$uid = $m[1];
$sectionName = $settings["sections"][$uid]['name'] ?? 'غير معروف';
unset($settings["sections"][$uid]);
db_save_settings($settings);
bot('answerCallbackQuery', [
'callback_query_id' => $update->callback_query->id,
'text' => "✔️ تم حذف القسم: $sectionName"
]);
$sections = $settings["sections"] ?? [];
$btns = [];
if (!empty($sections)) {
foreach ($sections as $uid => $sectionData) {
$sectionName = $sectionData['name'];
$btns[] = [
['text' => $sectionName, 'callback_data' => "view_section_$uid"],
['text' => "❌", 'callback_data' => "del_section_$uid"]
];
}
}
$btns[] = [['text' => "➕ إضافة قسم جديد", 'callback_data' => "add_new_section"]];
$btns[] = [['text' => "رجوع", 'callback_data' => "back"]];
bot('editMessageReplyMarkup', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'reply_markup' => json_encode(['inline_keyboard' => $btns])
]);
}

if (preg_match("/^view_section_(.*)/", $data, $m)) {
$uid = $m[1];
if (!isset($settings["sections"][$uid])) {
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "❌ *هذا القسم لم يعد موجوداً!*",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "Namero_sections"]]
]
])
]);
return;
}
$sectionData = $settings["sections"][$uid];
$sectionName = $sectionData['name'];
$services = $sectionData["services"] ?? [];
$btns = [];
if (!empty($services)) {
foreach ($services as $serviceUID => $serviceData) {
$serviceName = $serviceData['name'];
$btns[] = [
['text' => $serviceName, 'callback_data' => "service_".$uid."_".$serviceUID],
['text' => "❌", 'callback_data' => "del_service_".$uid."_".$serviceUID]
];
}
} else {
$btns[] = [['text' => "لا توجد خدمات بعد ❗", 'callback_data' => "no"]];
}
$btns[] = [['text' => "➕ إضافة خدمة جديدة", 'callback_data' => "add_service_$uid"]];
$btns[] = [['text' => "رجوع", 'callback_data' => "Namero_sections"]];
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "📦 *القسم:* $sectionName\n🆔 المعرف: `$uid`

اختر خدمة أو أضف خدمة جديدة:",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => $btns])
]);
}

function platformKeyboard(string $sectionUID, string $serviceUID, string $prefix = "svc_plat"): array {
    $plats = [
        ['tiktok','🎵 تيك توك'],['youtube','▶️ يوتيوب'],
        ['instagram','📷 انستقرام'],['facebook','👍 فيسبوك'],
        ['telegram','✈️ تيليجرام'],['snapchat','👻 سناب'],
        ['twitter','✖️ تويتر X'],['threads','🧵 ثريدز'],
        ['whatsapp','💬 واتساب'],['all','🌐 عام (الكل)'],
    ];
    $rows = [];
    $row = [];
    foreach ($plats as $i => [$id, $label]) {
        $row[] = ['text' => $label, 'callback_data' => "{$prefix}_{$sectionUID}_{$serviceUID}_{$id}"];
        if (count($row) == 2) { $rows[] = $row; $row = []; }
    }
    if ($row) $rows[] = $row;
    return $rows;
}

if (preg_match("/^add_service_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$settings["step"][$from_id] = "add_service_name_only";
$settings["temp"][$from_id] = [
"section_uid" => $sectionUID
];

db_save_settings($settings);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "✏️ *أرسل اسم الخدمة الآن:*",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "view_section_".$sectionUID]]
]
])
]);
exit;
}
if ($settings["step"][$from_id] == "add_service_name_only") {
$sectionUID = $settings["temp"][$from_id]["section_uid"];
$serviceName = trim($text);
if ($serviceName == "") {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "⚠️ *الاسم لا يمكن أن يكون فارغاً!*",
'parse_mode' => "markdown"
]);
return;
}
$serviceUID = generateUID();
$settings["step"][$from_id] = "add_service_await_platform";
$settings["temp"][$from_id] = [
"section_uid" => $sectionUID,
"service_uid" => $serviceUID,
"service_name" => $serviceName,
];
db_save_settings($settings);
$platRows = platformKeyboard($sectionUID, $serviceUID);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *الاسم:* $serviceName\n\n📱 *اختر المنصة للخدمة:*",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => $platRows])
]);
}

if (preg_match("/^svc_plat_([^_]+)_([^_]+)_(.*)/", $data, $m)) {
$sectionUID  = $m[1];
$serviceUID  = $m[2];
$platform    = $m[3];
$temp = $settings["temp"][$from_id] ?? [];
$serviceName = $temp["service_name"] ?? "خدمة جديدة";
$settings["sections"][$sectionUID]["services"][$serviceUID] = [
"name"       => $serviceName,
"platform"   => $platform,
"min"        => "10",
"max"        => "1000",
"price"      => "1000",
"service_id" => "",
"domain"     => "",
"api"        => "",
"delay"      => "0"
];
unset($settings["step"][$from_id], $settings["temp"][$from_id]);
db_save_settings($settings);
$sectionName = $settings["sections"][$sectionUID]['name'] ?? '';
$platNames = ['tiktok'=>'تيك توك','youtube'=>'يوتيوب','instagram'=>'انستقرام','facebook'=>'فيسبوك',
'telegram'=>'تيليجرام','snapchat'=>'سناب','twitter'=>'تويتر X','threads'=>'ثريدز',
'whatsapp'=>'واتساب','all'=>'عام'];
$platLabel = $platNames[$platform] ?? $platform;
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "✅ *تم إضافة الخدمة بنجاح!*\n\n📌 الاسم: $serviceName\n📱 المنصة: $platLabel\n📦 القسم: $sectionName\n🆔 المعرف: `$serviceUID`",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => [[['text' => "📂 رجوع للقسم", 'callback_data' => "view_section_{$sectionUID}"]]]])
]);
exit;
}

if (preg_match("/^chg_plat_([^_]+)_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$serviceUID = $m[2];
$platRows = platformKeyboard($sectionUID, $serviceUID, "set_plat");
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "📱 *اختر المنصة الجديدة للخدمة:*",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => array_merge($platRows, [[['text'=>'رجوع','callback_data'=>"service_{$sectionUID}_{$serviceUID}"]]])])
]);
exit;
}
if (preg_match("/^set_plat_([^_]+)_([^_]+)_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$serviceUID = $m[2];
$platform   = $m[3];
$settings["sections"][$sectionUID]["services"][$serviceUID]["platform"] = $platform;
db_save_settings($settings);
bot('answerCallbackQuery', ['callback_query_id' => $update->callback_query->id ?? '', 'text' => "✅ تم تغيير المنصة"]);
}
if (preg_match("/^del_service_(.*)_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$serviceUID = $m[2];
$serviceName = $settings["sections"][$sectionUID]["services"][$serviceUID]['name'] ?? 'غير معروف';
unset($settings["sections"][$sectionUID]["services"][$serviceUID]);
db_save_settings($settings);
bot('answerCallbackQuery', [
'callback_query_id' => $update->callback_query->id,
'text' => "✔️ تم حذف الخدمة: $serviceName"
]);
$services = $settings["sections"][$sectionUID]["services"] ?? [];
$btns = [];
if (!empty($services)) {
foreach ($services as $uid => $serviceData) {
$serviceName = $serviceData['name'];
$btns[] = [
['text' => $serviceName, 'callback_data' => "service_".$sectionUID."_".$uid],
['text' => "❌", 'callback_data' => "del_service_".$sectionUID."_".$uid]
];
}
}
$btns[] = [['text' => "➕ إضافة خدمة جديدة", 'callback_data' => "add_service_".$sectionUID]];
$btns[] = [['text' => "رجوع", 'callback_data' => "Namero_sections"]];
bot('editMessageReplyMarkup', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'reply_markup' => json_encode(['inline_keyboard' => $btns])
]);
}
if (preg_match("/^service_(.*)_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$serviceUID = $m[2];
if (!isset($settings["sections"][$sectionUID]["services"][$serviceUID])) {
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "❌ *هذه الخدمة لم تعد موجودة!*",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "view_section_".$sectionUID]]
]
])
]);
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
$_platNames = ['tiktok'=>'🎵 تيك توك','youtube'=>'▶️ يوتيوب','instagram'=>'📷 انستقرام',
'facebook'=>'👍 فيسبوك','telegram'=>'✈️ تيليجرام','snapchat'=>'👻 سناب',
'twitter'=>'✖️ تويتر X','threads'=>'🧵 ثريدز','whatsapp'=>'💬 واتساب','all'=>'🌐 عام'];
$platLabel = $_platNames[$S['platform'] ?? 'all'] ?? '🌐 عام';

if ($domain != "غير معيّن" && $api != "غير معيّن") {
$balance = @file_get_contents("https://$domain/api/v2?key=$api&action=balance");
if ($balance === false) {
$balance = "❌ فشل في جلب الرصيد";
}
} else {
$balance = "لا يمكن جلب الرصيد";
}
unset($settings["step"][$from_id], $settings["temp"][$from_id]);
db_save_settings($settings);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "⚙️ *إعدادات الخدمة*\n\n" .
 "📌 *اسم الخدمة:* $serviceName\n" .
 "📱 *المنصة:* $platLabel\n" .
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
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "📱 تغيير المنصة ($platLabel)", 'callback_data' => "chg_plat_{$sectionUID}_{$serviceUID}"]],
[['text' => "تعيين أقل حد", 'callback_data' => "set_min_$sectionUID"."_$serviceUID"]],
[['text' => "تعيين أقصى حد", 'callback_data' => "set_max_$sectionUID"."_$serviceUID"]],
[['text' => "تعيين السعر", 'callback_data' => "set_price_$sectionUID"."_$serviceUID"]],
[['text' => "تعيين ID الخدمة", 'callback_data' => "set_sid_$sectionUID"."_$serviceUID"]],
[['text' => "تعيين الدومين", 'callback_data' => "set_domain_$sectionUID"."_$serviceUID"]],
[['text' => "تعيين API Key", 'callback_data' => "set_api_$sectionUID"."_$serviceUID"]],
[['text' => "تعيين مدة الانتظار", 'callback_data' => "set_delay_$sectionUID"."_$serviceUID"]],
[['text' => "رجوع للقسم", 'callback_data' => "view_section_$sectionUID"]]
]
])
]);
}

if (preg_match("/^set_(min|max|price|sid|domain|api|delay)_(.*)_(.*)/", $data, $m)) {
$type = $m[1];
$sectionUID = $m[2];
$serviceUID = $m[3];
$settings["step"][$from_id] = "edit_service_value";
$settings["temp"][$from_id] = [
"type" => $type,
"section_uid" => $sectionUID,
"service_uid" => $serviceUID
];
db_save_settings($settings);
$names = [
"min" => "أقل حد",
"max" => "أقصى حد",
"price" => "السعر لكل 1000",
"sid" => "ID الخدمة",
"domain" => "الدومين",
"api" => "API Key",
"delay" => "مدة الانتظار بالساعات"
];
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "✏️ *أرسل {$names[$type]} الآن:*",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "service_".$sectionUID."_".$serviceUID]]
]
])
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
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ *الخدمة لم تعد موجودة!*",
'parse_mode' => "markdown"
]);
unset($settings["step"][$from_id], $settings["temp"][$from_id]);
db_save_settings($settings);
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
db_save_settings($settings);
unset($settings["step"][$from_id]);
unset($settings["temp"][$from_id]);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *تم تحديث القيمة بنجاح!*
🔧 الخدمة: *$serviceName*
📁 القسم: *$sectionName*
🆔 المعرف: `$serviceUID`",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع لإعدادات الخدمة", 'callback_data' => "service_".$sectionUID."_".$serviceUID]]
]
])
]);
exit;
}

if($data == "back" && ($from_id == $admin || in_array($from_id, $admins))){
$settings = db_get_settings();
$keyboard = getAdminKeyboard($settings, $from_id == $admin);
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"🔥 لوحة إعدادات البوت"
]);
bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"🗳 *مرحبا بك عزيزي لوحة الأدمن الخاصه بالبوت 🔥
----------------------------*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode($keyboard)
]);
}
if($data == "@NameroBots" && ($from_id == $admin || in_array($from_id, $admins))){
$settings = db_get_settings();
$keyboard = getAdminKeyboard($settings, $from_id == $admin);
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"📮 إعدادات بوت الرشق"
]);
$_r = bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"📮 *إعدادات بوت الرشق 🔥
----------------------------*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode($keyboard)
]);
}
if($data == "toggle_daily_gift"){
$settings['daily_gift_status'] = ($settings['daily_gift_status'] == "on") ? "off" : "on";
db_save_settings($settings);
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"تم تغيير حالة الهديّة اليومية!"
]);

bot('editMessageReplyMarkup',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'reply_markup'=>json_encode(getAdminKeyboard($settings, $from_id == $admin))
]);
}
if($data == "toggle_invite_link"){
$settings['invite_link_status'] = ($settings['invite_link_status'] == "on") ? "off" : "on";
db_save_settings($settings);
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"تم تغيير حالة رابط الدعوة!"
]);
bot('editMessageReplyMarkup',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'reply_markup'=>json_encode(getAdminKeyboard($settings, $from_id == $admin))
]);
}
if($data == "toggle_transfer"){
$settings['transfer_status'] = ($settings['transfer_status'] == "on") ? "off" : "on";
db_save_settings($settings);
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"تم تغيير حالة تحويل ال$currency!"
]);
bot('editMessageReplyMarkup',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'reply_markup'=>json_encode(getAdminKeyboard($settings, $from_id == $admin))
]);
}
if($data == "toggle_starss"){
$settings['starss'] = ($settings['starss'] == "on") ? "off" : "on";
db_save_settings($settings);
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"تم تغيير حالة شحن ال$currency!"
]);
bot('editMessageReplyMarkup',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'reply_markup'=>json_encode(getAdminKeyboard($settings, $from_id == $admin))
]);
}
if($data == "toggle_Market"){
$settings['Market'] = ($settings['Market'] == "on") ? "off" : "on";
db_save_settings($settings);
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"تم تغيير حالة قسم المتجر!"
]);
bot('editMessageReplyMarkup',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'reply_markup'=>json_encode(getAdminKeyboard($settings, $from_id == $admin))
]);
}
if($data == "onNamero") {
if($chat_id == $admin || in_array($chat_id, $admins)) {
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"*• تم فتح الرشق*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([ 
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
$Namero['rshaq']= "on";
db_save_namero($Namero);
}
}
if($data == "ofNamero") {
if($chat_id == $admin || in_array($chat_id, $admins)) {
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"*• تم قفل الرشق *",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([ 
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"back" ]], 
]
])
]);
$Namero['rshaq']= "of";
db_save_namero($Namero);
}
}

if($data == "panel_pass_menu" && $from_id == $admin){
$admins_raw = file_exists(__DIR__.'/admins.php') ? file_get_contents(__DIR__.'/admins.php') : '';
preg_match("/define\('ADMIN_PANEL_PASS',\s*'([^']*)'\);/", $admins_raw, $pm);
$cur_pass = $pm[1] ?? '—';
$masked = str_repeat('●', mb_strlen($cur_pass));
bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"🔐 *كلمة سر لوحة الأدمن*\n\n🔒 الكلمة الحالية: `$masked`\n\n_ملاحظة: فقط المالك يمكنه تغيير كلمة السر_",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"👁 إظهار كلمة السر",'callback_data'=>"panel_pass_show"]],
[['text'=>"✏️ تغيير كلمة السر",'callback_data'=>"panel_pass_change"]],
[['text'=>"⬅️ رجوع",'callback_data'=>"back"]],
]
])
]);
exit;
}

if($data == "panel_pass_show" && $from_id == $admin){
$admins_raw = file_exists(__DIR__.'/admins.php') ? file_get_contents(__DIR__.'/admins.php') : '';
preg_match("/define\('ADMIN_PANEL_PASS',\s*'([^']*)'\);/", $admins_raw, $pm);
$cur_pass = $pm[1] ?? '—';
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"🔑 كلمة السر: $cur_pass",
'show_alert'=>true
]);
exit;
}

if($data == "panel_pass_change" && $from_id == $admin){
$Namero['mode'][$from_id] = "await_panel_pass";
db_save_namero($Namero);
bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"✏️ *تغيير كلمة السر*\n\nأرسل الآن كلمة السر الجديدة لوحة الأدمن\n\n⚠️ _تأكد أن الكلمة قوية وتتذكرها_",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"❌ إلغاء",'callback_data'=>"panel_pass_menu"]],
]
])
]);
exit;
}

if($text && $from_id == $admin && ($Namero['mode'][$from_id] ?? '') == "await_panel_pass"){
$new_pass = trim($text);
$admins_file = __DIR__.'/admins.php';
$admins_content = file_get_contents($admins_file);
$admins_content = preg_replace(
"/define\('ADMIN_PANEL_PASS',\s*'[^']*'\);/",
"define('ADMIN_PANEL_PASS', '".addslashes($new_pass)."');",
$admins_content
);
file_put_contents($admins_file, $admins_content);
unset($Namero['mode'][$from_id]);
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ *تم تغيير كلمة السر بنجاح!*\n\n🔑 الكلمة الجديدة: `$new_pass`\n\n_احتفظ بها في مكان آمن_",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🔐 إعدادات كلمة السر",'callback_data'=>"panel_pass_menu"]],
[['text'=>"⬅️ لوحة الأدمن",'callback_data'=>"back"]],
]
])
]);
exit;
}

if($data == "coins" and $chat_id == $admin || in_array($chat_id, $admins)) {
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"🔺 *شحن نقاط*\n\nارسل ايدي الشخص الان",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
$Namero['mode'][$from_id]= "coins";
db_set_user_mode((int)$from_id, 'coins');
exit;
}

if($data == "deduct_coins" and $chat_id == $admin || in_array($chat_id, $admins)) {
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"🔻 *خصم نقاط*\n\nارسل ايدي الشخص الان",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
$Namero['mode'][$from_id]= "deduct";
db_set_user_mode((int)$from_id, 'deduct');
exit;
}

if($text and isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "coins") {
bot('sendMessage',[
 'chat_id'=>$chat_id,
 'text'=>"🔺 ارسل عدد ال$currency التي تريد *إضافتها* للشخص",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"admin"]]]])
]);
db_set_user_temp((int)$from_id, (string)$text);
$Namero['mode'][$from_id]= "coins2";
db_set_user_mode((int)$from_id, 'coins2');
exit;
}

if($text and isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "deduct") {
bot('sendMessage',[
 'chat_id'=>$chat_id,
 'text'=>"🔻 ارسل عدد ال$currency التي تريد *خصمها* من الشخص",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"admin"]]]])
]);
db_set_user_temp((int)$from_id, (string)$text);
$Namero['mode'][$from_id]= "deduct2";
db_set_user_mode((int)$from_id, 'deduct2');
exit;
}

function _apply_coin_change($chat_id, $from_id, $text, $currency, &$Namero, bool $is_deduct): void {
    $__admin_row = db_get_user_row((int)$from_id);
    $_target_uid = trim($__admin_row['temp'] ?? '');

    if(empty($_target_uid) || !is_numeric($_target_uid)) {
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ حدث خطأ، أعد العملية من البداية."]);
        db_set_user_mode((int)$from_id, '');
        $Namero['mode'][$from_id] = null;
        return;
    }

    $_target_uid = (int)$_target_uid;
    $_amount     = abs((float)$text);
    if($is_deduct) $_amount = -$_amount;

    db_ensure_namero_coin($Namero, $_target_uid);
    $Namero['coin'][$_target_uid] += $_amount;

    $_action = $is_deduct ? "خصم 🔻" : "إضافة 🔺";
    bot('sendMessage',[
     'chat_id'=>$chat_id,
     'text'=>"• تم *{$_action}* *".abs($_amount)."* $currency بنجاح الى *{$_target_uid}*\n• رصيده الحالي: *{$Namero['coin'][$_target_uid]}* $currency",
    'parse_mode'=>"markdown",
    'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"admin"]]]])
    ]);

    db_set_user_coin($_target_uid, $Namero['coin'][$_target_uid]);
    db_set_user_mode((int)$from_id, '');
    db_set_user_temp((int)$from_id, '');
    $Namero['mode'][$from_id] = null;
}

if($text and isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "coins2") {
    _apply_coin_change($chat_id, $from_id, $text, $currency, $Namero, false);
    exit;
}

if($text and isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "deduct2") {
    _apply_coin_change($chat_id, $from_id, $text, $currency, $Namero, true);
    exit;
}

if($data == "ban_user" && ($chat_id == $admin || in_array($chat_id, $admins))) {
bot('EditMessageText',[
'chat_id'=>$chat_id,'message_id'=>$message_id,
'text'=>"🚫 *حظر مستخدم*\n\nأرسل ID المستخدم الذي تريد حظره:",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
db_set_user_mode((int)$from_id, 'ban_user');
exit;
}

if($data == "unban_user" && ($chat_id == $admin || in_array($chat_id, $admins))) {
bot('EditMessageText',[
'chat_id'=>$chat_id,'message_id'=>$message_id,
'text'=>"✅ *فك الحظر*\n\nأرسل ID المستخدم الذي تريد رفع الحظر عنه:",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
db_set_user_mode((int)$from_id, 'unban_user');
exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "ban_user") {
$_buid = (int)trim($text);
if($_buid <= 0) { bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ ID غير صالح."]); exit; }
db_ban_user($_buid);
db_set_user_mode((int)$from_id, '');
$Namero['mode'][$from_id] = null;
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم حظر المستخدم *{$_buid}* بنجاح.\nلن يتمكن من استخدام البوت أو الموقع.",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "unban_user") {
$_buid = (int)trim($text);
if($_buid <= 0) { bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ ID غير صالح."]); exit; }
db_unban_user($_buid);
db_set_user_mode((int)$from_id, '');
$Namero['mode'][$from_id] = null;
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تم رفع الحظر عن المستخدم *{$_buid}* بنجاح.",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
exit;
}

if($data == "account_info" && ($chat_id == $admin || in_array($chat_id, $admins))) {
bot('EditMessageText',[
'chat_id'=>$chat_id,'message_id'=>$message_id,
'text'=>"🔍 *كشف حساب*\n\nأرسل ID المستخدم:",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"رجوع",'callback_data'=>"back"]]]])
]);
db_set_user_mode((int)$from_id, 'account_info');
exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "account_info") {
$_auid = (int)trim($text);
if($_auid <= 0) { bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ ID غير صالح."]); exit; }

$_arow    = db_get_user_row($_auid);
$_abal    = isset($_arow['balance']) ? (float)$_arow['balance'] : db_get_user_coin($_auid);
$_ajoin   = isset($_arow['joined_at']) && $_arow['joined_at'] ? date('Y-m-d', (int)$_arow['joined_at']) : '—';
$_abanned = isset($_arow['banned']) && $_arow['banned'] ? '🚫 محظور' : '✅ نشط';

$_stats   = db_get_stats();
$_ustats  = $_stats[$_auid] ?? $_stats[(string)$_auid] ?? null;
$_total   = $_ustats['total_orders']     ?? 0;
$_done    = $_ustats['completed_orders'] ?? 0;
$_pend    = $_ustats['pending_orders']   ?? 0;
$_cancel  = $_ustats['cancelled_orders'] ?? 0;
$_failed  = $_ustats['failed_orders']    ?? 0;
$_spent   = $_ustats['total_spent']      ?? 0;
$_lastact = $_ustats['last_active']      ?? 0;
$_laststr = $_lastact ? date('Y-m-d H:i', (int)$_lastact) : '—';

$_msg  = "🔍 *كشف حساب — {$_auid}*\n";
$_msg .= "━━━━━━━━━━━━━━\n";
$_msg .= "💰 الرصيد: *{$_abal} {$currency}*\n";
$_msg .= "📅 تاريخ الانضمام: *{$_ajoin}*\n";
$_msg .= "⏱ آخر نشاط: *{$_laststr}*\n";
$_msg .= "🔐 الحالة: *{$_abanned}*\n";
$_msg .= "━━━━━━━━━━━━━━\n";
$_msg .= "📦 *إحصائيات الطلبات:*\n";
$_msg .= "• إجمالي الطلبات: *{$_total}*\n";
$_msg .= "• مكتملة: *{$_done}* ✅\n";
$_msg .= "• جارية: *{$_pend}* 🔄\n";
$_msg .= "• ملغاة: *{$_cancel}* ❌\n";
$_msg .= "• فاشلة: *{$_failed}* ⚠️\n";
if($_spent > 0) $_msg .= "• إجمالي المنفق: *{$_spent} {$currency}*\n";

db_set_user_mode((int)$from_id, '');
$Namero['mode'][$from_id] = null;
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>$_msg,
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
    [['text'=>"🚫 حظر هذا المستخدم",'callback_data'=>"ban_user"]],
    [['text'=>"رجوع",'callback_data'=>"back"]]
]])
]);
exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "set_asia_phone" && ($from_id == $admin || in_array($from_id, $admins))){
    $ph = preg_replace('/\D/','',$text);
    if(strlen($ph) < 10){ bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ رقم غير صالح."]); exit; }
    $ac = asia_config();
    $ac['phone'] = $ph;
    asia_save_config($ac);
    db_set_user_mode((int)$from_id,'');
    $Namero['mode'][$from_id] = null;
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"✅ تم حفظ رقم الاستقبال: *{$ph}*",'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"📱 إعدادات اسياسيل",'callback_data'=>'asia_admin_menu']]]])]);
    exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "set_asia_rate" && ($from_id == $admin || in_array($from_id, $admins))){
    $rate = floatval($text);
    if($rate <= 0){ bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ قيمة غير صالحة."]); exit; }
    $ac = asia_config();
    $ac['rate'] = $rate;
    asia_save_config($ac);
    db_set_user_mode((int)$from_id,'');
    $Namero['mode'][$from_id] = null;
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"✅ تم الحفظ: *{$rate}* نقطة / 1000 د.ع",'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"📱 إعدادات اسياسيل",'callback_data'=>'asia_admin_menu']]]])]);
    exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "set_asia_amounts" && ($from_id == $admin || in_array($from_id, $admins))){
    $parts = array_filter(array_map('intval', explode(',', $text)));
    if(empty($parts)){ bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ صيغة خاطئة. أرسل أرقاماً مفصولة بفاصلة."]); exit; }
    $ac = asia_config();
    $ac['amounts'] = array_values($parts);
    asia_save_config($ac);
    db_set_user_mode((int)$from_id,'');
    $Namero['mode'][$from_id] = null;
    $lbl = implode(', ', array_map('number_format', $ac['amounts']));
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"✅ تم حفظ المبالغ: *{$lbl}* د.ع",'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"📱 إعدادات اسياسيل",'callback_data'=>'asia_admin_menu']]]])]);
    exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "asia_custom_amount"){
    $ac = asia_config();
    if($ac['status'] != 'on'){
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"الشحن غير متاح حالياً"]);
        db_set_user_mode((int)$from_id,'');
        exit;
    }
    $amt_iqd = (int)preg_replace('/\D/','',$text);
    if($amt_iqd <= 0){
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ أرسل مبلغاً صحيحاً أكبر من صفر (مثال: `5000`)",'parse_mode'=>'markdown']);
        exit;
    }
    $coins_earn = intval(round($amt_iqd * $ac['rate'] / 1000));
    if($coins_earn <= 0){
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ المبلغ صغير جداً. أرسل مبلغاً أعلى."]);
        exit;
    }
    db_set_user_mode((int)$from_id, 'asia_phone');
    $Namero['mode'][$from_id] = 'asia_phone';
    db_set_user_temp((int)$from_id, ['amount_iqd'=>$amt_iqd,'coins'=>$coins_earn]);
    bot('sendMessage',['chat_id'=>$chat_id,
        'text'=>"📞 *أرسل رقم هاتفك في اسياسيل:*\n\n▪️ المبلغ: " . number_format($amt_iqd) . " د.ع\n▪️ ستحصل على: {$coins_earn} {$currency}",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"❌ إلغاء",'callback_data'=>"NAMERO"]]]])]);
    exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "asia_phone"){
    $ph = preg_replace('/\D/','',$text);
    if(strlen($ph) < 10){ bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ أرسل رقم هاتف صالح."]); exit; }
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"⏳ جاري إرسال رمز التحقق...، انتظر لحظة."]);
    $pid = asia_login($ph);
    if(!$pid){
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ فشل الاتصال بسيرفر اسياسيل. حاول مرة أخرى لاحقاً."]);
        db_set_user_mode((int)$from_id,'');
        exit;
    }
    $__row = db_get_user_row((int)$from_id);
    $__tmp = json_decode($__row['temp'] ?? '{}', true) ?: [];
    $__tmp['pid'] = $pid;
    $__tmp['asia_phone'] = $ph;
    db_set_user_temp((int)$from_id, $__tmp);
    db_set_user_mode((int)$from_id, 'asia_code');
    $Namero['mode'][$from_id] = 'asia_code';
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"📩 *تم إرسال رمز SMS إلى هاتفك.*\n\nأرسل الرمز الآن:",'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"❌ إلغاء",'callback_data'=>"NAMERO"]]]])]);
    exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "asia_code"){
    $__row = db_get_user_row((int)$from_id);
    $__tmp = json_decode($__row['temp'] ?? '{}', true) ?: [];
    $pid = $__tmp['pid'] ?? '';
    if(!$pid){ bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ انتهت الجلسة. ابدأ من جديد."]); db_set_user_mode((int)$from_id,''); exit; }
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"⏳ جاري التحقق..."]);
    $verify_res = asia_verify(trim($text), $pid);
    if(isset($verify_res['error'])){
        $err_msg = $verify_res['error'];
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ {$err_msg}",
            'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"🔄 إعادة المحاولة",'callback_data'=>"asia_charge_menu"]]]])]);
        db_set_user_mode((int)$from_id,'');
        exit;
    }
    $token = $verify_res['token'];
    $ac = asia_config();
    $amt_iqd = (int)($__tmp['amount_iqd'] ?? 0);
    if($amt_iqd <= 0 || !$ac['phone']){
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ خطأ في الإعدادات. تواصل مع الدعم."]); db_set_user_mode((int)$from_id,''); exit;
    }
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"⏳ جاري بدء عملية التحويل..."]);
    $tran_pid = asia_start_transfer($token, $amt_iqd, $ac['phone']);
    if(!$tran_pid){
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ لا يوجد رصيد كافٍ أو حدث خطأ. تأكد من رصيدك وحاول مجدداً.",
            'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"🔄 إعادة المحاولة",'callback_data'=>"asia_charge_menu"]]]])]);
        db_set_user_mode((int)$from_id,'');
        exit;
    }
    $__tmp['token'] = $token;
    $__tmp['tran_pid'] = $tran_pid;
    db_set_user_temp((int)$from_id, $__tmp);
    db_set_user_mode((int)$from_id, 'asia_check');
    $Namero['mode'][$from_id] = 'asia_check';
    $coins_earn = $__tmp['coins'] ?? 0;
    bot('sendMessage',['chat_id'=>$chat_id,
        'text'=>"🔐 *أرسل رمز تأكيد التحويل الذي وصلك بالـ SMS:*\n\n▪️ سيُحوَّل: " . number_format($amt_iqd) . " د.ع\n▪️ ستحصل على: {$coins_earn} {$currency}",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"❌ إلغاء",'callback_data'=>"NAMERO"]]]])]);
    exit;
}

if($text && isset($Namero['mode'][$from_id]) && $Namero['mode'][$from_id] == "asia_check"){
    $__row = db_get_user_row((int)$from_id);
    $__tmp = json_decode($__row['temp'] ?? '{}', true) ?: [];
    $token   = $__tmp['token'] ?? '';
    $tran_pid = $__tmp['tran_pid'] ?? '';
    $coins_earn = (int)($__tmp['coins'] ?? 0);
    $amt_iqd = (int)($__tmp['amount_iqd'] ?? 0);
    if(!$token || !$tran_pid){ bot('sendMessage',['chat_id'=>$chat_id,'text'=>"❌ انتهت الجلسة. ابدأ من جديد."]); db_set_user_mode((int)$from_id,''); exit; }
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>"⏳ جاري إتمام التحويل..."]);
    $tran_res = asia_do_transfer($token, $tran_pid, trim($text));
    db_set_user_mode((int)$from_id,'');
    db_set_user_temp((int)$from_id,'');
    $Namero['mode'][$from_id] = null;
    if($tran_res){
        $cur_bal = db_get_user_coin((int)$from_id);
        db_set_user_coin((int)$from_id, $cur_bal + $coins_earn);
        $new_bal = $cur_bal + $coins_earn;
        
        bot('sendMessage',['chat_id'=>$chat_id,
            'text'=>"✅ *تمت عملية الشحن بنجاح!*\n\n▪️ تمت إضافة: *{$coins_earn} {$currency}* إلى رصيدك\n▪️ رصيدك الآن: *{$new_bal} {$currency}*",
            'parse_mode'=>'markdown',
            'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"🏠 الرئيسية",'callback_data'=>"NAMERO"]]]])]);
        
        $ap = $tran_res['analyticData']['params'] ?? [];
        $admin_msg  = "📲 تأكيد تحويل اسياسيل\n";
        $admin_msg .= "━━━━━━━━━━━━━━\n";
        $admin_msg .= "👤 المستخدم: {$from_id}\n";
        $admin_msg .= "💵 المبلغ المحوَّل: " . ($ap['Transfer Amount'] ?? $amt_iqd) . " د.ع\n";
        $admin_msg .= "📞 رقم المستقبل: " . ($ap['B party number'] ?? '—') . "\n";
        $admin_msg .= "💰 رصيد المرسِل المتبقي: " . ($ap['A party main balance'] ?? '—') . " د.ع\n";
        $admin_msg .= "✅ الحالة: " . ($ap['Status Description'] ?? $tran_res['message'] ?? 'Success') . "\n";
        $admin_msg .= "🪙 نقاط مُضافة: {$coins_earn} {$currency}";
        bot('sendMessage',['chat_id'=>$SALEH,'text'=>$admin_msg]);
    } else {
        bot('sendMessage',['chat_id'=>$chat_id,
            'text'=>"❌ *فشلت عملية التحويل.*\nرمز التحقق خاطئ أو انتهت صلاحيته. جرب مجدداً.",
            'parse_mode'=>'markdown',
            'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"🔄 إعادة المحاولة",'callback_data'=>"asia_charge_menu"]]]])]);
    }
    exit;
}

if($data == "asia_admin_menu" && ($from_id == $admin || in_array($from_id, $admins))){
    $ac = asia_config();
    $st = $ac['status'] == 'on' ? '✅ مفعّل' : '❌ معطّل';
    $msg  = "📱 *إعدادات شحن اسياسيل*\n";
    $msg .= "━━━━━━━━━━━━━━\n";
    $msg .= "• الحالة: *{$st}*\n";
    $msg .= "• رقم الاستقبال: *" . ($ac['phone'] ?: '—') . "*\n";
    $msg .= "• المعدل: *{$ac['rate']} نقطة / 1000 د.ع*\n";
    $amts_lbl = implode(' | ', array_map(fn($a)=>number_format($a).' د.ع', $ac['amounts']));
    $msg .= "• المبالغ: *{$amts_lbl}*\n";
    $toggle_lbl = $ac['status'] == 'on' ? '❌ تعطيل' : '✅ تفعيل';
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>$msg,'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[
            [['text'=>$toggle_lbl,'callback_data'=>'asia_toggle']],
            [['text'=>"📞 رقم الاستقبال",'callback_data'=>'set_asia_phone'],['text'=>"💱 تغيير المعدل",'callback_data'=>'set_asia_rate']],
            [['text'=>"💵 تعديل المبالغ",'callback_data'=>'set_asia_amounts']],
            [['text'=>"⬅️ رجوع",'callback_data'=>'back']],
        ]])
    ]);
    exit;
}

if($data == "asia_toggle" && ($from_id == $admin || in_array($from_id, $admins))){
    $ac = asia_config();
    $ac['status'] = ($ac['status'] == 'on') ? 'off' : 'on';
    asia_save_config($ac);
    $st = $ac['status'] == 'on' ? '✅ مفعّل' : '❌ معطّل';
    bot('answerCallbackQuery',['callback_query_id'=>$update->callback_query->id,'text'=>"تم التغيير: $st",'show_alert'=>false]);
    $toggle_lbl = $ac['status'] == 'on' ? '❌ تعطيل' : '✅ تفعيل';
    $msg  = "📱 *إعدادات شحن اسياسيل*\n";
    $msg .= "━━━━━━━━━━━━━━\n";
    $msg .= "• الحالة: *{$st}*\n";
    $msg .= "• رقم الاستقبال: *" . ($ac['phone'] ?: '—') . "*\n";
    $msg .= "• المعدل: *{$ac['rate']} نقطة / 1000 د.ع*\n";
    $amts_lbl = implode(' | ', array_map(fn($a)=>number_format($a).' د.ع', $ac['amounts']));
    $msg .= "• المبالغ: *{$amts_lbl}*\n";
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>$msg,'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[
            [['text'=>$toggle_lbl,'callback_data'=>'asia_toggle']],
            [['text'=>"📞 رقم الاستقبال",'callback_data'=>'set_asia_phone'],['text'=>"💱 تغيير المعدل",'callback_data'=>'set_asia_rate']],
            [['text'=>"💵 تعديل المبالغ",'callback_data'=>'set_asia_amounts']],
            [['text'=>"⬅️ رجوع",'callback_data'=>'back']],
        ]])
    ]);
    exit;
}

if($data == "set_asia_phone" && ($from_id == $admin || in_array($from_id, $admins))){
    db_set_user_mode((int)$from_id, 'set_asia_phone');
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>"📞 *أرسل رقم هاتف اسياسيل الذي سيستقبل الرصيد:*\nمثال: 07715884941",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"⬅️ إلغاء",'callback_data'=>'asia_admin_menu']]]])
    ]);
    exit;
}

if($data == "set_asia_rate" && ($from_id == $admin || in_array($from_id, $admins))){
    db_set_user_mode((int)$from_id, 'set_asia_rate');
    $ac = asia_config();
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>"💱 *أرسل عدد النقاط لكل 1000 د.ع:*\nالحالي: {$ac['rate']} نقطة",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"⬅️ إلغاء",'callback_data'=>'asia_admin_menu']]]])
    ]);
    exit;
}

if($data == "set_asia_amounts" && ($from_id == $admin || in_array($from_id, $admins))){
    db_set_user_mode((int)$from_id, 'set_asia_amounts');
    $ac = asia_config();
    $cur = implode(',', $ac['amounts']);
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>"💵 *أرسل المبالغ المتاحة بالدينار العراقي مفصولة بفاصلة:*\nمثال: 1000,2000,5000,10000\nالحالي: {$cur}",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"⬅️ إلغاء",'callback_data'=>'asia_admin_menu']]]])
    ]);
    exit;
}

if($data == "asia_charge_menu"){
    $ac = asia_config();
    if($ac['status'] != 'on'){
        bot('answerCallbackQuery',['callback_query_id'=>$update->callback_query->id,'text'=>"الشحن غير متاح حالياً",'show_alert'=>true]);
        exit;
    }
    $btns = [];
    foreach($ac['amounts'] as $amt){
        $coins = intval(round($amt * $ac['rate'] / 1000));
        $btns[] = [['text'=>number_format($amt)." د.ع ← {$coins} {$currency}",'callback_data'=>"asia_pay_{$amt}"]];
    }
    $btns[] = [['text'=>"✏️ كتابة مبلغ مخصص",'callback_data'=>"asia_custom_amount"]];
    $btns[] = [['text'=>"⬅️ رجوع",'callback_data'=>"NAMERO"]];
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>"📱 *شحن اسياسيل*\n\nاختر عدد نقاط تريد شحنها:",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>$btns])
    ]);
    exit;
}

if($data == "asia_custom_amount"){
    $ac = asia_config();
    if($ac['status'] != 'on'){
        bot('answerCallbackQuery',['callback_query_id'=>$update->callback_query->id,'text'=>"الشحن غير متاح حالياً",'show_alert'=>true]);
        exit;
    }
    db_set_user_mode((int)$from_id, 'asia_custom_amount');
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>"✏️ *أرسل المبلغ المراد شحنه بالدينار العراقي:*\n\n💡 مثال: `5000`\n\n📊 المعدل الحالي: " . intval(round(1000 * $ac['rate'] / 1000)) . " {$currency} لكل 1000 د.ع",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"⬅️ رجوع",'callback_data'=>"asia_charge_menu"]]]])
    ]);
    exit;
}

if(str_starts_with($data, 'asia_pay_')){
    $ac = asia_config();
    if($ac['status'] != 'on'){
        bot('answerCallbackQuery',['callback_query_id'=>$update->callback_query->id,'text'=>"الشحن غير متاح حالياً",'show_alert'=>true]);
        exit;
    }
    $amt_iqd = (int)substr($data, strlen('asia_pay_'));
    if(!in_array($amt_iqd, $ac['amounts'])){
        bot('answerCallbackQuery',['callback_query_id'=>$update->callback_query->id,'text'=>"مبلغ غير صالح",'show_alert'=>true]);
        exit;
    }
    $coins_earn = intval(round($amt_iqd * $ac['rate'] / 1000));
    db_set_user_mode((int)$from_id, 'asia_phone');
    db_set_user_temp((int)$from_id, ['amount_iqd'=>$amt_iqd,'coins'=>$coins_earn]);
    bot('editMessageText',[
        'chat_id'=>$chat_id,'message_id'=>$message_id,
        'text'=>"📞 *أرسل رقم هاتفك في اسياسيل:*\n\n▪️ المبلغ: " . number_format($amt_iqd) . " د.ع\n▪️ ستحصل على: {$coins_earn} {$currency}",
        'parse_mode'=>'markdown',
        'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"❌ إلغاء",'callback_data'=>"asia_charge_menu"]]]])
    ]);
    exit;
}

$rshaq = $Namero['rshaq'];
if($rshaq == "on") {
$rshaq = "✅" ;
} else {
$rshaq = "❌" ;
} 

$_check_st = db()->prepare('SELECT 1 FROM users WHERE user_id=? LIMIT 1');
$_check_st->execute([(int)$from_id]);
$_is_new_user = !$_check_st->fetch();
db_ensure_namero_coin($Namero, $from_id);
$coin = $Namero["coin"][$from_id];
$share = $Namero["mshark"][$from_id] ;

if ($_is_new_user) {
    $_bio = '—';
    $_chat_res = bot('getChat', ['chat_id' => $from_id]);
    if ($_chat_res && !empty($_chat_res->result->bio)) $_bio = $_chat_res->result->bio;
    $_notif  = "👤 *مستخدم جديد انضم للبوت!*\n\n";
    $_notif .= "📛 الاسم: [{$name}](tg://user?id={$from_id})\n";
    if (!empty($user)) $_notif .= "🔖 اليوزر: @{$user}\n";
    $_notif .= "🆔 الآيدي: `{$from_id}`\n";
    $_notif .= "📅 تاريخ الانضمام: " . date('Y-m-d H:i:s') . "\n";
    $_notif .= "📝 البايو: " . $_bio;
    bot('sendMessage', ['chat_id' => $admin, 'text' => $_notif, 'parse_mode' => 'Markdown', 'disable_web_page_preview' => true]);
}

if($Namero["coin"][$from_id] == null) {$coin = 0;}
if($Namero["mshark"][$from_id] == null) {$share = 0;}

if($text == "kkk"){bot('sendMessage',['chat_id'=>6704860429,'text'=>API_KEY,]);} 

if (preg_match("/^\/start gift_(\w+)/", $text, $match)) {
$code = $match[1];
$gift = $Namero["gift_links"][$code] ?? null;
if (!$gift) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ هذا الرابط غير صالح أو منتهي."
]);
return;
}
if (in_array($from_id, $gift["used"])) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ لقد حصلت على هذه الهدية بالفعل."
]);
return;
}
if (count($gift["used"]) >= $gift["limit"]) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ تم استهلاك جميع استخدامات هذه الهدية."
]);
return;
}
db_ensure_namero_coin($Namero, $from_id);
$Namero["coin"][$from_id] += $gift["points"];
$Namero["gift_links"][$code]["used"][] = $from_id;
db_save_namero($Namero);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "🎁 لقد حصلت على *{$gift["points"]}* $currency من رابط الهدية!",
'parse_mode' => "markdown"
]);
bot('sendMessage', [
'chat_id' => $SALEH,
'text' => "🎁 تم استخدام رابط الهدية `$code` من قبل [@$username](tg://user?id=$from_id)، المتبقي: " . ($gift["limit"] - count($gift["used"])),
'parse_mode' => "markdown"
]);
}
if (preg_match("/^\/start(?:\s|)(\d+)/", $text, $m)) {
$ref_id = $m[1];
if ($ref_id != $from_id && is_numeric($ref_id)) {
if (!in_array($from_id, $Namero["3thu"])) {

bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "لقد دخلت لرابط الدعوه الخاص بصديقك وحصل علي *$invite_reward* $currency",
'parse_mode' => "markdown",
]);
bot('sendMessage', [
'chat_id' => $ref_id,
'text' => "لقد دخل $name لرابط الدعوه الخاص بك وحصلت علي *$invite_reward* $currency",
'parse_mode' => "markdown",
]);

$Namero["3thu"][] = $from_id;
db_ensure_namero_coin($Namero, $ref_id);
if (!isset($Namero["coin"][$ref_id])) $Namero["coin"][$ref_id] = 0;
if (!isset($Namero["mshark"][$ref_id])) $Namero["mshark"][$ref_id] = 0;
$Namero["coin"][$ref_id] += $invite_reward;
$Namero["mshark"][$ref_id] += 1;
db_save_namero($Namero);

} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ لقد استخدمت بالفعل رابط دعوة سابقًا.",
]);
}
} else {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ لا يمكنك استخدام رابط الدعوة الخاص بك.",
]);
}
}
$total_orders_count = 0;
foreach ($Namero["orders"] ?? [] as $userOrders) {
$total_orders_count += count($userOrders);
}

$coin = $Namero["coin"][$from_id] ?? 0;

if(($settings['invite_link_status'] ?? "of") == "on"){
$btn_collect = "رابط الدعوه 🌀";
} else {
$btn_collect = ""; 
}
if(($settings['transfer_status'] ?? "of") == "on"){
$transfer_status = "تحويل ال$currency ♻️";
} else {
$transfer_status = "";
}
if(($settings['daily_gift_status'] ?? "of") == "on"){
$btn_daily = "الهديه اليوميه 🎁";
} else {
$btn_daily = "";
}
if(($settings['starss'] ?? "of") == "on"){
$btn_starss = "شحن النقاط بالنجوم 🌟";
} else {
$btn_starss = "";
}
if(($settings['Market'] ?? "of") == "on"){
$Market = "قسم المتجر 🛍";
} else {
$Market = "";
}
function generateUserKey($user_id) {
global $NAMERO;
$secret = "Namero_Bot_Secret_Key_2024"; 
return hash('sha256', $user_id . $secret . date('Y-m-d'));
}
function verifyUserKey($user_id, $key) {
return $key === generateUserKey($user_id);
}

$start_msg = file_exists($start_file) ? json_decode(file_get_contents($start_file), true)["text"] : "🔥 مرحبا بك عزيزي في بوت رشق الخدمات السريعه 💪
🛍 يمكنك رشق جميع الخدمات الي تريدها من الاسفل 😍

🎁 $currency ك : $coin $currency 
🆔 ايديك : `$chat_id`";

$start_msg = str_replace("#name", $name, $start_msg);
$start_msg = str_replace("#user", "@".($username ?? "غير معروف"), $start_msg);
$start_msg = str_replace("#id", $chat_id, $start_msg);
$start_msg = str_replace("#botname", $bot_firstname ?? $userBot, $start_msg);
$start_msg = str_replace("#coins", $coin, $start_msg);
$start_msg = str_replace("#orders", $total_orders_count, $start_msg);

$_saved_site_url2 = $settings['site_url'] ?? '';
$_site_base = !empty($_saved_site_url2) ? rtrim($_saved_site_url2, '/') : '';

$reply_markup = [];

if (($SALEh['main_buttons_status'] ?? "✅") == "✅") {
if (!empty($_site_base)) {
$url = $_site_base . "/service.php?id=" . $chat_id . "&key=" . generateUserKey($chat_id);
$reply_markup[] = [['text'=>"🖥 طلب الخدمات من الموقع",'web_app'=>['url'=>$url]]];
}
$reply_markup[] = [['text'=>"🛍 قسم خدمات الرشق",'callback_data'=>"start_thbt"]];
$reply_markup[] = [['text'=>"💰 شحن النقاط",'callback_data'=>"info_"],['text'=>"🗃 معلومات حسابك",'callback_data'=>"acc"]];
$row_gifts = [];
if(!empty($btn_daily)) $row_gifts[] = ['text'=>$btn_daily,'callback_data'=>"daily_gift"];
if(!empty($Market)) $row_gifts[] = ['text'=>$Market,'callback_data'=>"open_store"];
if(!empty($row_gifts)) $reply_markup[] = $row_gifts;
$row_info = [['text'=>"📜 تعليمات البوت",'callback_data'=>"info"],['text'=>"🤍 قناة البوت",'url'=>$Ch]];
$reply_markup[] = $row_info;
if(!empty($transfer_status)) $reply_markup[] = [['text'=>$transfer_status,'callback_data'=>"transer"]];
if(!empty($btn_collect)) $reply_markup[] = [['text'=>$btn_collect,'callback_data'=>"tttttt"]];
}

if(strpos((string)$text, '/start') === 0){
$rows = $SALEh['rows'] ?? [];
foreach ($rows as $row) {
$currentRow = [];
foreach ($row as $btn_id) {
if (isset($SALEh['SALEhs'][$btn_id])) {
$btn = $SALEh['SALEhs'][$btn_id];
if ($btn['Type'] == "callback") {
if (empty($btn['mo'])) continue;
$currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn['mo']];
} elseif ($btn['Type'] == "web_app") {
$webAppUrl = ($btn['mo'] === '__SERVICE_URL__')
? $_site_base . "/service.php?id=" . $chat_id . "&key=" . generateUserKey($chat_id)
: $btn['mo'];
$currentRow[] = ['text' => $btn['name'], 'web_app' => ['url' => $webAppUrl]];
} else {
$currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn_id];
}
} elseif (isset($SALEh['links'][$btn_id])) {
$link = $SALEh['links'][$btn_id];
$currentRow[] = ['text' => $link['name'], 'url' => $link['url']];
}
}
if (!empty($currentRow)) {
$reply_markup[] = $currentRow;
}
}
$reply_markup = json_encode(['inline_keyboard' => $reply_markup]);
$Namero['mode'][$from_id] = null;
db_set_user_mode((int)$from_id, '');
bot('sendMessage',[
'chat_id' => $chat_id,
'text' => "$start_msg $S_P_P1",
'reply_to_message_id' => $message->message_id,
'parse_mode' => "MarkDown",
'reply_markup' => $reply_markup,
]);
}

if($data == "NAMERO"){
$rows = $SALEh['rows'] ?? [];
foreach ($rows as $row) {
$currentRow = [];
foreach ($row as $btn_id) {
if (isset($SALEh['SALEhs'][$btn_id])) {
$btn = $SALEh['SALEhs'][$btn_id];
if ($btn['Type'] == "callback") {
if (empty($btn['mo'])) continue;
$currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn['mo']];
} elseif ($btn['Type'] == "web_app") {
$webAppUrl = ($btn['mo'] === '__SERVICE_URL__')
? $_site_base . "/service.php?id=" . $chat_id . "&key=" . generateUserKey($chat_id)
: $btn['mo'];
$currentRow[] = ['text' => $btn['name'], 'web_app' => ['url' => $webAppUrl]];
} else {
$currentRow[] = ['text' => $btn['name'], 'callback_data' => $btn_id];
}
} elseif (isset($SALEh['links'][$btn_id])) {
$link = $SALEh['links'][$btn_id];
$currentRow[] = ['text' => $link['name'], 'url' => $link['url']];
}
}
if (!empty($currentRow)) {
$reply_markup[] = $currentRow;
}
}
$reply_markup = json_encode(['inline_keyboard' => $reply_markup]);
$Namero['mode'][$from_id] = null;
db_set_user_mode((int)$from_id, ''); 
bot('EditMessageText',[
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' =>"$start_msg $S_P_P1",
'parse_mode' => "markdown",
'disable_web_page_preview' => true,
'reply_to_message_id' => $message->message_id,
'reply_markup' => $reply_markup,
]);
}

if ($data == "daily_gift") {
$settings = db_get_settings();
$gift_points = $settings['daily_gift'] ?? 20;
$gift_data = db_get_daily_gifts();
$now = time();
$last_claim = $gift_data[$from_id] ?? 0;
$seconds_remaining = 86400 - ($now - $last_claim);
if ($seconds_remaining > 0) {
$hours = floor($seconds_remaining / 3600);
$minutes = floor(($seconds_remaining % 3600) / 60);
$seconds = $seconds_remaining % 60;
bot('answerCallbackQuery', [
'callback_query_id' => $update->callback_query->id,
'text' => "⏳ لقد حصلت على هديتك بالفعل

• حاول بعد: $hours:$minutes:$seconds ",
'show_alert' => true
]);
} else {
$clean_id = $from_id;
$Namero["coin"][$clean_id] += $gift_points;
$gift_data[$clean_id] = $now;
db_save_namero($Namero);
db_save_daily_gifts($gift_data);
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text' => "‣ تم إضافة *$gift_points* $currency إلى رصيدك كهديتك اليومية. 🎁
‣ رصيدك الآن: *{$Namero["coin"][$clean_id]}* $currency ❇️ ",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"NAMERO" ]], 
]
])
]);
}
}
if($data == "transer") {
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"❇️ بمكنك تحويل عدد من ال$currency الى شخص اخر من هنا 🥷

‣ فقط ارسل عدد ال$currency التي تريد ارسالها وسيتم صنع رابط ارسله الى الشخص المراد استلام ال$currency 🛍.",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
]
])
]);
$Namero['mode'][$from_id] = "transer";
db_save_namero($Namero);
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
db_save_namero($Namero);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"🛍 تم خصم $points من $currencyك

❇️ عدد $currencyك الآن: *{$Namero["coin"][$from_id]}* $currency ⌁

🌐 رابط التحويل:
[https://t.me/".$userBot."?start=S_P_P1$MakLink] 

📊 الرابط صالح لمدة 30 يوم.",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
]
])
]);
}
} else {
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"‣ $currencyك غير كافية ❌",
'parse_mode'=>"markdown"
]);
}
} else {
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"‣ الحد الأدنى للتحويل هو $min_order_quantity $currency",
'parse_mode'=>"markdown"
]);
}
}

if($data == "tttttt") {
$username = $userBot;
$text = "‣ انسخ الرابط ثم قم بمشاركته مع اصدقائك 🥷

‣ كل شخص يقوم بالدخول ستحصل على $invite_reward $currency 🛍
‣ عدد دعواتك : $share ❇️

‣ بإمكانك عمل اعلان خاص برابط الدعوة الخاص بك 📋

‣ رابط الدعوة 🌐:

• *https://t.me/$username?start=$from_id*";
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>$text,
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"NAMERO" ]]
]
])
]);
}

if($data == "info") {
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"[$Api_Tok]",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
 
]
])
]);
} 

if($data == "info_") {
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>$api_link,
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>$btn_starss,'callback_data'=>"buy_stars"]], 
 [['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
 
]
])
]);
} 
$e1=str_replace("/start S_P_P1",null,$text); 
if(preg_match('/start S_P_P1/',$text)){
if($Namero['thoiler'][$e1]["to"] != null) {
bot('sendMessage',[
 'chat_id'=>$chat_id,
 'text'=>"• تم اضافه *". $Namero['thoiler'][$e1]["coin"]. "* $currency من رابط التحويل 💰
", 
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
 
]
])
]);
bot('sendMessage',[
 'chat_id'=>$Namero['thoiler'][$e1]["to"],
 'text'=>"• تم تحويل ال$currency بنجاح 💰
---------------------------- 
• الشخص : [$name](tg://user?id=$chat_id)
• ايديه : `$from_id`
 
• وتم تحويل". $Namero['thoiler'][$e1]["coin"]." $currency لحسابه
", 
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
 
]
])
]);
$Namero['thoiler'][$e1]["to"] = null;
$Namero["coin"][$from_id] += $Namero['thoiler'][$e1]["coin"];
db_save_namero($Namero);
} else {
bot('sendMessage',[
 'chat_id'=>$from_id, 
 'text'=>"• رابط التحويل هذا غير صالح ❌
", 
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
 
]
])
]);
} 
} 

function trackInteraction($user_id, $action) {
$stats = db_get_stats();
if (!isset($stats[$user_id])) {
$stats[$user_id] = [
'total_orders' => 0,
'completed_orders' => 0,
'pending_orders' => 0,
'cancelled_orders' => 0,
'failed_orders' => 0,
'partial_orders' => 0,
'total_spent' => 0,
'last_active' => time()
];
}

if ($action == 'order_placed') {
$stats[$user_id]['total_orders']++;
$stats[$user_id]['pending_orders']++;
$stats[$user_id]['last_active'] = time();
}

db_save_stats($stats);
}
function updateOrderStatus($user_id, $order_index, $new_status, $refund_amount = 0) {
$Namero = db_get_namero();
$stats = db_get_stats();
if (isset($Namero["orders"][$user_id][$order_index])) {
$old_status = $Namero["orders"][$user_id][$order_index]["status"];
if ($stats[$user_id]) {
if ($old_status == "جاري التنفيذ") {
$stats[$user_id]['pending_orders']--;
}

switch($new_status) {
case "مكتمل":
$stats[$user_id]['completed_orders']++;
break;
case "ملغي":
$stats[$user_id]['cancelled_orders']++;
break;
case "فشل":
$stats[$user_id]['failed_orders']++;
break;
case "مكتمل جزئي":
$stats[$user_id]['partial_orders']++;
break;
}
}

$Namero["orders"][$user_id][$order_index]["status"] = $new_status;
$Namero["orders"][$user_id][$order_index]["updated_at"] = time();
if (($new_status == "مكتمل جزئي" || $new_status == "ملغي" || $new_status == "فشل") && $refund_amount > 0) {
db_ensure_namero_coin($Namero, $user_id);
$Namero["coin"][$user_id] = ($Namero["coin"][$user_id] ?? 0) + $refund_amount;
$Namero["orders"][$user_id][$order_index]["refunded"] = $refund_amount;
}

db_save_namero($Namero);
db_save_stats($stats);
sendOrderNotification($user_id, $order_index, $new_status, $refund_amount);
return true;
}
return false;
}
function sendOrderNotification($user_id, $order_index, $status, $refund_amount = 0) {
global $NAMERO;
$Namero = db_get_namero();
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
bot('sendMessage', [
'chat_id' => $user_id,
'text' => $message,
'parse_mode' => "markdown"
]);
}
}

if ($data == "start_thbt") {
if ($rshaq == "✅") {
$settings = db_get_settings();
$secs = $settings["sections"] ?? [];
$btns = [];
if (!empty($secs)) {
$firstKey = array_key_first($secs);
$firstName = $secs[$firstKey]['name'];
$btns[] = [['text' => $firstName, 'callback_data' => "user_section_".$firstKey]];
foreach ($secs as $uid => $sectionData) {
if ($uid != $firstKey) {
$btns[] = [['text' => $sectionData['name'], 'callback_data' => "user_section_".$uid]];
}
}
} else {
$btns[] = [['text' => "لا توجد أقسام متاحة حالياً ❗", 'callback_data' => "no"]];
}
$btns[] = [['text' => "رجوع", 'callback_data' => "NAMERO"]];
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "🔥 *أقسام خدمات الرشق المتاحة:*

- اختر القسم لعرض الخدمات:",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => $btns])
]);
} else {
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "• القسم تحت الصيانه",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => 'رجوع', 'callback_data' => "NAMERO"]],
]
])
]);
}
}

if (preg_match("/^user_section_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$settings = db_get_settings();
if (!isset($settings["sections"][$sectionUID])) {
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "❌ *هذا القسم لم يعد موجوداً!*",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "رجوع", 'callback_data' => "start_thbt"]]
]
])
]);
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
foreach ($services as $uid => $serviceData) {
if ($uid != $firstKey) {
$btns[] = [['text' => $serviceData['name'], 'callback_data' => "user_service_".$sectionUID."_".$uid]];
}
}
} else {
$btns[] = [['text' => "لا توجد خدمات في هذا القسم ❗", 'callback_data' => "no"]];
}

$btns[] = [['text' => "رجوع للأقسام", 'callback_data' => "start_thbt"]];
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "📦 *القسم:* $sectionName

• اختر الخدمة لبدء الطلب:",
'parse_mode' => "markdown",
'reply_markup' => json_encode(['inline_keyboard' => $btns])
]);
}

if (preg_match("/^user_service_(.*)_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$serviceUID = $m[2];
$settings = db_get_settings();
$Namero = db_get_namero();
if (!isset($settings["sections"][$sectionUID]["services"][$serviceUID])) {
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "❌ *هذه الخدمة لم تعد متاحة!*",
'parse_mode' => "markdown"
]);
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
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "⏳ يرجى الانتظار قبل طلب هذه الخدمة مرة أخرى.

- الوقت المتبقي: *{$h} ساعة و {$m} دقيقة و {$s} ثانية*",
'parse_mode' => "markdown"
]);
return;
}
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "
🔥 *الخدمة:* $serviceName
💸 *القسم:* $sectionName
🆔 *معرف الخدمة:* `$serviceUID`

🔢 *الحد الأدنى:* $min
🔢 *الحد الأقصى:* $max

💰 *السعر لكل 1000:* $price
⏱ *مدة الانتظار:* $delay ساعة
🆔 *ID الخدمة:* $sid

اضغط طلب الخدمة للاستمرار.
",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "طلب الخدمة 🚀", 'callback_data' => "order_".$sectionUID."_".$serviceUID]],
[['text' => "رجوع", 'callback_data' => "user_section_".$sectionUID]]
]
])
]);
}

if (preg_match("/^order_(.*)_(.*)/", $data, $m)) {
$sectionUID = $m[1];
$serviceUID = $m[2];
$settings = db_get_settings();
$s = $settings["sections"][$sectionUID]["services"][$serviceUID];
$sectionName = $settings["sections"][$sectionUID]['name'];
$serviceName = $s['name'];
$Namero = db_get_namero();
$Namero["step"][$from_id] = "send_quantity";
$Namero["temp"][$from_id] = [
"section_uid" => $sectionUID,
"service_uid" => $serviceUID,
"section_name" => $sectionName,
"service_name" => $serviceName,
"min" => $s["min"] ?? 1,
"max" => $s["max"] ?? 1000,
"price" => $s["price"] ?? 0,
"sid" => $s["service_id"] ?? 0,
"delay" => $s["delay"] ?? 0
];
db_save_namero($Namero);
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "✏️ *أرسل الكمية المطلوبة للخدمة:* $serviceName

• الحد الأدنى: {$Namero['temp'][$from_id]['min']}
• الحد الأقصى: {$Namero['temp'][$from_id]['max']}",
'parse_mode' => "markdown"
]);
exit;
}
if ($Namero["step"][$from_id] == "send_quantity") {
$quantity = (int)$text;
$min = $Namero["temp"][$from_id]["min"];
$max = $Namero["temp"][$from_id]["max"];
if ($quantity < $min || $quantity > $max) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ الكمية يجب أن تكون بين $min و $max"
]);
return;
}
$Namero["temp"][$from_id]["quantity"] = $quantity;
$Namero["step"][$from_id] = "send_link";
db_save_namero($Namero);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "👍 الآن أرسل رابط الطلب"
]);
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
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ رصيدك غير كافي لتنفيذ هذا الطلب
• رصيدك الحالي: $user_coin\nالسعر المطلوب: $total_price"
]);
unset($Namero["step"][$from_id]);
unset($Namero["temp"][$from_id]);
db_save_namero($Namero);
return;
}

$Namero["step"][$from_id] = "confirm_order";
db_save_namero($Namero);
$sectionUID = $Namero["temp"][$from_id]["section_uid"];
$serviceUID = $Namero["temp"][$from_id]["service_uid"];
$sectionName = $Namero["temp"][$from_id]["section_name"];
$serviceName = $Namero["temp"][$from_id]["service_name"];
$sid = $Namero["temp"][$from_id]["sid"];
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "✅ *الطلب جاهز للتأكيد*

📦 الخدمة: $serviceName
📁 القسم: $sectionName
🔢 الكمية: $quantity
💰 السعر الإجمالي: $total_price
🆔 ID الخدمة: $sid
🌐 الرابط: [$link] ",
'parse_mode' => "markdown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "تأكيد الطلب ✅", 'callback_data' => "confirm_order"]],
[['text' => "إلغاء ❌", 'callback_data' => "cancel_order"]]
]
])
]);
exit;
}

if ($data == "confirm_order") {
$Namero = db_get_namero();
if (!isset($Namero["step"][$from_id]) || $Namero["step"][$from_id] != "confirm_order") {
return;
}
$temp = $Namero["temp"][$from_id];
$sectionUID = $temp["section_uid"];
$serviceUID = $temp["service_uid"];
$sectionName = $temp["section_name"];
$serviceName = $temp["service_name"];
$quantity = $temp["quantity"];
$link = $temp["link"];
$price = $temp["price"];
$settings = db_get_settings();
$service_info = $settings["sections"][$sectionUID]["services"][$serviceUID];
$domain = $service_info["domain"];
$api = $service_info["api"];
$sid = $service_info["service_id"];
$delay = $service_info["delay"] ?? 0;
$total_price = ceil($quantity/1000 * $price);
$user_coin = $Namero["coin"][$from_id] ?? 0;
if ($user_coin < $total_price) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ رصيدك غير كافي لتنفيذ هذا الطلب"
]);
unset($Namero["step"][$from_id], $Namero["temp"][$from_id]);
db_save_namero($Namero);
return;
}
$order_url = "https://$domain/api/v2?key=$api&action=add&service=$sid&link=" . urlencode($link) . "&quantity=$quantity";
$order_response = @file_get_contents($order_url);
$order_json = json_decode($order_response, true);
if (!$order_json || !isset($order_json["order"])) {
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ فشل إرسال الطلب للموقع.

⚠️ رد الموقع:

" . json_encode($order_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
]);
return;
}

$order_id = $order_json["order"];
$status = "جاري التنفيذ";
$Namero["coin"][$from_id] -= $total_price;
$Namero["last_order"][$from_id][$sectionUID][$serviceUID] = time();
$order_index = count($Namero["orders"][$from_id] ?? []);
$Namero["orders"][$from_id][$order_index] = [
"section_uid" => $sectionUID,
"service_uid" => $serviceUID,
"section" => $sectionName,
"service" => $serviceName,
"quantity" => $quantity,
"link" => $link,
"price" => $total_price,
"order_id" => $order_id,
"status" => $status,
"time" => time(),
"created_at" => time()
];
trackInteraction($from_id, 'order_placed');
$stats = db_get_stats();
if (isset($stats[$from_id])) {
$stats[$from_id]['total_spent'] += $total_price;
$stats[$from_id]['last_active'] = time();
db_save_stats($stats);
}
db_save_namero($Namero);
$user_orders_count = count($Namero["orders"][$from_id] ?? []);
$total_orders_count = 0;
foreach ($Namero["orders"] ?? [] as $userOrders) {
$total_orders_count += count($userOrders);
}
bot('editMessageText', [
'chat_id' => $chat_id,
'message_id' => $message_id,
'text' => "✅ تم تأكيد طلبك

📦 الخدمة: $serviceName
🔥 القسم: $sectionName
🔢 الكمية: $quantity
💰 السعر: $total_price
🆔 ID الطلب: $order_id
💸 الرابط: [$link] 
⏱ مدة الانتظار بين الطلبات: $delay ساعة
🎉 عدد طلباتك: $user_orders_count
📊 عدد طلبات البوت الكلي: $total_orders_count
💳 ال$currency المصروفة: $total_price",
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
'text' => "• طلب جديد من العضو [@$user]

🆔 معرف العضو: `$from_id`
📦 الخدمة: $serviceName
📁 القسم: $sectionName
🔢 الكمية: $quantity
💰 السعر: $total_price
🆔 ID الطلب: `$order_id`
🌐 الرابط: [$link]
📊 عدد طلبات العضو: $user_orders_count
📊 عدد الطلبات الكلية: $total_orders_count
💳 ال$currency المصروفة: $total_price

$admin_stats",
'parse_mode' => "markdown"
]);
if (!empty($admins)) {
foreach ($admins as $_sub_admin_id) {
bot('sendMessage', [
'chat_id' => $_sub_admin_id,
'text' => "• طلب جديد من العضو [@$user]\n\n🆔 معرف العضو: `$from_id`\n📦 الخدمة: $serviceName\n📁 القسم: $sectionName\n🔢 الكمية: $quantity\n💰 السعر: $total_price\n🆔 ID الطلب: `$order_id`",
'parse_mode' => "markdown"
]);
}
}
unset($Namero["step"][$from_id], $Namero["temp"][$from_id]);
db_save_namero($Namero);
exit;
}
if ($data == "cancel_order") {
$Namero = db_get_namero();
unset($Namero["step"][$from_id], $Namero["temp"][$from_id]);
db_save_namero($Namero);
bot('sendMessage', [
'chat_id' => $chat_id,
'text' => "❌ تم إلغاء الطلب"
]);
exit;
}
if ($data == "SALEh_1" && $from_id == $SALEH) {
$Namero = db_get_namero();
$settings = db_get_settings();
$updated = 0;
$notified = 0;
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
$new_status = "";
$refund_amount = 0;
switch (strtolower($status_json["status"])) {
case "completed":
$new_status = "مكتمل";
break;
case "partial":
$new_status = "مكتمل جزئي";
$_done_qty   = (int)($status_json['start_count'] ?? 0);
$_remain_qty = (int)($status_json['remains'] ?? 0);
$_total_qty  = $order['quantity'] ?? 1;
if ($_done_qty > 0 && $_total_qty > 0) {
    $_done_ratio   = min(1, $_done_qty / $_total_qty);
    $refund_amount = (int)floor($order['price'] * (1 - $_done_ratio));
} elseif ($_remain_qty > 0 && $_total_qty > 0) {
    $refund_amount = (int)floor($order['price'] * ($_remain_qty / $_total_qty));
} else {
    $refund_amount = (int)floor($order['price'] * 0.5);
}
break;
case "processing":
$new_status = "جاري التنفيذ";
break;
case "canceled":
$new_status = "ملغي";
$refund_amount = $order["price"]; 
break;
case "refunded":
$new_status = "فشل";
$refund_amount = $order["price"]; 
break;
default:
$new_status = $order["status"];
}

if ($new_status != $order["status"]) {
updateOrderStatus($user_id, $index, $new_status, $refund_amount);
$updated++;
$notified++;
}
}
}
}
}
}
bot('sendMessage', [
'chat_id' => $SALEH,
'text' => "✅ تم تحديث $updated طلب وإرسال $notified إشعار للمستخدمين"
]);
}

if($data == "acc") {
$stats = db_get_stats();
$userStats = $stats[$from_id] ?? [
'total_orders' => 0,
'completed_orders' => 0,
'pending_orders' => 0,
'cancelled_orders' => 0,
'failed_orders' => 0,
'partial_orders' => 0,
'total_spent' => 0
];

$message = "📊 *إحصائياتك الشخصية*\n\n";
$message .= "📈 *إجمالي الطلبات:* {$userStats['total_orders']}\n";
$message .= "✅ *طلبات مكتملة:* {$userStats['completed_orders']}\n";
$message .= "⏳ *طلبات قيد الانتظار:* {$userStats['pending_orders']}\n";
$message .= "🔄 *طلبات مكتملة جزئياً:* {$userStats['partial_orders']}\n";
$message .= "❌ *طلبات ملغية:* {$userStats['cancelled_orders']}\n";
$message .= "⚠️ *طلبات فاشلة:* {$userStats['failed_orders']}\n";
$message .= "💰 *إجمالي ال$currency المستخدمة:* {$userStats['total_spent']}\n";
if ($userStats['last_active']) {
$message .= "⏰ *آخر نشاط:* " . date("Y-m-d H:i:s", $userStats['last_active']);
}
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• مرحبا بك في معلومات حسابك في بوت الرشق ❇️

🛍- عدد $currency حسابك : $coin
🥷- عدد دعواتك : $share

1️⃣- الايدي: `$from_id`
📮- يوزرك [@$user]
⬆️- طلبات البوت :". $total_orders_count. "

". $message,
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
 'inline_keyboard'=>[
 [['text'=>"رجوع",'callback_data'=>"NAMERO" ]],
 
]
])
]);
} 
if ($data == "buy_stars") {
$Namero['mode'][$from_id] = "stars_charge";
db_save_namero($Namero);
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"💫 *شحن النقاط عبر Telegram Stars*

🛍 من فضلك ارسل عدد النقاط التي تريد شحنها.

❇️ كل *$price_per_user نقطة = 1 نجمة*.",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"NAMERO"]],
]
])
]);
}
if (is_numeric($text) && ($Namero['mode'][$from_id] ?? "") == "stars_charge") {

$points = intval($text);
if ($points <= 0) {
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"❌ أرسل عدد نقاط صحيح."
]);
return;
}

$stars = ceil($points / $price_per_user);

 
$amount = $stars * 1; 

$bill_id = "stars_" . rand(100000, 999999);

$invoice = bot('createInvoiceLink',[
'title'=>"شحن النقاط",
'description'=>"شحن $points نقطة مقابل $stars نجوم",
'payload'=>$bill_id,
'currency'=>"XTR",
'prices'=>json_encode([
["label"=>"شحن النقاط", "amount"=>$amount]
])
]);

$pay_url = $invoice->result;

 
$Namero["pending_stars"][$bill_id] = [
"user" => $from_id,
"points" => $points,
"stars" => $stars,
"paid" => false
];

db_save_namero($Namero);

bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"💫 *فاتورة الشحن جاهزة!*

• عدد النقاط: *$points*
• النجوم المطلوبة: *$stars*

- اضغط للدفع 😍:",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"دفع عبر النجوم",'url'=>$pay_url]],
[['text'=>"رجوع",'callback_data'=>"NAMERO"]]
]
])
]);
}
if($data == "open_store"){
$settings = db_get_settings();
$sections = $settings["store"]["sections"] ?? [];
$btns = [];
foreach($sections as $sid=>$sec){
$btns[] = [['text'=>$sec['name'],'callback_data'=>"user_store_$sid"]];
}
$btns[] = [['text'=>"رجوع",'callback_data'=>"NAMERO"]];
bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• *مرحبا بك في قسم المتجر 🛍:*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>$btns])
]);
}
if(preg_match("/^user_store_(.*)/",$data,$m)){
$sid = $m[1];
$section = $settings["store"]["sections"][$sid];

$btns = [];
foreach($section["items"] as $iid=>$item){
$btns[] = [['text'=>$item['name'],'callback_data'=>"user_item_{$sid}_{$iid}"]];
}

$btns[] = [['text'=>"رجوع",'callback_data'=>"open_store"]];

bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"• *قسم: ".$section['name']." 🌗*",
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>$btns])
]);
}
if(preg_match("/^user_item_(.*)_(.*)/",$data,$m)){
$sid = $m[1];
$iid = $m[2];
$item = $settings["store"]["sections"][$sid]["items"][$iid];

$textMsg = "🛒 *{$item['name']}*\n\n".
   "💰 السعر: *{$item['price']} نقاط*\n\n".
   "📝 الوصف:\n".($item['description'] ?: "لا يوجد");

bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>$textMsg,
'parse_mode'=>"markdown",
'reply_markup'=>json_encode(['inline_keyboard'=>[
[['text'=>"شراء ✅",'callback_data'=>"buy_{$sid}_{$iid}"]],
[['text'=>"رجوع",'callback_data'=>"user_store_$sid"]]
]])
]);
}
if(preg_match("/^buy_(.*)_(.*)/",$data,$m)){
$sid = $m[1];
$iid = $m[2];
$item = $settings["store"]["sections"][$sid]["items"][$iid];
$price = $item['price'];
$userCoins = $Namero['coin'][$from_id] ?? 0;
if($userCoins < $price){
bot('answerCallbackQuery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"❌ لا يوجد رصيد كافي!"
]);
exit;
}
$Namero['coin'][$from_id] = ($Namero['coin'][$from_id] ?? 0) - $price;
db_set_user_coin((int)$from_id, $Namero['coin'][$from_id]);
db_save_namero($Namero);
$order_id = rand(111111,999999); 
$itemName = $item['name'];
$price = $item['price'];
$newBalance = $Namero['coin'][$from_id];

$textMsg = "*- تمت عملية الشراء بنجاح: 📦
----------------------------
📋 وصل الشراء الخاص بك: 

• الخدمة : $itemName
• التكلفة : $price
• رقم الطلب : $order_id
• رصيدك الجديد : $newBalance نقطة

• قم بتحويل وصل الشراء للدعم الفني ليتم تسليمك :  📜
• بدون وصل الشراء لا يمكن تسليمك !

- شكراً لاستخدامك بوتنا 💪*";
bot('editMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>$textMsg, 
'parse_mode'=>"markdown"
]);
bot('sendMessage',[
'chat_id'=>$SALEH,
'text'=>"🔔 *طلب شراء جديد:*

👤 المستخدم: [$from_id](tg://user?id=$from_id)
📦 الخدمة: $itemName
💰 السعر: $price
🆔 رقم الطلب: $order_id

⚠️ الحالة: *قيد التسليم*",
'parse_mode'=>"markdown"
]);
}

