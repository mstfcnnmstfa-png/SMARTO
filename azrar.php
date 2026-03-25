<?php
// azrar.php - المنطق الأساسي للمستخدم العادي
$update = json_decode(file_get_contents("php://input"));
$message = $update->message ?? null;
$chat_id = $message->chat->id ?? $GLOBALS['chat_id'] ?? null;
$message_id = $message->message_id ?? $GLOBALS['message_id'] ?? null;
$data = $update->callback_query->data ?? $GLOBALS['data'] ?? null;
$text = $message->text ?? $GLOBALS['text'] ?? null;
$from_id = $message->from->id ?? $GLOBALS['from_id'] ?? null;
$username = $message->from->username ?? $update->callback_query->from->username ?? null;

global $NAMERO, $settings, $currency, $price_per_user, $invite_reward, $min_order, $daily_gift, $Ch, $rshaq, $api_link, $Api_Tok;

if (!isset($NAMERO) || !is_dir($NAMERO)) {
    $bot_info = bot("getMe");
    $bot_id = $bot_info->result->id;
    $NAMERO = __DIR__ . '/NAMERO/' . $bot_id . '/';
    if (!is_dir($NAMERO)) mkdir($NAMERO, 0777, true);
}

$Namero = json_decode(file_get_contents($NAMERO . "Namero.json"), true);
if (!$Namero) $Namero = [];
$settings = json_decode(file_get_contents($NAMERO . "api_settings.json"), true);
if (!$settings) $settings = [];

$currency = $settings['currency'] ?? 'نقاط';
$invite_reward = $settings['invite_reward'] ?? 5;
$min_order = $settings['min_order_quantity'] ?? 10;
$daily_gift = $settings['daily_gift'] ?? 20;
$price_per_user = $settings['user_price'] ?? 100;
$Ch = $settings['Ch'] ?? 'https://t.me/TJUI9';
$rshaq = $Namero['rshaq'] ?? 'on';
$Api_Tok = $settings['token'] ?? '';
$api_link = $settings['domain'] ?? '';

$coin = $Namero["coin"][$from_id] ?? 0;
$share = $Namero["mshark"][$from_id] ?? 0;
$total_orders_count = 0;
foreach ($Namero["orders"] ?? [] as $orders) {
    $total_orders_count += count($orders);
}

// ================== معالجة الأزرار ==================

if ($data == "buy") {
    $buy_text = $settings['buy'] ?? "• *لشراء رصيد من بوت سميث ماتريكس* :  \n\n• 5$ : 5000 نقطة\n• 10$ : 10000 نقطة\n• 15$ : 15000 نقطة\n• 25$ : 25000 نقطة\n• 50$ : 50000 نقطة\n\n• *للتواصل مع الدعم الفني* :\n• @ypui5\n\n•︙*طرق الدفع المتاحة* : \n\n•︙زين كاش، فودافون كاش، اورنج كاش، اتصالات كاش، آسيا.";
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $buy_text,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
    ]);
    exit;
}

if ($data == "termss") {
    $terms = $settings['KLISHA'] ?? "📜 *شروط استخدام البوت*\n\n1️⃣ ممنوع استخدام الخدمات في الأعمال المخالفة\n2️⃣ جميع الطلبات غير قابلة للاسترداد بعد التنفيذ\n3️⃣ للاستفسار والدعم الفني يرجى مراسلة @ypui5";
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $terms,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
    ]);
    exit;
}

if ($data == "daily_gift") {
    $now = time();
    $last_claim = $Namero['daily_gift_last'][$from_id] ?? 0;
    $seconds_remaining = 86400 - ($now - $last_claim);
    
    if ($seconds_remaining <= 0) {
        $gift_amount = $daily_gift;
        $Namero['coin'][$from_id] = ($Namero['coin'][$from_id] ?? 0) + $gift_amount;
        $Namero['daily_gift_last'][$from_id] = $now;
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "🎁 تم إضافة *$gift_amount* $currency إلى رصيدك كهدية يومية!\n\n💰 رصيدك الحالي: " . ($Namero['coin'][$from_id] ?? 0) . " $currency",
            'parse_mode' => 'markdown',
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
        ]);
    } else {
        $hours = floor($seconds_remaining / 3600);
        $minutes = floor(($seconds_remaining % 3600) / 60);
        bot('answerCallbackQuery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "⏳ يمكنك الحصول على هديتك بعد $hours ساعة $minutes دقيقة",
            'show_alert' => true
        ]);
    }
    exit;
}

if ($data == "linkme") {
    $bot_username = bot('getMe')->result->username;
    $link = "https://t.me/$bot_username?start=$from_id";
    $text_msg = "🔗 *رابط الدعوة الخاص بك*\n\n$link\n\n✨ كل شخص يدخل عبر هذا الرابط ستحصل على *$invite_reward* $currency\n👥 عدد دعواتك: *$share*";
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text_msg,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
    ]);
    exit;
}

if ($data == "acc") {
    $user_orders_count = count($Namero["orders"][$from_id] ?? []);
    $stats = json_decode(file_get_contents($NAMERO . "stats.json"), true) ?? [];
    $user_stats = $stats[$from_id] ?? [];
    
    $text_msg = "📊 *معلومات حسابك*\n\n";
    $text_msg .= "💰 الرصيد: *$coin* $currency\n";
    $text_msg .= "👥 عدد الدعوات: *$share*\n";
    $text_msg .= "📦 عدد طلباتك: *$user_orders_count*\n";
    $text_msg .= "🆔 ايديك: `$from_id`\n";
    if (!empty($user_stats['total_spent'])) {
        $text_msg .= "💸 إجمالي المصروف: *{$user_stats['total_spent']}* $currency\n";
    }
    $text_msg .= "\n✨ تم التطوير بواسطة [@ypui5](https://t.me/ypui5)";
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text_msg,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
    ]);
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
            [['text' => '📢 قناة البوت', 'url' => $Ch]]
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

// ================== معالجة النصوص (الكود، التحويل) ==================

if ($text && $Namero['mode'][$from_id] == "hdia") {
    $code = trim($text);
    if (isset($Namero[$code])) {
        $parts = explode('|', $Namero[$code]);
        if ($parts[0] == 'on') {
            $value = (int)$parts[1];
            $max_uses = (int)($parts[2] ?? 1);
            $used = $Namero['TASY_' . $code] ?? 0;
            if ($used < $max_uses && empty($Namero['mehdia'][$from_id][$code])) {
                $Namero['coin'][$from_id] = ($Namero['coin'][$from_id] ?? 0) + $value;
                $Namero['TASY_' . $code] = $used + 1;
                $Namero['mehdia'][$from_id][$code] = 'on';
                file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "✅ تم إضافة $value $currency إلى حسابك.",
                    'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "❌ الكود غير صالح أو تم استخدامه سابقاً.",
                    'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
                ]);
            }
        }
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ الكود غير موجود.",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
        ]);
    }
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($text && $Namero['mode'][$from_id] == "transer") {
    $amount = (int)$text;
    $min = $settings['AKTHAR'] ?? 20;
    if ($amount >= $min && ($Namero['coin'][$from_id] ?? 0) >= $amount) {
        $link_code = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, 10);
        $Namero['coin'][$from_id] -= $amount;
        $Namero['thoiler'][$link_code]['coin'] = $amount;
        $Namero['thoiler'][$link_code]['to'] = $from_id;
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        $bot_username = bot('getMe')->result->username;
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ تم إنشاء رابط تحويل بقيمة $amount $currency\n\nhttps://t.me/$bot_username?start=Bero$link_code\n\nتم خصم $amount من رصيدك.",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
        ]);
        $Namero['mode'][$from_id] = null;
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ رصيدك غير كافٍ أو المبلغ أقل من $min.",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
        ]);
    }
    exit;
}

if ($text && $Namero['mode'][$from_id] == "infotlb") {
    $order_id = (int)$text;
    if (isset($Namero['ordn'][$order_id])) {
        $site = $Namero['sites'][$order_id] ?? $api_link;
        $key = $Namero['keys'][$order_id] ?? $Api_Tok;
        if (!empty($site) && !empty($key)) {
            $url = "https://$site/api/v2?key=$key&action=status&order=$order_id";
            $response = @file_get_contents($url);
            $data = json_decode($response, true);
            if ($data && isset($data['remains'])) {
                $status = ($data['remains'] == 0) ? "مكتمل 🟢" : "قيد التنفيذ ⏳";
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "📊 معلومات الطلب $order_id:\nالخدمة: {$Namero['ordn'][$order_id]}\nالحالة: $status\nالمتبقي: {$data['remains']}",
                    'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "⚠️ لم نتمكن من جلب معلومات الطلب.",
                    'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
                ]);
            }
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ لم يتم تعيين بيانات API للخدمة.",
                'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
            ]);
        }
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ هذا الطلب غير موجود أو ليس من طلباتك.",
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
        ]);
    }
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($text && $Namero['mode'][$from_id] == "SETd") {
    $quantity = (int)$text;
    $min = (int)explode('|', $Namero['min_mix'][$from_id] ?? '1|1000000')[0];
    $max = (int)explode('|', $Namero['min_mix'][$from_id] ?? '1|1000000')[1];
    
    if ($quantity < $min) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ الحد الأدنى للخدمة هو $min"]);
        exit;
    }
    if ($quantity > $max) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ الحد الأقصى للخدمة هو $max"]);
        exit;
    }
    
    $price_per_unit = $Namero['S3RS'][$from_id] ?? 1;
    $total_price = $price_per_unit * $quantity;
    
    if (($Namero['coin'][$from_id] ?? 0) < $total_price) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "💰 رصيدك غير كافٍ.\nالسعر الكلي: $total_price\nرصيدك: {$Namero['coin'][$from_id]}"
        ]);
        exit;
    }
    
    $Namero['3dd'][$from_id] = $quantity;
    $Namero['coinn'] = $total_price;
    $Namero['mode'][$from_id] = 'MJK';
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📝 تم تحديد الكمية: $quantity\nالسعر: $total_price\nالآن أرسل الرابط المطلوب"
    ]);
    exit;
}

if ($text && $Namero['mode'][$from_id] == "MJK") {
    $link = trim($text);
    if (!filter_var($link, FILTER_VALIDATE_URL)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ الرابط غير صالح."]);
        exit;
    }
    
    $quantity = $Namero['3dd'][$from_id] ?? 0;
    $service_id = $Namero['IDX'][$from_id] ?? 0;
    $site = $Namero['web'][$from_id] ?? $api_link;
    $api_key = $Namero['key'][$from_id] ?? $Api_Tok;
    $service_name = $Namero['='][$from_id] ?? 'الخدمة';
    
    $url = "https://$site/api/v2?key=$api_key&action=add&service=$service_id&link=" . urlencode($link) . "&quantity=$quantity";
    $response = @file_get_contents($url);
    $res = json_decode($response, true);
    
    if ($res && isset($res['order'])) {
        $order_id = $res['order'];
        $Namero['coin'][$from_id] -= $Namero['coinn'];
        $Namero['cointlb'][$from_id] = ($Namero['cointlb'][$from_id] ?? 0) + $Namero['coinn'];
        $Namero['tlby'][$from_id] = ($Namero['tlby'][$from_id] ?? 0) + 1;
        $Namero['orders'][$from_id][] = "طلب #$order_id | $service_name | الكمية: $quantity";
        $Namero['ordn'][$order_id] = $service_name;
        $Namero['sites'][$order_id] = $site;
        $Namero['keys'][$order_id] = $api_key;
        $Namero['bot_tlb'] = ($Namero['bot_tlb'] ?? 0) + 1;
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ تم إنشاء الطلب بنجاح!\nرقم الطلب: `$order_id`\nالرابط: $link\nالكمية: $quantity",
            'parse_mode' => 'markdown',
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
        ]);
        
        // إشعار للمطور
        bot('sendMessage', [
            'chat_id' => 7816487928,
            'text' => "📦 طلب جديد:\nالمستخدم: $from_id\nالخدمة: $service_name\nالكمية: $quantity\nالسعر: {$Namero['coinn']}\nالرابط: $link\nالطلب: $order_id"
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ حدث خطأ أثناء إنشاء الطلب.\nيرجى المحاولة لاحقاً."
        ]);
    }
    
    unset($Namero['3dd'][$from_id]);
    unset($Namero['coinn']);
    unset($Namero['IDX'][$from_id]);
    unset($Namero['web'][$from_id]);
    unset($Namero['key'][$from_id]);
    unset($Namero['min_mix'][$from_id]);
    unset($Namero['='][$from_id]);
    unset($Namero['WSFV'][$from_id]);
    $Namero['mode'][$from_id] = null;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "hdia") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "💳 أرسل الكود:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
    ]);
    $Namero['mode'][$from_id] = "hdia";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "transer") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "💰 أرسل عدد الرصيد الذي تريد تحويله:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
    ]);
    $Namero['mode'][$from_id] = "transer";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

if ($data == "infotlb") {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "🔢 أرسل ايدي الطلب:",
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => 'رجوع', 'callback_data' => 'tobot']]]])
    ]);
    $Namero['mode'][$from_id] = "infotlb";
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    exit;
}

// ================== معالجة روابط الإحالة والهدايا ==================

if (preg_match("/^\/start gift_(\w+)/", $text, $match)) {
    $code = $match[1];
    $gift = $Namero["gift_links"][$code] ?? null;
    if (!$gift) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ هذا الرابط غير صالح أو منتهي."]);
        exit;
    }
    if (in_array($from_id, $gift["used"])) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ لقد حصلت على هذه الهدية بالفعل."]);
        exit;
    }
    if (count($gift["used"]) >= $gift["limit"]) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ تم استهلاك جميع استخدامات هذه الهدية."]);
        exit;
    }
    $Namero["coin"][$from_id] += $gift["points"];
    $Namero["gift_links"][$code]["used"][] = $from_id;
    file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🎁 لقد حصلت على *{$gift["points"]}* $currency من رابط الهدية!",
        'parse_mode' => "markdown"
    ]);
    exit;
}

if (preg_match("/^\/start(\d+)/", $text, $m)) {
    $ref_id = (int)$m[1];
    if ($ref_id != $from_id && is_numeric($ref_id)) {
        if (!in_array($from_id, $Namero["3thu"] ?? [])) {
            $Namero["3thu"][] = $from_id;
            $Namero["coin"][$ref_id] = ($Namero["coin"][$ref_id] ?? 0) + $invite_reward;
            $Namero["mshark"][$ref_id] = ($Namero["mshark"][$ref_id] ?? 0) + 1;
            file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "🎉 لقد دخلت عبر رابط صديقك، وحصل على *$invite_reward* $currency",
                'parse_mode' => "markdown"
            ]);
            bot('sendMessage', [
                'chat_id' => $ref_id,
                'text' => "🎉 لقد دخل $name عبر رابط الدعوة الخاص بك وحصلت على *$invite_reward* $currency",
                'parse_mode' => "markdown"
            ]);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ لقد استخدمت رابط دعوة سابقاً."]);
        }
    }
    exit;
}

if (preg_match('/start Bero/', $text)) {
    $e1 = str_replace("/start Bero", "", $text);
    if ($Namero['thoiler'][$e1]["to"] != null) {
        $amount = $Namero['thoiler'][$e1]["coin"];
        $Namero['coin'][$from_id] = ($Namero['coin'][$from_id] ?? 0) + $amount;
        unset($Namero['thoiler'][$e1]);
        file_put_contents($NAMERO . "Namero.json", json_encode($Namero, JSON_PRETTY_PRINT));
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "💰 تم إضافة *$amount* $currency من رابط التحويل",
            'parse_mode' => "markdown"
        ]);
        bot('sendMessage', [
            'chat_id' => $Namero['thoiler'][$e1]["to"],
            'text' => "✅ تم تحويل $amount $currency إلى [$name](tg://user?id=$from_id)",
            'parse_mode' => "markdown"
        ]);
    } else {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ رابط التحويل غير صالح"]);
    }
    exit;
}
