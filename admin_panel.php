<?php
// admin_panel.php - لوحة تحكم المدير (محاكاة كاملة للبوت)
session_start();
ob_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['bot_token'])) {
    header("Location: admin_login.php");
    exit();
}

$ADMIN_ID = $_SESSION['admin_id'];
$BOT_TOKEN = $_SESSION['bot_token'];
$BOT_USERNAME = $_SESSION['bot_username'] ?? 'bot';
$BOT_NAME = $_SESSION['bot_name'] ?? 'Bot';

// تحديد المسارات
$NAMERO_DIR = __DIR__ . '/NAMERO/';

// البحث عن مجلد البوت
$bot_folders = glob($NAMERO_DIR . '*', GLOB_ONLYDIR);
if (empty($bot_folders)) {
    // إنشاء المجلد إذا لم يكن موجوداً
    $bot_id = explode(':', $BOT_TOKEN)[0];
    $BOT_ID_DIR = $NAMERO_DIR . $bot_id . '/';
    if (!is_dir($BOT_ID_DIR)) {
        mkdir($BOT_ID_DIR, 0777, true);
    }
} else {
    $BOT_ID_DIR = $bot_folders[0] . '/';
}

// تحميل البيانات
$api_settings_file = $BOT_ID_DIR . 'api_settings.json';
$Namero_file = $BOT_ID_DIR . 'Namero.json';

$settings = file_exists($api_settings_file) ? json_decode(file_get_contents($api_settings_file), true) : [];
$Namero = file_exists($Namero_file) ? json_decode(file_get_contents($Namero_file), true) : [];

// دوال مساعدة
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

// معالجة طلبات POST
$message = '';
$message_type = '';

// تحديث الإعدادات العامة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings['currency'] = $_POST['currency'] ?? 'نقاط';
    $settings['daily_gift'] = floatval($_POST['daily_gift'] ?? 20);
    $settings['min_order_quantity'] = intval($_POST['min_order_quantity'] ?? 10);
    $settings['invite_reward'] = floatval($_POST['invite_reward'] ?? 5);
    $settings['user_price'] = floatval($_POST['user_price'] ?? 100);
    $settings['Ch'] = $_POST['channel_link'] ?? 'https://t.me/TJUI9';
    $settings['domain'] = $_POST['charge_cliche'] ?? '';
    $settings['token'] = $_POST['terms_text'] ?? '';
    
    // حالات التشغيل
    $settings['daily_gift_status'] = isset($_POST['daily_gift_status']) ? 'on' : 'off';
    $settings['invite_link_status'] = isset($_POST['invite_link_status']) ? 'on' : 'off';
    $settings['transfer_status'] = isset($_POST['transfer_status']) ? 'on' : 'off';
    $settings['starss'] = isset($_POST['starss']) ? 'on' : 'off';
    $settings['Market'] = isset($_POST['Market']) ? 'on' : 'off';
    $settings['rshaq'] = isset($_POST['rshaq']) ? 'on' : 'off';
    
    file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $Namero['rshaq'] = $settings['rshaq'];
    file_put_contents($Namero_file, json_encode($Namero, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $message = "✅ تم تحديث الإعدادات بنجاح!";
    $message_type = "success";
}

// إضافة قسم جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $section_name = trim($_POST['section_name'] ?? '');
    
    if (!empty($section_name)) {
        $existing_uid = findUIDbyName($section_name, $settings["sections"] ?? []);
        if ($existing_uid === null) {
            $new_uid = generateUID();
            $settings["sections"][$new_uid] = [
                "name" => $section_name,
                "services" => []
            ];
            file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = "✅ تم إضافة القسم: $section_name";
            $message_type = "success";
        } else {
            $message = "❌ هذا القسم موجود بالفعل!";
            $message_type = "error";
        }
    }
}

// حذف قسم
if (isset($_GET['delete_section'])) {
    $section_uid = $_GET['delete_section'];
    if (isset($settings["sections"][$section_uid])) {
        $section_name = $settings["sections"][$section_uid]['name'];
        unset($settings["sections"][$section_uid]);
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم حذف القسم: $section_name";
        $message_type = "success";
    }
}

// إضافة خدمة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $section_uid = $_POST['section_uid'] ?? '';
    $service_name = trim($_POST['service_name'] ?? '');
    $min = floatval($_POST['min'] ?? 10);
    $max = floatval($_POST['max'] ?? 1000);
    $price = floatval($_POST['price'] ?? 1000);
    $service_id = trim($_POST['service_id'] ?? '');
    $domain = trim($_POST['domain'] ?? '');
    $api_key = trim($_POST['api_key'] ?? '');
    $delay = intval($_POST['delay'] ?? 0);
    
    if (!empty($section_uid) && !empty($service_name) && isset($settings["sections"][$section_uid])) {
        $service_uid = generateUID();
        $settings["sections"][$section_uid]["services"][$service_uid] = [
            "name" => $service_name,
            "min" => $min,
            "max" => $max,
            "price" => $price,
            "service_id" => $service_id,
            "domain" => $domain,
            "api" => $api_key,
            "delay" => $delay
        ];
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم إضافة الخدمة: $service_name";
        $message_type = "success";
    }
}

// تحديث خدمة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    $section_uid = $_POST['section_uid'] ?? '';
    $service_uid = $_POST['service_uid'] ?? '';
    $service_name = trim($_POST['service_name'] ?? '');
    $min = floatval($_POST['min'] ?? 10);
    $max = floatval($_POST['max'] ?? 1000);
    $price = floatval($_POST['price'] ?? 1000);
    $service_id = trim($_POST['service_id'] ?? '');
    $domain = trim($_POST['domain'] ?? '');
    $api_key = trim($_POST['api_key'] ?? '');
    $delay = intval($_POST['delay'] ?? 0);
    
    if (!empty($section_uid) && !empty($service_uid) && isset($settings["sections"][$section_uid]["services"][$service_uid])) {
        $settings["sections"][$section_uid]["services"][$service_uid] = [
            "name" => $service_name,
            "min" => $min,
            "max" => $max,
            "price" => $price,
            "service_id" => $service_id,
            "domain" => $domain,
            "api" => $api_key,
            "delay" => $delay
        ];
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم تحديث الخدمة: $service_name";
        $message_type = "success";
    }
}

// حذف خدمة
if (isset($_GET['delete_service'])) {
    $parts = explode('_', $_GET['delete_service']);
    $section_uid = $parts[0] ?? '';
    $service_uid = $parts[1] ?? '';
    
    if (isset($settings["sections"][$section_uid]["services"][$service_uid])) {
        $service_name = $settings["sections"][$section_uid]["services"][$service_uid]['name'];
        unset($settings["sections"][$section_uid]["services"][$service_uid]);
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم حذف الخدمة: $service_name";
        $message_type = "success";
    }
}

// إضافة قسم متجر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_store_section'])) {
    $section_name = trim($_POST['store_section_name'] ?? '');
    
    if (!empty($section_name)) {
        $new_uid = generateUID();
        $settings["store"]["sections"][$new_uid] = [
            "name" => $section_name,
            "items" => []
        ];
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم إضافة قسم المتجر: $section_name";
        $message_type = "success";
    }
}

// حذف قسم متجر
if (isset($_GET['delete_store_section'])) {
    $section_uid = $_GET['delete_store_section'];
    if (isset($settings["store"]["sections"][$section_uid])) {
        $section_name = $settings["store"]["sections"][$section_uid]['name'];
        unset($settings["store"]["sections"][$section_uid]);
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم حذف قسم المتجر: $section_name";
        $message_type = "success";
    }
}

// إضافة منتج للمتجر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_store_item'])) {
    $section_uid = $_POST['store_section_uid'] ?? '';
    $item_name = trim($_POST['item_name'] ?? '');
    $item_price = floatval($_POST['item_price'] ?? 0);
    $item_description = trim($_POST['item_description'] ?? '');
    
    if (!empty($section_uid) && !empty($item_name) && isset($settings["store"]["sections"][$section_uid])) {
        $item_uid = generateUID();
        $settings["store"]["sections"][$section_uid]["items"][$item_uid] = [
            "name" => $item_name,
            "price" => $item_price,
            "description" => $item_description
        ];
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم إضافة المنتج: $item_name";
        $message_type = "success";
    }
}

// تحديث منتج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_store_item'])) {
    $section_uid = $_POST['store_section_uid'] ?? '';
    $item_uid = $_POST['item_uid'] ?? '';
    $item_name = trim($_POST['item_name'] ?? '');
    $item_price = floatval($_POST['item_price'] ?? 0);
    $item_description = trim($_POST['item_description'] ?? '');
    
    if (!empty($section_uid) && !empty($item_uid) && isset($settings["store"]["sections"][$section_uid]["items"][$item_uid])) {
        $settings["store"]["sections"][$section_uid]["items"][$item_uid] = [
            "name" => $item_name,
            "price" => $item_price,
            "description" => $item_description
        ];
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم تحديث المنتج: $item_name";
        $message_type = "success";
    }
}

// حذف منتج
if (isset($_GET['delete_store_item'])) {
    $parts = explode('_', $_GET['delete_store_item']);
    $section_uid = $parts[0] ?? '';
    $item_uid = $parts[1] ?? '';
    
    if (isset($settings["store"]["sections"][$section_uid]["items"][$item_uid])) {
        $item_name = $settings["store"]["sections"][$section_uid]["items"][$item_uid]['name'];
        unset($settings["store"]["sections"][$section_uid]["items"][$item_uid]);
        file_put_contents($api_settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "✅ تم حذف المنتج: $item_name";
        $message_type = "success";
    }
}

// متابعة الطلبات
if (isset($_GET['check_orders'])) {
    $updated = 0;
    $notified = 0;
    
    foreach ($Namero["orders"] ?? [] as $user_id => $orders) {
        foreach ($orders as $index => $order) {
            if ($order["status"] == "جاري التنفيذ") {
                $sectionUID = $order["section_uid"] ?? '';
                $serviceUID = $order["service_uid"] ?? '';
                
                if (isset($settings["sections"][$sectionUID]["services"][$serviceUID])) {
                    $service_info = $settings["sections"][$sectionUID]["services"][$serviceUID];
                    $domain = $service_info["domain"] ?? '';
                    $api = $service_info["api"] ?? '';
                    $order_id = $order["order_id"] ?? '';
                    
                    if (!empty($domain) && !empty($api) && !empty($order_id)) {
                        $status_url = "https://$domain/api/v2?key=$api&action=status&order=$order_id";
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $status_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                        $status_response = curl_exec($ch);
                        curl_close($ch);
                        
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
                                    $refund_amount = floor($order["price"] * 0.5);
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
                                $Namero["orders"][$user_id][$index]["status"] = $new_status;
                                $Namero["orders"][$user_id][$index]["updated_at"] = time();
                                
                                if ($refund_amount > 0) {
                                    $Namero["coin"][$user_id] = ($Namero["coin"][$user_id] ?? 0) + $refund_amount;
                                    $Namero["orders"][$user_id][$index]["refunded"] = $refund_amount;
                                }
                                
                                $updated++;
                                
                                // إرسال إشعار للمستخدم
                                $notify_text = "📢 *تحديث حالة الطلب*\n\n";
                                $notify_text .= "🆔 رقم الطلب: `{$order_id}`\n";
                                $notify_text .= "📦 الخدمة: {$order['service']}\n";
                                $notify_text .= "🔢 الكمية: {$order['quantity']}\n";
                                $notify_text .= "💰 السعر: {$order['price']}\n";
                                $notify_text .= "📊 الحالة: {$new_status}\n";
                                
                                if ($refund_amount > 0) {
                                    $notify_text .= "\n💰 تم استرداد: {$refund_amount} {$settings['currency']}\n";
                                }
                                
                                sendTelegramMessage($BOT_TOKEN, $user_id, $notify_text, 'markdown');
                                $notified++;
                            }
                        }
                    }
                }
            }
        }
    }
    
    file_put_contents($Namero_file, json_encode($Namero, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $message = "✅ تم تحديث $updated طلب وإرسال $notified إشعار";
    $message_type = "success";
}

// إرسال رسالة تيليجرام
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

// الصفحة الحالية
$current_tab = $_GET['tab'] ?? 'dashboard';

// جلب الخدمة المحددة للتعديل
$edit_service = null;
if (isset($_GET['edit_service'])) {
    $parts = explode('_', $_GET['edit_service']);
    $section_uid = $parts[0] ?? '';
    $service_uid = $parts[1] ?? '';
    if (isset($settings["sections"][$section_uid]["services"][$service_uid])) {
        $edit_service = [
            'section_uid' => $section_uid,
            'service_uid' => $service_uid,
            'data' => $settings["sections"][$section_uid]["services"][$service_uid]
        ];
    }
}

// جلب المنتج المحدد للتعديل
$edit_item = null;
if (isset($_GET['edit_store_item'])) {
    $parts = explode('_', $_GET['edit_store_item']);
    $section_uid = $parts[0] ?? '';
    $item_uid = $parts[1] ?? '';
    if (isset($settings["store"]["sections"][$section_uid]["items"][$item_uid])) {
        $edit_item = [
            'section_uid' => $section_uid,
            'item_uid' => $item_uid,
            'data' => $settings["store"]["sections"][$section_uid]["items"][$item_uid]
        ];
    }
}

// إحصائيات سريعة
$total_sections = count($settings["sections"] ?? []);
$total_services = 0;
foreach ($settings["sections"] ?? [] as $section) {
    $total_services += count($section["services"] ?? []);
}
$total_store_sections = count($settings["store"]["sections"] ?? []);
$total_store_items = 0;
foreach ($settings["store"]["sections"] ?? [] as $section) {
    $total_store_items += count($section["items"] ?? []);
}
$total_users = count($Namero["coin"] ?? []);
$total_orders = 0;
foreach ($Namero["orders"] ?? [] as $orders) {
    $total_orders += count($orders);
}
$total_balance = array_sum($Namero["coin"] ?? []);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - سميث ماتريكس</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
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

        /* خلفية متحركة */
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

        /* الهيدر */
        .header {
            background: linear-gradient(145deg, var(--bg-secondary), #000000);
            border-bottom: 2px solid var(--accent-purple);
            padding: 20px 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-purple);
            border-radius: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            color: white;
            transform: perspective(500px) rotateX(10deg);
            box-shadow: 0 10px 20px var(--accent-purple-glow);
        }

        .logo-text h1 {
            color: var(--accent-purple);
            font-size: 1.8em;
            text-shadow: 0 0 10px var(--accent-purple-glow);
        }

        .logo-text p {
            color: var(--text-secondary);
            font-size: 0.9em;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .bot-badge {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bot-badge i {
            color: var(--accent-purple);
        }

        .logout-btn {
            background: linear-gradient(145deg, var(--danger), #cc0000);
            border: none;
            border-radius: 15px;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(255,68,68,0.3);
        }

        /* الحاوية الرئيسية */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* تبويبات */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab-btn {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 15px 25px;
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
        }

        .tab-btn:hover {
            background: var(--accent-purple);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px var(--accent-purple-glow);
        }

        .tab-btn.active {
            background: var(--accent-purple);
            color: white;
            box-shadow: 0 0 20px var(--accent-purple-glow);
        }

        .tab-btn i {
            font-size: 1.2em;
        }

        /* بطاقات الإحصائيات */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(145deg, var(--bg-secondary), #000000);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            transform: perspective(1000px) rotateX(2deg);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: perspective(1000px) rotateX(5deg) translateY(-5px);
            box-shadow: 0 20px 40px var(--accent-purple-glow);
            border-color: var(--accent-purple);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-purple);
            border-radius: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            color: white;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: 900;
            color: var(--accent-purple);
            text-shadow: 0 0 15px var(--accent-purple-glow);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 1.1em;
        }

        /* الرسائل */
        .alert {
            background: linear-gradient(145deg, var(--bg-secondary), #000000);
            border: 1px solid;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
        }

        .alert.success {
            border-color: var(--accent-purple);
            box-shadow: 0 0 20px var(--accent-purple-glow);
        }

        .alert.error {
            border-color: var(--danger);
            box-shadow: 0 0 20px rgba(255,68,68,0.2);
        }

        .alert-icon {
            font-size: 1.5em;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* النماذج */
        .form-card {
            background: linear-gradient(145deg, var(--bg-secondary), #000000);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
            transform: perspective(1000px) rotateX(2deg);
        }

        .form-title {
            font-size: 1.8em;
            color: var(--accent-purple);
            margin-bottom: 25px;
            text-shadow: 0 0 10px var(--accent-purple-glow);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 600;
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
            font-size: 1em;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-purple);
            box-shadow: 0 0 20px var(--accent-purple-glow);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--accent-purple);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px var(--accent-purple-glow);
        }

        .btn-danger {
            background: linear-gradient(145deg, var(--danger), #cc0000);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255,68,68,0.3);
        }

        .btn-success {
            background: linear-gradient(145deg, #00ff00, #00cc00);
            color: black;
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,255,0,0.3);
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.9em;
        }

        /* جداول */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--accent-purple);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--glass-border);
            text-align: center;
        }

        .table tr:hover {
            background: var(--glass-bg);
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }

        .badge-success {
            background: rgba(138,43,226,0.2);
            color: var(--accent-purple);
            border: 1px solid var(--accent-purple);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        /* قائمة الأقسام */
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .section-card {
            background: linear-gradient(145deg, var(--bg-secondary), #000000);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s;
        }

        .section-card:hover {
            border-color: var(--accent-purple);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px var(--accent-purple-glow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--glass-border);
        }

        .section-name {
            font-size: 1.3em;
            color: var(--accent-purple);
        }

        .section-id {
            font-size: 0.8em;
            color: var(--text-secondary);
        }

        .services-list {
            max-height: 200px;
            overflow-y: auto;
            margin: 10px 0;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            background: var(--glass-bg);
            border-radius: 8px;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .service-price {
            color: var(--accent-purple);
            font-weight: bold;
        }

        /* تذييل */
        .footer {
            text-align: center;
            padding: 30px;
            margin-top: 50px;
            color: var(--text-secondary);
            border-top: 1px solid var(--glass-border);
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="background-3d">
        <div class="grid-3d"></div>
    </div>

    <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="logo-text">
                    <h1>سميث ماتريكس</h1>
                    <p>لوحة التحكم - إدارة الخدمات</p>
                </div>
            </div>
            <div class="user-info">
                <div class="bot-badge">
                    <i class="fab fa-telegram"></i>
                    <span>@<?php echo htmlspecialchars($BOT_USERNAME); ?></span>
                </div>
                <a href="admin_login.php?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>تسجيل خروج</span>
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($message)): ?>
        <div class="alert <?php echo $message_type; ?>">
            <div class="alert-icon"><?php echo $message_type == 'success' ? '✅' : '❌'; ?></div>
            <div><?php echo $message; ?></div>
        </div>
        <?php endif; ?>

        <!-- تبويبات -->
        <div class="tabs">
            <a href="?tab=dashboard" class="tab-btn <?php echo $current_tab == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>الرئيسية</span>
            </a>
            <a href="?tab=settings" class="tab-btn <?php echo $current_tab == 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>الإعدادات</span>
            </a>
            <a href="?tab=sections" class="tab-btn <?php echo $current_tab == 'sections' ? 'active' : ''; ?>">
                <i class="fas fa-folder"></i>
                <span>أقسام الخدمات</span>
            </a>
            <a href="?tab=store" class="tab-btn <?php echo $current_tab == 'store' ? 'active' : ''; ?>">
                <i class="fas fa-store"></i>
                <span>المتجر</span>
            </a>
            <a href="?tab=orders" class="tab-btn <?php echo $current_tab == 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>الطلبات</span>
            </a>
        </div>

        <?php if ($current_tab == 'dashboard'): ?>
        <!-- لوحة المعلومات -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_sections; ?></div>
                </div>
                <div class="stat-label">أقسام الخدمات</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_services; ?></div>
                </div>
                <div class="stat-label">الخدمات</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_store_sections; ?></div>
                </div>
                <div class="stat-label">أقسام المتجر</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_store_items; ?></div>
                </div>
                <div class="stat-label">منتجات المتجر</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                </div>
                <div class="stat-label">المستخدمين</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                </div>
                <div class="stat-label">إجمالي الطلبات</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_balance, 2); ?></div>
                </div>
                <div class="stat-label">إجمالي الرصيد</div>
            </div>
        </div>

        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-chart-line"></i>
                إحصائيات سريعة
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div>
                    <h3 style="color: var(--accent-purple); margin-bottom: 15px;">آخر الطلبات</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_orders = [];
                                foreach ($Namero["orders"] ?? [] as $user_id => $orders) {
                                    foreach ($orders as $order) {
                                        $recent_orders[] = [
                                            'user' => $user_id,
                                            'service' => $order['service'] ?? '',
                                            'status' => $order['status'] ?? '',
                                            'time' => $order['time'] ?? 0
                                        ];
                                    }
                                }
                                usort($recent_orders, function($a, $b) {
                                    return $b['time'] - $a['time'];
                                });
                                $recent_orders = array_slice($recent_orders, 0, 5);
                                ?>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['user']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service']); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="3">لا توجد طلبات بعد</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h3 style="color: var(--accent-purple); margin-bottom: 15px;">أعلى الرصيد</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>المستخدم</th>
                                    <th>الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $top_balances = [];
                                foreach ($Namero["coin"] ?? [] as $user_id => $balance) {
                                    $top_balances[] = [
                                        'user' => $user_id,
                                        'balance' => $balance
                                    ];
                                }
                                usort($top_balances, function($a, $b) {
                                    return $b['balance'] - $a['balance'];
                                });
                                $top_balances = array_slice($top_balances, 0, 5);
                                ?>
                                <?php foreach ($top_balances as $balance): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($balance['user']); ?></td>
                                    <td><?php echo number_format($balance['balance'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($top_balances)): ?>
                                <tr>
                                    <td colspan="2">لا يوجد مستخدمين بعد</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($current_tab == 'settings'): ?>
        <!-- الإعدادات العامة -->
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-cog"></i>
                الإعدادات العامة
            </h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-coins"></i> اسم العملة</label>
                        <input type="text" name="currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency'] ?? 'نقاط'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-gift"></i> الهدية اليومية</label>
                        <input type="number" name="daily_gift" class="form-control" step="0.001" value="<?php echo htmlspecialchars($settings['daily_gift'] ?? 20); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-chart-line"></i> الحد الأدنى للتحويل</label>
                        <input type="number" name="min_order_quantity" class="form-control" value="<?php echo htmlspecialchars($settings['min_order_quantity'] ?? 10); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-users"></i> مكافأة الدعوة</label>
                        <input type="number" name="invite_reward" class="form-control" step="0.001" value="<?php echo htmlspecialchars($settings['invite_reward'] ?? 5); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-star"></i> سعر النقاط للنجوم</label>
                        <input type="number" name="user_price" class="form-control" step="0.001" value="<?php echo htmlspecialchars($settings['user_price'] ?? 100); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-telegram"></i> رابط القناة</label>
                        <input type="url" name="channel_link" class="form-control" value="<?php echo htmlspecialchars($settings['Ch'] ?? 'https://t.me/TJUI9'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-file-alt"></i> كليشة الشروط</label>
                        <textarea name="terms_text" class="form-control" rows="4"><?php echo htmlspecialchars($settings['token'] ?? '📜 شروط الاستخدام:\n\n1️⃣ ممنوع استخدام الخدمات في الأعمال المخالفة\n2️⃣ جميع الطلبات غير قابلة للاسترداد بعد التنفيذ\n3️⃣ للاستفسار والدعم الفني يرجى مراسلة البوت @ypiu5'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-coins"></i> كليشة الشحن</label>
                        <textarea name="charge_cliche" class="form-control" rows="4"><?php echo htmlspecialchars($settings['domain'] ?? '📌 لشحن النقاط يرجى مراسلة البوت:\n\n@ypiu5\n\n💰 سعر الشحن:\n100 نجمة = 1000 نقطة'); ?></textarea>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="daily_gift_status" <?php echo ($settings['daily_gift_status'] ?? 'on') == 'on' ? 'checked' : ''; ?>>
                        <span>تفعيل الهدية اليومية</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="invite_link_status" <?php echo ($settings['invite_link_status'] ?? 'on') == 'on' ? 'checked' : ''; ?>>
                        <span>تفعيل رابط الدعوة</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="transfer_status" <?php echo ($settings['transfer_status'] ?? 'on') == 'on' ? 'checked' : ''; ?>>
                        <span>تفعيل تحويل النقاط</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="starss" <?php echo ($settings['starss'] ?? 'on') == 'on' ? 'checked' : ''; ?>>
                        <span>تفعيل شحن النقاط بالنجوم</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="Market" <?php echo ($settings['Market'] ?? 'on') == 'on' ? 'checked' : ''; ?>>
                        <span>تفعيل قسم الاستبدال</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="rshaq" <?php echo ($settings['rshaq'] ?? 'on') == 'on' ? 'checked' : ''; ?>>
                        <span>فتح الرشق</span>
                    </label>
                </div>

                <button type="submit" name="update_settings" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.2em;">
                    <i class="fas fa-save"></i> حفظ الإعدادات
                </button>
            </form>
        </div>

        <?php elseif ($current_tab == 'sections'): ?>
        <!-- أقسام الخدمات -->
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-folder-plus"></i>
                إضافة قسم جديد
            </h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> اسم القسم</label>
                    <input type="text" name="section_name" class="form-control" placeholder="مثال: خدمات انستغرام" required>
                </div>
                <button type="submit" name="add_section" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة القسم
                </button>
            </form>
        </div>

        <?php if ($edit_service): ?>
        <!-- تعديل خدمة -->
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-edit"></i>
                تعديل الخدمة
            </h2>
            <form method="POST">
                <input type="hidden" name="section_uid" value="<?php echo $edit_service['section_uid']; ?>">
                <input type="hidden" name="service_uid" value="<?php echo $edit_service['service_uid']; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-tag"></i> اسم الخدمة</label>
                        <input type="text" name="service_name" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-arrow-down"></i> أقل كمية</label>
                        <input type="number" name="min" class="form-control" step="1" value="<?php echo htmlspecialchars($edit_service['data']['min']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-arrow-up"></i> أقصى كمية</label>
                        <input type="number" name="max" class="form-control" step="1" value="<?php echo htmlspecialchars($edit_service['data']['max']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> سعر 1000</label>
                        <input type="number" name="price" class="form-control" step="0.001" value="<?php echo htmlspecialchars($edit_service['data']['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-id-card"></i> ID الخدمة</label>
                        <input type="text" name="service_id" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['service_id']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-globe"></i> الدومين</label>
                        <input type="text" name="domain" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['domain']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-key"></i> API Key</label>
                        <input type="text" name="api_key" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['api']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-clock"></i> مدة الانتظار (ساعات)</label>
                        <input type="number" name="delay" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['delay']); ?>">
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="update_service" class="btn btn-primary">
                        <i class="fas fa-save"></i> تحديث الخدمة
                    </button>
                    <a href="?tab=sections" class="btn btn-danger">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- عرض الأقسام -->
        <div class="sections-grid">
            <?php foreach ($settings["sections"] ?? [] as $section_uid => $section): ?>
            <div class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-name">
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($section['name']); ?>
                        </div>
                        <div class="section-id"><?php echo $section_uid; ?></div>
                    </div>
                    <div class="action-buttons">
                        <a href="?delete_section=<?php echo $section_uid; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>

                <div class="services-list">
                    <?php foreach ($section["services"] ?? [] as $service_uid => $service): ?>
                    <div class="service-item">
                        <span><?php echo htmlspecialchars($service['name']); ?></span>
                        <span class="service-price"><?php echo number_format($service['price'], 3); ?></span>
                        <div class="action-buttons">
                            <a href="?tab=sections&edit_service=<?php echo $section_uid . '_' . $service_uid; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete_service=<?php echo $section_uid . '_' . $service_uid; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- إضافة خدمة جديدة للقسم -->
                <form method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="section_uid" value="<?php echo $section_uid; ?>">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                        <input type="text" name="service_name" class="form-control" placeholder="اسم الخدمة" required>
                        <input type="number" name="price" class="form-control" placeholder="السعر" step="0.001" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">
                        <input type="number" name="min" class="form-control" placeholder="أقل كمية" value="10">
                        <input type="number" name="max" class="form-control" placeholder="أقصى كمية" value="1000">
                        <input type="number" name="delay" class="form-control" placeholder="تأخير" value="0">
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; margin-top: 10px;">
                        <input type="text" name="service_id" class="form-control" placeholder="ID الخدمة">
                        <input type="text" name="domain" class="form-control" placeholder="الدومين">
                    </div>
                    <input type="text" name="api_key" class="form-control" style="margin-top: 10px;" placeholder="API Key">
                    <button type="submit" name="add_service" class="btn btn-success btn-sm" style="width: 100%; margin-top: 10px;">
                        <i class="fas fa-plus"></i> إضافة خدمة
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($current_tab == 'store'): ?>
        <!-- المتجر -->
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-store-alt"></i>
                إضافة قسم متجر جديد
            </h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> اسم القسم</label>
                    <input type="text" name="store_section_name" class="form-control" placeholder="مثال: حسابات انستغرام" required>
                </div>
                <button type="submit" name="add_store_section" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة القسم
                </button>
            </form>
        </div>

        <?php if ($edit_item): ?>
        <!-- تعديل منتج -->
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-edit"></i>
                تعديل المنتج
            </h2>
            <form method="POST">
                <input type="hidden" name="store_section_uid" value="<?php echo $edit_item['section_uid']; ?>">
                <input type="hidden" name="item_uid" value="<?php echo $edit_item['item_uid']; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-tag"></i> اسم المنتج</label>
                        <input type="text" name="item_name" class="form-control" value="<?php echo htmlspecialchars($edit_item['data']['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> السعر</label>
                        <input type="number" name="item_price" class="form-control" step="0.001" value="<?php echo htmlspecialchars($edit_item['data']['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-align-left"></i> الوصف</label>
                        <textarea name="item_description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_item['data']['description']); ?></textarea>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="update_store_item" class="btn btn-primary">
                        <i class="fas fa-save"></i> تحديث المنتج
                    </button>
                    <a href="?tab=store" class="btn btn-danger">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- عرض أقسام المتجر -->
        <?php foreach ($settings["store"]["sections"] ?? [] as $section_uid => $section): ?>
        <div class="form-card" style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: var(--accent-purple);">
                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($section['name']); ?>
                    <small style="color: var(--text-secondary);"><?php echo $section_uid; ?></small>
                </h3>
                <a href="?delete_store_section=<?php echo $section_uid; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">
                    <i class="fas fa-trash"></i> حذف القسم
                </a>
            </div>

            <!-- عرض المنتجات -->
            <div class="store-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <?php foreach ($section["items"] ?? [] as $item_uid => $item): ?>
                <div class="product-card" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 15px; padding: 15px;">
                    <h4 style="color: var(--accent-purple); margin-bottom: 10px;"><?php echo htmlspecialchars($item['name']); ?></h4>
                    <p style="color: var(--text-secondary); font-size: 0.9em; margin-bottom: 10px;"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    <div style="font-size: 1.5em; color: var(--accent-purple); font-weight: bold; margin-bottom: 10px;">
                        <?php echo number_format($item['price'], 3); ?> <?php echo $settings['currency'] ?? 'نقاط'; ?>
                    </div>
                    <div class="action-buttons">
                        <a href="?tab=store&edit_store_item=<?php echo $section_uid . '_' . $item_uid; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="?delete_store_item=<?php echo $section_uid . '_' . $item_uid; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                            <i class="fas fa-trash"></i> حذف
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- إضافة منتج جديد -->
            <form method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--glass-border);">
                <h4 style="color: var(--accent-purple); margin-bottom: 15px;">إضافة منتج جديد</h4>
                <input type="hidden" name="store_section_uid" value="<?php echo $section_uid; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <input type="text" name="item_name" class="form-control" placeholder="اسم المنتج" required>
                    </div>
                    <div class="form-group">
                        <input type="number" name="item_price" class="form-control" placeholder="السعر" step="0.001" required>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="item_description" class="form-control" placeholder="وصف المنتج (اختياري)" rows="2"></textarea>
                </div>
                <button type="submit" name="add_store_item" class="btn btn-success">
                    <i class="fas fa-plus"></i> إضافة المنتج
                </button>
            </form>
        </div>
        <?php endforeach; ?>

        <?php elseif ($current_tab == 'orders'): ?>
        <!-- الطلبات -->
        <div class="form-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="form-title" style="margin-bottom: 0;">
                    <i class="fas fa-history"></i>
                    جميع الطلبات
                </h2>
                <a href="?tab=orders&check_orders=1" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> متابعة الطلبات
                </a>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>الخدمة</th>
                            <th>القسم</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>رقم الطلب</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $all_orders = [];
                        foreach ($Namero["orders"] ?? [] as $user_id => $orders) {
                            foreach ($orders as $index => $order) {
                                $all_orders[] = [
                                    'user' => $user_id,
                                    'service' => $order['service'] ?? '',
                                    'section' => $order['section'] ?? '',
                                    'quantity' => $order['quantity'] ?? 0,
                                    'price' => $order['price'] ?? 0,
                                    'order_id' => $order['order_id'] ?? '',
                                    'status' => $order['status'] ?? '',
                                    'time' => $order['time'] ?? 0
                                ];
                            }
                        }
                        usort($all_orders, function($a, $b) {
                            return $b['time'] - $a['time'];
                        });
                        ?>
                        <?php foreach ($all_orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['user']); ?></td>
                            <td><?php echo htmlspecialchars($order['service']); ?></td>
                            <td><?php echo htmlspecialchars($order['section']); ?></td>
                            <td><?php echo number_format($order['quantity']); ?></td>
                            <td><?php echo number_format($order['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td>
                                <span class="badge badge-success">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', $order['time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($all_orders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 50px;">
                                <i class="fas fa-box-open" style="font-size: 3em; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                                لا توجد طلبات بعد
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>© 2024 سميث ماتريكس - جميع الحقوق محفوظة</p>
            <p style="margin-top: 5px;">⚡ برمجة وتطوير <a href="https://t.me/ypiu5" style="color: var(--accent-purple); text-decoration: none;" target="_blank">@ypiu5</a></p>
        </div>
    </div>
</body>
</html>