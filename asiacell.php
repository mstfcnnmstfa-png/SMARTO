<?php

define('ASIA_API', 'https://odpapp.asiacell.com/api/v1');
define('ASIA_KEY', '1ccbc4c913bc4ce785a0a2de444aa0d6');
define('ASIA_FCM', 'fltra-HST9CB_7QBnDLoZq:APA91bGD-RwvzxPm0BAB5BANGWhls8CoTlHJfe5Emb9aqF9Ro4Jq1kTzFL_PSDT3I44HSQVVpmcwJAJ9GsPpZEWPBjXZyPgk-_kpqk6_c3z4AP3j0tvPmbo');

define('ASIA_DEVICE_FIXED', '5ca5bf46-4131-4b0c-9b67-1660d8e8f3a6');

function _asia_headers(string $token = '', bool $random_device = false): array {
    if ($random_device) {
        $chars = str_split('abcdef0123456789');
        shuffle($chars);
        $did = '5ca5bf46-4131-4b0c-9b67-' . implode('', array_slice($chars, 0, 12));
    } else {
        $did = ASIA_DEVICE_FIXED;
    }
    $h = [
        'X-ODP-API-KEY: ' . ASIA_KEY,
        'Cache-Control: no-cache',
        'DeviceID: ' . $did,
        'X-OS-Version: 14',
        'X-Device-Type: [Android][Xiaomi][21081111RG 14][UPSIDE_DOWN_CAKE][HMS][4.4.0:90000336]',
        'X-ODP-APP-VERSION: 4.4.0',
        'X-FROM-APP: odp',
        'X-ODP-CHANNEL: mobile',
        'X-SCREEN-TYPE: false',
        'Content-Type: application/json; charset=UTF-8',
        'Host: odpapp.asiacell.com',
        'Connection: Keep-Alive',
        'User-Agent: okhttp/5.1.0',
    ];
    if ($token) $h[] = 'Authorization: Bearer ' . $token;
    return $h;
}

function _asia_post(string $endpoint, array $data, string $token = '', bool $random_device = false): ?array {
    $ch = curl_init(ASIA_API . $endpoint . '?lang=en');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => _asia_headers($token, $random_device),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    if (!$res) return null;
    return json_decode($res, true);
}

function asia_login(string $phone): ?string {
    $res = _asia_post('/login', ['captchaCode' => '', 'username' => $phone], '', true);
    error_log('[ASIA_LOGIN] phone=' . $phone . ' res=' . json_encode($res));
    if (!$res) return null;
    $next = $res['nextUrl'] ?? '';
    
    $query = parse_url($next, PHP_URL_QUERY) ?? '';
    parse_str($query, $params);
    if (!empty($params['PID'])) return $params['PID'];
    
    if (strpos($next, '=') !== false) {
        $parts = explode('=', $next);
        return $parts[2] ?? null;
    }
    return null;
}

function asia_verify(string $otp, string $pid): array {
    $res = _asia_post('/smsvalidation', [
        'PID'      => $pid,
        'passcode' => $otp,
        'token'    => ASIA_FCM,
    ]);
    if (!$res) return ['error' => 'فشل الاتصال بسيرفر اسياسيل. حاول مجدداً.'];
    error_log('[ASIA_VERIFY] res=' . json_encode($res));
    if (empty($res['success'])) {
        $msg = $res['message'] ?? ($res['error'] ?? ($res['errorMessage'] ?? 'رمز التحقق خاطئ أو منتهي'));
        return ['error' => $msg];
    }
    $token = $res['access_token'] ?? '';
    if (!$token) return ['error' => 'تم التحقق لكن لم يُستلم رمز الدخول. تواصل مع الدعم.'];
    return ['token' => $token];
}

function asia_start_transfer(string $token, int $amount, string $dest_phone): ?string {
    $res = _asia_post('/credit-transfer/start', [
        'receiverMsisdn' => $dest_phone,
        'amount'         => (string)$amount,
    ], $token);
    if (!$res || empty($res['success'])) return null;
    return $res['PID'] ?? null;
}

function asia_do_transfer(string $token, string $pid, string $otp): ?array {
    $res = _asia_post('/credit-transfer/do-transfer', [
        'PID'      => $pid,
        'passcode' => $otp,
    ], $token);
    if (!$res || empty($res['success'])) return null;
    return $res;
}

function asia_config(): array {
    $raw = _db_get_setting('asiacell', '{}');
    $cfg = json_decode($raw, true) ?: [];
    return array_merge([
        'status'  => 'off',
        'phone'   => '',
        'rate'    => 10,
        'amounts' => [1000, 2000, 5000],
    ], $cfg);
}

function asia_save_config(array $cfg): void {
    _db_set_setting('asiacell', json_encode($cfg, JSON_UNESCAPED_UNICODE));
}
