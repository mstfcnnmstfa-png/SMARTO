<?php
// service.php - النسخة النهائية مع جميع الإصلاحات والتعديلات الجديدة وإخفاء جميع الروابط
session_start();
ob_start();

// ========== الإعدادات الأساسية ==========
$BOT_TOKEN = "8575984011:AAGk4WNw26C3zuXKMMAS2TWMLjJdZ3WzqIA";
$ADMIN_ID = "7816487928";
$SECRET_KEY = "Namero_Bot_Secret_Key_2024";

// منع عرض التوكن في أي مكان
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: no-referrer");

// الحصول على الباراميترات
$chat_id = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['chat_id']) ? $_GET['chat_id'] : '');
$key = isset($_GET['key']) ? $_GET['key'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 'main';

// دالة للتحقق من المفتاح
function verifyUserKey($user_id, $key, $secret) {
    $expected_key = hash('sha256', $user_id . $secret . date('Y-m-d'));
    return $key === $expected_key;
}

// التحقق من المفتاح
if (empty($chat_id) || empty($key) || !verifyUserKey($chat_id, $key, $SECRET_KEY)) {
    die("
<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>خطأ في التحقق</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
body {
background: #0a0a0a;
min-height: 100vh;
display: flex;
justify-content: center;
align-items: center;
padding: 20px;
}
.error-container {
background: linear-gradient(145deg, #1a1a1a, #0d0d0d);
border-radius: 30px;
padding: 50px;
text-align: center;
max-width: 450px;
box-shadow: 0 30px 60px rgba(138,43,226,0.1), 0 0 0 1px rgba(138,43,226,0.2);
border: 1px solid rgba(138,43,226,0.1);
transform: perspective(1000px) rotateX(2deg);
}
.error-icon {
font-size: 70px;
margin-bottom: 20px;
filter: drop-shadow(0 0 20px #ff4444);
}
h2 { color: #ff4444; margin-bottom: 15px; font-size: 2em; }
p { color: #888; margin-bottom: 10px; line-height: 1.8; }
.btn {
display: inline-block;
margin-top: 25px;
padding: 15px 40px;
background: linear-gradient(145deg, #8a2be2, #6a1b9a);
color: white;
text-decoration: none;
border-radius: 15px;
font-weight: bold;
transition: all 0.3s;
box-shadow: 0 10px 20px rgba(138,43,226,0.3);
border: 1px solid rgba(255,255,255,0.1);
}
.btn:hover { 
transform: translateY(-5px) scale(1.05);
box-shadow: 0 20px 40px rgba(138,43,226,0.5);
}
</style>
<link href='https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap' rel='stylesheet'>
</head>
<body>
<div class='error-container'>
<div class='error-icon'>🔒</div>
<h2>❌ خطأ في التحقق</h2>
<p>المفتاح غير صالح أو منتهي الصلاحية.</p>
<p style='font-size: 13px; color: #666;'>يرجى فتح الرابط من البوت فقط.</p>
<a href='https://t.me/TJUI9' class='btn' target='_blank'>الدعم الفني</a>
</div>
</body>
</html>
");
}

// تحديد المسارات
$NAMERO_DIR = __DIR__ . '/NAMERO/';
$bot_folders = glob($NAMERO_DIR . '*', GLOB_ONLYDIR);
if (empty($bot_folders)) {
    die("❌ خطأ: لم يتم العثور على مجلد البوت.");
}
$BOT_ID_DIR = $bot_folders[0] . '/';

// تحميل البيانات
$api_settings_file = $BOT_ID_DIR . 'api_settings.json';
$Namero_file = $BOT_ID_DIR . 'Namero.json';
$daily_gifts_file = $BOT_ID_DIR . 'daily_gifts.json';

$api_settings = file_exists($api_settings_file) ? json_decode(file_get_contents($api_settings_file), true) : die("❌ خطأ: ملف الإعدادات غير موجود");
$Namero_data = file_exists($Namero_file) ? json_decode(file_get_contents($Namero_file), true) : die("❌ خطأ: ملف البيانات غير موجود");
$daily_gifts = file_exists($daily_gifts_file) ? json_decode(file_get_contents($daily_gifts_file), true) : [];

// استخراج البيانات
$currency = $api_settings['currency'] ?? 'نقاط';
$rshaq = $Namero_data['rshaq'] ?? 'of';
$coin = $Namero_data["coin"][$chat_id] ?? 0;
$share = $Namero_data["mshark"][$chat_id] ?? 0; // عدد الدعوات
$invite_reward = $api_settings['invite_reward'] ?? 5; // قيمة الدعوة
$user_orders = $Namero_data["orders"][$chat_id] ?? [];
$user_orders_count = count($user_orders);
$channel_link = $api_settings['Ch'] ?? 'https://t.me/TJUI9';
$daily_gift_amount = $api_settings['daily_gift'] ?? 20;
$charge_cliche = $api_settings['domain'] ?? 'لم يتم التعيين';
$terms_text = $api_settings['token'] ?? 'لم يتم التعيين';
$invite_link_status = $api_settings['invite_link_status'] ?? 'off'; // حالة تفعيل الدعوات

// حساب إجمالي الطلبات
$total_bot_orders = 0;
foreach ($Namero_data["orders"] ?? [] as $orders) {
    $total_bot_orders += count($orders);
}

// الحصول على الأقسام والاستبدال
$sections = $api_settings["sections"] ?? [];
$store_sections = $api_settings["store"]["sections"] ?? [];

// الحصول على صورة المستخدم بطريقة آمنة (بدون توكن)
$first_name = 'مستخدم';
$username = '';
$photo_url = 'https://t.me/i/userpic/320/placeholder.svg'; // صورة افتراضية

if (!empty($BOT_TOKEN) && !empty($chat_id)) {
    // جلب معلومات المستخدم
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/getChat?chat_id={$chat_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $user_data = json_decode($response, true);
    if ($user_data && $user_data['ok']) {
        $first_name = $user_data['result']['first_name'] ?? 'مستخدم';
        $username = $user_data['result']['username'] ?? '';
        
        // استخدام رابط آمن لصورة المستخدم (بدون توكن)
        if (!empty($username)) {
            // إذا كان المستخدم عنده يوزر، نستخدم رابط الصورة الآمن
            $photo_url = "https://t.me/i/userpic/320/{$username}.svg";
        } else {
            // استخدام الرابط الافتراضي مع أول حرفين من الاسم
            $photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($first_name) . '&background=8a2be2&color=fff&size=200&bold=true&length=2';
        }
    }
}

// متغيرات للقائمة المنبثقة
$show_modal = false;
$modal_title = '';
$modal_message = '';
$modal_icon = '';
$modal_type = '';

// معالجة الهدية اليومية
$gift_message = '';
$gift_status = '';

if (isset($_GET['claim']) && $_GET['claim'] == 1 && $page == 'daily_gift') {
    $now = time();
    $last_claim = $daily_gifts[$chat_id] ?? 0;
    $seconds_remaining = 86400 - ($now - $last_claim);
    
    if ($seconds_remaining <= 0) {
        // إضافة الهدية للرصيد
        $old_coin = $coin;
        $Namero_data["coin"][$chat_id] = ($Namero_data["coin"][$chat_id] ?? 0) + $daily_gift_amount;
        $daily_gifts[$chat_id] = $now;
        
        // حفظ البيانات
        file_put_contents($Namero_file, json_encode($Namero_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($daily_gifts_file, json_encode($daily_gifts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // تحديث قيمة الرصيد
        $coin = $Namero_data["coin"][$chat_id];
        
        // إعداد القائمة المنبثقة
        $show_modal = true;
        $modal_type = 'success';
        $modal_title = '🎁 تهانينا!';
        $modal_message = "✅ تم إضافة {$daily_gift_amount} {$currency} إلى رصيدك كهدية يومية!\n\n💰 رصيدك السابق: {$old_coin} {$currency}\n💰 رصيدك الحالي: {$coin} {$currency}";
        $modal_icon = '🎉';
    }
}

// معالجة شراء من المتجر (الخطوة الأولى: اختيار المنتج وعرض صفحة التفاصيل)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_store_item'])) {
    $section_uid = $_POST['section_uid'] ?? '';
    $item_uid = $_POST['item_uid'] ?? '';
    
    if (isset($store_sections[$section_uid]['items'][$item_uid])) {
        $item = $store_sections[$section_uid]['items'][$item_uid];
        // تخزين مؤقت لبيانات المنتج في الجلسة لاستخدامها في صفحة التفاصيل
        $_SESSION['temp_store_item'] = [
            'section_uid' => $section_uid,
            'item_uid' => $item_uid,
            'item_name' => $item['name'],
            'item_price' => floatval($item['price'] ?? 0),
            'item_description' => $item['description'] ?? ''
        ];
        // التوجيه إلى صفحة تفاصيل المنتج
        header("Location: ?id=" . urlencode($chat_id) . "&key=" . urlencode($key) . "&page=store_item_details");
        exit;
    }
}

// معالجة شراء من المتجر (الطريقة الجديدة)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_store_buy'])) {
    $section_uid = $_POST['store_section_uid'] ?? '';
    $item_uid = $_POST['store_item_uid'] ?? '';
    $delivery_info = trim($_POST['delivery_info'] ?? '');
    
    if (empty($delivery_info)) {
        $show_modal = true;
        $modal_type = 'error';
        $modal_title = '❌ خطأ';
        $modal_message = 'يرجى إدخال حساب الاستلام.';
        $modal_icon = '⚠️';
    } elseif (isset($store_sections[$section_uid]['items'][$item_uid])) {
        $item = $store_sections[$section_uid]['items'][$item_uid];
        $item_name = $item['name'];
        $item_price = floatval($item['price'] ?? 0);
        $old_coin = $coin;
        
        if ($coin >= $item_price) {
            // خصم الرصيد
            $Namero_data["coin"][$chat_id] = $coin - $item_price;
            
            // إنشاء طلب شراء
            $order_id = rand(100000, 999999);
            
            // إشعار للأدمن
            $admin_notify = "🛍 *طلب شراء جديد من المتجر!*\n\n";
            $admin_notify .= "👤 المستخدم: [$first_name](tg://user?id=$chat_id)\n";
            $admin_notify .= "🆔 الايدي: `$chat_id`\n";
            $admin_notify .= "📦 المنتج: {$item_name}\n";
            $admin_notify .= "💰 السعر: {$item_price} {$currency}\n";
            $admin_notify .= "📞 حساب الاستلام: `{$delivery_info}`\n";
            $admin_notify .= "🆔 رقم الطلب: `{$order_id}`\n";
            $admin_notify .= "⏰ الوقت: " . date('Y-m-d H:i:s');
            
            sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $admin_notify, 'markdown');
            
            // إشعار للمستخدم
            $user_notify = "✅ تم شراء {$item_name} بنجاح!\n";
            $user_notify .= "💰 تم خصم {$item_price} {$currency} من رصيدك\n";
            $user_notify .= "📞 حساب الاستلام: {$delivery_info}\n";
            $user_notify .= "🆔 رقم الطلب: `{$order_id}`\n";
            $user_notify .= "📞 سيتم التواصل معك قريباً للتسليم.";
            
            sendTelegramMessage($BOT_TOKEN, $chat_id, $user_notify);
            
            // حفظ التغييرات
            file_put_contents($Namero_file, json_encode($Namero_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // تحديث الرصيد
            $coin = $Namero_data["coin"][$chat_id];
            
            // إعداد القائمة المنبثقة
            $show_modal = true;
            $modal_type = 'success';
            $modal_title = '🛍 تم الشراء بنجاح!';
            $modal_message = "✅ المنتج: {$item_name}\n💰 المبلغ: {$item_price} {$currency}\n📞 حساب الاستلام: {$delivery_info}\n🆔 رقم الطلب: {$order_id}\n\n💰 رصيدك الجديد: {$coin} {$currency}";
            $modal_icon = '🎉';
        } else {
            // إعداد القائمة المنبثقة للخطأ
            $show_modal = true;
            $modal_type = 'error';
            $modal_title = '❌ فشل الشراء';
            $modal_message = "رصيدك غير كافٍ للشراء!\n\n💰 رصيدك الحالي: {$coin} {$currency}\n💰 سعر المنتج: {$item_price} {$currency}";
            $modal_icon = '⚠️';
        }
    }
}

// معالجة طلب الخدمة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $post_link = filter_var($_POST['link'] ?? '', FILTER_SANITIZE_URL);
    $quantity = floatval($_POST['quantity'] ?? 0);
    $section_uid = $_POST['section_uid'] ?? '';
    $service_uid = $_POST['service_uid'] ?? '';
    $old_coin = $coin;

    // التحقق من الحقول المطلوبة - المعدل
    if (empty($section_uid) || empty($service_uid) || empty($post_link) || $quantity <= 0) {
        $show_modal = true;
        $modal_type = 'error';
        $modal_title = '❌ خطأ في الطلب';
        $modal_message = 'يرجى اختيار القسم والخدمة وإدخال الرابط والكمية بشكل صحيح.';
        $modal_icon = '⚠️';
    } elseif (isset($sections[$section_uid]['services'][$service_uid])) {
        $service_data = $sections[$section_uid]['services'][$service_uid];
        $section_name = $sections[$section_uid]['name'];
        $service_name = $service_data['name'];
        
        $min = intval($service_data['min'] ?? 0);
        $max = intval($service_data['max'] ?? 0);
        $price = floatval($service_data['price'] ?? 0);
        $service_id = $service_data['service_id'] ?? '';
        $domain = $service_data['domain'] ?? '';
        $api_key = $service_data['api'] ?? '';
        
        if ($quantity < $min || $quantity > $max) {
            $show_modal = true;
            $modal_type = 'error';
            $modal_title = '❌ كمية غير صحيحة';
            $modal_message = "الكمية يجب أن تكون بين {$min} و {$max}.";
            $modal_icon = '⚠️';
        } else {
            // حساب السعر الإجمالي بدقة مع الأسعار العشرية
            $total_price = ($quantity / 1000) * $price;
            
            if ($coin < $total_price) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ رصيد غير كافٍ';
                $modal_message = "💰 رصيدك الحالي: {$coin} {$currency}\n💰 السعر المطلوب: {$total_price} {$currency}";
                $modal_icon = '💔';
            } elseif (empty($domain) || empty($api_key) || empty($service_id)) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ خطأ في الخدمة';
                $modal_message = 'بيانات API للخدمة غير مكتملة. يرجى مراسلة الدعم الفني.';
                $modal_icon = '🔧';
            } else {
                $api_url = "https://$domain/api/v2?key=$api_key&action=add&service=$service_id&link=" . urlencode($post_link) . "&quantity=$quantity";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $api_response = curl_exec($ch);
                curl_close($ch);
                
                $api_result = json_decode($api_response, true);
                
                if ($api_result && isset($api_result['order'])) {
                    $order_id = $api_result['order'];
                    
                    $Namero_data["coin"][$chat_id] = $coin - $total_price;
                    $Namero_data["last_order"][$chat_id][$section_uid][$service_uid] = time();
                    
                    $new_order_index = count($Namero_data["orders"][$chat_id] ?? []);
                    $Namero_data["orders"][$chat_id][$new_order_index] = [
                        "section" => $section_name,
                        "service" => $service_name,
                        "quantity" => $quantity,
                        "link" => $post_link,
                        "price" => $total_price,
                        "order_id" => $order_id,
                        "status" => "جاري التنفيذ",
                        "time" => time(),
                        "created_at" => time()
                    ];
                    
                    file_put_contents($Namero_file, json_encode($Namero_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    $user_notify = "✅ تم تأكيد طلبك بنجاح!\n\n📦 الخدمة: $service_name\n🔢 الكمية: $quantity\n💰 السعر: $total_price $currency\n🆔 رقم الطلب: `$order_id`";
                    sendTelegramMessage($BOT_TOKEN, $chat_id, $user_notify);
                    
                    $admin_notify = "🔔 *طلب جديد من الموقع!*\n\n👤 المستخدم: [$first_name](tg://user?id=$chat_id)\n🆔 الايدي: `$chat_id`\n📦 الخدمة: $service_name\n📁 القسم: $section_name\n🔢 الكمية: $quantity\n💰 السعر: $total_price $currency\n🆔 رقم الطلب: `$order_id`\n🌐 [الرابط]($post_link)";
                    sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $admin_notify, 'markdown');
                    
                    // تحديث الرصيد
                    $coin = $Namero_data["coin"][$chat_id];
                    
                    // إعداد القائمة المنبثقة
                    $show_modal = true;
                    $modal_type = 'success';
                    $modal_title = '✅ تم إرسال الطلب!';
                    $modal_message = "📦 الخدمة: {$service_name}\n🔢 الكمية: {$quantity}\n💰 السعر: {$total_price} {$currency}\n🆔 رقم الطلب: {$order_id}\n\n💰 رصيدك المتبقي: {$coin} {$currency}";
                    $modal_icon = '🚀';
                } else {
                    $show_modal = true;
                    $modal_type = 'error';
                    $modal_title = '❌ فشل إرسال الطلب';
                    $modal_message = 'حدث خطأ في الاتصال بالموقع. يرجى المحاولة لاحقاً.';
                    $modal_icon = '🌐';
                    
                    $fail_notify = "⚠️ *فشل طلب من الموقع*\n\n👤 المستخدم: $first_name\n🆔 $chat_id\n📦 الخدمة: $service_name\n🔢 الكمية: $quantity";
                    sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $fail_notify, 'markdown');
                }
            }
        }
    }
}

// معالجة استبدال كود الهدية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_gift_code'])) {
    $gift_code = trim($_POST['gift_code'] ?? '');
    
    if (empty($gift_code)) {
        $show_modal = true;
        $modal_type = 'error';
        $modal_title = '❌ خطأ';
        $modal_message = 'يرجى إدخال كود الهدية.';
        $modal_icon = '⚠️';
    } else {
        // التحقق من وجود الكود في ملف الهدايا
        $gifts_file = $BOT_ID_DIR . 'gift_codes.json';
        $gifts_data = file_exists($gifts_file) ? json_decode(file_get_contents($gifts_file), true) : [];
        
        $code_found = false;
        $code_data = null;
        $code_key = null;
        
        foreach ($gifts_data as $key => $gift) {
            if ($gift['code'] == $gift_code) {
                $code_found = true;
                $code_data = $gift;
                $code_key = $key;
                break;
            }
        }
        
        if (!$code_found) {
            $show_modal = true;
            $modal_type = 'error';
            $modal_title = '❌ كود غير صالح';
            $modal_message = 'الكود الذي أدخلته غير موجود أو منتهي الصلاحية.';
            $modal_icon = '🔍';
        } else {
            // التحقق من تاريخ انتهاء الصلاحية
            $current_time = time();
            $expiry_time = $code_data['created_at'] + ($code_data['days'] * 86400);
            
            if ($current_time > $expiry_time) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ كود منتهي';
                $modal_message = 'عذراً، هذا الكود انتهت صلاحيته.';
                $modal_icon = '⏰';
            }
            // التحقق من أن الكود لم يصل للحد الأقصى
            elseif (count($code_data['used_by']) >= $code_data['users']) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ كود مستنفذ';
                $modal_message = 'عذراً، هذا الكود استخدمه الحد الأقصى من المستخدمين.';
                $modal_icon = '👥';
            }
            // التحقق من أن المستخدم لم يستخدم الكود من قبل
            elseif (in_array($chat_id, $code_data['used_by'])) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ كود مستخدم';
                $modal_message = 'لقد استخدمت هذا الكود من قبل بالفعل.';
                $modal_icon = '⚠️';
            }
            else {
                // كل شيء تمام - إضافة النقاط للمستخدم
                $old_coin = $coin;
                $Namero_data["coin"][$chat_id] = $coin + $code_data['points'];
                
                // تسجيل استخدام الكود
                $gifts_data[$code_key]['used_by'][] = $chat_id;
                
                // حفظ التغييرات
                file_put_contents($Namero_file, json_encode($Namero_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                file_put_contents($gifts_file, json_encode($gifts_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                // تحديث الرصيد
                $coin = $Namero_data["coin"][$chat_id];
                
                // إشعار للأدمن
                $admin_notify = "🎁 *تم استبدال كود هدية!*\n\n";
                $admin_notify .= "👤 المستخدم: [$first_name](tg://user?id=$chat_id)\n";
                $admin_notify .= "🆔 الايدي: `$chat_id`\n";
                $admin_notify .= "🎫 الكود: `{$code_data['code']}`\n";
                $admin_notify .= "💰 النقاط: {$code_data['points']} {$currency}\n";
                $admin_notify .= "👥 المستخدمون المتبقون: " . ($code_data['users'] - count($code_data['used_by'])) . "\n";
                $admin_notify .= "⏰ الوقت: " . date('Y-m-d H:i:s');
                
                sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $admin_notify, 'markdown');
                
                // إشعار للمستخدم
                $user_notify = "🎁 *تهانينا!*\n\n";
                $user_notify .= "✅ تم إضافة {$code_data['points']} {$currency} إلى رصيدك بنجاح!\n";
                $user_notify .= "💰 رصيدك السابق: {$old_coin} {$currency}\n";
                $user_notify .= "💰 رصيدك الحالي: {$coin} {$currency}";
                
                sendTelegramMessage($BOT_TOKEN, $chat_id, $user_notify, 'markdown');
                
                // إعداد القائمة المنبثقة
                $show_modal = true;
                $modal_type = 'success';
                $modal_title = '🎉 تهانينا!';
                $modal_message = "✅ تم إضافة {$code_data['points']} {$currency} إلى رصيدك بنجاح!\n\n💰 رصيدك السابق: {$old_coin} {$currency}\n💰 رصيدك الحالي: {$coin} {$currency}";
                $modal_icon = '🎁';
            }
        }
    }
}

function sendTelegramMessage($token, $chat_id, $text, $parse_mode = '') {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text];
    if ($parse_mode) $data['parse_mode'] = $parse_mode;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    curl_close($ch);
}

// الحصول على يوزر البوت للإشارة إليه في روابط الشحن والدعوة
$username_bot = '';
if (!empty($BOT_TOKEN)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/getMe");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $bot_info_response = curl_exec($ch);
    curl_close($ch);
    $bot_info_data = json_decode($bot_info_response, true);
    if ($bot_info_data && $bot_info_data['ok']) {
        $username_bot = $bot_info_data['result']['username'];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>سميث ماتريكس - منصة الخدمات الاحترافية</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
* {
margin: 0;
padding: 0;
box-sizing: border-box;
font-family: 'Cairo', sans-serif;
/* ===== الحماية الجديدة: منع التحديد والقوائم المنبثقة ===== */
-webkit-touch-callout: none; /* يمنع قائمة اللمس المطول في iOS */
-webkit-user-select: none;   /* لمنع التحديد في Safari و Chrome */
-khtml-user-select: none;    /* لمنع التحديد في Konqueror */
-moz-user-select: none;      /* لمنع التحديد في Firefox */
-ms-user-select: none;       /* لمنع التحديد في Internet Explorer/Edge */
user-select: none;           /* المعيار الحديث */
-webkit-tap-highlight-color: transparent; /* يخفي تأثير اللمس الرمادي */
}
/* منع سحب العناصر */
img, .stat-card-3d, .product-card-3d, .section-card-3d, .buy-btn, .submit-btn, .claim-btn, .menu-button {
-webkit-user-drag: none; /* منع السحب في Safari و Chrome */
user-drag: none; /* المعيار القديم */
-khtml-user-drag: none; /* منع السحب في Konqueror */
-moz-user-drag: none; /* منع السحب في Firefox (قديم) */
}
* {
margin: 0;
padding: 0;
box-sizing: border-box;
font-family: 'Cairo', sans-serif;
-webkit-touch-callout: none;
-webkit-user-select: none;
-khtml-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;
-webkit-tap-highlight-color: transparent;
}

:root {
--bg-primary: #0a0a0a;
--bg-secondary: #111111;
--bg-tertiary: #1a1a1a;
--text-primary: #ffffff;
--text-secondary: #888888;
--accent-purple: #8a2be2;
--accent-purple-dark: #6a1b9a;
--accent-purple-glow: rgba(138, 43, 226, 0.3);
--danger: #ff4444;
--success: #8a2be2;
--warning: #ffbb33;
--glass-bg: rgba(255, 255, 255, 0.03);
--glass-border: rgba(138, 43, 226, 0.1);
}

body {
background: var(--bg-primary);
min-height: 100vh;
color: var(--text-primary);
position: relative;
overflow-x: hidden;
}

/* منع الضغط المطول */
.menu-button, .stat-card-3d, .product-card-3d, .section-card-3d, .buy-btn, .submit-btn, .claim-btn, .back-link, button {
-webkit-touch-callout: none;
-webkit-user-select: none;
user-select: none;
-webkit-tap-highlight-color: transparent;
cursor: pointer;
}

/* خلفية متحركة 3D */
.background-3d {
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
z-index: -1;
overflow: hidden;
}

.grid-3d {
position: absolute;
width: 200%;
height: 200%;
background: 
linear-gradient(90deg, var(--glass-border) 1px, transparent 1px),
linear-gradient(0deg, var(--glass-border) 1px, transparent 1px);
background-size: 50px 50px;
transform: perspective(500px) rotateX(60deg) translateY(-20%);
animation: moveGrid 20s linear infinite;
opacity: 0.3;
}

@keyframes moveGrid {
0% { transform: perspective(500px) rotateX(60deg) translateY(-20%) translateX(0); }
100% { transform: perspective(500px) rotateX(60deg) translateY(-20%) translateX(50px); }
}

/* القائمة الجانبية */
.sidebar {
position: fixed;
right: -300px;
top: 0;
width: 300px;
height: 100vh;
background: linear-gradient(145deg, var(--bg-secondary), #000000);
border-left: 1px solid var(--glass-border);
box-shadow: -10px 0 30px rgba(138,43,226,0.1);
transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
z-index: 1000;
padding: 0;
backdrop-filter: blur(10px);
overflow: hidden;
display: flex;
flex-direction: column;
}

.sidebar.active {
right: 0;
}

.sidebar-toggle {
position: fixed;
right: 20px;
top: 20px;
width: 50px;
height: 50px;
background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
border-radius: 15px;
display: flex;
justify-content: center;
align-items: center;
cursor: pointer;
z-index: 1001;
box-shadow: 0 0 20px var(--accent-purple-glow);
border: 1px solid rgba(255,255,255,0.1);
transform: perspective(500px) rotateY(0deg);
transition: all 0.3s;
border: none;
color: white;
font-size: 24px;
}

.sidebar-toggle:hover {
transform: perspective(500px) rotateY(10deg) scale(1.1);
box-shadow: 0 0 30px var(--accent-purple);
}

.sidebar-header {
padding: 30px 20px 20px;
text-align: center;
border-bottom: 1px solid var(--glass-border);
flex-shrink: 0;
}

.sidebar-header h3 {
color: var(--accent-purple);
font-size: 1.5em;
text-shadow: 0 0 10px var(--accent-purple-glow);
}

/* حاوية القائمة القابلة للتمرير */
.sidebar-menu-container {
flex: 1;
overflow-y: auto;
padding: 20px;
scrollbar-width: thin;
scrollbar-color: var(--accent-purple) var(--bg-tertiary);
}

/* تخصيص شريط التمرير */
.sidebar-menu-container::-webkit-scrollbar {
width: 4px;
}

.sidebar-menu-container::-webkit-scrollbar-track {
background: var(--bg-tertiary);
}

.sidebar-menu-container::-webkit-scrollbar-thumb {
background: var(--accent-purple);
border-radius: 4px;
}

.sidebar-menu {
list-style: none;
margin: 0;
padding: 0;
}

.sidebar-menu li {
margin-bottom: 10px;
}

.menu-button {
width: 100%;
padding: 15px 20px;
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 15px;
color: var(--text-primary);
text-decoration: none;
transition: all 0.3s;
transform: perspective(500px) translateZ(0);
display: flex;
align-items: center;
gap: 15px;
font-size: 1em;
font-family: 'Cairo', sans-serif;
cursor: pointer;
text-align: right;
margin-bottom: 10px;
}

.menu-button:hover {
background: var(--accent-purple);
color: white;
transform: perspective(500px) translateZ(20px) translateX(-10px);
box-shadow: 0 10px 30px var(--accent-purple-glow);
}

.menu-button i {
font-size: 20px;
width: 30px;
}

.menu-button.active {
background: var(--accent-purple);
color: white;
}

.sidebar-footer {
padding: 15px 20px;
border-top: 1px solid var(--glass-border);
text-align: center;
font-size: 0.8em;
color: var(--text-secondary);
background: rgba(0,0,0,0.3);
flex-shrink: 0;
}
/* الحاوية الرئيسية */
.main-container {
max-width: 1200px;
margin: 0 auto;
padding: 80px 20px 20px;
transition: all 0.3s;
}

/* بطاقة المستخدم ثلاثية الأبعاد */
.user-profile-3d {
background: linear-gradient(145deg, var(--bg-secondary), #000000);
border-radius: 30px;
padding: 30px;
margin-bottom: 30px;
border: 1px solid var(--glass-border);
box-shadow: 0 20px 40px rgba(138,43,226,0.1);
transform: perspective(1000px) rotateX(2deg);
transition: all 0.3s;
position: relative;
overflow: hidden;
}

.user-profile-3d::before {
content: '';
position: absolute;
top: -50%;
left: -50%;
width: 200%;
height: 200%;
background: radial-gradient(circle, var(--accent-purple-glow) 0%, transparent 70%);
opacity: 0.1;
animation: rotate 20s linear infinite;
}

@keyframes rotate {
from { transform: rotate(0deg); }
to { transform: rotate(360deg); }
}

.user-profile-content {
display: flex;
align-items: center;
gap: 30px;
position: relative;
z-index: 1;
}

.user-avatar-3d {
width: 100px;
height: 100px;
border-radius: 50%;
object-fit: cover;
border: 4px solid var(--accent-purple);
box-shadow: 0 0 30px var(--accent-purple-glow);
transform: perspective(500px) rotateY(0deg) rotateX(0deg);
transition: all 0.3s;
}

.user-avatar-3d:hover {
transform: perspective(500px) rotateY(180deg) rotateX(10deg);
}

.user-info-3d h2 {
font-size: 1.8em;
margin-bottom: 5px;
color: var(--accent-purple);
text-shadow: 0 0 10px var(--accent-purple-glow);
}

.user-info-3d p {
color: var(--text-secondary);
margin-bottom: 3px;
font-size: 0.9em;
}

.user-stats-3d {
display: flex;
gap: 15px;
margin-top: 10px;
}

.stat-badge {
background: var(--glass-bg);
border: 1px solid var(--glass-border);
padding: 5px 15px;
border-radius: 30px;
font-weight: bold;
font-size: 0.9em;
}

.stat-badge i {
color: var(--accent-purple);
margin-left: 5px;
}

/* بطاقات الإحصائيات */
.stats-grid-3d {
display: grid;
grid-template-columns: repeat(2, 1fr);
gap: 20px;
margin-bottom: 30px;
}

.stat-card-3d {
background: linear-gradient(145deg, var(--bg-secondary), #000000);
border: 1px solid var(--glass-border);
border-radius: 20px;
padding: 20px;
text-align: center;
transform: perspective(1000px) rotateX(2deg) rotateY(0deg);
transition: all 0.3s;
position: relative;
overflow: hidden;
border: none;
color: var(--text-primary);
font-family: 'Cairo', sans-serif;
font-size: 1em;
width: 100%;
cursor: pointer;
}

.stat-card-3d::after {
content: '';
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
background: linear-gradient(45deg, transparent, var(--accent-purple-glow), transparent);
transform: translateX(-100%);
transition: 0.5s;
}

.stat-card-3d:hover::after {
transform: translateX(100%);
}

.stat-card-3d:hover {
transform: perspective(1000px) rotateX(5deg) rotateY(5deg) translateY(-5px);
box-shadow: 0 20px 40px rgba(138,43,226,0.2);
}

.stat-icon-3d {
font-size: 2.5em;
color: var(--accent-purple);
margin-bottom: 10px;
filter: drop-shadow(0 0 10px var(--accent-purple-glow));
}

.stat-value-3d {
font-size: 2em;
font-weight: 900;
color: var(--accent-purple);
line-height: 1.2;
text-shadow: 0 0 15px var(--accent-purple-glow);
}

.stat-label-3d {
color: var(--text-secondary);
font-size: 1em;
}

/* بطاقة الرصيد */
.balance-card-3d {
background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
border-radius: 25px;
padding: 30px;
margin-bottom: 30px;
transform: perspective(1000px) rotateX(2deg);
box-shadow: 0 20px 40px rgba(138,43,226,0.3);
position: relative;
overflow: hidden;
}

.balance-card-3d::before {
content: '';
position: absolute;
top: -50%;
left: -50%;
width: 200%;
height: 200%;
background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
animation: rotate 15s linear infinite;
}

.balance-content {
display: flex;
justify-content: space-between;
align-items: center;
position: relative;
z-index: 1;
}

.balance-label {
font-size: 1.2em;
color: white;
font-weight: bold;
}

.balance-amount {
font-size: 2.5em;
font-weight: 900;
color: white;
text-shadow: 0 0 20px rgba(255,255,255,0.5);
}

/* بطاقات الأقسام */
.section-grid {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
gap: 20px;
margin-top: 20px;
}

.section-card-3d {
background: linear-gradient(145deg, var(--bg-secondary), #000000);
border: 1px solid var(--glass-border);
border-radius: 20px;
padding: 20px;
transform: perspective(1000px) rotateX(2deg);
transition: all 0.3s;
cursor: pointer;
}

.section-card-3d:hover {
transform: perspective(1000px) rotateX(5deg) translateY(-5px);
box-shadow: 0 20px 40px rgba(138,43,226,0.2);
border-color: var(--accent-purple);
}

.section-header {
display: flex;
align-items: center;
gap: 15px;
margin-bottom: 15px;
}

.section-icon {
width: 40px;
height: 40px;
background: var(--accent-purple);
border-radius: 12px;
display: flex;
justify-content: center;
align-items: center;
color: white;
font-size: 20px;
transform: perspective(500px) rotateX(10deg);
box-shadow: 0 10px 20px rgba(138,43,226,0.3);
}

.section-title {
font-size: 1.2em;
color: var(--accent-purple);
}

.service-list {
max-height: 250px;
overflow-y: auto;
margin-top: 10px;
}

.service-item {
display: flex;
justify-content: space-between;
align-items: center;
padding: 10px;
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 10px;
margin-bottom: 5px;
transition: all 0.3s;
font-size: 0.9em;
}

.service-item:hover {
background: var(--accent-purple);
color: white;
transform: translateX(-5px);
}

.service-item:hover .service-price {
color: white;
}

.service-name {
font-weight: 600;
}

.service-price {
color: var(--accent-purple);
font-weight: bold;
}

/* منتجات المتجر */
.store-sections-grid {
display: flex;
flex-direction: column;
gap: 20px;
margin-top: 20px;
}

.store-section-card {
background: linear-gradient(145deg, var(--bg-secondary), #000000);
border: 1px solid var(--glass-border);
border-radius: 20px;
padding: 20px;
transform: perspective(1000px) rotateX(2deg);
transition: all 0.3s;
}

.store-section-card:hover {
transform: perspective(1000px) rotateX(5deg) translateY(-5px);
box-shadow: 0 20px 40px rgba(138,43,226,0.2);
border-color: var(--accent-purple);
}

.store-section-title {
font-size: 1.4em;
color: var(--accent-purple);
margin-bottom: 15px;
padding-bottom: 10px;
border-bottom: 1px solid var(--glass-border);
}

.store-items-grid {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
gap: 15px;
}

.store-item-card {
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 15px;
padding: 15px;
transition: all 0.3s;
cursor: pointer;
}

.store-item-card:hover {
background: var(--accent-purple);
transform: translateY(-5px);
box-shadow: 0 10px 20px var(--accent-purple-glow);
}

.store-item-card:hover .store-item-name,
.store-item-card:hover .store-item-price {
color: white;
}

.store-item-name {
font-size: 1.1em;
font-weight: bold;
margin-bottom: 8px;
color: var(--text-primary);
}

.store-item-price {
color: var(--accent-purple);
font-weight: bold;
font-size: 1.2em;
}

.store-item-form {
margin-top: 10px;
}

.view-item-btn {
width: 100%;
padding: 8px;
background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
border: none;
border-radius: 10px;
color: white;
font-weight: bold;
cursor: pointer;
transition: all 0.3s;
}

.view-item-btn:hover {
transform: scale(1.05);
box-shadow: 0 5px 15px var(--accent-purple-glow);
}

/* بطاقة تفاصيل المنتج */
.product-details-card {
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 20px;
padding: 30px;
margin: 20px 0;
text-align: center;
}

.product-details-name {
font-size: 2em;
color: var(--accent-purple);
margin-bottom: 15px;
}

.product-details-price {
font-size: 2.5em;
font-weight: 900;
color: var(--accent-purple);
margin: 20px 0;
text-shadow: 0 0 10px var(--accent-purple-glow);
}

.product-details-description {
background: var(--bg-tertiary);
border: 1px solid var(--glass-border);
border-radius: 15px;
padding: 20px;
margin: 20px 0;
color: var(--text-secondary);
line-height: 1.8;
text-align: right;
white-space: pre-line;
}

/* نموذج الطلب */
.order-form-3d {
background: linear-gradient(145deg, var(--bg-secondary), #000000);
border: 1px solid var(--glass-border);
border-radius: 25px;
padding: 25px;
margin-top: 20px;
transform: perspective(1000px) rotateX(2deg);
}

.form-title {
font-size: 1.8em;
color: var(--accent-purple);
margin-bottom: 20px;
text-shadow: 0 0 10px var(--accent-purple-glow);
}

.form-group {
margin-bottom: 15px;
}

.form-label {
display: block;
margin-bottom: 8px;
color: var(--text-secondary);
font-weight: 600;
font-size: 0.9em;
}

.form-label i {
color: var(--accent-purple);
margin-left: 5px;
}

.form-control {
width: 100%;
padding: 12px 15px;
background: var(--bg-tertiary);
border: 1px solid var(--glass-border);
border-radius: 12px;
color: var(--text-primary);
font-size: 0.95em;
transition: all 0.3s;
}

.form-control:focus {
outline: none;
border-color: var(--accent-purple);
box-shadow: 0 0 20px var(--accent-purple-glow);
transform: perspective(500px) translateZ(5px);
}

.service-details-3d {
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 15px;
padding: 15px;
margin: 15px 0;
display: none;
}

.service-details-3d.active {
display: block;
animation: fadeInUp 0.5s ease;
}

.total-price-3d {
background: linear-gradient(145deg, var(--accent-purple-dark), #4a0080);
border-radius: 15px;
padding: 15px;
margin: 15px 0;
text-align: center;
font-size: 1.2em;
font-weight: bold;
color: white;
box-shadow: 0 5px 15px var(--accent-purple-glow);
border: 1px solid rgba(255,255,255,0.2);
display: none;
}

.total-price-3d span {
font-size: 1.5em;
font-weight: 900;
text-shadow: 0 0 10px white;
}

@keyframes fadeInUp {
from {
opacity: 0;
transform: translateY(20px);
}
to {
opacity: 1;
transform: translateY(0);
}
}

.detail-row {
display: flex;
justify-content: space-between;
padding: 8px 0;
border-bottom: 1px dashed var(--glass-border);
font-size: 0.95em;
}

.detail-label {
color: var(--text-secondary);
}

.detail-value {
color: var(--accent-purple);
font-weight: bold;
}

.submit-btn {
width: 100%;
padding: 15px;
background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
border: none;
border-radius: 15px;
color: white;
font-size: 1.2em;
font-weight: 900;
cursor: pointer;
transition: all 0.3s;
transform: perspective(500px) translateZ(0);
box-shadow: 0 10px 30px rgba(138,43,226,0.3);
margin-top: 15px;
}

.submit-btn:hover {
transform: perspective(500px) translateZ(20px) scale(1.02);
box-shadow: 0 20px 40px rgba(138,43,226,0.5);
}

/* القائمة المنبثقة 3D */
.modal-overlay {
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
background: rgba(0, 0, 0, 0.8);
backdrop-filter: blur(10px);
z-index: 10000;
display: <?php echo $show_modal ? 'flex' : 'none'; ?>;
justify-content: center;
align-items: center;
animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
from { opacity: 0; }
to { opacity: 1; }
}

.modal-3d {
background: linear-gradient(145deg, #1a1a1a, #0d0d0d);
border: 2px solid var(--accent-purple);
border-radius: 40px;
padding: 40px;
max-width: 500px;
width: 90%;
text-align: center;
transform: perspective(1000px) rotateX(5deg) scale(0.9);
animation: modalPop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
box-shadow: 0 30px 60px rgba(138,43,226,0.3);
position: relative;
overflow: hidden;
}

.modal-3d::before {
content: '';
position: absolute;
top: -50%;
left: -50%;
width: 200%;
height: 200%;
background: radial-gradient(circle, var(--accent-purple-glow) 0%, transparent 70%);
animation: rotate 20s linear infinite;
opacity: 0.1;
}

@keyframes modalPop {
0% {
transform: perspective(1000px) rotateX(5deg) scale(0.5);
opacity: 0;
}
100% {
transform: perspective(1000px) rotateX(5deg) scale(1);
opacity: 1;
}
}

.modal-icon {
font-size: 80px;
margin-bottom: 20px;
filter: drop-shadow(0 0 30px var(--accent-purple-glow));
animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
0%, 100% { transform: translateY(0); }
50% { transform: translateY(-10px); }
}

.modal-title {
font-size: 2.5em;
color: var(--accent-purple);
margin-bottom: 20px;
text-shadow: 0 0 20px var(--accent-purple-glow);
font-weight: 900;
}

.modal-message {
color: var(--text-secondary);
margin-bottom: 30px;
line-height: 1.8;
font-size: 1.1em;
white-space: pre-line;
}

.modal-button {
background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
border: none;
border-radius: 50px;
padding: 15px 50px;
color: white;
font-size: 1.3em;
font-weight: 900;
cursor: pointer;
transition: all 0.3s;
box-shadow: 0 10px 30px var(--accent-purple-glow);
border: 1px solid rgba(255,255,255,0.1);
}

.modal-button:hover {
transform: scale(1.05);
box-shadow: 0 20px 40px var(--accent-purple-glow);
}

.modal-close {
position: absolute;
top: 20px;
left: 20px;
width: 40px;
height: 40px;
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 50%;
display: flex;
justify-content: center;
align-items: center;
cursor: pointer;
transition: all 0.3s;
z-index: 1;
border: none;
color: var(--text-secondary);
font-size: 20px;
}

.modal-close:hover {
background: var(--danger);
transform: rotate(90deg);
}

.modal-close i {
font-size: 20px;
color: var(--text-secondary);
}

.modal-close:hover i {
color: white;
}

/* شاشة التحميل */
.loading-3d {
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
background: rgba(0,0,0,0.9);
backdrop-filter: blur(10px);
z-index: 9999;
display: none;
justify-content: center;
align-items: center;
flex-direction: column;
}

.loading-3d.active {
display: flex;
}

.spinner-3d {
width: 60px;
height: 60px;
border: 4px solid transparent;
border-top: 4px solid var(--accent-purple);
border-right: 4px solid var(--accent-purple);
border-radius: 50%;
animation: spin3d 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
box-shadow: 0 0 30px var(--accent-purple-glow);
}

@keyframes spin3d {
0% { transform: perspective(500px) rotateY(0deg) rotateX(0deg); }
100% { transform: perspective(500px) rotateY(360deg) rotateX(360deg); }
}

/* تذييل */
.footer-3d {
text-align: center;
padding: 20px;
margin-top: 30px;
color: var(--text-secondary);
border-top: 1px solid var(--glass-border);
font-size: 0.9em;
}

.footer-3d button {
background: none;
border: none;
color: var(--accent-purple);
font-family: 'Cairo', sans-serif;
font-size: inherit;
cursor: pointer;
display: inline;
padding: 0;
}

.footer-3d button:hover {
text-decoration: underline;
}

/* Scrollbar */
::-webkit-scrollbar {
width: 6px;
}

::-webkit-scrollbar-track {
background: var(--bg-primary);
}

::-webkit-scrollbar-thumb {
background: var(--accent-purple);
border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
background: var(--accent-purple-dark);
}

/* تصميم متجاوب */
@media (max-width: 768px) {
.user-profile-content {
flex-direction: column;
text-align: center;
}

.balance-content {
flex-direction: column;
gap: 10px;
text-align: center;
}

.balance-amount {
font-size: 2em;
}

.stats-grid-3d {
gap: 15px;
}

.stat-card-3d {
padding: 15px;
}

.stat-value-3d {
font-size: 1.5em;
}

.modal-3d {
padding: 30px 20px;
}

.modal-title {
font-size: 2em;
}
}

/* تصميم بطاقة الهدية */
.gift-card {
background: linear-gradient(145deg, #1a1a1a, #0d0d0d);
border: 2px solid var(--accent-purple);
border-radius: 25px;
padding: 30px;
text-align: center;
margin: 20px 0;
position: relative;
overflow: hidden;
}

.gift-card::before {
content: '🎁';
position: absolute;
top: -20px;
right: -20px;
font-size: 120px;
opacity: 0.1;
transform: rotate(15deg);
}

.gift-icon {
font-size: 60px;
color: var(--accent-purple);
margin-bottom: 15px;
filter: drop-shadow(0 0 20px var(--accent-purple-glow));
animation: giftFloat 3s ease-in-out infinite;
}

@keyframes giftFloat {
0%, 100% { transform: translateY(0); }
50% { transform: translateY(-10px); }
}

.gift-amount {
font-size: 3em;
font-weight: 900;
color: var(--accent-purple);
text-shadow: 0 0 20px var(--accent-purple-glow);
margin: 15px 0;
}

.gift-timer {
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 50px;
padding: 12px;
margin: 15px 0;
font-size: 1.1em;
}

.claim-btn {
background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
color: white;
border: none;
border-radius: 50px;
padding: 15px 40px;
font-size: 1.3em;
font-weight: 900;
cursor: pointer;
transition: all 0.3s;
margin: 15px 0;
box-shadow: 0 10px 30px var(--accent-purple-glow);
display: inline-block;
text-decoration: none;
}

.claim-btn:hover {
transform: scale(1.05);
box-shadow: 0 20px 40px var(--accent-purple-glow);
}

.claim-btn:disabled {
opacity: 0.5;
cursor: not-allowed;
transform: none;
}

/* كليشه الشحن */
.charge-cliche {
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 15px;
padding: 20px;
margin: 15px 0;
font-size: 1em;
line-height: 1.8;
white-space: pre-line;
}

/* زر الرجوع */
.back-link {
display: inline-block;
margin-top: 20px;
color: var(--accent-purple);
text-decoration: none;
font-size: 1em;
transition: all 0.3s;
background: none;
border: none;
font-family: 'Cairo', sans-serif;
cursor: pointer;
}

.back-link:hover {
transform: translateX(-5px);
text-shadow: 0 0 10px var(--accent-purple-glow);
}

/* بطاقة الدعوة */
.invite-card {
background: linear-gradient(145deg, #1a1a1a, #0d0d0d);
border: 2px solid var(--accent-purple);
border-radius: 25px;
padding: 30px;
text-align: center;
margin: 20px 0;
position: relative;
overflow: hidden;
}

.invite-card::before {
content: '🔗';
position: absolute;
top: -20px;
right: -20px;
font-size: 120px;
opacity: 0.1;
transform: rotate(15deg);
}

.invite-link-box {
background: var(--bg-tertiary);
border: 1px solid var(--glass-border);
border-radius: 15px;
padding: 15px;
margin: 20px 0;
direction: ltr;
text-align: left;
font-family: monospace;
font-size: 0.9em;
word-break: break-all;
color: var(--accent-purple);
border: 1px solid var(--accent-purple);
}

.copy-btn {
background: var(--accent-purple);
color: white;
border: none;
border-radius: 10px;
padding: 10px 20px;
margin-top: 10px;
cursor: pointer;
transition: all 0.3s;
font-size: 1em;
}

.copy-btn:hover {
transform: scale(1.05);
box-shadow: 0 0 20px var(--accent-purple-glow);
}

.invite-stats {
display: flex;
justify-content: space-around;
margin: 20px 0;
padding: 15px;
background: var(--glass-bg);
border: 1px solid var(--glass-border);
border-radius: 15px;
}

.invite-stat-item {
text-align: center;
}

.invite-stat-value {
font-size: 2em;
font-weight: 900;
color: var(--accent-purple);
}

.invite-stat-label {
color: var(--text-secondary);
font-size: 0.9em;
}
/* ===== القوائم المنسدلة المتحركة ===== */
.custom-dropdown {
    position: relative;
    width: 100%;
    margin-bottom: 15px;
}

.dropdown-selected {
    background: var(--bg-tertiary);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    padding: 15px;
    color: var(--text-primary);
    font-size: 1em;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}

.dropdown-selected:hover {
    border-color: var(--accent-purple);
    box-shadow: 0 0 15px var(--accent-purple-glow);
}

.dropdown-selected i {
    color: var(--accent-purple);
    transition: transform 0.3s;
}

.dropdown-selected.active i {
    transform: rotate(180deg);
}

.dropdown-items {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg-secondary);
    border: 1px solid var(--accent-purple);
    border-radius: 12px;
    margin-top: 5px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 10px 30px rgba(138, 43, 226, 0.3);
    backdrop-filter: blur(10px);
}

.dropdown-items.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    padding: 15px;
    cursor: pointer;
    transition: all 0.2s;
    border-bottom: 1px solid var(--glass-border);
    color: var(--text-primary);
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item:hover {
    background: var(--accent-purple);
    color: white;
    transform: translateX(-5px);
}

.dropdown-item.selected {
    background: var(--accent-purple);
    color: white;
}

/* تخصيص شريط التمرير */
.dropdown-items::-webkit-scrollbar {
    width: 4px;
}

.dropdown-items::-webkit-scrollbar-track {
    background: var(--bg-tertiary);
}

.dropdown-items::-webkit-scrollbar-thumb {
    background: var(--accent-purple);
    border-radius: 4px;
}

</style>
</head>
<body oncontextmenu="return false;" onselectstart="return false;" ondragstart="return false;">
<!-- خلفية 3D -->
<div class="background-3d">
<div class="grid-3d"></div>
</div>

<!-- شاشة التحميل -->
<div class="loading-3d" id="loading">
<div class="spinner-3d"></div>
<p style="margin-top: 20px; color: var(--accent-purple); font-size: 1.1em;">جاري معالجة طلبك...</p>
</div>

<!-- القائمة المنبثقة 3D -->
<?php if ($show_modal): ?>
<div class="modal-overlay" id="modalOverlay">
<div class="modal-3d">
<div class="modal-close" onclick="closeModal()">
<i class="fas fa-times"></i>
</div>
<div class="modal-icon"><?php echo $modal_icon; ?></div>
<h2 class="modal-title"><?php echo $modal_title; ?></h2>
<div class="modal-message"><?php echo nl2br(htmlspecialchars($modal_message)); ?></div>
<button class="modal-button" onclick="closeModal()">حسناً</button>
</div>
</div>
<?php endif; ?>

<!-- زر فتح القائمة الجانبية -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- القائمة الجانبية المعدلة - كلها أزرار الآن -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>سميث ماتريكس</h3>
        <p style="color: var(--text-secondary); font-size: 0.9em;">منصة الخدمات</p>
    </div>
    
    <!-- حاوية القائمة القابلة للتمرير -->
    <div class="sidebar-menu-container">
        <ul class="sidebar-menu">
            <li>
                <button onclick="navigateToPage('main')" class="menu-button <?php echo $page == 'main' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>الرئيسية</span>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('services')" class="menu-button <?php echo $page == 'services' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>طلب خدمة</span>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('store')" class="menu-button <?php echo $page == 'store' ? 'active' : ''; ?>">
                    <i class="fas fa-store"></i>
                    <span>قسم الاستبدال</span>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('invite')" class="menu-button <?php echo $page == 'invite' ? 'active' : ''; ?>">
                    <i class="fas fa-link"></i>
                    <span>رابط الدعوة</span>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('terms')" class="menu-button <?php echo $page == 'terms' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>شروط البوت</span>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('charge')" class="menu-button <?php echo $page == 'charge' ? 'active' : ''; ?>">
                    <i class="fas fa-coins"></i>
                    <span>شحن النقاط</span>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('daily_gift')" class="menu-button <?php echo $page == 'daily_gift' ? 'active' : ''; ?>">
                    <i class="fas fa-gift"></i>
                    <span>الهدية اليومية</span>
                </button>
            </li>
            <li>
    <button onclick="navigateToPage('redeem_gift')" class="menu-button <?php echo $page == 'redeem_gift' ? 'active' : ''; ?>">
        <i class="fas fa-ticket-alt"></i>
        <span>استبدال كود هدية</span>
    </button>
</li>
            <li>
            
                <button onclick="window.open('<?php echo $channel_link; ?>', '_blank')" class="menu-button">
                    <i class="fas fa-telegram"></i>
                    <span>قناة البوت</span>
                </button>
            </li>
        </ul>
    </div>
    
    <!-- تذييل القائمة -->
    <div class="sidebar-footer">
        <i class="fas fa-crown" style="color: gold;"></i> الإصدار 3.0
    </div>
</div>

<!-- المحتوى الرئيسي -->
<div class="main-container">
    <!-- بطاقة المستخدم 3D - تظهر في جميع الصفحات -->
    <div class="user-profile-3d">
        <div class="user-profile-content">
            <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="User Avatar" class="user-avatar-3d">
            <div class="user-info-3d">
                <h2><?php echo htmlspecialchars($first_name); ?></h2>
                <p><i class="fas fa-at"></i> @<?php echo htmlspecialchars($username ?: 'لا يوجد'); ?></p>
                <p><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($chat_id); ?></p>
                <div class="user-stats-3d">
                    <div class="stat-badge">
                        <i class="fas fa-coins"></i> <?php echo number_format($coin, 4); ?> <?php echo $currency; ?>
                    </div>
                    <div class="stat-badge">
                        <i class="fas fa-chart-line"></i> <?php echo $user_orders_count; ?> طلب
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($page == 'main'): ?>
    <!-- الصفحة الرئيسية فقط - تظهر فيها الإحصائيات وبطاقة الرصيد -->
    <div class="stats-grid-3d">
        <div class="stat-card-3d">
            <div class="stat-icon-3d"><i class="fas fa-wallet"></i></div>
            <div class="stat-value-3d"><?php echo number_format($coin, 4); ?></div>
            <div class="stat-label-3d">رصيدك الحالي</div>
        </div>
        <div class="stat-card-3d">
            <div class="stat-icon-3d"><i class="fas fa-history"></i></div>
            <div class="stat-value-3d"><?php echo number_format($user_orders_count); ?></div>
            <div class="stat-label-3d">طلباتك الشخصية</div>
        </div>
    </div>

    <div class="balance-card-3d">
        <div class="balance-content">
            <span class="balance-label"><i class="fas fa-coins"></i> رصيدك القابل للاستخدام</span>
            <span class="balance-amount">
                <?php echo number_format($coin, 4); ?> 
                <small style="font-size: 0.5em;"><?php echo $currency; ?></small>
            </span>
        </div>
    </div>

    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-rocket"></i> ابدأ طلبك الآن</h2>
        <p style="color: var(--text-secondary); margin-bottom: 20px;">اختر من قائمة الخدمات المتاحة لدينا</p>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
            <!-- تم استبدال الروابط بأزرار -->
            <button onclick="navigateToPage('services')" class="stat-card-3d" style="text-align: center; padding: 15px; border: none; background: linear-gradient(145deg, var(--bg-secondary), #000000);">
                <div class="stat-icon-3d"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-label-3d">طلب خدمة</div>
            </button>
            <button onclick="navigateToPage('store')" class="stat-card-3d" style="text-align: center; padding: 15px; border: none; background: linear-gradient(145deg, var(--bg-secondary), #000000);">
                <div class="stat-icon-3d"><i class="fas fa-store"></i></div>
                <div class="stat-label-3d">قسم الاستبدال</div>
            </button>
            <button onclick="navigateToPage('invite')" class="stat-card-3d" style="text-align: center; padding: 15px; border: none; background: linear-gradient(145deg, var(--bg-secondary), #000000);">
                <div class="stat-icon-3d"><i class="fas fa-link"></i></div>
                <div class="stat-label-3d">رابط الدعوة</div>
            </button>
            <button onclick="navigateToPage('daily_gift')" class="stat-card-3d" style="text-align: center; padding: 15px; border: none; background: linear-gradient(145deg, var(--bg-secondary), #000000);">
                <div class="stat-icon-3d"><i class="fas fa-gift"></i></div>
                <div class="stat-label-3d">الهدية اليومية</div>
            </button>
            <button onclick="navigateToPage('charge')" class="stat-card-3d" style="text-align: center; padding: 15px; border: none; background: linear-gradient(145deg, var(--bg-secondary), #000000);">
                <div class="stat-icon-3d"><i class="fas fa-coins"></i></div>
                <div class="stat-label-3d">شحن النقاط</div>
                <button onclick="navigateToPage('redeem_gift')" class="stat-card-3d" style="text-align: center; padding: 15px; border: none; background: linear-gradient(145deg, var(--bg-secondary), #000000);">
    <div class="stat-icon-3d"><i class="fas fa-ticket-alt"></i></div>
    <div class="stat-label-3d">استبدال كود</div>
</button>
            </button>
        </div>
    </div>

    <?php elseif ($page == 'services'): ?>
    <!-- صفحة طلب الخدمة فقط - بدون إحصائيات -->
    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-shopping-cart"></i> طلب خدمة جديدة</h2>

        <form method="POST" action="?id=<?php echo $chat_id; ?>&key=<?php echo $key; ?>&page=services" onsubmit="showLoading()">
<!-- قسم القسم -->
<div class="form-group">
    <label class="form-label"><i class="fas fa-folder"></i> اختر القسم</label>
    <div class="custom-dropdown" id="categoryDropdown">
        <div class="dropdown-selected" onclick="toggleDropdown('category')">
            <span id="selectedCategory">اضغط لاختيار القسم</span>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div class="dropdown-items" id="categoryItems">
            <?php foreach ($sections as $uid => $section): ?>
            <div class="dropdown-item" onclick="selectCategory('<?php echo $uid; ?>', '<?php echo htmlspecialchars($section['name']); ?>')">
                <?php echo htmlspecialchars($section['name']); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- قسم الخدمة -->
<div class="form-group">
    <label class="form-label"><i class="fas fa-tag"></i> اختر الخدمة</label>
    <div class="custom-dropdown" id="serviceDropdown">
        <div class="dropdown-selected" onclick="toggleDropdown('service')">
            <span id="selectedService">اختر القسم أولاً</span>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div class="dropdown-items" id="serviceItems">
            <!-- هتتعبأ بالجافاسكريبت -->
        </div>
    </div>
</div>

<input type="hidden" name="section_uid" id="sectionUid">
<input type="hidden" name="service_uid" id="serviceUid">

            <div class="service-details-3d" id="serviceInfo">
                <div class="detail-row">
                    <span class="detail-label">🔢 أقل كمية:</span>
                    <span class="detail-value" id="serviceMin">0</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🔢 أقصى كمية:</span>
                    <span class="detail-value" id="serviceMax">0</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">💰 سعر 1000:</span>
                    <span class="detail-value" id="servicePrice">0</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-link"></i> رابط الحساب / المنشور</label>
                <input type="url" name="link" class="form-control" placeholder="https://..." required>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-sort-numeric-up"></i> الكمية المطلوبة</label>
                <input type="number" name="quantity" id="quantityInput" class="form-control" min="1" value="1000" step="any" required onkeyup="calculateTotalPrice()" onchange="calculateTotalPrice()">
            </div>

            <!-- حقل عرض التكلفة الإجمالية المباشرة -->
            <div class="total-price-3d" id="totalPriceDisplay" style="display: none;">
                💰 التكلفة الإجمالية: <span id="totalPriceValue">0</span> <?php echo $currency; ?>
            </div>

            <button type="submit" name="submit_order" class="submit-btn">
                <i class="fas fa-paper-plane"></i> إرسال الطلب الآن
            </button>
        </form>
        
        <!-- زر الرجوع (تم استبداله بزر) -->
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="navigateToPage('main')" class="back-link">
                <i class="fas fa-arrow-right"></i> رجوع للرئيسية
            </button>
        </div>
    </div>

    <?php elseif ($page == 'store'): ?>
    <!-- صفحة الاستبدال - بنفس تصميم الخدمات (اختر القسم ثم المنتج) -->
    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-store"></i> قسم الاستبدال</h2>

        <form method="POST" action="?id=<?php echo $chat_id; ?>&key=<?php echo $key; ?>&page=store" onsubmit="showLoading()">
            <!-- اختيار القسم -->
            <div class="form-group">
                <label class="form-label"><i class="fas fa-folder"></i> اختر القسم</label>
                <select name="store_section" id="storeSectionSelect" class="form-control" required onchange="loadStoreItems(this.value)">
                    <option value="">اضغط لاختيار القسم</option>
                    <?php foreach ($store_sections as $uid => $section): ?>
                        <option value="<?php echo htmlspecialchars($uid); ?>">
                            <?php echo htmlspecialchars($section['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- اختيار المنتج -->
            <div class="form-group">
                <label class="form-label"><i class="fas fa-tag"></i> اختر المنتج</label>
                <select name="store_item" id="storeItemSelect" class="form-control" required onchange="showStoreItemInfo()" disabled>
                    <option value="">اختر القسم أولاً</option>
                </select>
            </div>

            <input type="hidden" name="store_section_uid" id="storeSectionUid">
            <input type="hidden" name="store_item_uid" id="storeItemUid">

            <!-- تفاصيل المنتج -->
            <div class="service-details-3d" id="storeItemInfo" style="display: none;">
                <div class="detail-row">
                    <span class="detail-label">📦 اسم المنتج:</span>
                    <span class="detail-value" id="storeItemName">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">💰 السعر:</span>
                    <span class="detail-value" id="storeItemPrice">0</span>
                </div>
                <div class="detail-row" style="border-bottom: none;">
                    <span class="detail-label">📝 الوصف:</span>
                    <span class="detail-value" id="storeItemDescription" style="font-size: 0.9em; color: var(--text-secondary);">-</span>
                </div>
            </div>

            <!-- حقل حساب الاستلام -->
            <div class="form-group" id="deliveryField" style="display: none;">
                <label class="form-label"><i class="fas fa-pen"></i> أدخل حساب الاستلام</label>
                <input type="text" name="delivery_info" id="deliveryInfo" class="form-control" placeholder="مثال: @username أو رقم الهاتف" required>
                <small style="color: var(--text-secondary); display: block; margin-top: 5px;">سيتم إرسال هذا الحساب للأدمن لتسليمك المنتج.</small>
            </div>

            <button type="submit" name="confirm_store_buy" id="storeBuyBtn" class="submit-btn" style="display: none;">
                <i class="fas fa-check"></i> تأكيد الشراء
            </button>
        </form>

        <!-- زر الرجوع (تم استبداله بزر) -->
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="navigateToPage('main')" class="back-link">
                <i class="fas fa-arrow-right"></i> رجوع للرئيسية
            </button>
        </div>
    </div>

    <?php elseif ($page == 'invite'): ?>
    <!-- صفحة رابط الدعوة -->
    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-link"></i> رابط الدعوة الخاص بك</h2>

        <?php if ($invite_link_status != 'on'): ?>
        <div class="gift-card" style="border-color: var(--warning);">
            <div class="gift-icon"><i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i></div>
            <p style="color: var(--text-secondary); margin: 20px 0;">ميزة الدعوة معطلة حالياً من قبل الإدارة.</p>
        </div>
        <?php else: ?>
        <div class="invite-card">
            <div class="gift-icon"><i class="fas fa-share-alt"></i></div>

            <div class="invite-stats">
                <div class="invite-stat-item">
                    <div class="invite-stat-value"><?php echo number_format($share); ?></div>
                    <div class="invite-stat-label">عدد الدعوات</div>
                </div>
                <div class="invite-stat-item">
                    <div class="invite-stat-value"><?php echo number_format($invite_reward); ?></div>
                    <div class="invite-stat-label">نقاط لكل دعوة</div>
                </div>
            </div>

            <p style="color: var(--text-secondary); margin-bottom: 10px;">رابط الدعوة الخاص بك:</p>
            <div class="invite-link-box" id="inviteLink">https://t.me/<?php echo $username_bot; ?>?start=<?php echo $chat_id; ?></div>

            <button class="copy-btn" onclick="copyInviteLink()">
                <i class="fas fa-copy"></i> نسخ الرابط
            </button>

            <p style="color: var(--text-secondary); margin-top: 20px; font-size: 0.9em;">
                <i class="fas fa-info-circle"></i> كل شخص يدخل البوت عبر رابطك ستحصل على <?php echo $invite_reward; ?> <?php echo $currency; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- زر الرجوع (تم استبداله بزر) -->
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="navigateToPage('main')" class="back-link">
                <i class="fas fa-arrow-right"></i> رجوع للرئيسية
            </button>
        </div>
    </div>

    <?php elseif ($page == 'terms'): ?>
    <!-- صفحة الشروط - تعرض كليشه تعليمات البوت -->
    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-file-alt"></i> شروط وتعليمات البوت</h2>
        <div class="charge-cliche">
            <?php echo nl2br(htmlspecialchars($terms_text)); ?>
        </div>
        
        <!-- زر الرجوع (تم استبداله بزر) -->
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="navigateToPage('main')" class="back-link">
                <i class="fas fa-arrow-right"></i> رجوع للرئيسية
            </button>
        </div>
    </div>

    <?php elseif ($page == 'charge'): ?>
    <!-- صفحة شحن النقاط - تعرض كليشه الشحن وزر الدعم -->
    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-coins"></i> شحن النقاط</h2>
        <div class="charge-cliche">
            <?php echo nl2br(htmlspecialchars($charge_cliche)); ?>
        </div>
        <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
            <!-- تم استبدال الروابط الخارجية بأزرار -->
            <button onclick="window.open('https://t.me/ypiu5', '_blank')" class="claim-btn" style="display: inline-block; padding: 12px 30px; font-size: 1.1em; background: var(--accent-purple); border: none;">
                <i class="fas fa-telegram"></i> شحن عبر البوت
            </button>
            <button onclick="window.open('https://t.me/TJUI9', '_blank')" class="claim-btn" style="display: inline-block; padding: 12px 30px; font-size: 1.1em; background: linear-gradient(145deg, #ff7b00, #b45f06); border: none;">
                <i class="fas fa-headset"></i> الدعم الفني
            </button>
        </div>
        
        <!-- زر الرجوع (تم استبداله بزر) -->
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="navigateToPage('main')" class="back-link">
                <i class="fas fa-arrow-right"></i> رجوع للرئيسية
            </button>
        </div>
    </div>

    <?php elseif ($page == 'daily_gift'): ?>
    <!-- صفحة الهدية اليومية فقط -->
    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-gift"></i> الهدية اليومية</h2>

        <?php
        $now = time();
        $last_claim = $daily_gifts[$chat_id] ?? 0;
        $seconds_remaining = 86400 - ($now - $last_claim);
        $can_claim = ($seconds_remaining <= 0);
        ?>

        <div class="gift-card">
            <div class="gift-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="gift-amount">
                <?php echo number_format($daily_gift_amount); ?> <?php echo $currency; ?>
            </div>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">هدية يومية مجانية لك كل 24 ساعة</p>

            <?php if (!$can_claim): ?>
            <div class="gift-timer">
                <i class="fas fa-hourglass-half"></i>
                الوقت المتبقي: 
                <?php 
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                echo "{$hours} ساعة {$minutes} دقيقة";
                ?>
            </div>
            <?php endif; ?>

            <?php if ($can_claim): ?>
            <button onclick="claimDailyGift()" class="claim-btn">
                <i class="fas fa-gift"></i> احصل على هديتك
            </button>
            <?php else: ?>
            <button class="claim-btn" disabled>
                <i class="fas fa-clock"></i> انتظر حتى موعد الهدية
            </button>
            <?php endif; ?>
        </div>

        <!-- زر الرجوع (تم استبداله بزر) -->
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="navigateToPage('main')" class="back-link">
                <i class="fas fa-arrow-right"></i> رجوع للرئيسية
            </button>
        </div>
    </div>
    <?php endif; ?>
<?php if ($page == 'daily_gift'): ?>
    <!-- محتوى صفحة الهدية اليومية -->
    <div class="order-form-3d">...</div>
<?php elseif ($page == 'redeem_gift'): ?>
    <!-- صفحة استبدال كود الهدية -->
    <div class="order-form-3d">
        <h2 class="form-title"><i class="fas fa-gift"></i> استبدال كود هدية</h2>
        
        <div class="gift-card" style="border-color: var(--accent-purple);">
            <div class="gift-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                أدخل كود الهدية الذي حصلت عليه لشحن رصيدك فوراً.
            </p>
            
            <form method="POST" action="?id=<?php echo $chat_id; ?>&key=<?php echo $key; ?>&page=redeem_gift" onsubmit="showLoading()">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-key"></i> كود الهدية</label>
                    <input type="text" name="gift_code" class="form-control" placeholder="أدخل الكود هنا" required style="text-align: center; font-size: 1.2em; letter-spacing: 2px; direction: ltr;">
                </div>
                
                <button type="submit" name="redeem_gift_code" class="submit-btn">
                    <i class="fas fa-gift"></i> استبدل الكود
                </button>
            </form>
            
            <!-- زر الرجوع -->
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="navigateToPage('main')" class="back-link">
                    <i class="fas fa-arrow-right"></i> رجوع للرئيسية
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
    <!-- التذييل - يظهر في جميع الصفحات -->
    <div class="footer-3d">
        <p>© 2024 سميث ماتريكس - جميع الحقوق محفوظة</p>
        <p style="margin-top: 5px;">⚡ برمجة وتطوير 
            <button onclick="window.open('https://t.me/ypiu5', '_blank')" style="color: var(--accent-purple); background: none; border: none; font-family: inherit; font-size: inherit; cursor: pointer;">
                @ypiu5
            </button>
        </p>
    </div>
</div>

<script>
// بيانات الخدمات
const sectionsData = <?php echo json_encode($sections); ?>;
// بيانات المتجر
const storeSectionsData = <?php echo json_encode($store_sections); ?>;

// منع القائمة المنبثقة عند الضغط المطول
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

document.addEventListener('touchstart', function(e) {
    const target = e.target;
    if (target.tagName === 'BUTTON' || target.closest('button') || target.classList.contains('stat-card-3d')) {
        // السماح بالنقر العادي
        return true;
    }
    
    // منع الضغط المطول على باقي العناصر
    let timer = setTimeout(() => {
        e.preventDefault();
    }, 300);
    
    const touchEndHandler = () => clearTimeout(timer);
    const touchMoveHandler = () => clearTimeout(timer);
    
    target.addEventListener('touchend', touchEndHandler, { once: true });
    target.addEventListener('touchmove', touchMoveHandler, { once: true });
}, { passive: false });

// دالة التنقل بين الصفحات بدون إظهار الرابط
function navigateToPage(page) {
    showLoading();
    
    // بناء الرابط مع الحفاظ على parameters
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page);
    
    // التنقل
    window.location.href = '?' + urlParams.toString();
}

// دالة الهدية اليومية
function claimDailyGift() {
    showLoading();
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('claim', '1');
    window.location.href = '?' + urlParams.toString();
}

// دالة إغلاق القائمة المنبثقة
function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
}

// النقر خارج القائمة المنبثقة لإغلاقها
document.addEventListener('click', function(e) {
    const modalOverlay = document.getElementById('modalOverlay');
    if (e.target === modalOverlay) {
        closeModal();
    }
});

// toggle sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.sidebar-toggle');

    if (!sidebar.contains(event.target) && !toggle.contains(event.target) && sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
    }
});

// show loading
function showLoading() {
    document.getElementById('loading').classList.add('active');
}

// hide loading after page load
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('loading').classList.remove('active');
    }, 500);
});

// copy invite link
function copyInviteLink() {
    const linkText = document.getElementById('inviteLink').innerText;
    navigator.clipboard.writeText(linkText).then(function() {
        alert('✅ تم نسخ الرابط بنجاح!');
    }, function() {
        alert('❌ فشل نسخ الرابط');
    });
}

// نظام القوائم المنسدلة
function toggleDropdown(type) {
    // إغلاق أي دروب داون مفتوح
    closeAllDropdowns();
    
    const items = document.getElementById(type + 'Items');
    const selected = document.querySelector(`#${type}Dropdown .dropdown-selected`);
    
    items.classList.toggle('show');
    selected.classList.toggle('active');
}

function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-items').forEach(item => {
        item.classList.remove('show');
    });
    document.querySelectorAll('.dropdown-selected').forEach(sel => {
        sel.classList.remove('active');
    });
}

// اختيار القسم
function selectCategory(uid, name) {
    document.getElementById('selectedCategory').textContent = name;
    document.getElementById('sectionUid').value = uid;
    
    // إغلاق القائمة
    closeAllDropdowns();
    
    // تحميل الخدمات
    loadServicesCustom(uid);
}

// تحميل الخدمات في الدروب داون
function loadServicesCustom(sectionUid) {
    const serviceItems = document.getElementById('serviceItems');
    const selectedService = document.getElementById('selectedService');
    const serviceInfo = document.getElementById('serviceInfo');
    const totalPriceDiv = document.getElementById('totalPriceDisplay');
    
    serviceItems.innerHTML = '';
    selectedService.textContent = 'اختر الخدمة';
    document.getElementById('serviceUid').value = '';
    serviceInfo.classList.remove('active');
    totalPriceDiv.style.display = 'none';
    
    if (sectionUid && sectionsData[sectionUid] && sectionsData[sectionUid].services) {
        const services = sectionsData[sectionUid].services;
        
        for (let uid in services) {
            const div = document.createElement('div');
            div.className = 'dropdown-item';
            div.textContent = services[uid].name;
            div.setAttribute('onclick', `selectService('${uid}', '${services[uid].name}', ${services[uid].min || 0}, ${services[uid].max || 0}, ${services[uid].price || 0})`);
            serviceItems.appendChild(div);
        }
    }
}

// اختيار الخدمة
function selectService(uid, name, min, max, price) {
    document.getElementById('selectedService').textContent = name;
    document.getElementById('serviceUid').value = uid;
    
    // تحديث تفاصيل الخدمة
    document.getElementById('serviceMin').textContent = min;
    document.getElementById('serviceMax').textContent = max;
    document.getElementById('servicePrice').textContent = price.toFixed(4);
    
    document.getElementById('serviceInfo').classList.add('active');
    
    // إغلاق القائمة
    closeAllDropdowns();
    
    // حساب السعر
    calculateTotalPrice();
}

// إغلاق الدروب داون عند النقر خارجها
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-dropdown')) {
        closeAllDropdowns();
    }
});

// تعديل دالة calculateTotalPrice للتعامل مع القيم الجديدة
function calculateTotalPrice() {
    const serviceUid = document.getElementById('serviceUid').value;
    const quantityInput = document.getElementById('quantityInput');
    const totalPriceDiv = document.getElementById('totalPriceDisplay');
    const totalPriceSpan = document.getElementById('totalPriceValue');
    
    if (!serviceUid || !quantityInput) return;
    
    // البحث عن سعر الخدمة
    let pricePerThousand = 0;
    for (let sectionUid in sectionsData) {
        if (sectionsData[sectionUid].services && sectionsData[sectionUid].services[serviceUid]) {
            pricePerThousand = sectionsData[sectionUid].services[serviceUid].price || 0;
            break;
        }
    }
    
    const quantity = parseFloat(quantityInput.value) || 0;
    
    if (quantity > 0 && pricePerThousand > 0) {
        const totalPrice = (quantity / 1000) * pricePerThousand;
        totalPriceSpan.textContent = totalPrice.toFixed(4);
        totalPriceDiv.style.display = 'block';
    } else {
        totalPriceDiv.style.display = 'none';
    }
}

// keyboard shortcut to toggle sidebar (Ctrl+B)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
    }
});

// تحميل منتجات المتجر حسب القسم
function loadStoreItems(sectionUid) {
    const itemSelect = document.getElementById('storeItemSelect');
    const itemInfo = document.getElementById('storeItemInfo');
    const sectionUidInput = document.getElementById('storeSectionUid');
    const deliveryField = document.getElementById('deliveryField');
    const buyBtn = document.getElementById('storeBuyBtn');
    
    sectionUidInput.value = sectionUid;
    
    itemSelect.innerHTML = '<option value="">اختر المنتج</option>';
    itemInfo.style.display = 'none';
    deliveryField.style.display = 'none';
    buyBtn.style.display = 'none';
    
    if (sectionUid && storeSectionsData[sectionUid] && storeSectionsData[sectionUid].items) {
        const items = storeSectionsData[sectionUid].items;
        
        for (let uid in items) {
            const option = document.createElement('option');
            option.value = uid;
            option.textContent = items[uid].name;
            option.dataset.price = items[uid].price || 0;
            option.dataset.description = items[uid].description || 'لا يوجد وصف';
            itemSelect.appendChild(option);
        }
        
        itemSelect.disabled = false;
    } else {
        itemSelect.disabled = true;
    }
}

// عرض معلومات المنتج
function showStoreItemInfo() {
    const itemSelect = document.getElementById('storeItemSelect');
    const itemInfo = document.getElementById('storeItemInfo');
    const itemUidInput = document.getElementById('storeItemUid');
    const deliveryField = document.getElementById('deliveryField');
    const buyBtn = document.getElementById('storeBuyBtn');
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        itemUidInput.value = selectedOption.value;
        
        document.getElementById('storeItemName').textContent = selectedOption.textContent;
        document.getElementById('storeItemPrice').textContent = parseFloat(selectedOption.dataset.price).toFixed(4) + ' <?php echo $currency; ?>';
        document.getElementById('storeItemDescription').textContent = selectedOption.dataset.description;
        
        itemInfo.style.display = 'block';
        deliveryField.style.display = 'block';
        buyBtn.style.display = 'block';
    } else {
        itemInfo.style.display = 'none';
        deliveryField.style.display = 'none';
        buyBtn.style.display = 'none';
    }
}

// منع الضغط المطول على كل العناصر القابلة للنقر
document.querySelectorAll('.stat-card-3d, .product-card-3d, .section-card-3d, .buy-btn, .submit-btn, .claim-btn, .back-link, .menu-button').forEach(el => {
    el.addEventListener('contextmenu', (e) => e.preventDefault());
    el.addEventListener('touchstart', (e) => {
        // منع الضغط المطول لكن السماح بالنقر العادي
        let timer = setTimeout(() => {
            e.preventDefault();
        }, 300);
        
        el.addEventListener('touchend', () => clearTimeout(timer), { once: true });
        el.addEventListener('touchmove', () => clearTimeout(timer), { once: true });
    }, { passive: false });
});
</script>
</body>
</html>