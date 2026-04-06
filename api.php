<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache');

function resp($data) { echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
function err($msg)   { resp(['error' => $msg]); }

$NAMERO_DIR = __DIR__ . '/NAMERO/';
$bot_folders = glob($NAMERO_DIR . '*', GLOB_ONLYDIR);
if (empty($bot_folders)) err('Server not configured.');

$BOT_ID_DIR = $bot_folders[0] . '/';

require_once __DIR__ . '/db.php';
db_init($BOT_ID_DIR . 'botdata.sqlite');

$_db_cnt = db()->query('SELECT COUNT(*) AS n FROM settings')->fetch();
if ((int)$_db_cnt['n'] === 0) err('Server error.');

$settings    = db_get_settings();
$Namero_data = db_get_namero(true); 

if (($settings['api_enabled'] ?? 'off') !== 'on') {
    err('API is currently disabled.');
}

$currency = $settings['currency'] ?? 'نقاط';

$key    = trim($_REQUEST['key'] ?? '');
$action = strtolower(trim($_REQUEST['action'] ?? ''));

if (empty($key))    err('Invalid API key.');
if (empty($action)) err('Action is required.');

$chat_id = $Namero_data['api_keys'][$key] ?? null;
if (!$chat_id) err('Invalid API key.');

function getServiceMap($sections) {
    $map = [];
    $id  = 1;
    foreach ($sections as $sec_uid => $section) {
        foreach ($section['services'] ?? [] as $svc_uid => $svc) {
            $map[$id] = ['sec' => $sec_uid, 'svc' => $svc_uid];
            $id++;
        }
    }
    return $map;
}

$sections   = $settings['sections'] ?? [];
$serviceMap = getServiceMap($sections);

if ($action === 'balance') {
    $balance = $Namero_data['coin'][$chat_id] ?? 0;
    resp(['balance' => (string)$balance, 'currency' => $currency]);
}

if ($action === 'services') {
    $list = [];
    foreach ($serviceMap as $api_id => $ref) {
        $sec = $sections[$ref['sec']] ?? null;
        $svc = $sec['services'][$ref['svc']] ?? null;
        if (!$svc || !$sec) continue;
        $list[] = [
            'service'  => $api_id,
            'name'     => $sec['name'] . ' - ' . $svc['name'],
            'type'     => 'Default',
            'rate'     => $svc['price'] ?? 0,
            'min'      => $svc['min']   ?? 10,
            'max'      => $svc['max']   ?? 10000,
            'category' => $sec['name'],
        ];
    }
    resp($list);
}

if ($action === 'add') {
    $service_id_req = (int)($_REQUEST['service'] ?? 0);
    $link           = trim($_REQUEST['link']     ?? '');
    $quantity       = (int)($_REQUEST['quantity'] ?? 0);

    if (!$service_id_req) err('Service ID is required.');
    if (!$link)           err('Link is required.');
    if ($quantity < 1)    err('Quantity must be positive.');

    $ref = $serviceMap[$service_id_req] ?? null;
    if (!$ref) err('Service not found.');

    $sec_uid = $ref['sec'];
    $svc_uid = $ref['svc'];
    $svc     = $sections[$sec_uid]['services'][$svc_uid] ?? null;
    $sec     = $sections[$sec_uid] ?? null;
    if (!$svc) err('Service not found.');

    $min = (int)($svc['min'] ?? 10);
    $max = (int)($svc['max'] ?? 10000);
    if ($quantity < $min) err("Minimum quantity is $min.");
    if ($quantity > $max) err("Maximum quantity is $max.");

    $price_per_unit = (float)($svc['price'] ?? 0);
    $total_price    = $price_per_unit * $quantity;
    $user_balance   = $Namero_data['coin'][$chat_id] ?? 0;

    if ($user_balance < $total_price) err('Insufficient balance.');

    

    $domain   = $svc['domain'] ?? '';
    $api_cred = $svc['api']    ?? '';
    $ext_service_id = $svc['service_id'] ?? '';

    $our_order_id = time() . rand(10, 99);

    if (!empty($domain) && !empty($api_cred) && !empty($ext_service_id)) {
        $order_url = "https://$domain/api/v2";
        $post_data = [
            'key'      => $api_cred,
            'action'   => 'add',
            'service'  => $ext_service_id,
            'link'     => $link,
            'quantity' => $quantity,
        ];
        $ch = curl_init($order_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post_data),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp_raw = curl_exec($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($curl_err) err('Failed to connect to provider: ' . $curl_err);

        $ext_resp = json_decode($resp_raw, true);
        if (isset($ext_resp['error'])) err('Provider error: ' . $ext_resp['error']);
        if (!empty($ext_resp['order'])) $our_order_id = $ext_resp['order'];
    }

    

    $Namero_data['coin'][$chat_id] = $user_balance - $total_price;
    $order_index = count($Namero_data['orders'][$chat_id] ?? []);
    $Namero_data['orders'][$chat_id][$order_index] = [
        'service_name' => $svc['name'] ?? '',
        'section_name' => $sec['name'] ?? '',
        'quantity'     => $quantity,
        'price'        => $total_price,
        'link'         => $link,
        'order_id'     => $our_order_id,
        'status'       => 'Pending',
        'domain'       => $domain,
        'api'          => $api_cred,
        'ext_service'  => $ext_service_id,
        'source'       => 'api',
        'created_at'   => time(),
    ];
    db_save_namero($Namero_data);

    resp(['order' => (int)$our_order_id]);
}

if ($action === 'status') {
    $order_id_req = trim($_REQUEST['order'] ?? '');
    if (!$order_id_req) err('Order ID is required.');

    $found = null;
    foreach ($Namero_data['orders'][$chat_id] ?? [] as $ord) {
        if ((string)($ord['order_id'] ?? '') === (string)$order_id_req) {
            $found = $ord;
            break;
        }
    }
    if (!$found) err('Order not found.');

    resp([
        'charge'    => (string)($found['price']    ?? 0),
        'start_count' => '0',
        'status'    => $found['status']   ?? 'Pending',
        'remains'   => '0',
        'currency'  => $currency,
    ]);
}

if ($action === 'orders') {
    $ids_raw = trim($_REQUEST['orders'] ?? '');
    if (!$ids_raw) err('Order IDs are required (comma-separated).');
    $ids = array_map('trim', explode(',', $ids_raw));

    $result = [];
    foreach ($Namero_data['orders'][$chat_id] ?? [] as $ord) {
        $oid = (string)($ord['order_id'] ?? '');
        if (in_array($oid, $ids)) {
            $result[$oid] = [
                'charge'      => (string)($ord['price'] ?? 0),
                'start_count' => '0',
                'status'      => $ord['status'] ?? 'Pending',
                'remains'     => '0',
                'currency'    => $currency,
            ];
        }
    }
    resp($result);
}

err('Unknown action.');
