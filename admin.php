<?php
// admin.php - لوحة تحكم المطور
$SALEH = 7816487928;

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$chat_id = $message->chat->id ?? $GLOBALS['chat_id'] ?? null;
$text = $message->text ?? $GLOBALS['text'] ?? null;
$chat_id2 = $update->callback_query->message->chat->id ?? $GLOBALS['chat_id'] ?? null;
$message_id = $update->callback_query->message->message_id ?? $GLOBALS['message_id'] ?? null;
$data = $update->callback_query->data ?? $GLOBALS['data'] ?? null;
$from_id = $message->from->id ?? $GLOBALS['from_id'] ?? null;

global $NAMERO;

if (!isset($NAMERO) || !is_dir($NAMERO)) {
    $bot_info = bot("getMe");
    $bot_id = $bot_info->result->id;
    $NAMERO = __DIR__ . '/NAMERO/' . $bot_id . '/';
    if (!is_dir($NAMERO)) mkdir($NAMERO, 0777, true);
}

// تحميل البيانات
$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
if (!$Namero) $Namero = [];
$settings = file_exists($NAMERO . "api_settings.json") ? json_decode(file_get_contents($NAMERO . "api_settings.json"), true) : [];

$currency = $settings['currency'] ?? "نقاط";
$rshaq = $Namero['rshaq'] ?? "on";
$Api_Tok = $settings['token'] ?? "❌ غير محدد";
$api_link = $settings['domain'] ?? "❌ غير محدد";

// ================== معالجة الأزرار ==================

if ($data == "admin_panel" || $data == "back_to_admin" || $data == "rshqG") {
    $flos = 0;
    $treqa = '';
    if (!empty($api_link) && $api_link != "❌ غير محدد" && !empty($Api_Tok) && $Api_Tok != "❌ غير محدد") {
        $balance = @file_get_contents("https://$api_link/api/v2?key=$Api_Tok&action=balance");
        $bal = json_decode($balance);
        $flos = $bal->balance ?? 0;
        $treqa = $bal->currency ?? '';
    }
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => "التحكم في الخدمات 🗳", 'callback_data' => "Namero_sections"]],
            [['text' => "التحكم في المتجر 🛍", 'callback_data' => "store_sections"]],
            [['text' => "فتح الهدية اليومي", 'callback_data' => "onhdia"], ['text' => "قفل الهدية اليومي", 'callback_data' => "ofhdia"]],
            [['text' => "تعيين عدد الهدية", 'callback_data' => "sethdia"]],
            [['text' => "تعيين أقل عدد للتحويل", 'callback_data' => "sAKTHAR"]],
            [['text' => "إضافة أو خصم رصيد", 'callback_data' => "coins"], ['text' => "تصفير نقاط شخص", 'callback_data' => "msfrn"]],
            [['text' => "صنع كود هدية", 'callback_data' => "hdiamk"]],
            [['text' => "فتح استقبال الرشق", 'callback_data' => "onrshq"], ['text' => "قفل استقبال الرشق", 'callback_data' => "ofrshq"]],
            [['text' => "تعيين توكن للموقع 🎟️", 'callback_data' => "token"], ['text' => "تعيين موقع الرشق ⚙️", 'callback_data' => "SiteDomen"]],
            [['text' => "تعيين قناة الإثباتات 🤖", 'callback_data' => "sCh"]],
            [['text' => "معلومات حول الرشق 📋", 'callback_data' => "infoRshq"]],
            [['text' => "قسم الخدمات 📋", 'callback_data' => "xdmat"]],
            [['text' => "شحن او خصم رصيد", 'callback_data' => "coins"]],
            [['text' => "تحكم من الموقع كامل", 'web_app' => ['url' => "https://" . $_SERVER['HTTP_HOST'] . "/admin_panel.php"]]],
            [['text' => "رجوع", 'callback_data' => "tobot"]]
        ]
    ];
    
    $text_msg = "*◉︙ قسم الرشق*\nيمكنك إضافة أو خصم رصيد\nيمكن قفل استقبال الرشق وفتحها\nيمكنك صنع هدايا\n\nرصيدك في الموقع: *$flos$treqa*\nالحد الأدنى للتحويل: *" . ($settings['AKTHAR'] ?? 20) . "*";
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text_msg,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
    exit;
}

if ($data == "token") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل توكن الموقع:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "sToken";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "SiteDomen") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل رابط موقع الرشق:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "SiteDomen";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "sCh") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل معرف القناة (مثال: @channel):",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "sCh";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "infoRshq") {
    $sTok = $settings['token'] ?? 'لم يتم التعيين';
    $Sdom = $settings['domain'] ?? 'لم يتم التعيين';
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "*◉︙ معلومات الرشق*\n\nتوكن الموقع: `$sTok`\nدومين موقع الرشق: `$Sdom`",
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($data == "xdmat") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "*◉︙ قسم الخدمات*",
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode(['inline_keyboard' => [
            [['text' => 'الأقسام', 'callback_data' => 'qsmsa']],
            [['text' => 'رجوع', 'callback_data' => 'rshqG']]
        ]])
    ]);
    exit;
}

if ($data == "onrshq") {
    if (empty($settings['domain']) || empty($settings['token'])) {
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "⚠️ يجب تعيين توكن الموقع ودومين الموقع أولاً.",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
        ]);
    } else {
        $Namero['rshaq'] = "on";
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "✅ تم فتح استقبال الرشق.",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
        ]);
    }
    exit;
}

if ($data == "ofrshq") {
    $Namero['rshaq'] = "of";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "❌ تم إغلاق استقبال الرشق.",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($data == "onhdia") {
    $Namero['HDIA'] = "on";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "✅ تم تفعيل الهدية اليومية.",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($data == "ofhdia") {
    $Namero['HDIA'] = "of";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "❌ تم إيقاف الهدية اليومية.",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($data == "sethdia") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل عدد الهدية اليومية:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "sethdia";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "sAKTHAR") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل الحد الأدنى للتحويل:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "sAKTHAR";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "coins") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل ايدي الشخص:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "coins";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "msfrn") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل ايدي الشخص لتصفير رصيده:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "msfrn";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "hdiamk") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "• أرسل قيمة الهدية (الرصيد):",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    $Namero['mode'][$from_id] = "hdiamk";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

// ================== معالجة النصوص (إدخال البيانات) ==================

if ($text && $Namero['mode'][$from_id] == "sToken") {
    $settings['token'] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين توكن الموقع.",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "SiteDomen") {
    $domain = parse_url($text, PHP_URL_HOST) ?? $text;
    $settings['domain'] = $domain;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين موقع الرشق: $domain",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "sCh") {
    $channel = ltrim($text, '@');
    $settings['Ch'] = "https://t.me/$channel";
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين قناة الإثباتات: @$channel",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "sethdia" && is_numeric($text)) {
    $settings['daily_gift'] = (int)$text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين عدد الهدية: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "sAKTHAR" && is_numeric($text)) {
    $settings['AKTHAR'] = (int)$text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين الحد الأدنى للتحويل: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "coins") {
    $Namero['mode'][$from_id] = "coins2";
    $Namero['id'][$from_id] = $text;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم حفظ الايدي $text\nأرسل الآن قيمة الرصيد (+ للإضافة، - للخصم).",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "coins2") {
    $amount = (int)$text;
    $target_id = $Namero['id'][$from_id];
    if ($target_id) {
        $Namero['coin'][$target_id] = ($Namero['coin'][$target_id] ?? 0) + $amount;
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ تم إضافة $amount $currency للمستخدم $target_id",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
        ]);
    }
    $Namero['mode'][$from_id] = null;
    $Namero['id'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($text && $Namero['mode'][$from_id] == "msfrn" && is_numeric($text)) {
    $Namero['coin'][$text] = 0;
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تصفير رصيد المستخدم $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "hdiamk" && is_numeric($text)) {
    $Namero['mode'][$from_id] = "hdiamk2";
    $Namero['hd_value'][$from_id] = (int)$text;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم حفظ قيمة الهدية: $text\nأرسل الآن:\nعدد الاستخدام\nاسم الكود\nمثال:\n5\nBERO",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "hdiamk2") {
    $lines = explode("\n", $text);
    $uses = (int)($lines[0] ?? 0);
    $code = trim($lines[1] ?? '');
    $value = $Namero['hd_value'][$from_id] ?? 0;
    
    if ($uses > 0 && !empty($code)) {
        $Namero[$code] = "on|$value|$uses";
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ تم إنشاء الكود: $code\nالقيمة: $value\nعدد المرات: $uses",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ الصيغة غير صحيحة.\nأرسل العدد ثم الكود في سطرين.",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'rshqG']]]])
        ]);
    }
    $Namero['mode'][$from_id] = null;
    $Namero['hd_value'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "tobot") {
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
            [['text' => '📢 قناة البوت', 'url' => $settings['Ch'] ?? 'https://t.me/TJUI9']]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $start_msg,
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode($reply_markup)
    ]);
    exit;
}

// ================== دوال الأقسام والمتجر ==================

if ($data == "qsmsa") {
    $key = ['inline_keyboard' => []];
    foreach ($settings['qsm'] ?? [] as $item) {
        $parts = explode('-', $item);
        $name = $parts[0];
        $id = $parts[1];
        if (($settings['IFWORK>'][$id] ?? '') !== 'NOT') {
            $key['inline_keyboard'][] = [
                ['text' => $name, 'callback_data' => "edits|$id"],
                ['text' => "🗑", 'callback_data' => "delets|$id"]
            ];
        }
    }
    $key['inline_keyboard'][] = [['text' => "+ إضافة قسم جديد", 'callback_data' => "addqsm"]];
    $key['inline_keyboard'][] = [['text' => "رجوع", 'callback_data' => "rshqG"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "*الأقسام الموجودة في البوت*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode($key)
    ]);
    exit;
}

if ($data == "addqsm") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل اسم القسم الجديد:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'xdmat']]]])
    ]);
    $Namero['mode'][$from_id] = "addqsm";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($text && $Namero['mode'][$from_id] == "addqsm") {
    $new_id = "BERO" . rand(0, 999999999999999);
    $settings['qsm'][] = $text . '-' . $new_id;
    $settings['NAMES'][$new_id] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم إضافة القسم: $text\nكود القسم: $new_id",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'xdmat']]]])
    ]);
    exit;
}

if (preg_match("/^edits\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $key = ['inline_keyboard' => []];
    foreach ($settings['xdmaxs'][$section_id] ?? [] as $index => $service) {
        $key['inline_keyboard'][] = [
            ['text' => $service, 'callback_data' => "editss|$section_id|$index"],
            ['text' => "🗑", 'callback_data' => "delets|$section_id|$index"]
        ];
    }
    $key['inline_keyboard'][] = [['text' => "+ إضافة خدمة", 'callback_data' => "add|$section_id"]];
    $key['inline_keyboard'][] = [['text' => "رجوع", 'callback_data' => "rshqG"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "*الخدمات الموجودة في قسم " . ($settings['NAMES'][$section_id] ?? '') . "*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode($key)
    ]);
    exit;
}

if (preg_match("/^add\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $Namero['mode'][$from_id] = "add_service";
    $Namero['add_section'][$from_id] = $section_id;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل اسم الخدمة الجديدة:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "edits|$section_id"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "add_service") {
    $section_id = $Namero['add_section'][$from_id];
    $settings['xdmaxs'][$section_id][] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['add_section'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم إضافة الخدمة: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "edits|$section_id"]]]])
    ]);
    exit;
}

if (preg_match("/^delets\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    if (isset($settings['xdmaxs'][$section_id][$index])) {
        unset($settings['xdmaxs'][$section_id][$index]);
        $settings['xdmaxs'][$section_id] = array_values($settings['xdmaxs'][$section_id]);
        file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    }
    bot('answerCallbackQuery', ['callback_query_id' => $update->callback_query->id, 'text' => "✅ تم الحذف"]);
    // إعادة عرض القسم
    $key = ['inline_keyboard' => []];
    foreach ($settings['xdmaxs'][$section_id] ?? [] as $idx => $service) {
        $key['inline_keyboard'][] = [
            ['text' => $service, 'callback_data' => "editss|$section_id|$idx"],
            ['text' => "🗑", 'callback_data' => "delets|$section_id|$idx"]
        ];
    }
    $key['inline_keyboard'][] = [['text' => "+ إضافة خدمة", 'callback_data' => "add|$section_id"]];
    $key['inline_keyboard'][] = [['text' => "رجوع", 'callback_data' => "rshqG"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "*الخدمات الموجودة في قسم " . ($settings['NAMES'][$section_id] ?? '') . "*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode($key)
    ]);
    exit;
}

if (preg_match("/^editss\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $service_name = $settings['xdmaxs'][$section_id][$index] ?? '';
    
    $key = [
        'inline_keyboard' => [
            [['text' => "تعيين سعر الخدمة", 'callback_data' => "setprice|$section_id|$index"]],
            [['text' => "تعيين الحد الأدنى", 'callback_data' => "setmin|$section_id|$index"]],
            [['text' => "تعيين الحد الأقصى", 'callback_data' => "setmix|$section_id|$index"]],
            [['text' => "تعيين وصف الخدمة", 'callback_data' => "setdes|$section_id|$index"]],
            [['text' => "تعيين ID الخدمة", 'callback_data' => "setid|$section_id|$index"]],
            [['text' => "تعيين رابط الموقع", 'callback_data' => "setWeb|$section_id|$index"]],
            [['text' => "تعيين API KEY", 'callback_data' => "setkey|$section_id|$index"]],
            [['text' => "حذف الخدمة", 'callback_data' => "delt|$section_id|$index"]],
            [['text' => "رجوع", 'callback_data' => "edits|$section_id"]]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "*إعدادات الخدمة: $service_name*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode($key)
    ]);
    exit;
}

if (preg_match("/^setprice\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $Namero['mode'][$from_id] = "setprice";
    $Namero['set_data'][$from_id] = "$section_id|$index";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل سعر الخدمة (لكل 1000):",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setprice" && is_numeric($text)) {
    $parts = explode('|', $Namero['set_data'][$from_id]);
    $section_id = $parts[0];
    $index = (int)$parts[1];
    $settings['S3RS'][$section_id][$index] = $text / 1000;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين السعر: " . ($text / 1000) . " لكل 1000",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if (preg_match("/^setmin\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $Namero['mode'][$from_id] = "setmin";
    $Namero['set_data'][$from_id] = "$section_id|$index";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل الحد الأدنى للخدمة:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setmin" && is_numeric($text)) {
    $parts = explode('|', $Namero['set_data'][$from_id]);
    $section_id = $parts[0];
    $index = (int)$parts[1];
    $settings['min'][$section_id][$index] = (int)$text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين الحد الأدنى: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if (preg_match("/^setmix\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $Namero['mode'][$from_id] = "setmix";
    $Namero['set_data'][$from_id] = "$section_id|$index";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل الحد الأقصى للخدمة:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setmix" && is_numeric($text)) {
    $parts = explode('|', $Namero['set_data'][$from_id]);
    $section_id = $parts[0];
    $index = (int)$parts[1];
    $settings['mix'][$section_id][$index] = (int)$text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين الحد الأقصى: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if (preg_match("/^setid\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $Namero['mode'][$from_id] = "setid";
    $Namero['set_data'][$from_id] = "$section_id|$index";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل ID الخدمة:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setid") {
    $parts = explode('|', $Namero['set_data'][$from_id]);
    $section_id = $parts[0];
    $index = (int)$parts[1];
    $settings['IDSSS'][$section_id][$index] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين ID الخدمة: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if (preg_match("/^setkey\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $Namero['mode'][$from_id] = "setkey";
    $Namero['set_data'][$from_id] = "$section_id|$index";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل API KEY للخدمة:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setkey") {
    $parts = explode('|', $Namero['set_data'][$from_id]);
    $section_id = $parts[0];
    $index = (int)$parts[1];
    $settings['key'][$section_id][$index] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين API KEY.",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if (preg_match("/^setWeb\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $Namero['mode'][$from_id] = "setWeb";
    $Namero['set_data'][$from_id] = "$section_id|$index";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل رابط الموقع:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setWeb") {
    $parts = explode('|', $Namero['set_data'][$from_id]);
    $section_id = $parts[0];
    $index = (int)$parts[1];
    $domain = parse_url($text, PHP_URL_HOST) ?? $text;
    $settings['Web'][$section_id][$index] = $domain;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين رابط الموقع: $domain",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if (preg_match("/^setdes\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    $Namero['mode'][$from_id] = "setdes";
    $Namero['set_data'][$from_id] = "$section_id|$index";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل وصف الخدمة:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setdes") {
    $parts = explode('|', $Namero['set_data'][$from_id]);
    $section_id = $parts[0];
    $index = (int)$parts[1];
    $settings['WSF'][$section_id][$index] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين وصف الخدمة.",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "editss|$section_id|$index"]]]])
    ]);
    exit;
}

if (preg_match("/^delt\|(.*)\|(.*)/", $data, $m)) {
    $section_id = $m[1];
    $index = (int)$m[2];
    if (isset($settings['xdmaxs'][$section_id][$index])) {
        unset($settings['xdmaxs'][$section_id][$index]);
        $settings['xdmaxs'][$section_id] = array_values($settings['xdmaxs'][$section_id]);
        file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    }
    bot('answerCallbackQuery', ['callback_query_id' => $update->callback_query->id, 'text' => "✅ تم حذف الخدمة"]);
    // إعادة عرض القسم
    $key = ['inline_keyboard' => []];
    foreach ($settings['xdmaxs'][$section_id] ?? [] as $idx => $service) {
        $key['inline_keyboard'][] = [
            ['text' => $service, 'callback_data' => "editss|$section_id|$idx"],
            ['text' => "🗑", 'callback_data' => "delets|$section_id|$idx"]
        ];
    }
    $key['inline_keyboard'][] = [['text' => "+ إضافة خدمة", 'callback_data' => "add|$section_id"]];
    $key['inline_keyboard'][] = [['text' => "رجوع", 'callback_data' => "rshqG"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "*الخدمات الموجودة في قسم " . ($settings['NAMES'][$section_id] ?? '') . "*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode($key)
    ]);
    exit;
}

if ($data == "store_sections") {
    $sections = $settings["store"]["sections"] ?? [];
    $btns = [];
    foreach ($sections as $sid => $sec) {
        $btns[] = [['text' => $sec['name'], 'callback_data' => "view_store_section_$sid"], ['text' => "❌", 'callback_data' => "del_store_section_$sid"]];
    }
    $btns[] = [['text' => "➕ إضافة قسم جديد", 'callback_data' => "add_store_section"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "rshqG"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "🗂 *أقسام المتجر:*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode(['inline_keyboard' => $btns])
    ]);
    exit;
}

if ($data == "add_store_section") {
    $Namero['mode'][$from_id] = "add_store_section";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل اسم القسم:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'store_sections']]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "add_store_section") {
    $new_id = generateUID();
    $settings['store']['sections'][$new_id] = ['name' => $text, 'items' => []];
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم إضافة القسم: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'store_sections']]]])
    ]);
    exit;
}

if (preg_match("/^del_store_section_(.*)/", $data, $m)) {
    $sid = $m[1];
    unset($settings['store']['sections'][$sid]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    bot('answerCallbackQuery', ['callback_query_id' => $update->callback_query->id, 'text' => "✅ تم الحذف"]);
    // إعادة عرض الأقسام
    $sections = $settings["store"]["sections"] ?? [];
    $btns = [];
    foreach ($sections as $uid => $sec) {
        $btns[] = [['text' => $sec['name'], 'callback_data' => "view_store_section_$uid"], ['text' => "❌", 'callback_data' => "del_store_section_$uid"]];
    }
    $btns[] = [['text' => "➕ إضافة قسم جديد", 'callback_data' => "add_store_section"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "rshqG"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "🗂 *أقسام المتجر:*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode(['inline_keyboard' => $btns])
    ]);
    exit;
}

if (preg_match("/^view_store_section_(.*)/", $data, $m)) {
    $sid = $m[1];
    $section = $settings['store']['sections'][$sid];
    $items = $section['items'] ?? [];
    $btns = [];
    foreach ($items as $iid => $item) {
        $btns[] = [['text' => $item['name'], 'callback_data' => "view_item_{$sid}_{$iid}"], ['text' => "❌", 'callback_data' => "del_item_{$sid}_{$iid}"]];
    }
    $btns[] = [['text' => "➕ إضافة سلعة", 'callback_data' => "add_item_$sid"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "store_sections"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "📂 *قسم: " . $section['name'] . "*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode(['inline_keyboard' => $btns])
    ]);
    exit;
}

if (preg_match("/^add_item_(.*)/", $data, $m)) {
    $sid = $m[1];
    $Namero['mode'][$from_id] = "add_item";
    $Namero['add_item_sid'][$from_id] = $sid;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل اسم السلعة:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "view_store_section_$sid"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "add_item") {
    $sid = $Namero['add_item_sid'][$from_id];
    $iid = generateUID();
    $settings['store']['sections'][$sid]['items'][$iid] = ['name' => $text, 'price' => 0, 'description' => ''];
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['add_item_sid'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم إضافة السلعة: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "view_store_section_$sid"]]]])
    ]);
    exit;
}

if (preg_match("/^view_item_(.*)_(.*)/", $data, $m)) {
    $sid = $m[1];
    $iid = $m[2];
    $item = $settings['store']['sections'][$sid]['items'][$iid];
    $textMsg = "🛒 *{$item['name']}*\n\n💰 السعر: *{$item['price']}*\n\n📝 الوصف:\n" . ($item['description'] ?: "لا يوجد");
    $key = [
        'inline_keyboard' => [
            [['text' => "تعيين السعر", 'callback_data' => "setprice_item_{$sid}_{$iid}"]],
            [['text' => "تعيين الوصف", 'callback_data' => "setdesc_item_{$sid}_{$iid}"]],
            [['text' => "حذف", 'callback_data' => "del_item_{$sid}_{$iid}"]],
            [['text' => "رجوع", 'callback_data' => "view_store_section_$sid"]]
        ]
    ];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $textMsg,
        'parse_mode' => "markdown",
        'reply_markup' => json_encode($key)
    ]);
    exit;
}

if (preg_match("/^setprice_item_(.*)_(.*)/", $data, $m)) {
    $sid = $m[1];
    $iid = $m[2];
    $Namero['mode'][$from_id] = "setprice_item";
    $Namero['set_item_data'][$from_id] = "$sid|$iid";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل السعر:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "view_item_{$sid}_{$iid}"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setprice_item" && is_numeric($text)) {
    $parts = explode('|', $Namero['set_item_data'][$from_id]);
    $sid = $parts[0];
    $iid = $parts[1];
    $settings['store']['sections'][$sid]['items'][$iid]['price'] = (float)$text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_item_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين السعر: $text",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "view_item_{$sid}_{$iid}"]]]])
    ]);
    exit;
}

if (preg_match("/^setdesc_item_(.*)_(.*)/", $data, $m)) {
    $sid = $m[1];
    $iid = $m[2];
    $Namero['mode'][$from_id] = "setdesc_item";
    $Namero['set_item_data'][$from_id] = "$sid|$iid";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "أرسل الوصف:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "view_item_{$sid}_{$iid}"]]]])
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "setdesc_item") {
    $parts = explode('|', $Namero['set_item_data'][$from_id]);
    $sid = $parts[0];
    $iid = $parts[1];
    $settings['store']['sections'][$sid]['items'][$iid]['description'] = $text;
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    $Namero['mode'][$from_id] = null;
    $Namero['set_item_data'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ تم تعيين الوصف.",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => "view_item_{$sid}_{$iid}"]]]])
    ]);
    exit;
}

if (preg_match("/^del_item_(.*)_(.*)/", $data, $m)) {
    $sid = $m[1];
    $iid = $m[2];
    unset($settings['store']['sections'][$sid]['items'][$iid]);
    file_put_contents($NAMERO . "api_settings.json", json_encode($settings, JSON_PRETTY_PRINT));
    bot('answerCallbackQuery', ['callback_query_id' => $update->callback_query->id, 'text' => "✅ تم الحذف"]);
    // إعادة عرض القسم
    $section = $settings['store']['sections'][$sid];
    $items = $section['items'] ?? [];
    $btns = [];
    foreach ($items as $uid => $item) {
        $btns[] = [['text' => $item['name'], 'callback_data' => "view_item_{$sid}_{$uid}"], ['text' => "❌", 'callback_data' => "del_item_{$sid}_{$uid}"]];
    }
    $btns[] = [['text' => "➕ إضافة سلعة", 'callback_data' => "add_item_$sid"]];
    $btns[] = [['text' => "رجوع", 'callback_data' => "store_sections"]];
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "📂 *قسم: " . $section['name'] . "*",
        'parse_mode' => "markdown",
        'reply_markup' => json_encode(['inline_keyboard' => $btns])
    ]);
    exit;
}

function generateUID() {
    return substr(md5(uniqid(rand(), true)), 0, 8);
}
