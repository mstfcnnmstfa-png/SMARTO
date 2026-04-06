<?php

error_reporting(0);
ini_set('display_errors', 0);
session_start();
ob_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['bot_token'])) {
    header("Location: admin_login.php");
    exit();
}

$ADMIN_ID = $_SESSION['admin_id'];
$BOT_TOKEN = $_SESSION['bot_token'];
$BOT_USERNAME = $_SESSION['bot_username'] ?? 'bot';
$BOT_NAME = $_SESSION['bot_name'] ?? 'Bot';

$NAMERO_DIR = __DIR__ . '/NAMERO/';

$bot_folders = glob($NAMERO_DIR . '*', GLOB_ONLYDIR);
if (empty($bot_folders)) {
    

    $bot_id = explode(':', $BOT_TOKEN)[0];
    $BOT_ID_DIR = $NAMERO_DIR . $bot_id . '/';
    if (!is_dir($BOT_ID_DIR)) {
        mkdir($BOT_ID_DIR, 0777, true);
    }
} else {
    $BOT_ID_DIR = $bot_folders[0] . '/';
}

$_db_file = $BOT_ID_DIR . 'botdata.sqlite';
db_init($_db_file);

$api_settings_file = $BOT_ID_DIR . 'api_settings.json';
$Namero_file       = $BOT_ID_DIR . 'Namero.json';

$settings = db_get_settings();
$Namero   = db_get_namero();

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

function platPickerHTML($name, $selected = 'tiktok') {
    $platforms = [
        'tiktok'    => ['fab fa-tiktok',    'تيك توك',  '#ee1d52'],
        'youtube'   => ['fab fa-youtube',   'يوتيوب',   '#ff0000'],
        'instagram' => ['fab fa-instagram', 'انستقرام', '#c13584'],
        'facebook'  => ['fab fa-facebook',  'فيسبوك',   '#1877f2'],
        'telegram'  => ['fab fa-telegram',  'تيليجرام', '#0088cc'],
        'snapchat'  => ['fab fa-snapchat',  'سناب',     '#ffcc00'],
        'twitter'   => ['fab fa-x-twitter', 'تويتر',   '#e7e7e7'],
        'threads'   => ['fab fa-threads',   'ثريدز',    '#cccccc'],
        'whatsapp'  => ['fab fa-whatsapp',  'واتساب',   '#25d366'],
    ];
    $uid = uniqid('pp_');
    $html = '<div class="plat-picker">';
    foreach ($platforms as $key => [$faClass, $label, $color]) {
        $checked = ($selected === $key) ? 'checked' : '';
        $html .= '<input type="radio" name="' . htmlspecialchars($name) . '" id="' . $uid . '_' . $key . '" value="' . $key . '" ' . $checked . '>';
        $html .= '<label for="' . $uid . '_' . $key . '" style="--pc:' . $color . '">'
               . '<i class="' . $faClass . ' plat-icon"></i>'
               . '<span>' . $label . '</span>'
               . '</label>';
    }
    $html .= '</div>';
    return $html;
}

function platLabel($key) {
    $map = [
        'tiktok'=>'🎵 تيك توك','youtube'=>'▶️ يوتيوب','instagram'=>'📷 انستقرام',
        'facebook'=>'👍 فيسبوك','telegram'=>'✈️ تيليجرام','snapchat'=>'👻 سناب',
        'twitter'=>'✖️ تويتر','threads'=>'🧵 ثريدز','whatsapp'=>'💬 واتساب','all'=>'🌐 عام',
    ];
    return $map[$key] ?? '🌐 عام';
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_panel_pass'])) {
    $new_pass  = trim($_POST['new_pass']  ?? '');
    $new_pass2 = trim($_POST['new_pass2'] ?? '');
    if (empty($new_pass)) {
        $message = "❌ كلمة السر لا تكون فارغة";
        $message_type = "error";
    } elseif ($new_pass !== $new_pass2) {
        $message = "❌ كلمتا السر غير متطابقتين";
        $message_type = "error";
    } else {
        $admins_file = __DIR__ . '/admins.php';
        $admins_content = file_get_contents($admins_file);
        $admins_content = preg_replace(
            "/define\('ADMIN_PANEL_PASS',\s*'[^']*'\);/",
            "define('ADMIN_PANEL_PASS', '" . addslashes($new_pass) . "');",
            $admins_content
        );
        file_put_contents($admins_file, $admins_content);
        $message = "✅ تم تغيير كلمة السر بنجاح";
        $message_type = "success";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings['currency'] = $_POST['currency'] ?? 'نقاط';
    $settings['daily_gift'] = floatval($_POST['daily_gift'] ?? 20);
    $settings['min_order_quantity'] = intval($_POST['min_order_quantity'] ?? 10);
    $settings['invite_reward'] = floatval($_POST['invite_reward'] ?? 5);
    $settings['user_price'] = floatval($_POST['user_price'] ?? 100);
    $settings['Ch'] = $_POST['channel_link'] ?? 'https://t.me/Dragon_Supor';
    $settings['domain'] = $_POST['charge_cliche'] ?? '';
    $settings['token'] = $_POST['terms_text'] ?? '';
    
    

    $settings['daily_gift_status'] = isset($_POST['daily_gift_status']) ? 'on' : 'off';
    $settings['invite_link_status'] = isset($_POST['invite_link_status']) ? 'on' : 'off';
    $settings['transfer_status'] = isset($_POST['transfer_status']) ? 'on' : 'off';
    $settings['starss'] = isset($_POST['starss']) ? 'on' : 'off';
    $settings['Market'] = isset($_POST['Market']) ? 'on' : 'off';
    $settings['rshaq'] = isset($_POST['rshaq']) ? 'on' : 'off';
    $settings['api_enabled'] = isset($_POST['api_enabled']) ? 'on' : 'off';
    $settings['maintenance_mode'] = isset($_POST['maintenance_mode']) ? 'on' : 'off';
    
    db_save_settings($settings);
    
    $Namero['rshaq'] = $settings['rshaq'];
    db_save_namero($Namero);
    
    $message = "✅ تم تحديث الإعدادات بنجاح!";
    $message_type = "success";
}

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
            db_save_settings($settings);
            $message = "✅ تم إضافة القسم: $section_name";
            $message_type = "success";
        } else {
            $message = "❌ هذا القسم موجود بالفعل!";
            $message_type = "error";
        }
    }
}

if (isset($_GET['delete_section'])) {
    $section_uid = $_GET['delete_section'];
    if (isset($settings["sections"][$section_uid])) {
        $section_name = $settings["sections"][$section_uid]['name'];
        unset($settings["sections"][$section_uid]);
        db_save_settings($settings);
        $message = "✅ تم حذف القسم: $section_name";
        $message_type = "success";
    }
}

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
    $platform = trim($_POST['platform'] ?? 'all');
    
    if (!empty($section_uid) && !empty($service_name) && isset($settings["sections"][$section_uid])) {
        $service_uid = generateUID();
        $settings["sections"][$section_uid]["services"][$service_uid] = [
            "name" => $service_name,
            "platform" => $platform,
            "min" => $min,
            "max" => $max,
            "price" => $price,
            "service_id" => $service_id,
            "domain" => $domain,
            "api" => $api_key,
            "delay" => $delay
        ];
        db_save_settings($settings);
        $message = "✅ تم إضافة الخدمة: $service_name";
        $message_type = "success";
    }
}

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
    $platform = trim($_POST['platform'] ?? 'all');
    
    if (!empty($section_uid) && !empty($service_uid) && isset($settings["sections"][$section_uid]["services"][$service_uid])) {
        $settings["sections"][$section_uid]["services"][$service_uid] = [
            "name" => $service_name,
            "platform" => $platform,
            "min" => $min,
            "max" => $max,
            "price" => $price,
            "service_id" => $service_id,
            "domain" => $domain,
            "api" => $api_key,
            "delay" => $delay
        ];
        db_save_settings($settings);
        $message = "✅ تم تحديث الخدمة: $service_name";
        $message_type = "success";
    }
}

if (isset($_GET['delete_service'])) {
    $parts = explode('_', $_GET['delete_service']);
    $section_uid = $parts[0] ?? '';
    $service_uid = $parts[1] ?? '';
    
    if (isset($settings["sections"][$section_uid]["services"][$service_uid])) {
        $service_name = $settings["sections"][$section_uid]["services"][$service_uid]['name'];
        unset($settings["sections"][$section_uid]["services"][$service_uid]);
        db_save_settings($settings);
        $message = "✅ تم حذف الخدمة: $service_name";
        $message_type = "success";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $coupon_code  = strtoupper(trim($_POST['coupon_code'] ?? ''));
    $discount     = floatval($_POST['coupon_discount'] ?? 0);
    $expiry_type  = $_POST['expiry_type'] ?? 'date';
    $expiry_date  = trim($_POST['expiry_date'] ?? '');
    $max_uses     = intval($_POST['max_uses'] ?? 0);
    $coupon_ok    = true;

    if (empty($coupon_code) || $discount <= 0 || $discount > 100) {
        $message = "❌ يرجى إدخال كود صحيح ونسبة خصم بين 1 و 100.";
        $message_type = "error";
        $coupon_ok = false;
    } elseif (isset($settings['coupons'][$coupon_code])) {
        $message = "❌ هذا الكود موجود بالفعل!";
        $message_type = "error";
        $coupon_ok = false;
    } elseif ($expiry_type === 'date' && empty($expiry_date)) {
        $message = "❌ يرجى تحديد تاريخ انتهاء الكوبون.";
        $message_type = "error";
        $coupon_ok = false;
    }

    if ($coupon_ok) {
        $coupon_data = [
            'discount'     => $discount,
            'expiry_type'  => $expiry_type,
            'current_uses' => 0,
            'active'       => true,
        ];
        if ($expiry_type === 'date') {
            $coupon_data['expiry_date'] = $expiry_date;
        } else {
            $coupon_data['max_uses'] = max(1, $max_uses);
        }
        $settings['coupons'][$coupon_code] = $coupon_data;
        db_save_settings($settings);
        $message = "✅ تم إضافة كوبون الخصم: $coupon_code";
        $message_type = "success";
    }
}

if (isset($_GET['delete_coupon'])) {
    $del_code = strtoupper(trim($_GET['delete_coupon']));
    if (isset($settings['coupons'][$del_code])) {
        unset($settings['coupons'][$del_code]);
        db_save_settings($settings);
        $message = "✅ تم حذف الكوبون: $del_code";
        $message_type = "success";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_api_provider'])) {
    $pname   = trim($_POST['provider_name'] ?? '');
    $pdomain = trim($_POST['provider_domain'] ?? '');
    $pkey    = trim($_POST['provider_key'] ?? '');
    if ($pname && $pdomain && $pkey) {
        $pdomain = preg_replace('#^https?://#','', rtrim($pdomain,'/'));
        $uid = 'p_' . substr(md5($pdomain.$pkey.time()), 0, 8);
        if (!isset($settings['api_providers'])) $settings['api_providers'] = [];
        $settings['api_providers'][$uid] = [
            'name'       => $pname,
            'domain'     => $pdomain,
            'api_key'    => $pkey,
            'created_at' => date('Y-m-d'),
        ];
        db_save_settings($settings);
        $message = "✅ تم إضافة المزوّد: $pname";
        $message_type = "success";
    } else {
        $message = "❌ يرجى ملء جميع الحقول.";
        $message_type = "error";
    }
}

if (isset($_GET['delete_provider'])) {
    $del_uid = $_GET['delete_provider'];
    if (isset($settings['api_providers'][$del_uid])) {
        $del_name = $settings['api_providers'][$del_uid]['name'];
        unset($settings['api_providers'][$del_uid]);
        db_save_settings($settings);
        $message = "✅ تم حذف المزوّد: $del_name";
        $message_type = "success";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_provider'])) {
    header('Content-Type: application/json; charset=utf-8');
    ob_clean();
    $tdomain = trim($_POST['t_domain'] ?? '');
    $tkey    = trim($_POST['t_key'] ?? '');
    if (!$tdomain || !$tkey) { die(json_encode(['ok'=>false,'msg'=>'بيانات ناقصة.'])); }
    $url = "https://$tdomain/api/v2?key=$tkey&action=balance";
    $ch = curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false]);
    $resp = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    if ($err) { die(json_encode(['ok'=>false,'msg'=>"فشل الاتصال: $err"],JSON_UNESCAPED_UNICODE)); }
    $data = json_decode($resp, true);
    if (isset($data['balance'])) {
        die(json_encode(['ok'=>true,'msg'=>"✅ متصل! الرصيد: ".$data['balance']." ".(isset($data['currency'])?$data['currency']:'')],JSON_UNESCAPED_UNICODE));
    } else {
        $errMsg = $data['error'] ?? ($resp ?: 'رد غير معروف');
        die(json_encode(['ok'=>false,'msg'=>"❌ ".$errMsg],JSON_UNESCAPED_UNICODE));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_store_section'])) {
    $section_name = trim($_POST['store_section_name'] ?? '');
    
    if (!empty($section_name)) {
        $new_uid = generateUID();
        $settings["store"]["sections"][$new_uid] = [
            "name" => $section_name,
            "items" => []
        ];
        db_save_settings($settings);
        $message = "✅ تم إضافة قسم المتجر: $section_name";
        $message_type = "success";
    }
}

if (isset($_GET['delete_store_section'])) {
    $section_uid = $_GET['delete_store_section'];
    if (isset($settings["store"]["sections"][$section_uid])) {
        $section_name = $settings["store"]["sections"][$section_uid]['name'];
        unset($settings["store"]["sections"][$section_uid]);
        db_save_settings($settings);
        $message = "✅ تم حذف قسم المتجر: $section_name";
        $message_type = "success";
    }
}

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
        db_save_settings($settings);
        $message = "✅ تم إضافة المنتج: $item_name";
        $message_type = "success";
    }
}

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
        db_save_settings($settings);
        $message = "✅ تم تحديث المنتج: $item_name";
        $message_type = "success";
    }
}

if (isset($_GET['delete_store_item'])) {
    $parts = explode('_', $_GET['delete_store_item']);
    $section_uid = $parts[0] ?? '';
    $item_uid = $parts[1] ?? '';
    
    if (isset($settings["store"]["sections"][$section_uid]["items"][$item_uid])) {
        $item_name = $settings["store"]["sections"][$section_uid]["items"][$item_uid]['name'];
        unset($settings["store"]["sections"][$section_uid]["items"][$item_uid]);
        db_save_settings($settings);
        $message = "✅ تم حذف المنتج: $item_name";
        $message_type = "success";
    }
}

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
    
    db_save_namero($Namero);
    
    $message = "✅ تم تحديث $updated طلب وإرسال $notified إشعار";
    $message_type = "success";
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

$current_tab = $_GET['tab'] ?? 'dashboard';

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
    <title>لوحة التحكم - دراجون فولو</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        :root {
            --bg:           

            --surface:      

            --surface-high: 

            --surface-low:  

            --border:       rgba(73,72,71,0.35);
            --border-accent:rgba(0,227,253,0.25);
            --cyan:         

            --cyan-dim:     

            --cyan-glow:    rgba(0,227,253,0.15);
            --cyan-glow-md: rgba(0,227,253,0.25);
            --on-surface:   

            --on-surface-v: 

            --on-surface-m: 

            --danger:       

            --danger-glow:  rgba(255,110,132,0.25);
            --success:      

            --warning:      

            --r-sm: 4px; --r-md: 8px; --r-lg: 16px; --r-xl: 24px;
        }

        html, body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--on-surface);
            min-height: 100vh;
            overflow-x: hidden;
        }

        
        .background-3d { position:fixed;inset:0;z-index:-1;overflow:hidden;pointer-events:none; }
        .grid-3d { display:none; }
        .blob1,.blob2 { position:absolute;border-radius:50%;filter:blur(120px);opacity:0.6; }
        .blob1 { top:-10%;right:-10%;width:50%;height:50%;background:rgba(0,227,253,0.05); }
        .blob2 { bottom:-5%;left:20%;width:35%;height:35%;background:rgba(0,206,219,0.04); }

        
        ::-webkit-scrollbar { width:4px; }
        ::-webkit-scrollbar-track { background:var(--bg); }
        ::-webkit-scrollbar-thumb { background:rgba(0,227,253,0.25);border-radius:2px; }
        ::-webkit-scrollbar-thumb:hover { background:var(--cyan); }

        
        .header {
            background: rgba(19,19,19,0.9);
            border-bottom: 1px solid var(--border);
            padding: 14px 24px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
        }
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .logo { display:flex;align-items:center;gap:12px; }
        .logo-icon {
            width: 40px; height: 40px;
            background: rgba(0,227,253,0.12);
            border: 1px solid var(--border-accent);
            border-radius: var(--r-md);
            display: flex; justify-content:center; align-items:center;
            font-size: 18px; color: var(--cyan);
            box-shadow: 0 0 16px var(--cyan-glow);
        }
        .logo-text h1 { color:var(--cyan);font-size:1.4em;font-weight:800;letter-spacing:-0.5px; }
        .logo-text p  { color:var(--on-surface-v);font-size:0.72em;text-transform:uppercase;letter-spacing:1px;margin-top:1px; }

        .user-info { display:flex;align-items:center;gap:12px;flex-wrap:wrap; }
        .bot-badge {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 50px;
            padding: 7px 16px;
            display: flex; align-items:center; gap:8px;
            font-size:0.85em; color:var(--on-surface-v);
        }
        .bot-badge i { color:var(--cyan); }

        .logout-btn {
            background: rgba(255,110,132,0.12);
            border: 1px solid rgba(255,110,132,0.3);
            border-radius: var(--r-md);
            padding: 8px 16px;
            color: var(--danger);
            text-decoration: none;
            display: flex; align-items:center; gap:8px;
            font-size:0.85em; font-family:'Cairo',sans-serif;
            transition: all 0.2s;
        }
        .logout-btn:hover { background:rgba(255,110,132,0.2);box-shadow:0 0 16px var(--danger-glow); }

        
        .container { max-width:1400px;margin:28px auto;padding:0 20px; }

        
        .tabs { display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap; }
        .tab-btn {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 11px 20px;
            color: var(--on-surface-v);
            text-decoration: none;
            display: flex; align-items:center; gap:8px;
            font-size: 0.88em; font-family:'Cairo',sans-serif;
            transition: all 0.2s;
            flex: 1; min-width: 130px;
        }
        .tab-btn:hover { color:var(--on-surface);border-color:var(--border-accent);box-shadow:0 0 16px var(--cyan-glow); }
        .tab-btn.active {
            background: rgba(0,227,253,0.1);
            color: var(--cyan);
            border-color: var(--border-accent);
            box-shadow: 0 0 20px var(--cyan-glow);
        }
        .tab-btn i { font-size:1em;width:16px;text-align:center; }

        
        .stats-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
        .stat-card {
            background: rgba(26,25,25,0.6);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 20px 16px;
            text-align: center;
            transition: all 0.25s;
        }
        .stat-card:hover { border-color:var(--border-accent);box-shadow:0 0 24px var(--cyan-glow); }
        .stat-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:12px; }
        .stat-icon {
            width: 42px; height: 42px;
            background: rgba(0,227,253,0.1);
            border: 1px solid var(--border-accent);
            border-radius: var(--r-md);
            display: flex; justify-content:center; align-items:center;
            font-size: 18px; color: var(--cyan);
        }
        .stat-value { font-family:'Cairo',sans-serif;font-size:2em;font-weight:800;color:var(--on-surface);direction:ltr; }
        .stat-label { color:var(--on-surface-v);font-size:0.75em;margin-top:4px;text-transform:uppercase;letter-spacing:1.2px; }

        
        .alert {
            background: var(--surface);
            border: 1px solid;
            border-radius: var(--r-lg);
            padding: 14px 18px;
            margin-bottom: 18px;
            display: flex; align-items:center; gap:12px;
            animation: slideIn 0.35s ease;
        }
        .alert.success { border-color:rgba(52,211,153,0.4);box-shadow:0 0 16px rgba(52,211,153,0.1); }
        .alert.error   { border-color:rgba(255,110,132,0.4);box-shadow:0 0 16px var(--danger-glow); }
        .alert-icon { font-size:1.3em; }
        @keyframes slideIn { from{opacity:0;transform:translateY(-12px)} to{opacity:1;transform:translateY(0)} }

        
        .form-card {
            background: rgba(26,25,25,0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            padding: 28px;
            margin-bottom: 20px;
        }
        .form-title {
            font-size: 1.15em;
            font-weight: 800;
            color: var(--on-surface);
            margin-bottom: 20px;
            display: flex; align-items:center; gap:10px;
            letter-spacing: -0.3px;
        }
        .form-title i { color:var(--cyan); }

        .form-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px; }
        .form-group { margin-bottom:16px; }
        .form-label {
            display: block;
            margin-bottom: 6px;
            color: var(--on-surface-v);
            font-size: 0.82em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .form-label i { color:var(--cyan);margin-left:5px; }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            background: var(--surface-high);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            color: var(--on-surface);
            font-size: 0.95em;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s;
        }
        .form-control:focus { outline:none;border-color:var(--border-accent);box-shadow:0 0 16px var(--cyan-glow); }
        textarea.form-control { min-height:90px;resize:vertical; }

        .checkbox-group { display:flex;flex-wrap:wrap;gap:16px;margin:16px 0; }
        .checkbox-label { display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.9em;color:var(--on-surface-v); }
        .checkbox-label input[type="checkbox"] { width:18px;height:18px;accent-color:var(--cyan); }

        
        .plat-picker { display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin:10px 0; }
        .plat-picker input[type="radio"] { display:none; }
        .plat-picker label {
            display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:5px;padding:10px 4px;border-radius:12px;cursor:pointer;
            border:2px solid color-mix(in srgb, var(--pc) 35%, transparent);
            background:color-mix(in srgb, var(--pc) 8%, var(--surface-2));
            transition:all .18s;font-size:0.72em;
            color:color-mix(in srgb, var(--pc) 80%, 

            text-align:center;line-height:1.2;
        }
        .plat-picker label .plat-icon {
            font-size:1.4em;line-height:1;
            color:var(--pc);
            filter:drop-shadow(0 0 4px color-mix(in srgb, var(--pc) 50%, transparent));
        }
        .plat-picker input[type="radio"]:checked + label {
            border-color:var(--pc);
            background:color-mix(in srgb, var(--pc) 22%, var(--surface-2));
            color:var(--pc);
            box-shadow:0 0 14px color-mix(in srgb, var(--pc) 45%, transparent), inset 0 0 8px color-mix(in srgb, var(--pc) 10%, transparent);
        }
        .plat-picker input[type="radio"]:checked + label .plat-icon {
            filter:drop-shadow(0 0 7px color-mix(in srgb, var(--pc) 80%, transparent));
        }
        .plat-picker label:hover {
            border-color:var(--pc);
            background:color-mix(in srgb, var(--pc) 15%, var(--surface-2));
        }
        .plat-badge {
            display:inline-flex;align-items:center;gap:3px;font-size:0.7em;
            background:rgba(0,230,200,0.13);border:1px solid var(--cyan);
            color:var(--cyan);border-radius:6px;padding:1px 7px;
        }

        
        .btn {
            padding: 11px 22px;
            border: none;
            border-radius: var(--r-md);
            font-size: 0.9em;
            font-weight: 700;
            font-family: 'Cairo', sans-serif;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
            display: inline-flex; align-items:center; justify-content:center; gap:8px;
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }
        .btn::after {
            content:'';position:absolute;inset:0;
            background:rgba(255,255,255,0);
            transition: background 0.2s;
        }
        .btn:active::after { background:rgba(255,255,255,0.06); }

        .btn-primary {
            background: linear-gradient(135deg, rgba(0,227,253,0.22) 0%, rgba(0,227,253,0.1) 100%);
            color: var(--cyan);
            border: 1px solid var(--border-accent);
            box-shadow: 0 2px 10px rgba(0,0,0,0.25), inset 0 1px 0 rgba(0,227,253,0.15);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, rgba(0,227,253,0.32) 0%, rgba(0,227,253,0.18) 100%);
            box-shadow: 0 0 22px var(--cyan-glow), 0 4px 14px rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }
        .btn-primary:active { transform:translateY(0); }

        .btn-danger {
            background: linear-gradient(135deg, rgba(255,110,132,0.18) 0%, rgba(255,110,132,0.08) 100%);
            color: var(--danger);
            border: 1px solid rgba(255,110,132,0.35);
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, rgba(255,110,132,0.28) 0%, rgba(255,110,132,0.16) 100%);
            box-shadow: 0 0 20px var(--danger-glow), 0 4px 14px rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }
        .btn-danger:active { transform:translateY(0); }

        .btn-success {
            background: linear-gradient(135deg, rgba(52,211,153,0.2) 0%, rgba(52,211,153,0.1) 100%);
            color: var(--success);
            border: 1px solid rgba(52,211,153,0.35);
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, rgba(52,211,153,0.3) 0%, rgba(52,211,153,0.18) 100%);
            box-shadow: 0 0 20px rgba(52,211,153,0.22), 0 4px 14px rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }
        .btn-success:active { transform:translateY(0); }

        .btn-sm { padding:7px 13px;font-size:0.82em;border-radius:var(--r-sm); }
        .btn-lg { padding:14px 32px;font-size:1.05em;border-radius:var(--r-lg); }

        
        .table-container { overflow-x:auto;margin-top:16px; }
        .table { width:100%;border-collapse:collapse; }
        .table th {
            background: rgba(0,227,253,0.08);
            color: var(--cyan);
            padding: 13px 14px;
            text-align: center;
            font-weight: 700;
            font-size: 0.82em;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border-accent);
        }
        .table td { padding:11px 14px;border-bottom:1px solid var(--border);text-align:center;font-size:0.9em;color:var(--on-surface-v); }
        .table tr:hover td { background:rgba(0,227,253,0.03);color:var(--on-surface); }

        .badge { display:inline-block;padding:4px 10px;border-radius:50px;font-size:0.82em;font-weight:600; }
        .badge-success { background:rgba(52,211,153,0.12);color:var(--success);border:1px solid rgba(52,211,153,0.3); }
        .badge-pending { background:rgba(251,191,36,0.12);color:var(--warning);border:1px solid rgba(251,191,36,0.3); }
        .badge-fail    { background:rgba(255,110,132,0.12);color:var(--danger);border:1px solid rgba(255,110,132,0.3); }

        .action-buttons { display:flex;gap:6px;justify-content:center; }

        
        .sections-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-top:16px; }
        .section-card {
            background: rgba(26,25,25,0.6);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 18px;
            transition: all 0.25s;
        }
        .section-card:hover { border-color:var(--border-accent);box-shadow:0 0 20px var(--cyan-glow); }
        .section-header {
            display: flex; justify-content:space-between; align-items:center;
            margin-bottom: 12px; padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        .section-name { font-size:1em;font-weight:700;color:var(--cyan); }
        .section-id   { font-size:0.72em;color:var(--on-surface-m);margin-top:2px; }

        .services-list { max-height:200px;overflow-y:auto;margin:10px 0; }
        .service-item {
            display: flex; justify-content:space-between; align-items:center;
            padding: 8px 10px;
            background: var(--surface-high);
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            margin-bottom: 5px;
            font-size: 0.85em;
        }
        .service-price { color:var(--cyan);font-weight:700;font-family:'Cairo',sans-serif; }

        
        .footer {
            text-align: center;
            padding: 28px;
            margin-top: 40px;
            color: var(--on-surface-m);
            border-top: 1px solid var(--border);
            font-size: 0.78em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        
        .admin-layout { display:flex;min-height:100vh; }

        .admin-sidebar {
            width:240px;
            background:linear-gradient(180deg,var(--surface-low) 0%,var(--bg) 100%);
            border-left:1px solid var(--border);
            position:fixed;top:0;right:0;
            height:100vh;
            display:flex;flex-direction:column;
            z-index:200;
            transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow-y:auto;
        }
        .admin-main { flex:1;margin-right:240px;display:flex;flex-direction:column;min-height:100vh; }

        .sidebar-brand {
            padding:20px 16px 16px;
            border-bottom:1px solid var(--border);
            display:flex;align-items:center;gap:11px;
            flex-shrink:0;
        }
        .brand-icon {
            width:36px;height:36px;
            background:rgba(0,227,253,0.12);
            border:1px solid var(--border-accent);
            border-radius:var(--r-md);
            display:flex;justify-content:center;align-items:center;
            color:var(--cyan);font-size:15px;
            box-shadow:0 0 14px var(--cyan-glow);
            flex-shrink:0;
        }
        .brand-text h2 { color:var(--cyan);font-size:1.05em;font-weight:800;letter-spacing:-0.3px; }
        .brand-text p  { color:var(--on-surface-m);font-size:0.62em;text-transform:uppercase;letter-spacing:1.5px;margin-top:2px; }

        .sidebar-nav { flex:1;padding:8px 8px;overflow-y:auto; }
        .nav-section-label {
            font-size:0.6em;text-transform:uppercase;letter-spacing:1.8px;
            color:var(--on-surface-m);padding:14px 10px 4px;font-weight:700;
        }
        .nav-item {
            display:flex;align-items:center;gap:10px;
            padding:10px 12px;
            border-radius:var(--r-sm);
            color:var(--on-surface-v);
            text-decoration:none;
            font-size:0.86em;font-family:'Cairo',sans-serif;
            transition:all 0.2s;
            margin-bottom:1px;
        }
        .nav-item:hover { background:var(--surface-high);color:var(--on-surface); }
        .nav-item.active {
            background:rgba(0,227,253,0.1);
            color:var(--cyan);
            border-right:2px solid var(--cyan);
            box-shadow:inset 0 0 16px rgba(0,227,253,0.04);
        }
        .nav-item i { font-size:13px;width:16px;text-align:center;flex-shrink:0; }
        .nav-item > span:first-of-type { flex:1; }
        .nav-badge {
            background:rgba(0,227,253,0.12);
            color:var(--cyan);border:1px solid var(--border-accent);
            border-radius:50px;padding:1px 7px;
            font-size:0.68em;font-family:'Cairo',sans-serif;font-weight:700;
        }

        .sidebar-bot-info {
            padding:11px 16px;border-top:1px solid var(--border);
            display:flex;align-items:center;gap:8px;
            color:var(--on-surface-m);font-size:0.78em;flex-shrink:0;
        }
        .sidebar-bot-info i { color:var(--cyan); }
        .sidebar-logout {
            display:flex;align-items:center;gap:8px;
            padding:11px 16px;color:var(--danger);
            text-decoration:none;font-size:0.82em;font-family:'Cairo',sans-serif;
            border-top:1px solid var(--border);transition:all 0.2s;flex-shrink:0;
        }
        .sidebar-logout:hover { background:rgba(255,110,132,0.07); }

        
        .admin-header {
            background:rgba(14,14,14,0.9);
            border-bottom:1px solid var(--border);
            padding:13px 22px;
            backdrop-filter:blur(20px);
            display:flex;align-items:center;gap:12px;
            flex-shrink:0;position:sticky;top:0;z-index:100;
        }
        .mob-toggle-inline {
            display:none;
            background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r-md);color:var(--cyan);
            width:36px;height:36px;font-size:14px;cursor:pointer;
            flex-shrink:0;align-items:center;justify-content:center;
        }
        .header-page-title { display:flex;align-items:center;gap:11px; }
        .header-page-title > i { font-size:1.05em;color:var(--cyan); }
        .header-page-title h1 { font-size:1em;font-weight:800;color:var(--on-surface);letter-spacing:-0.3px;margin:0; }
        .header-page-title p  { font-size:0.68em;color:var(--on-surface-v);text-transform:uppercase;letter-spacing:0.8px;margin:2px 0 0; }

        
        .admin-content { flex:1;padding:22px; }
        .content-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }

        
        .admin-footer {
            text-align:center;padding:16px 22px;
            color:var(--on-surface-m);font-size:0.68em;
            text-transform:uppercase;letter-spacing:1px;
            border-top:1px solid var(--border);flex-shrink:0;
        }

        
        .settings-group-title {
            font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;
            color:var(--on-surface-v);padding-bottom:10px;margin-bottom:14px;
            border-bottom:1px solid var(--border);
            display:flex;align-items:center;gap:8px;
        }
        .settings-group-title i { color:var(--cyan); }

        
        .sidebar-overlay {
            display:none;position:fixed;inset:0;
            background:rgba(0,0,0,0.55);z-index:199;
            backdrop-filter:blur(4px);
        }
        .sidebar-overlay.active { display:block; }

        
        .orders-header {
            display: flex; justify-content:space-between; align-items:center;
            margin-bottom: 18px; flex-wrap: wrap; gap: 10px;
        }
        .orders-empty {
            text-align: center; padding: 50px 20px;
            color: var(--on-surface-m);
        }
        .orders-empty i { font-size: 2.8em; display:block; margin-bottom: 12px; opacity:0.4; }
        .orders-empty p { font-size: 0.9em; text-transform:uppercase; letter-spacing:1px; }

        
        .orders-desktop { display: block; }
        .orders-cards   { display: none; }

        
        .order-card {
            background: var(--surface-high);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 14px;
            margin-bottom: 10px;
            transition: border-color 0.2s;
        }
        .order-card:hover { border-color: var(--border-accent); }
        .order-card-top {
            display: flex; justify-content:space-between; align-items:center;
            margin-bottom: 12px; padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        .order-card-service {
            font-weight: 700; font-size: 0.92em; color: var(--on-surface);
            display: flex; align-items:center; gap: 7px;
        }
        .order-card-service i { color: var(--cyan); font-size: 12px; }
        .order-card-rows { display: flex; flex-direction: column; gap: 7px; }
        .order-card-row {
            display: flex; justify-content:space-between; align-items:center;
            font-size: 0.84em;
        }
        .ocr-label {
            color: var(--on-surface-m); font-size: 0.82em;
            display: flex; align-items:center; gap: 5px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .ocr-label i { font-size: 10px; width: 12px; text-align:center; color: var(--cyan); opacity:0.7; }
        .ocr-val { color: var(--on-surface); font-weight: 600; text-align: left; direction: ltr; }

        
        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
        }
        
        @media (max-width: 900px) {
            
            .admin-sidebar { transform:translateX(100%); width:260px; }
            .admin-sidebar.open { transform:translateX(0); box-shadow: -8px 0 40px rgba(0,0,0,0.6); }
            .admin-main { margin-right:0; }
            .mob-toggle-inline { display:flex; }

            
            .admin-content { padding:12px; }
            .admin-header { padding:11px 14px; }

            
            .form-card { padding:16px 14px; border-radius:var(--r-lg); margin-bottom:12px; }
            .form-title { font-size:1em; margin-bottom:14px; }

            
            .stats-grid { grid-template-columns:repeat(2,1fr); gap:8px; margin-bottom:14px; }
            .stat-card { padding:13px 10px; }
            .stat-value { font-size:1.5em; }
            .stat-label { font-size:0.7em; }
            .stat-icon { width:34px; height:34px; font-size:14px; }

            .content-grid-2 { grid-template-columns:1fr; gap:12px; }
            .form-grid { grid-template-columns:1fr; gap:10px; }
            .sections-grid { grid-template-columns:1fr; }

            
            .form-control { padding:12px 13px; font-size:1em; }
            .form-group { margin-bottom:10px; }

            
            .btn { padding:12px 18px; font-size:0.92em; }
            .btn-sm { padding:9px 12px; font-size:0.82em; }
            .btn-lg { padding:14px 24px; }

            
            .checkbox-group { gap:12px; }
            .checkbox-label { font-size:0.95em; }

            
            .table th, .table td { padding:10px 8px; font-size:0.82em; }

            
            .header-page-title h1 { font-size:0.95em; }
            .header-page-title p  { display:none; }

            
            .badge { font-size:0.72em; padding:3px 8px; }

            
            .orders-desktop { display: none; }
            .orders-cards   { display: block; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns:repeat(2,1fr); gap:6px; }
            .stat-card { padding:11px 8px; }
            .stat-value { font-size:1.3em; }
            .admin-content { padding:10px; }
            .form-card { padding:14px 12px; }
        }
    </style>
</head>
<body>
<div class="background-3d">
    <div class="blob1"></div>
    <div class="blob2"></div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="admin-layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-crown"></i></div>
            <div class="brand-text">
                <h2>دراجون فولو</h2>
                <p>Admin Panel</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">القائمة الرئيسية</div>
            <a href="?tab=dashboard" class="nav-item <?php echo $current_tab=='dashboard'?'active':''; ?>">
                <i class="fas fa-home"></i><span>الرئيسية</span>
            </a>
            <a href="?tab=settings" class="nav-item <?php echo $current_tab=='settings'?'active':''; ?>">
                <i class="fas fa-cog"></i><span>الإعدادات</span>
            </a>
            <div class="nav-section-label">إدارة المحتوى</div>
            <a href="?tab=sections" class="nav-item <?php echo $current_tab=='sections'?'active':''; ?>">
                <i class="fas fa-folder"></i><span>أقسام الخدمات</span>
                <?php if($total_sections>0): ?><span class="nav-badge"><?php echo $total_sections; ?></span><?php endif; ?>
            </a>
            <a href="?tab=store" class="nav-item <?php echo $current_tab=='store'?'active':''; ?>">
                <i class="fas fa-store"></i><span>المتجر</span>
                <?php if($total_store_sections>0): ?><span class="nav-badge"><?php echo $total_store_sections; ?></span><?php endif; ?>
            </a>
            <div class="nav-section-label">البيانات</div>
            <a href="?tab=orders" class="nav-item <?php echo $current_tab=='orders'?'active':''; ?>">
                <i class="fas fa-history"></i><span>الطلبات</span>
                <?php if($total_orders>0): ?><span class="nav-badge"><?php echo $total_orders; ?></span><?php endif; ?>
            </a>
            <div class="nav-section-label">التسويق</div>
            <a href="?tab=coupons" class="nav-item <?php echo $current_tab=='coupons'?'active':''; ?>">
                <i class="fas fa-ticket-alt"></i><span>كوبونات الخصم</span>
                <?php $tc=count($settings['coupons']??[]); if($tc>0): ?><span class="nav-badge"><?php echo $tc; ?></span><?php endif; ?>
            </a>
            <div class="nav-section-label">الربط</div>
            <a href="?tab=api_keys" class="nav-item <?php echo $current_tab=='api_keys'?'active':''; ?>">
                <i class="fas fa-plug"></i><span>مزوّدو API</span>
                <?php $tap=count($settings['api_providers']??[]); if($tap>0): ?><span class="nav-badge"><?php echo $tap; ?></span><?php endif; ?>
            </a>
        </nav>
        <div class="sidebar-bot-info">
            <i class="fab fa-telegram"></i>
            <span>@<?php echo htmlspecialchars($BOT_USERNAME); ?></span>
        </div>
        <a href="admin_login.php?logout=1" class="sidebar-logout">
            <i class="fas fa-sign-out-alt"></i><span>تسجيل خروج</span>
        </a>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="admin-main">

        <!-- Page Header -->
        <header class="admin-header">
            <button class="mob-toggle-inline" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <?php
            $page_meta = [
                'dashboard'=>['fas fa-home',   'الرئيسية',         'نظرة عامة على النظام'],
                'settings' =>['fas fa-cog',    'الإعدادات',        'ضبط إعدادات البوت'],
                'sections' =>['fas fa-folder', 'أقسام الخدمات',   'إدارة الأقسام والخدمات'],
                'store'    =>['fas fa-store',  'المتجر',           'إدارة منتجات المتجر'],
                'orders'   =>['fas fa-history','الطلبات',          'متابعة طلبات المستخدمين'],
                'coupons'  =>['fas fa-ticket-alt','كوبونات الخصم','إنشاء وإدارة كوبونات الخصم'],
            ];
            $pm = $page_meta[$current_tab] ?? $page_meta['dashboard'];
            ?>
            <div class="header-page-title">
                <i class="<?php echo $pm[0]; ?>"></i>
                <div><h1><?php echo $pm[1]; ?></h1><p><?php echo $pm[2]; ?></p></div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="admin-content">

        <?php if (!empty($message)): ?>
        <div class="alert <?php echo $message_type; ?>">
            <div class="alert-icon"><?php echo $message_type == 'success' ? '✅' : '❌'; ?></div>
            <div><?php echo $message; ?></div>
        </div>
        <?php endif; ?>

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

        <div class="content-grid-2">
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-clock"></i> آخر الطلبات</h2>
                <div class="table-container">
                    <table class="table">
                        <thead><tr><th>المستخدم</th><th>الخدمة</th><th>الحالة</th></tr></thead>
                        <tbody>
                            <?php
                            $recent_orders = [];
                            foreach ($Namero["orders"] ?? [] as $user_id => $orders) {
                                foreach ($orders as $order) {
                                    $recent_orders[] = ['user'=>$user_id,'service'=>$order['service']??'','status'=>$order['status']??'','time'=>$order['time']??0];
                                }
                            }
                            usort($recent_orders, function($a,$b){return $b['time']-$a['time'];});
                            $recent_orders = array_slice($recent_orders,0,5);
                            ?>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['user']); ?></td>
                                <td><?php echo htmlspecialchars($order['service']); ?></td>
                                <td><?php
                                    $st=$order['status'];
                                    $bc=($st=='مكتمل')?'badge-success':(($st=='جاري التنفيذ')?'badge-pending':'badge-fail');
                                ?><span class="badge <?php echo $bc; ?>"><?php echo htmlspecialchars($st); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_orders)): ?><tr><td colspan="3">لا توجد طلبات بعد</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-trophy"></i> أعلى الرصيد</h2>
                <div class="table-container">
                    <table class="table">
                        <thead><tr><th>المستخدم</th><th>الرصيد</th></tr></thead>
                        <tbody>
                            <?php
                            $top_balances = [];
                            foreach ($Namero["coin"] ?? [] as $user_id => $balance) {
                                $top_balances[] = ['user'=>$user_id,'balance'=>$balance];
                            }
                            usort($top_balances, function($a,$b){return $b['balance']-$a['balance'];});
                            $top_balances = array_slice($top_balances,0,5);
                            ?>
                            <?php foreach ($top_balances as $balance): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($balance['user']); ?></td>
                                <td style="color:var(--cyan);font-family:'Cairo',sans-serif;font-weight:700;"><?php echo number_format($balance['balance'],2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($top_balances)): ?><tr><td colspan="2">لا يوجد مستخدمين بعد</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($current_tab == 'settings'): ?>
        <!-- الإعدادات العامة -->
        <form method="POST">
            <!-- المجموعة 1: الإعدادات الأساسية -->
            <div class="form-card" style="margin-bottom:14px;">
                <div class="settings-group-title"><i class="fas fa-sliders-h"></i> الإعدادات الأساسية</div>
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
                        <label class="form-label"><i class="fas fa-arrow-down-short-wide"></i> الحد الأدنى للكمية</label>
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
                        <input type="url" name="channel_link" class="form-control" value="<?php echo htmlspecialchars($settings['Ch'] ?? 'https://t.me/Dragon_Supor'); ?>" required>
                    </div>
                </div>
            </div>
            <!-- المجموعة 2: النصوص والكليشات -->
            <div class="form-card" style="margin-bottom:14px;">
                <div class="settings-group-title"><i class="fas fa-file-alt"></i> النصوص والكليشات</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-scroll"></i> كليشة الشروط</label>
                        <textarea name="terms_text" class="form-control" rows="5"><?php echo htmlspecialchars($settings['token'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-bolt"></i> كليشة الشحن</label>
                        <textarea name="charge_cliche" class="form-control" rows="5"><?php echo htmlspecialchars($settings['domain'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            <!-- المجموعة 3: تفعيل الميزات -->
            <div class="form-card" style="margin-bottom:18px;">
                <div class="settings-group-title"><i class="fas fa-toggle-on"></i> تفعيل الميزات</div>
                <div class="checkbox-group">
                    <label class="checkbox-label"><input type="checkbox" name="daily_gift_status" <?php echo ($settings['daily_gift_status']??'on')=='on'?'checked':''; ?>><span>الهدية اليومية</span></label>
                    <label class="checkbox-label"><input type="checkbox" name="invite_link_status" <?php echo ($settings['invite_link_status']??'on')=='on'?'checked':''; ?>><span>رابط الدعوة</span></label>
                    <label class="checkbox-label"><input type="checkbox" name="transfer_status" <?php echo ($settings['transfer_status']??'on')=='on'?'checked':''; ?>><span>تحويل النقاط</span></label>
                    <label class="checkbox-label"><input type="checkbox" name="starss" <?php echo ($settings['starss']??'on')=='on'?'checked':''; ?>><span>الشحن بالنجوم</span></label>
                    <label class="checkbox-label"><input type="checkbox" name="Market" <?php echo ($settings['Market']??'on')=='on'?'checked':''; ?>><span>قسم المتجر</span></label>
                    <label class="checkbox-label"><input type="checkbox" name="api_enabled" <?php echo ($settings['api_enabled']??'off')=='on'?'checked':''; ?>><span>تفعيل API للمطوّرين</span></label>
                    <label class="checkbox-label"><input type="checkbox" name="rshaq" <?php echo ($settings['rshaq']??'on')=='on'?'checked':''; ?>><span>الرشق</span></label>
                    <label class="checkbox-label" style="border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:8px 12px;background:rgba(239,68,68,0.05);">
                        <input type="checkbox" name="maintenance_mode" <?php echo ($settings['maintenance_mode']??'off')=='on'?'checked':''; ?> style="accent-color:#ef4444;">
                        <span style="color:#f87171;font-weight:700;">🔧 وضع الصيانة</span>
                    </label>
                </div>
            </div>
            <button type="submit" name="update_settings" class="btn btn-primary" style="width:100%;padding:13px;font-size:1em;justify-content:center;">
                <i class="fas fa-save"></i> حفظ جميع الإعدادات
            </button>
        </form>

        <!-- كلمة سر لوحة الأدمن -->
        <div class="form-card" style="margin-top:16px;">
            <h2 class="form-title">
                <i class="fas fa-lock"></i>
                كلمة سر لوحة الأدمن
            </h2>

            <?php
            

            $admins_raw = file_get_contents(__DIR__ . '/admins.php');
            preg_match("/define\('ADMIN_PANEL_PASS',\s*'([^']*)'\);/", $admins_raw, $pm);
            $current_pass_val = $pm[1] ?? '—';
            ?>

            <!-- عرض كلمة السر الحالية -->
            <div class="form-group">
                <label class="form-label"><i class="fas fa-eye-slash"></i> كلمة السر الحالية</label>
                <div style="position:relative;">
                    <input type="password" id="showPassField" class="form-control"
                           value="<?php echo htmlspecialchars($current_pass_val); ?>"
                           readonly style="letter-spacing:3px;padding-left:48px;">
                    <button type="button" onclick="toggleShowPass()"
                            style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                                   background:none;border:none;color:var(--on-surface-m);cursor:pointer;
                                   font-size:15px;padding:4px;transition:color .2s;"
                            id="showPassBtn">
                        <i class="fas fa-eye" id="showPassIco"></i>
                    </button>
                </div>
            </div>

            <!-- تغيير كلمة السر -->
            <form method="POST" style="margin-top:12px;" onsubmit="return validatePassForm()">
                <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label"><i class="fas fa-key"></i> كلمة سر جديدة</label>
                        <div style="position:relative;">
                            <input type="password" name="new_pass" id="newPass1" class="form-control"
                                   placeholder="أدخل كلمة سر جديدة" style="padding-left:48px;">
                            <button type="button" onclick="toggleField('newPass1','ico1')"
                                    style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                                           background:none;border:none;color:var(--on-surface-m);cursor:pointer;font-size:13px;padding:4px;">
                                <i class="fas fa-eye" id="ico1"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label"><i class="fas fa-check-double"></i> تأكيد كلمة السر</label>
                        <div style="position:relative;">
                            <input type="password" name="new_pass2" id="newPass2" class="form-control"
                                   placeholder="أعد كلمة السر" style="padding-left:48px;">
                            <button type="button" onclick="toggleField('newPass2','ico2')"
                                    style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                                           background:none;border:none;color:var(--on-surface-m);cursor:pointer;font-size:13px;padding:4px;">
                                <i class="fas fa-eye" id="ico2"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="passMatchMsg" style="font-size:0.8em;margin-top:8px;display:none;"></div>
                <button type="submit" name="change_panel_pass" class="btn btn-primary" style="margin-top:14px;width:100%;justify-content:center;">
                    <i class="fas fa-save"></i> حفظ كلمة السر الجديدة
                </button>
            </form>
        </div>

        <script>
        function toggleShowPass() {
            var f = document.getElementById('showPassField');
            var i = document.getElementById('showPassIco');
            if (f.type === 'password') { f.type = 'text'; i.className = 'fas fa-eye-slash'; }
            else { f.type = 'password'; i.className = 'fas fa-eye'; }
        }
        function toggleField(fid, iid) {
            var f = document.getElementById(fid);
            var i = document.getElementById(iid);
            if (f.type === 'password') { f.type = 'text'; i.className = 'fas fa-eye-slash'; }
            else { f.type = 'password'; i.className = 'fas fa-eye'; }
        }
        function validatePassForm() {
            var p1 = document.getElementById('newPass1').value;
            var p2 = document.getElementById('newPass2').value;
            var msg = document.getElementById('passMatchMsg');
            if (!p1) { msg.style.display='block'; msg.style.color='#f87171'; msg.textContent='⚠️ أدخل كلمة السر الجديدة'; return false; }
            if (p1 !== p2) { msg.style.display='block'; msg.style.color='#f87171'; msg.textContent='❌ كلمتا السر غير متطابقتين'; return false; }
            msg.style.display='none';
            return true;
        }
        document.getElementById('newPass1').addEventListener('input', function() {
            var p1 = this.value;
            var p2 = document.getElementById('newPass2').value;
            var msg = document.getElementById('passMatchMsg');
            if (p2 && p1 !== p2) { msg.style.display='block'; msg.style.color='#f87171'; msg.textContent='❌ غير متطابق'; }
            else if (p2 && p1 === p2) { msg.style.display='block'; msg.style.color='#4ade80'; msg.textContent='✅ متطابق'; }
            else { msg.style.display='none'; }
        });
        document.getElementById('newPass2').addEventListener('input', function() {
            var p2 = this.value;
            var p1 = document.getElementById('newPass1').value;
            var msg = document.getElementById('passMatchMsg');
            if (p2 && p1 !== p2) { msg.style.display='block'; msg.style.color='#f87171'; msg.textContent='❌ غير متطابق'; }
            else if (p2 && p1 === p2) { msg.style.display='block'; msg.style.color='#4ade80'; msg.textContent='✅ متطابق'; }
            else { msg.style.display='none'; }
        });
        </script>

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
                    <?php if (!empty($settings['api_providers'])): ?>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-plug"></i> اختر مزوّد API</label>
                        <select class="form-control" onchange="fillProviderEdit(this)" style="margin:0;">
                            <option value="">— أو أدخل يدوياً —</option>
                            <?php foreach($settings['api_providers'] as $puid=>$pdata): ?>
                            <option value="<?php echo htmlspecialchars($pdata['domain']); ?>"
                                    data-key="<?php echo htmlspecialchars($pdata['api_key']); ?>"
                                    <?php echo ($edit_service['data']['domain']==$pdata['domain'])?'selected':''; ?>>
                                <?php echo htmlspecialchars($pdata['name']); ?> — <?php echo htmlspecialchars($pdata['domain']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-globe"></i> الدومين</label>
                        <input type="text" name="domain" id="editDomain" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['domain']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-key"></i> API Key</label>
                        <input type="text" name="api_key" id="editApiKey" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['api']); ?>">
                    </div>
                    <script>
                    function fillProviderEdit(sel) {
                        var opt = sel.options[sel.selectedIndex];
                        if (!opt.value) return;
                        document.getElementById('editDomain').value = opt.value;
                        document.getElementById('editApiKey').value = opt.getAttribute('data-key');
                    }
                    </script>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-clock"></i> مدة الانتظار (ساعات)</label>
                        <input type="number" name="delay" class="form-control" value="<?php echo htmlspecialchars($edit_service['data']['delay']); ?>">
                    </div>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label"><i class="fas fa-mobile-alt"></i> المنصة (فلتر الموقع)</label>
                    <?php $curPlat = $edit_service['data']['platform'] ?? 'tiktok'; if ($curPlat === 'all') $curPlat = 'tiktok'; echo platPickerHTML('platform', $curPlat); ?>
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
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($section['name'] ?? ''); ?>
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
                        <span>
                            <?php echo htmlspecialchars($service['name'] ?? ''); ?>
                            <?php if (!empty($service['platform']) && $service['platform'] !== 'all'): ?>
                            <span class="plat-badge"><?php echo platLabel($service['platform']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="service-price"><?php echo number_format($service['price'] ?? 0, 3); ?></span>
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
                <?php $sid = $section_uid; ?>
                <form method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="section_uid" value="<?php echo $sid; ?>">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                        <input type="text" name="service_name" class="form-control" placeholder="اسم الخدمة" required>
                        <input type="number" name="price" class="form-control" placeholder="السعر" step="0.001" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">
                        <input type="number" name="min" class="form-control" placeholder="أقل كمية" value="10">
                        <input type="number" name="max" class="form-control" placeholder="أقصى كمية" value="1000">
                        <input type="number" name="delay" class="form-control" placeholder="تأخير" value="0">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 8px; margin-top: 10px;">
                        <input type="text" name="service_id" class="form-control" placeholder="ID الخدمة">
                        <?php if (!empty($settings['api_providers'])): ?>
                        <select class="form-control"
                                onchange="fillProviderAdd_<?php echo $sid; ?>(this)"
                                style="margin:0;">
                            <option value="">— اختر مزوّد API —</option>
                            <?php foreach($settings['api_providers'] as $puid=>$pdata): ?>
                            <option value="<?php echo htmlspecialchars($pdata['domain']); ?>"
                                    data-key="<?php echo htmlspecialchars($pdata['api_key']); ?>">
                                <?php echo htmlspecialchars($pdata['name']); ?>
                            </option>
                            <?php endforeach; ?>
                            <option value="__manual__">✏️ أدخل يدوياً</option>
                        </select>
                        <?php endif; ?>
                        <input type="text" name="domain" id="addDomain_<?php echo $sid; ?>" class="form-control" placeholder="الدومين" <?php echo empty($settings['api_providers'])?'':'style="display:none;"'; ?>>
                        <input type="text" name="api_key" id="addApiKey_<?php echo $sid; ?>" class="form-control" placeholder="API Key" <?php echo empty($settings['api_providers'])?'':'style="display:none;"'; ?>>
                    </div>
                    <script>
                    function fillProviderAdd_<?php echo $sid; ?>(sel) {
                        var opt = sel.options[sel.selectedIndex];
                        var dom = document.getElementById('addDomain_<?php echo $sid; ?>');
                        var key = document.getElementById('addApiKey_<?php echo $sid; ?>');
                        if (opt.value === '__manual__' || !opt.value) {
                            dom.style.display = ''; key.style.display = '';
                            dom.value = ''; key.value = '';
                        } else {
                            dom.style.display = 'none'; key.style.display = 'none';
                            dom.value = opt.value;
                            key.value = opt.getAttribute('data-key');
                        }
                    }
                    </script>
                    <div style="margin-top:10px;">
                        <label class="form-label" style="font-size:0.85em;"><i class="fas fa-mobile-alt"></i> المنصة (فلتر الموقع)</label>
                        <?php echo platPickerHTML('platform', 'all'); ?>
                    </div>
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
                <h3 style="color: var(--cyan);">
                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($section['name'] ?? ''); ?>
                    <small style="color: var(--on-surface-v);"><?php echo $section_uid; ?></small>
                </h3>
                <a href="?delete_store_section=<?php echo $section_uid; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">
                    <i class="fas fa-trash"></i> حذف القسم
                </a>
            </div>

            <!-- عرض المنتجات -->
            <div class="store-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <?php foreach ($section["items"] ?? [] as $item_uid => $item): ?>
                <div class="product-card" style="background: var(--surface-high); border: 1px solid var(--border); border-radius: 15px; padding: 15px;">
                    <h4 style="color: var(--cyan); margin-bottom: 10px;"><?php echo htmlspecialchars($item['name'] ?? ''); ?></h4>
                    <p style="color: var(--on-surface-v); font-size: 0.9em; margin-bottom: 10px;"><?php echo nl2br(htmlspecialchars($item['description'] ?? '')); ?></p>
                    <div style="font-size: 1.5em; color: var(--cyan); font-weight: bold; margin-bottom: 10px;">
                        <?php echo number_format($item['price'] ?? 0, 3); ?> <?php echo $settings['currency'] ?? 'نقاط'; ?>
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
            <form method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                <h4 style="color: var(--cyan); margin-bottom: 15px;">إضافة منتج جديد</h4>
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
        <?php
        $all_orders = [];
        foreach ($Namero["orders"] ?? [] as $user_id => $orders) {
            foreach ($orders as $index => $order) {
                $all_orders[] = [
                    'user'     => $user_id,
                    'service'  => $order['service']  ?? '',
                    'section'  => $order['section']  ?? '',
                    'quantity' => $order['quantity']  ?? 0,
                    'price'    => $order['price']     ?? 0,
                    'order_id' => $order['order_id']  ?? '',
                    'status'   => $order['status']    ?? '',
                    'time'     => $order['time']      ?? 0,
                ];
            }
        }
        usort($all_orders, function($a, $b){ return $b['time'] - $a['time']; });
        ?>
        <div class="form-card">
            <div class="orders-header">
                <h2 class="form-title" style="margin:0;">
                    <i class="fas fa-history"></i> جميع الطلبات
                    <span class="nav-badge" style="font-size:0.65em;"><?php echo count($all_orders); ?></span>
                </h2>
                <a href="?tab=orders&check_orders=1" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync-alt"></i> متابعة الطلبات
                </a>
            </div>

            <?php if (empty($all_orders)): ?>
            <div class="orders-empty">
                <i class="fas fa-box-open"></i>
                <p>لا توجد طلبات بعد</p>
            </div>
            <?php else: ?>

            <!-- جدول — ديسكتوب -->
            <div class="table-container orders-desktop">
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
                        <?php foreach ($all_orders as $order):
                            $st = $order['status'];
                            $bc = ($st=='مكتمل') ? 'badge-success' : (($st=='جاري التنفيذ'||$st=='مكتمل جزئي') ? 'badge-pending' : 'badge-fail');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['user']); ?></td>
                            <td><?php echo htmlspecialchars($order['service']); ?></td>
                            <td><?php echo htmlspecialchars($order['section']); ?></td>
                            <td><?php echo number_format($order['quantity']); ?></td>
                            <td><?php echo number_format($order['price'], 2); ?></td>
                            <td style="font-family:'Cairo',sans-serif;font-size:0.78em;color:var(--on-surface-m);"><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><span class="badge <?php echo $bc; ?>"><?php echo htmlspecialchars($st); ?></span></td>
                            <td style="font-size:0.78em;color:var(--on-surface-m);"><?php echo date('Y-m-d H:i', $order['time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- بطاقات — موبايل -->
            <div class="orders-cards">
                <?php foreach ($all_orders as $order):
                    $st = $order['status'];
                    $bc = ($st=='مكتمل') ? 'badge-success' : (($st=='جاري التنفيذ'||$st=='مكتمل جزئي') ? 'badge-pending' : 'badge-fail');
                ?>
                <div class="order-card">
                    <div class="order-card-top">
                        <div class="order-card-service">
                            <i class="fas fa-tag"></i>
                            <?php echo htmlspecialchars($order['service']); ?>
                        </div>
                        <span class="badge <?php echo $bc; ?>"><?php echo htmlspecialchars($st); ?></span>
                    </div>
                    <div class="order-card-rows">
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-user"></i> المستخدم</span>
                            <span class="ocr-val"><?php echo htmlspecialchars($order['user']); ?></span>
                        </div>
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-folder"></i> القسم</span>
                            <span class="ocr-val"><?php echo htmlspecialchars($order['section']); ?></span>
                        </div>
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-sort-amount-up"></i> الكمية</span>
                            <span class="ocr-val"><?php echo number_format($order['quantity']); ?></span>
                        </div>
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-coins"></i> السعر</span>
                            <span class="ocr-val" style="color:var(--cyan);font-weight:700;"><?php echo number_format($order['price'], 2); ?></span>
                        </div>
                        <?php if (!empty($order['order_id'])): ?>
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-hashtag"></i> رقم الطلب</span>
                            <span class="ocr-val" style="font-family:'Cairo',sans-serif;font-size:0.82em;color:var(--on-surface-m);"><?php echo htmlspecialchars($order['order_id']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-clock"></i> التاريخ</span>
                            <span class="ocr-val" style="font-size:0.82em;color:var(--on-surface-m);"><?php echo date('Y-m-d H:i', $order['time']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php endif; ?>
        </div>
        <?php elseif ($current_tab == 'coupons'): ?>
        <!-- ════ الكوبونات ════ -->
        <div class="form-card">
            <h2 class="form-title"><i class="fas fa-ticket-alt"></i> إضافة كوبون خصم جديد</h2>
            <form method="POST">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="form-label">كود الخصم</label>
                        <input type="text" name="coupon_code" class="form-control" placeholder="مثال: SAVE20" required style="text-transform:uppercase;">
                    </div>
                    <div>
                        <label class="form-label">نسبة الخصم (%)</label>
                        <input type="number" name="coupon_discount" class="form-control" placeholder="مثال: 20" min="1" max="100" required>
                    </div>
                </div>
                <div style="margin-top:18px;">
                    <label class="form-label">نوع الانتهاء</label>
                    <div style="display:flex;gap:24px;margin-top:10px;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--on-surface);">
                            <input type="radio" name="expiry_type" value="date" checked onchange="toggleCouponExpiry(this.value)">
                            <span>📅 تاريخ محدد</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--on-surface);">
                            <input type="radio" name="expiry_type" value="unlimited" onchange="toggleCouponExpiry(this.value)">
                            <span>♾️ غير محدود الوقت</span>
                        </label>
                    </div>
                </div>
                <div id="couponDateField" style="margin-top:14px;">
                    <label class="form-label">تاريخ ووقت الانتهاء</label>
                    <input type="datetime-local" name="expiry_date" class="form-control">
                </div>
                <div id="couponMaxUsesField" style="margin-top:14px;display:none;">
                    <label class="form-label">الحد الأقصى لعدد الاستخدامات</label>
                    <input type="number" name="max_uses" class="form-control" placeholder="مثال: 100" min="1" value="100">
                    <p style="color:var(--on-surface-m);font-size:12px;margin-top:6px;">⚠️ الكوبون يتوقف تلقائياً بعد هذا العدد من الاستخدامات</p>
                </div>
                <div style="margin-top:20px;">
                    <button type="submit" name="add_coupon" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة الكوبون</button>
                </div>
            </form>
        </div>

        <?php $coupons_list = $settings['coupons'] ?? []; ?>
        <?php if (!empty($coupons_list)): ?>
        <div class="form-card" style="margin-top:20px;">
            <h2 class="form-title">
                <i class="fas fa-list-ul"></i> الكوبونات الحالية
                <span class="nav-badge" style="font-size:0.65em;"><?php echo count($coupons_list); ?></span>
            </h2>

            <!-- جدول ديسكتوب -->
            <div class="table-container orders-desktop">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الخصم</th>
                            <th>نوع الانتهاء</th>
                            <th>الانتهاء / الحد</th>
                            <th>الاستخدامات</th>
                            <th>الحالة</th>
                            <th>حذف</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($coupons_list as $code => $coupon):
                        $is_exp = false;
                        if ($coupon['expiry_type'] === 'date') {
                            $exp_ts = strtotime($coupon['expiry_date'] ?? '');
                            if ($exp_ts && time() > $exp_ts) $is_exp = true;
                        } else {
                            $mx = intval($coupon['max_uses'] ?? 0);
                            $cu = intval($coupon['current_uses'] ?? 0);
                            if ($mx > 0 && $cu >= $mx) $is_exp = true;
                        }
                        $s_lbl = $is_exp ? '❌ منتهي' : '✅ نشط';
                        $s_cls = $is_exp ? 'badge-fail' : 'badge-success';
                    ?>
                    <tr>
                        <td><strong style="color:var(--cyan);font-family:'Cairo',sans-serif;letter-spacing:1px;"><?php echo htmlspecialchars($code); ?></strong></td>
                        <td><span class="badge badge-success"><?php echo $coupon['discount']; ?>%</span></td>
                        <td><?php echo $coupon['expiry_type'] === 'date' ? '📅 محدد' : '♾️ غير محدود'; ?></td>
                        <td style="font-size:0.85em;color:var(--on-surface-m);">
                            <?php if ($coupon['expiry_type'] === 'date'): ?>
                                <?php echo htmlspecialchars($coupon['expiry_date'] ?? '—'); ?>
                            <?php else: ?>
                                <?php echo intval($coupon['max_uses'] ?? 0); ?> استخدام كحد أقصى
                            <?php endif; ?>
                        </td>
                        <td style="font-family:'Cairo',sans-serif;">
                            <?php echo intval($coupon['current_uses'] ?? 0); ?>
                            <?php if ($coupon['expiry_type'] === 'unlimited' && isset($coupon['max_uses'])): ?>
                                / <?php echo $coupon['max_uses']; ?>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?php echo $s_cls; ?>"><?php echo $s_lbl; ?></span></td>
                        <td>
                            <a href="?tab=coupons&delete_coupon=<?php echo urlencode($code); ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('حذف الكوبون <?php echo addslashes($code); ?>؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- بطاقات موبايل -->
            <div class="orders-cards">
                <?php foreach ($coupons_list as $code => $coupon):
                    $is_exp = false;
                    if ($coupon['expiry_type'] === 'date') {
                        $exp_ts = strtotime($coupon['expiry_date'] ?? '');
                        if ($exp_ts && time() > $exp_ts) $is_exp = true;
                    } else {
                        $mx = intval($coupon['max_uses'] ?? 0);
                        $cu = intval($coupon['current_uses'] ?? 0);
                        if ($mx > 0 && $cu >= $mx) $is_exp = true;
                    }
                    $s_lbl = $is_exp ? '❌ منتهي' : '✅ نشط';
                    $s_cls = $is_exp ? 'badge-fail' : 'badge-success';
                ?>
                <div class="order-card">
                    <div class="order-card-top">
                        <div class="order-card-service" style="color:var(--cyan);font-family:'Cairo',sans-serif;letter-spacing:1px;">
                            <i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($code); ?>
                        </div>
                        <span class="badge <?php echo $s_cls; ?>"><?php echo $s_lbl; ?></span>
                    </div>
                    <div class="order-card-rows">
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-percent"></i> الخصم</span>
                            <span class="ocr-val"><span class="badge badge-success"><?php echo $coupon['discount']; ?>%</span></span>
                        </div>
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-clock"></i> النوع</span>
                            <span class="ocr-val"><?php echo $coupon['expiry_type'] === 'date' ? '📅 محدد' : '♾️ غير محدود'; ?></span>
                        </div>
                        <div class="order-card-row">
                            <span class="ocr-label"><i class="fas fa-users"></i> الاستخدامات</span>
                            <span class="ocr-val">
                                <?php echo intval($coupon['current_uses'] ?? 0); ?>
                                <?php if ($coupon['expiry_type'] === 'unlimited' && isset($coupon['max_uses'])): ?>
                                    / <?php echo $coupon['max_uses']; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="order-card-row" style="margin-top:8px;">
                            <a href="?tab=coupons&delete_coupon=<?php echo urlencode($code); ?>"
                               class="btn btn-danger btn-sm" style="width:100%;justify-content:center;"
                               onclick="return confirm('حذف الكوبون <?php echo addslashes($code); ?>؟')">
                                <i class="fas fa-trash"></i> حذف الكوبون
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="form-card" style="margin-top:20px;">
            <div class="orders-empty"><i class="fas fa-ticket-alt"></i><p>لا توجد كوبونات بعد — أضف أول كوبون!</p></div>
        </div>
        <?php endif; ?>

        <script>
        function toggleCouponExpiry(val) {
            document.getElementById('couponDateField').style.display    = val === 'date'      ? '' : 'none';
            document.getElementById('couponMaxUsesField').style.display = val === 'unlimited' ? '' : 'none';
        }
        </script>

        <?php elseif ($current_tab == 'api_keys'): ?>

        <!-- ══════════════════ API Keys Tab ══════════════════ -->
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-plug"></i> مزوّدو API</h1>
                <p class="page-subtitle">أضف مواقع SMM Panel وادر مفاتيح API الخاصة بها</p>
            </div>
        </div>

        <!-- نموذج إضافة مزوّد جديد -->
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-plus-circle"></i> إضافة مزوّد جديد
            </div>
            <form method="POST" action="?tab=api_keys">
                <input type="hidden" name="add_api_provider" value="1">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> اسم المزوّد</label>
                    <input type="text" name="provider_name" class="form-control" placeholder="مثال: DarkFollow SMM" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-globe"></i> رابط الموقع / الدومين</label>
                    <input type="text" name="provider_domain" class="form-control" placeholder="مثال: darkfollow.shop أو https://darkfollow.shop" dir="ltr" required>
                    <small style="color:var(--on-surface-v);font-size:11px;margin-top:4px;display:block;">يمكنك كتابة الرابط كاملاً أو الدومين فقط — سيُعالج تلقائياً</small>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-key"></i> API Key</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="text" name="provider_key" id="newProviderKey" class="form-control" placeholder="أدخل مفتاح API هنا..." dir="ltr" style="margin:0;flex:1;font-family:monospace;letter-spacing:1px;" required>
                        <button type="button" onclick="toggleKeyVisibility('newProviderKey',this)" style="background:var(--surface-high);border:1px solid var(--border);color:var(--on-surface-v);padding:10px 14px;border-radius:8px;cursor:pointer;white-space:nowrap;"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:6px;">
                    <button type="button" id="testNewBtn" onclick="testNewProvider()" class="btn btn-primary btn-sm" style="flex:1;">
                        <i class="fas fa-wifi"></i> اختبار الاتصال
                    </button>
                    <button type="submit" class="btn btn-success" style="flex:2;">
                        <i class="fas fa-plus"></i> حفظ المزوّد
                    </button>
                </div>
                <div id="testNewResult" style="display:none;margin-top:10px;padding:10px 14px;border-radius:8px;font-size:13px;"></div>
            </form>
        </div>

        <!-- قائمة المزوّدين -->
        <?php
        $providers = $settings['api_providers'] ?? [];
        if (!empty($providers)):
        ?>
        <div class="form-card" style="margin-top:20px;">
            <div class="form-card-header">
                <i class="fas fa-list"></i> المزوّدون المضافون
                <span style="background:var(--cyan);color:#003a45;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;margin-right:auto;"><?php echo count($providers); ?></span>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($providers as $puid => $pdata): ?>
            <div style="background:var(--surface-high);border:1px solid var(--border);border-radius:12px;padding:16px;position:relative;">
                <!-- رأس البطاقة -->
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(0,227,253,0.1);border:1px solid rgba(0,227,253,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-server" style="color:var(--cyan);font-size:14px;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:14px;color:var(--on-surface);"><?php echo htmlspecialchars($pdata['name']); ?></div>
                        <div style="font-size:11px;color:var(--cyan);font-family:monospace;direction:ltr;text-align:right;margin-top:2px;"><?php echo htmlspecialchars($pdata['domain']); ?></div>
                    </div>
                    <span style="font-size:10px;color:var(--on-surface-v);white-space:nowrap;"><?php echo $pdata['created_at']??''; ?></span>
                </div>
                <!-- API Key معلوم -->
                <div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px 12px;display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <i class="fas fa-key" style="color:var(--on-surface-v);font-size:12px;flex-shrink:0;"></i>
                    <span id="key_<?php echo $puid; ?>" style="flex:1;font-family:monospace;font-size:12px;color:var(--on-surface-v);direction:ltr;word-break:break-all;">
                        <?php echo str_repeat('•',8) . substr(htmlspecialchars($pdata['api_key']),-4); ?>
                    </span>
                    <span style="display:none;" id="keyval_<?php echo $puid; ?>"><?php echo htmlspecialchars($pdata['api_key']); ?></span>
                    <button type="button" onclick="toggleKeyCard('<?php echo $puid; ?>')" style="background:none;border:none;color:var(--on-surface-v);cursor:pointer;padding:4px;"><i class="fas fa-eye" id="eyeicon_<?php echo $puid; ?>"></i></button>
                    <button type="button" onclick="copyKey('<?php echo $puid; ?>')" style="background:none;border:none;color:var(--on-surface-v);cursor:pointer;padding:4px;" title="نسخ"><i class="fas fa-copy"></i></button>
                </div>
                <!-- أزرار -->
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="testProvider('<?php echo htmlspecialchars($pdata['domain']); ?>','<?php echo htmlspecialchars($pdata['api_key']); ?>','result_<?php echo $puid; ?>')"
                        style="flex:1;padding:9px;border-radius:8px;background:rgba(0,227,253,0.08);border:1px solid rgba(0,227,253,0.2);color:var(--cyan);font-size:12px;cursor:pointer;font-weight:600;">
                        <i class="fas fa-wifi"></i> اختبار
                    </button>
                    <a href="?tab=api_keys&delete_provider=<?php echo urlencode($puid); ?>"
                       onclick="return confirm('حذف مزوّد <?php echo addslashes($pdata['name']); ?>؟')"
                       style="flex:1;padding:9px;border-radius:8px;background:rgba(255,110,132,0.08);border:1px solid rgba(255,110,132,0.2);color:var(--danger);font-size:12px;cursor:pointer;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <i class="fas fa-trash"></i> حذف
                    </a>
                </div>
                <div id="result_<?php echo $puid; ?>" style="display:none;margin-top:10px;padding:10px 12px;border-radius:8px;font-size:12px;"></div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="form-card" style="margin-top:20px;">
            <div class="orders-empty">
                <i class="fas fa-plug"></i>
                <p>لا يوجد مزوّدون بعد — أضف أول موقع API!</p>
            </div>
        </div>
        <?php endif; ?>

        <script>
        function toggleKeyVisibility(inputId, btn) {
            var inp = document.getElementById(inputId);
            if (!inp) return;
            var isText = inp.type === 'text';
            inp.type = isText ? 'password' : 'text';
            btn.innerHTML = isText ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        }
        function toggleKeyCard(uid) {
            var span = document.getElementById('key_'+uid);
            var val  = document.getElementById('keyval_'+uid);
            var icon = document.getElementById('eyeicon_'+uid);
            var hidden = span.textContent.indexOf('•') !== -1;
            if (hidden) {
                span.textContent = val.textContent;
                icon.className = 'fas fa-eye-slash';
            } else {
                span.textContent = '••••••••' + val.textContent.slice(-4);
                icon.className = 'fas fa-eye';
            }
        }
        function copyKey(uid) {
            var val = document.getElementById('keyval_'+uid);
            if (!val) return;
            navigator.clipboard.writeText(val.textContent.trim()).then(function(){
                alert('تم نسخ المفتاح!');
            });
        }
        function testProvider(domain, key, resultId) {
            var div = document.getElementById(resultId);
            div.style.display = 'block';
            div.style.background = 'rgba(251,191,36,0.08)';
            div.style.border = '1px solid rgba(251,191,36,0.2)';
            div.style.color = '#fbbf24';
            div.textContent = '⏳ جاري الاختبار...';
            var fd = new FormData();
            fd.append('test_provider','1');
            fd.append('t_domain', domain);
            fd.append('t_key', key);
            fetch(window.location.href, {method:'POST', body:fd})
                .then(function(r){return r.json();})
                .then(function(data){
                    div.style.background = data.ok ? 'rgba(52,211,153,0.08)' : 'rgba(255,110,132,0.08)';
                    div.style.border = data.ok ? '1px solid rgba(52,211,153,0.25)' : '1px solid rgba(255,110,132,0.25)';
                    div.style.color = data.ok ? '#34d399' : '#ff6e84';
                    div.textContent = data.msg;
                })
                .catch(function(){ div.textContent = '❌ فشل الاتصال بالسيرفر.'; });
        }
        function testNewProvider() {
            var domain = document.querySelector('[name="provider_domain"]').value.trim().replace(/^https?:\/\

            var key    = document.querySelector('[name="provider_key"]').value.trim();
            var div    = document.getElementById('testNewResult');
            if (!domain || !key) { alert('أدخل الدومين والمفتاح أولاً.'); return; }
            testProvider(domain, key, 'testNewResult');
        }
        </script>

        <?php endif; ?>

        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->

</div><!-- /.admin-layout -->

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}
</script>
</body>
</html>