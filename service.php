<?php

session_start();
ob_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$BOT_TOKEN = "8076347498:AAEq520aOraqgxY0kQW7_fiyM23khnxSKNU";
$ADMIN_ID = "7816487928";
$SECRET_KEY = "Namero_Bot_Secret_Key_2024";

header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");

$chat_id = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['chat_id']) ? $_GET['chat_id'] : '');
$key = isset($_GET['key']) ? $_GET['key'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 'main';

function verifyUserKey($user_id, $key, $secret) {
    for ($i = 0; $i <= 2; $i++) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        if ($key === hash('sha256', $user_id . $secret . $date)) return true;
    }
    return false;
}

require_once __DIR__ . '/db.php';
$_svc_bot_id = '';
$_svc_bc = __DIR__ . '/.bot_cache.json';
if (file_exists($_svc_bc)) {
    $_svc_bcd = @json_decode(file_get_contents($_svc_bc), true);
    $_svc_bot_id = $_svc_bcd['id'] ?? '';
}
$_svc_db_dir = __DIR__ . '/NAMERO/' . $_svc_bot_id . '/botdata.sqlite';
db_init($_svc_db_dir);

$_bot_status_file = __DIR__ . '/bot_status.txt';

$_admins_file = __DIR__ . '/admins.php';
$_sub_admins_maint = file_exists($_admins_file) ? array_map('strval', (array)include($_admins_file)) : [];
$_is_admin_user = !empty($chat_id) && (
    (string)$chat_id === (string)$ADMIN_ID ||
    in_array((string)$chat_id, $_sub_admins_maint)
);
if (!$_is_admin_user && db_get_bot_status() === 'disabled') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        ob_clean();
        die(json_encode(['ok'=>false,'type'=>'maintenance','icon'=>'🔧','title'=>'وضع الصيانة','message'=>'البوت في وضع الصيانة حالياً. يرجى المحاولة لاحقاً.'], JSON_UNESCAPED_UNICODE));
    }
    die("<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width,initial-scale=1,maximum-scale=1'>
<title>وضع الصيانة — Dragon Follow</title>
<link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
<link href='https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap' rel='stylesheet'>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Cairo',sans-serif;}
body{background:#0a0a0b;min-height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:24px;overflow:hidden;position:relative;}
.blob{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;}
.blob-1{width:500px;height:500px;top:-20%;left:50%;transform:translateX(-50%);background:radial-gradient(circle,rgba(0,227,253,0.06),transparent 70%);}
.blob-2{width:400px;height:400px;bottom:-15%;left:50%;transform:translateX(-50%);background:radial-gradient(circle,rgba(251,191,36,0.05),transparent 70%);}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(255,255,255,0.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.02) 1px,transparent 1px);background-size:40px 40px;z-index:0;pointer-events:none;}
.card{position:relative;z-index:1;background:linear-gradient(145deg,rgba(22,22,24,0.95),rgba(17,17,19,0.98));border:1px solid rgba(255,255,255,0.07);border-radius:32px;padding:48px 32px 40px;text-align:center;max-width:380px;width:100%;box-shadow:0 40px 100px rgba(0,0,0,0.8),0 0 0 1px rgba(255,255,255,0.03) inset;animation:appear .6s cubic-bezier(.34,1.56,.64,1) both;}
@keyframes appear{from{opacity:0;transform:translateY(24px) scale(.96);}to{opacity:1;transform:none;}}
.badge{display:inline-flex;align-items:center;gap:7px;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25);border-radius:20px;padding:5px 16px;font-size:11px;font-weight:700;color:#fbbf24;margin-bottom:32px;}
.badge-dot{width:7px;height:7px;border-radius:50%;background:#fbbf24;box-shadow:0 0 8px #fbbf24;animation:blink 1.4s infinite;}
@keyframes blink{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.4;transform:scale(.8);}}
.icon-wrap{width:96px;height:96px;border-radius:28px;background:linear-gradient(145deg,rgba(0,227,253,0.1),rgba(0,227,253,0.03));border:1px solid rgba(0,227,253,0.18);display:flex;align-items:center;justify-content:center;margin:0 auto 28px;animation:pulse-glow 2.5s ease-in-out infinite alternate;}
@keyframes pulse-glow{from{box-shadow:0 0 20px rgba(0,227,253,0.06);}to{box-shadow:0 0 50px rgba(0,227,253,0.18);}}
.gear{position:relative;width:52px;height:52px;animation:spin 6s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}
.gear-outer{position:absolute;inset:0;border-radius:50%;border:4px solid #00E3FD;box-shadow:0 0 12px rgba(0,227,253,0.4);}
.gear-inner{position:absolute;inset:14px;border-radius:50%;background:#00E3FD;box-shadow:0 0 8px rgba(0,227,253,0.5);}
.gear-tooth{position:absolute;width:6px;height:12px;background:#00E3FD;border-radius:2px;left:50%;margin-left:-3px;transform-origin:center 26px;box-shadow:0 0 4px rgba(0,227,253,0.3);}
.t0{transform:rotate(0deg) translateY(-32px);}.t1{transform:rotate(45deg) translateY(-32px);}.t2{transform:rotate(90deg) translateY(-32px);}.t3{transform:rotate(135deg) translateY(-32px);}
.t4{transform:rotate(180deg) translateY(-32px);}.t5{transform:rotate(225deg) translateY(-32px);}.t6{transform:rotate(270deg) translateY(-32px);}.t7{transform:rotate(315deg) translateY(-32px);}
h2{color:#fff;font-size:1.45em;font-weight:800;margin-bottom:10px;line-height:1.3;}
.sub{color:#6b7280;font-size:0.88em;line-height:1.8;margin-bottom:0;}
.divider{display:flex;align-items:center;gap:12px;margin:24px 0;}
.divider-line{flex:1;height:1px;background:linear-gradient(90deg,transparent,rgba(0,227,253,0.15),transparent);}
.divider-dot{color:#374151;font-size:12px;}
.msg-box{background:rgba(0,227,253,0.04);border:1px solid rgba(0,227,253,0.1);border-radius:16px;padding:16px 18px;font-size:0.85em;color:#9ca3af;line-height:1.8;text-align:right;}
.msg-box strong{color:#00E3FD;display:block;margin-bottom:4px;font-size:0.8em;}
.btn{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:24px;padding:15px 24px;background:linear-gradient(135deg,#00E3FD,#00b8d9);color:#003a45;text-decoration:none;border-radius:14px;font-weight:800;font-size:0.9em;box-shadow:0 8px 28px rgba(0,227,253,0.22);transition:all .25s;}
.btn:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(0,227,253,0.32);}
.btn svg{width:17px;height:17px;fill:currentColor;}
.brand{margin-top:28px;position:relative;z-index:1;text-align:center;}
.brand-name{font-size:1em;font-weight:800;color:rgba(255,255,255,0.08);letter-spacing:2px;}
.brand-tag{font-size:0.6em;color:#1f2937;margin-top:2px;}
</style>
</head>
<body>
<div class='blob blob-1'></div>
<div class='blob blob-2'></div>
<div class='card'>
  <div class='badge'><div class='badge-dot'></div> وضع الصيانة</div>
  <div class='icon-wrap'>
    <div class='gear'>
      <div class='gear-outer'></div>
      <div class='gear-inner'></div>
      <div class='gear-tooth t0'></div><div class='gear-tooth t1'></div>
      <div class='gear-tooth t2'></div><div class='gear-tooth t3'></div>
      <div class='gear-tooth t4'></div><div class='gear-tooth t5'></div>
      <div class='gear-tooth t6'></div><div class='gear-tooth t7'></div>
    </div>
  </div>
  <h2>جاري الصيانة</h2>
  <p class='sub'>نعتذر عن الإزعاج، الخدمة متوقفة مؤقتاً</p>
  <div class='divider'><div class='divider-line'></div><div class='divider-dot'>✦</div><div class='divider-line'></div></div>
  <div class='msg-box'>
    <strong>📢 تنبيه</strong>
    نعمل على تحسين الخدمة وسنعود قريباً 🚀
  </div>
  <a href='https://t.me/Dragon_Supor' class='btn' target='_blank'>
    <svg viewBox='0 0 24 24'><path d='M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.17 14.06l-2.94-.918c-.64-.203-.654-.64.135-.953l11.49-4.43c.533-.194 1.001.13.84.949z'/></svg>
    تابع القناة للتحديثات
  </a>
</div>
<div class='brand'><div class='brand-name'>DRAGON FOLLOW</div><div class='brand-tag'>منصة الخدمات الاحترافية</div></div>
</body>
</html>");
}

if (!empty($chat_id) && !$_is_admin_user && db_is_banned((int)$chat_id)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        ob_clean();
        die(json_encode(['ok'=>false,'type'=>'banned','icon'=>'🚫','title'=>'محظور','message'=>'تم حظر حسابك من استخدام هذه الخدمة.'], JSON_UNESCAPED_UNICODE));
    }
    die("<div style='font-family:Cairo,sans-serif;text-align:center;padding:60px;direction:rtl'><h1>🚫</h1><h2>تم حظر حسابك</h2><p>لا يمكنك الوصول إلى هذه الخدمة.</p></div>");
}

if (!empty($chat_id) && !$_is_admin_user && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_ch_list = db_get_force_channels();
    if (!empty($_ch_list)) {
        $_not_sub = [];
        foreach ($_ch_list as $_chan) {
            $_chan = ltrim($_chan, '@');
            $_ch_url = "https://api.telegram.org/bot{$BOT_TOKEN}/getChatMember?chat_id=@{$_chan}&user_id={$chat_id}";
            $_ch_res = @file_get_contents($_ch_url);
            $_ch_data = $_ch_res ? json_decode($_ch_res, true) : null;
            $_ch_status = $_ch_data['result']['status'] ?? 'left';
            if (!in_array($_ch_status, ['creator','administrator','member'])) {
                $_not_sub[] = $_chan;
            }
        }
        if (!empty($_not_sub)) {
            $_sub_btns_html = '';
            foreach ($_not_sub as $_chan) {
                $_sub_btns_html .= "<a href='https://t.me/{$_chan}' target='_blank' class='sub-btn'>
                    <span class='sub-icon'>📢</span>
                    <span class='sub-name'>@{$_chan}</span>
                    <span class='sub-arrow'>←</span>
                </a>";
            }
            $_done_url = htmlspecialchars($_SERVER['REQUEST_URI']);
            ob_clean();
            die("<!DOCTYPE html><html lang='ar' dir='rtl'>
<head>
<meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'>
<title>اشتراك إجباري</title>
<link rel='preconnect' href='https://fonts.googleapis.com'>
<link href='https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap' rel='stylesheet'>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Cairo',sans-serif;background:#0e0e0e;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#1a1919;border:1px solid #2a2a2a;border-radius:20px;padding:36px 28px;max-width:420px;width:100%;text-align:center;box-shadow:0 8px 40px #000a}
.icon{font-size:56px;margin-bottom:12px}
.title{font-size:22px;font-weight:900;color:#fff;margin-bottom:8px}
.sub{font-size:14px;color:#888;margin-bottom:28px;line-height:1.7}
.channels-label{font-size:13px;color:#00E3FD;margin-bottom:14px;font-weight:600;letter-spacing:.5px}
.sub-btn{display:flex;align-items:center;gap:12px;background:#111;border:1px solid #2a2a2a;border-radius:14px;padding:14px 18px;margin-bottom:10px;text-decoration:none;color:#fff;transition:.2s;font-weight:600;font-size:15px}
.sub-btn:hover{background:#1e1e1e;border-color:#00E3FD;transform:translateY(-1px)}
.sub-icon{font-size:20px;flex-shrink:0}
.sub-name{flex:1;text-align:right}
.sub-arrow{color:#00E3FD;font-size:18px;flex-shrink:0}
.done-btn{display:block;background:linear-gradient(135deg,#00E3FD,#0090a8);color:#000;font-weight:900;font-size:16px;padding:15px;border-radius:14px;text-decoration:none;margin-top:20px;border:none;cursor:pointer;width:100%;font-family:'Cairo',sans-serif}
.done-btn:hover{opacity:.9}
.divider{border:none;border-top:1px solid #2a2a2a;margin:20px 0}
</style>
</head>
<body>
<div class='card'>
  <div class='icon'>🔐</div>
  <div class='title'>اشتراك إجباري</div>
  <div class='sub'>يجب الاشتراك في القنوات التالية للمتابعة</div>
  <div class='channels-label'>القنوات المطلوبة</div>
  {$_sub_btns_html}
  <hr class='divider'>
  <button class='done-btn' onclick='window.location.href=\"{$_done_url}\"'>✅ اشتركت — تحقق الآن</button>
</div>
</body></html>");
        }
    }
}

if (empty($chat_id) || empty($key) || !verifyUserKey($chat_id, $key, $SECRET_KEY)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        ob_clean();
        die(json_encode(['ok' => false, 'type' => 'error', 'icon' => '🔒', 'title' => '❌ خطأ في التحقق', 'message' => 'المفتاح غير صالح أو منتهي الصلاحية.'], JSON_UNESCAPED_UNICODE));
    }
    
    $_bc_err = __DIR__ . '/.bot_cache.json';
    $_bot_uname_err = 'rc3BOT';
    if (file_exists($_bc_err)) {
        $_bc_d = @json_decode(file_get_contents($_bc_err), true);
        if (!empty($_bc_d['username'])) $_bot_uname_err = $_bc_d['username'];
    }
    $_bot_url_err = "https://t.me/{$_bot_uname_err}?start=open";
    die("
    <!DOCTYPE html>
    <html lang='ar' dir='rtl'>
    <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'>
    <title>خطأ في التحقق</title>
    <link rel='preconnect' href='https://fonts.googleapis.com'>
    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
    <link href='https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap' rel='stylesheet'>
    <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Cairo',sans-serif;}
    body{
      background:#0a0a0b;
      min-height:100vh;
      display:flex;
      justify-content:center;
      align-items:center;
      padding:24px;
      overflow:hidden;
      position:relative;
    }
    /* خلفية متوهجة */
    body::before{
      content:'';
      position:fixed;
      top:-30%;left:50%;
      transform:translateX(-50%);
      width:600px;height:400px;
      background:radial-gradient(ellipse,rgba(239,68,68,0.08) 0%,transparent 70%);
      pointer-events:none;
    }
    body::after{
      content:'';
      position:fixed;
      bottom:-20%;left:50%;
      transform:translateX(-50%);
      width:500px;height:300px;
      background:radial-gradient(ellipse,rgba(0,227,253,0.05) 0%,transparent 70%);
      pointer-events:none;
    }
    .card{
      position:relative;
      background:linear-gradient(145deg,#161618,#111113);
      border:1px solid rgba(255,255,255,0.07);
      border-radius:28px;
      padding:44px 32px 36px;
      text-align:center;
      max-width:360px;
      width:100%;
      box-shadow:0 32px 80px rgba(0,0,0,0.7),0 0 0 1px rgba(255,255,255,0.04) inset;
      animation:slideUp 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
    }
    @keyframes slideUp{
      from{opacity:0;transform:translateY(30px) scale(0.95);}
      to{opacity:1;transform:translateY(0) scale(1);}
    }
    /* شارة الحالة */
    .status-badge{
      display:inline-flex;align-items:center;gap:6px;
      background:rgba(239,68,68,0.1);
      border:1px solid rgba(239,68,68,0.25);
      border-radius:20px;padding:5px 14px;
      font-size:11px;font-weight:700;color:#f87171;
      margin-bottom:28px;
    }
    .status-dot{
      width:6px;height:6px;border-radius:50%;
      background:#ef4444;
      box-shadow:0 0 6px #ef4444;
      animation:pulse 1.5s infinite;
    }
    @keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.4;}}
    /* أيقونة التحذير */
    .lock-wrap{
      width:88px;height:88px;
      border-radius:24px;
      background:linear-gradient(145deg,rgba(239,68,68,0.12),rgba(239,68,68,0.05));
      border:1px solid rgba(239,68,68,0.2);
      display:flex;align-items:center;justify-content:center;
      margin:0 auto 24px;
      position:relative;
      box-shadow:0 0 40px rgba(239,68,68,0.1);
      animation:glow 2s ease-in-out infinite alternate;
    }
    @keyframes glow{
      from{box-shadow:0 0 20px rgba(239,68,68,0.08);}
      to{box-shadow:0 0 40px rgba(239,68,68,0.22);}
    }
    /* مثلث التحذير CSS */
    .warn-icon{
      position:relative;
      width:52px;height:46px;
    }
    .warn-icon::before{
      content:'';
      position:absolute;
      left:50%;top:0;
      transform:translateX(-50%);
      width:0;height:0;
      border-left:26px solid transparent;
      border-right:26px solid transparent;
      border-bottom:46px solid #ef4444;
      filter:drop-shadow(0 0 8px rgba(239,68,68,0.6));
    }
    .warn-icon::after{
      content:'!';
      position:absolute;
      left:50%;top:50%;
      transform:translate(-50%,-38%);
      color:#fff;
      font-size:22px;
      font-weight:900;
      font-family:'Cairo',sans-serif;
      line-height:1;
    }
    /* الخط الفاصل */
    .divider{
      width:48px;height:2px;
      background:linear-gradient(90deg,transparent,rgba(239,68,68,0.5),transparent);
      margin:20px auto;border-radius:2px;
    }
    h2{
      color:#fff;
      font-size:1.4em;font-weight:800;
      margin-bottom:10px;line-height:1.3;
    }
    .sub{
      color:#6b7280;font-size:0.82em;
      line-height:1.7;margin-bottom:4px;
    }
    .hint{
      color:#374151;font-size:0.72em;
      margin-top:8px;
      background:rgba(255,255,255,0.03);
      border:1px solid rgba(255,255,255,0.06);
      border-radius:10px;padding:10px 14px;
      line-height:1.6;
    }
    .hint span{color:#4b5563;}
    /* زر الدعم */
    .btn{
      display:flex;align-items:center;justify-content:center;gap:8px;
      margin-top:28px;padding:15px 28px;
      background:linear-gradient(135deg,#00E3FD,#00b8d9);
      color:#003a45;text-decoration:none;
      border-radius:14px;font-weight:800;font-size:0.95em;
      transition:all 0.25s;
      box-shadow:0 8px 24px rgba(0,227,253,0.25);
    }
    .btn:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,227,253,0.35);}
    .btn svg{width:18px;height:18px;fill:currentColor;}
    /* شعار صغير */
    .brand{
      margin-top:24px;
      color:#1f2937;font-size:0.65em;font-weight:600;
    }
    .brand span{color:#374151;}
    </style>
    </head>
    <body>
    <div class='card'>
      <div class='status-badge'><div class='status-dot'></div> وصول مرفوض</div>
      <div class='lock-wrap'><div class='warn-icon'></div></div>
      <h2>غير مصرح بالوصول</h2>
      <div class='divider'></div>
      <p class='sub'>انتهت صلاحية الرابط. احصل على رابط جديد من البوت.</p>
      <div class='hint'>
        <span>💡</span> اضغط الزر أدناه لفتح البوت والحصول على رابط محدّث
      </div>
      <a href='{$_bot_url_err}' class='btn' target='_blank' style='margin-bottom:10px'>
        <svg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'><path d='M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.17 14.06l-2.94-.918c-.64-.203-.654-.64.135-.953l11.49-4.43c.533-.194 1.001.13.84.949-.001-.001-.001.003 0 0z'/></svg>
        افتح البوت واحصل على رابط جديد
      </a>
      <div class='brand'>Dragon Follow &mdash; <span>منصة الخدمات</span></div>
    </div>
    </body>
    </html>
    ");
}

$NAMERO_DIR = __DIR__ . '/NAMERO/';
$bot_folders = glob($NAMERO_DIR . '*', GLOB_ONLYDIR);
if (empty($bot_folders)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { header('Content-Type: application/json; charset=utf-8'); ob_clean(); die(json_encode(['ok'=>false,'type'=>'error','icon'=>'❌','title'=>'خطأ في النظام','message'=>'لم يتم العثور على مجلد البوت.'],JSON_UNESCAPED_UNICODE)); }
    die("❌ خطأ: لم يتم العثور على مجلد البوت.");
}
$BOT_ID_DIR = $bot_folders[0] . '/';

require_once __DIR__ . '/db.php';
db_init($BOT_ID_DIR . 'botdata.sqlite');

$daily_gifts_file = $BOT_ID_DIR . 'daily_gifts.json';

$api_settings = db_get_settings();
$Namero_data  = db_get_namero(true); 

$_user_last_gift = db_get_user_daily_gift((int)$chat_id);

db_ensure_namero_coin($Namero_data, (int)$chat_id);

$currency = $api_settings['currency'] ?? 'نقاط';
$rshaq = $Namero_data['rshaq'] ?? 'of';
$coin = $Namero_data["coin"][$chat_id] ?? 0;
$share = $Namero_data["mshark"][$chat_id] ?? 0;
$invite_reward = $api_settings['invite_reward'] ?? 5;
$user_orders = $Namero_data["orders"][$chat_id] ?? [];
$user_orders_count = count($user_orders);
$channel_link = $api_settings['Ch'] ?? 'https://t.me/لا يوجد';
$daily_gift_amount = $api_settings['daily_gift'] ?? 20;
$charge_cliche = str_replace('\n', "\n", $api_settings['domain'] ?? 'لم يتم التعيين');
$terms_text    = str_replace('\n', "\n", $api_settings['token']  ?? 'لم يتم التعيين');
$invite_link_status = $api_settings['invite_link_status'] ?? 'off';

$total_bot_orders = 0;
foreach ($Namero_data["orders"] ?? [] as $orders) {
    $total_bot_orders += count($orders);
}

$sections = $api_settings["sections"] ?? [];
$store_sections = $api_settings["store"]["sections"] ?? [];

$api_enabled_flag = ($api_settings['api_enabled'] ?? 'off') === 'on';
$user_api_key = null;
if ($api_enabled_flag && !empty($chat_id)) {
    

    foreach ($Namero_data['api_keys'] ?? [] as $k => $uid) {
        if ((string)$uid === (string)$chat_id) { $user_api_key = $k; break; }
    }
    

    if (!$user_api_key) {
        $user_api_key = hash('sha256', $chat_id . time() . rand(1000,9999));
        if (!isset($Namero_data['api_keys'])) $Namero_data['api_keys'] = [];
        $Namero_data['api_keys'][$user_api_key] = (string)$chat_id;
        db_save_namero($Namero_data);
    }
}

$first_name = 'مستخدم';
$username   = '';
$photo_url  = 'https://t.me/i/userpic/320/placeholder.svg';

$_user_profile = db_get_user_profile((string)$chat_id);
if ($_user_profile !== null) {
    $first_name = $_user_profile['name'] ?? 'مستخدم';
    $username   = $_user_profile['username'] ?? '';
    if ($username === 'غير معروف') $username = '';
} elseif (!empty($BOT_TOKEN) && !empty($chat_id)) {
    

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/getChat?chat_id={$chat_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response  = curl_exec($ch);
    curl_close($ch);
    $user_data = json_decode($response, true);
    if ($user_data && $user_data['ok']) {
        $first_name = $user_data['result']['first_name'] ?? 'مستخدم';
        $username   = $user_data['result']['username'] ?? '';
        db_save_user_profile((string)$chat_id, $first_name, $username);
    }
}

if (!empty($username)) {
    $photo_url = "https://t.me/i/userpic/320/{$username}.svg";
} elseif ($first_name !== 'مستخدم') {
    $photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($first_name) . '&background=8a2be2&color=fff&size=200&bold=true&length=2';
}

$show_modal = false;
$modal_title = '';
$modal_message = '';
$modal_icon = '';
$modal_type = '';

if (isset($_GET['claim']) && $_GET['claim'] == 1 && $page == 'daily_gift') {
    $now = time();
    $last_claim = $_user_last_gift;
    $seconds_remaining = 86400 - ($now - $last_claim);

    if ($seconds_remaining <= 0) {
        $old_coin = $coin;
        $Namero_data["coin"][$chat_id] = ($Namero_data["coin"][$chat_id] ?? 0) + $daily_gift_amount;
        $_user_last_gift = $now;
        

        db_set_user_daily_gift((int)$chat_id, $now);
        db_set_user_coin((int)$chat_id, (float)$Namero_data["coin"][$chat_id]);

        $coin = $Namero_data["coin"][$chat_id];

        $show_modal = true;
        $modal_type = 'success';
        $modal_title = '🎁 تهانينا!';
        $modal_message = "✅ تم إضافة {$daily_gift_amount} {$currency} إلى رصيدك كهدية يومية!\n\n💰 رصيدك السابق: {$old_coin} {$currency}\n💰 رصيدك الحالي: {$coin} {$currency}";
        $modal_icon = '🎉';
    }
}

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
            $Namero_data["coin"][$chat_id] = $coin - $item_price;
            $order_id = rand(100000, 999999);
            
            $admin_notify = "🛍 *طلب شراء جديد من المتجر!*\n\n";
            $admin_notify .= "👤 المستخدم: [$first_name](tg://user?id=$chat_id)\n";
            $admin_notify .= "🆔 الايدي: `$chat_id`\n";
            $admin_notify .= "📦 المنتج: {$item_name}\n";
            $admin_notify .= "💰 السعر: {$item_price} {$currency}\n";
            $admin_notify .= "📞 حساب الاستلام: `{$delivery_info}`\n";
            $admin_notify .= "🆔 رقم الطلب: `{$order_id}`\n";
            $admin_notify .= "⏰ الوقت: " . date('Y-m-d H:i:s');
            
            sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $admin_notify, 'markdown');
            
            $user_notify = "✅ تم شراء {$item_name} بنجاح!\n";
            $user_notify .= "💰 تم خصم {$item_price} {$currency} من رصيدك\n";
            $user_notify .= "📞 حساب الاستلام: {$delivery_info}\n";
            $user_notify .= "🆔 رقم الطلب: `{$order_id}`\n";
            $user_notify .= "📞 سيتم التواصل معك قريباً للتسليم.";
            
            sendTelegramMessage($BOT_TOKEN, $chat_id, $user_notify);
            
            db_save_namero($Namero_data);
            $coin = $Namero_data["coin"][$chat_id];
            
            $show_modal = true;
            $modal_type = 'success';
            $modal_title = '🛍 تم الشراء بنجاح!';
            $modal_message = "✅ المنتج: {$item_name}\n💰 المبلغ: {$item_price} {$currency}\n📞 حساب الاستلام: {$delivery_info}\n🆔 رقم الطلب: {$order_id}\n\n💰 رصيدك الجديد: {$coin} {$currency}";
            $modal_icon = '🎉';
        } else {
            $show_modal = true;
            $modal_type = 'error';
            $modal_title = '❌ فشل الشراء';
            $modal_message = "رصيدك غير كافٍ للشراء!\n\n💰 رصيدك الحالي: {$coin} {$currency}\n💰 سعر المنتج: {$item_price} {$currency}";
            $modal_icon = '⚠️';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $post_link = filter_var($_POST['link'] ?? '', FILTER_SANITIZE_URL);
    $quantity = floatval($_POST['quantity'] ?? 0);
    $section_uid = $_POST['section_uid'] ?? '';
    $service_uid = $_POST['service_uid'] ?? '';
    $old_coin = $coin;

    if (empty($section_uid) || empty($service_uid) || empty($post_link) || $quantity <= 0) {
        $show_modal = true;
        $modal_type = 'error';
        $modal_title = '❌ خطأ في الطلب';
        $modal_message = 'يرجى اختيار القسم والخدمة وإدخال الرابط والكمية بشكل صحيح.';
        $modal_icon = '⚠️';
    } else {
        if (isset($sections[$section_uid]['services'][$service_uid])) {
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
                $total_price = ($quantity / 1000) * $price;

                

                $coupon_code_used = strtoupper(trim($_POST['coupon_code'] ?? ''));
                $coupon_discount_pct = 0;
                $coupon_discount_amt = 0;
                if (!empty($coupon_code_used)) {
                    $all_coupons = $api_settings['coupons'] ?? [];
                    if (isset($all_coupons[$coupon_code_used])) {
                        $ck = $all_coupons[$coupon_code_used];
                        $coupon_valid = true;
                        if ($ck['expiry_type'] === 'date') {
                            $exp_ts = strtotime($ck['expiry_date'] ?? '');
                            if ($exp_ts && time() > $exp_ts) $coupon_valid = false;
                        } else {
                            $mx = intval($ck['max_uses'] ?? 0);
                            $cu = intval($ck['current_uses'] ?? 0);
                            if ($mx > 0 && $cu >= $mx) $coupon_valid = false;
                        }
                        if ($coupon_valid) {
                            $coupon_discount_pct = floatval($ck['discount']);
                            $coupon_discount_amt = $total_price * ($coupon_discount_pct / 100);
                            $total_price = $total_price - $coupon_discount_amt;
                            $total_price = round($total_price, 4);
                        }
                    }
                }

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
                    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                    $api_response = curl_exec($ch);
                    $curl_error   = curl_error($ch);
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

                        db_save_namero($Namero_data);

                        

                        if (!empty($coupon_code_used) && $coupon_discount_pct > 0) {
                            $api_settings['coupons'][$coupon_code_used]['current_uses'] =
                                intval($api_settings['coupons'][$coupon_code_used]['current_uses'] ?? 0) + 1;
                            db_save_settings($api_settings);
                        }

                        $coupon_line = $coupon_discount_pct > 0 ? "\n🏷️ خصم مطبّق: {$coupon_discount_pct}% (وفّرت {$coupon_discount_amt} {$currency})" : '';
                        $user_notify = "✅ تم تأكيد طلبك بنجاح!\n\n📦 الخدمة: $service_name\n🔢 الكمية: $quantity\n💰 السعر: $total_price $currency{$coupon_line}\n🆔 رقم الطلب: `$order_id`";
                        sendTelegramMessage($BOT_TOKEN, $chat_id, $user_notify);

                        $admin_notify = "🔔 *طلب جديد من الموقع!*\n\n👤 المستخدم: [$first_name](tg://user?id=$chat_id)\n🆔 الايدي: `$chat_id`\n📦 الخدمة: $service_name\n📁 القسم: $section_name\n🔢 الكمية: $quantity\n💰 السعر: $total_price $currency\n🆔 رقم الطلب: `$order_id`\n🌐 [الرابط]($post_link)";
                        sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $admin_notify, 'markdown');

                        $coin = $Namero_data["coin"][$chat_id];

                        $show_modal = true;
                        $modal_type = 'success';
                        $modal_title = '✅ تم إرسال الطلب!';
                        $modal_message = "📦 الخدمة: {$service_name}\n🔢 الكمية: {$quantity}\n💰 السعر: {$total_price} {$currency}\n🆔 رقم الطلب: {$order_id}\n\n💰 رصيدك المتبقي: {$coin} {$currency}";
                        $modal_icon = '🚀';
                    } else {
                        

                        if (!empty($curl_error)) {
                            $api_error_msg = "❌ فشل الاتصال: {$curl_error}";
                        } elseif (empty($api_response)) {
                            $api_error_msg = "❌ لم يرد الخادم. يرجى المحاولة لاحقاً.";
                        } elseif ($api_result && !empty($api_result['error'])) {
                            $api_error_msg = "❌ " . $api_result['error'];
                        } elseif ($api_result && !empty($api_result['message'])) {
                            $api_error_msg = "❌ " . $api_result['message'];
                        } else {
                            $api_error_msg = "❌ رد غير متوقع من الخادم:\n" . mb_substr($api_response, 0, 200);
                        }

                        $show_modal = true;
                        $modal_type = 'error';
                        $modal_title = '❌ فشل إرسال الطلب';
                        $modal_message = $api_error_msg;
                        $modal_icon = '🌐';

                        $fail_notify = "⚠️ *فشل طلب من الموقع*\n\n👤 المستخدم: $first_name\n🆔 $chat_id\n📦 الخدمة: $service_name\n🔢 الكمية: $quantity\n\n🔴 السبب: {$api_error_msg}";
                        sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $fail_notify, 'markdown');
                    }
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_gift_code'])) {
    $gift_code = trim($_POST['gift_code'] ?? '');
    
    if (empty($gift_code)) {
        $show_modal = true;
        $modal_type = 'error';
        $modal_title = '❌ خطأ';
        $modal_message = 'يرجى إدخال كود الهدية.';
        $modal_icon = '⚠️';
    } else {
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
            $current_time = time();
            $expiry_time = $code_data['created_at'] + ($code_data['days'] * 86400);
            
            if ($current_time > $expiry_time) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ كود منتهي';
                $modal_message = 'عذراً، هذا الكود انتهت صلاحيته.';
                $modal_icon = '⏰';
            }
            elseif (count($code_data['used_by']) >= $code_data['users']) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ كود مستنفذ';
                $modal_message = 'عذراً، هذا الكود استخدمه الحد الأقصى من المستخدمين.';
                $modal_icon = '👥';
            }
            elseif (in_array($chat_id, $code_data['used_by'])) {
                $show_modal = true;
                $modal_type = 'error';
                $modal_title = '❌ كود مستخدم';
                $modal_message = 'لقد استخدمت هذا الكود من قبل بالفعل.';
                $modal_icon = '⚠️';
            }
            else {
                $old_coin = $coin;
                $Namero_data["coin"][$chat_id] = $coin + $code_data['points'];
                $gifts_data[$code_key]['used_by'][] = $chat_id;
                
                db_save_namero($Namero_data);
                file_put_contents($gifts_file, json_encode($gifts_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                $coin = $Namero_data["coin"][$chat_id];
                
                $admin_notify = "🎁 *تم استبدال كود هدية!*\n\n";
                $admin_notify .= "👤 المستخدم: [$first_name](tg://user?id=$chat_id)\n";
                $admin_notify .= "🆔 الايدي: `$chat_id`\n";
                $admin_notify .= "🎫 الكود: `{$code_data['code']}`\n";
                $admin_notify .= "💰 النقاط: {$code_data['points']} {$currency}\n";
                $admin_notify .= "👥 المستخدمون المتبقون: " . ($code_data['users'] - count($code_data['used_by'])) . "\n";
                $admin_notify .= "⏰ الوقت: " . date('Y-m-d H:i:s');
                
                sendTelegramMessage($BOT_TOKEN, $ADMIN_ID, $admin_notify, 'markdown');
                
                $user_notify = "🎁 *تهانينا!*\n\n";
                $user_notify .= "✅ تم إضافة {$code_data['points']} {$currency} إلى رصيدك بنجاح!\n";
                $user_notify .= "💰 رصيدك السابق: {$old_coin} {$currency}\n";
                $user_notify .= "💰 رصيدك الحالي: {$coin} {$currency}";
                
                sendTelegramMessage($BOT_TOKEN, $chat_id, $user_notify, 'markdown');
                
                $show_modal = true;
                $modal_type = 'success';
                $modal_title = '🎉 تهانينا!';
                $modal_message = "✅ تم إضافة {$code_data['points']} {$currency} إلى رصيدك بنجاح!\n\n💰 رصيدك السابق: {$old_coin} {$currency}\n💰 رصيدك الحالي: {$coin} {$currency}";
                $modal_icon = '🎁';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    header('Content-Type: application/json; charset=utf-8');
    ob_clean();
    $coupon_code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    $coupons = $api_settings['coupons'] ?? [];
    if (empty($coupon_code)) {
        die(json_encode(['ok'=>false,'message'=>'يرجى إدخال كود الخصم.'], JSON_UNESCAPED_UNICODE));
    }
    if (!isset($coupons[$coupon_code])) {
        die(json_encode(['ok'=>false,'message'=>'❌ الكود غير صحيح أو غير موجود.'], JSON_UNESCAPED_UNICODE));
    }
    $c = $coupons[$coupon_code];
    if ($c['expiry_type'] === 'date') {
        $expiry_ts = strtotime($c['expiry_date'] ?? '');
        if ($expiry_ts && time() > $expiry_ts) {
            die(json_encode(['ok'=>false,'message'=>'❌ انتهت صلاحية هذا الكوبون.'], JSON_UNESCAPED_UNICODE));
        }
    } else {
        $max_u = intval($c['max_uses'] ?? 0);
        $cur_u = intval($c['current_uses'] ?? 0);
        if ($max_u > 0 && $cur_u >= $max_u) {
            die(json_encode(['ok'=>false,'message'=>'❌ تم استخدام هذا الكوبون بالحد الأقصى المسموح.'], JSON_UNESCAPED_UNICODE));
        }
    }
    die(json_encode([
        'ok'       => true,
        'discount' => $c['discount'],
        'message'  => "✅ تم تطبيق خصم {$c['discount']}% بنجاح!",
    ], JSON_UNESCAPED_UNICODE));
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

$username_bot = '';
$_bot_cache_path = __DIR__ . '/.bot_cache.json';
if (file_exists($_bot_cache_path)) {
    $_bc = json_decode(file_get_contents($_bot_cache_path), true);
    $username_bot = $_bc['username'] ?? '';
}
if (empty($username_bot) && !empty($BOT_TOKEN)) {
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
        file_put_contents($_bot_cache_path, json_encode(['id' => $bot_info_data['result']['id'], 'username' => $username_bot]));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    ob_clean();
    echo json_encode([
        'ok'      => ($modal_type === 'success'),
        'type'    => $modal_type ?: 'error',
        'icon'    => $modal_icon ?: '❌',
        'title'   => $modal_title ?: '❌ خطأ',
        'message' => $modal_message ?: 'حدث خطأ غير معروف.',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function detectPlatform(string $name): string {
    $n = mb_strtolower($name, 'UTF-8');
    $map = [
        'facebook'  => ['facebook','فيسبوك','fb','فب'],
        'youtube'   => ['youtube','يوتيوب','يوتوب','yt'],
        'tiktok'    => ['tiktok','تيك توك','تيكتوك','تيك'],
        'instagram' => ['instagram','انستقرام','انستا','insta','ig'],
        'telegram'  => ['telegram','تيليجرام','تيلغرام','تلغرام','تيلجرام'],
        'snapchat'  => ['snapchat','سناب شات','سناب','snap'],
        'twitter'   => ['twitter','تويتر','تويت',' x ','#x'],
        'threads'   => ['threads','ثريدز','thread'],
        'whatsapp'  => ['whatsapp','واتساب','واتس','whts'],
    ];
    foreach ($map as $platform => $keywords) {
        foreach ($keywords as $kw) {
            if (mb_strpos($n, $kw, 0, 'UTF-8') !== false) return $platform;
        }
    }
    return 'all';
}

function getSectionPlatform(array $section): string {
    $services = $section['services'] ?? [];
    if (empty($services)) return detectPlatform($section['name'] ?? '');
    $counts = [];
    foreach ($services as $svc) {
        $p = $svc['platform'] ?? 'auto';
        if ($p !== 'auto' && $p !== 'all') {
            $counts[$p] = ($counts[$p] ?? 0) + 1;
        }
    }
    if (empty($counts)) return detectPlatform($section['name'] ?? '');
    arsort($counts);
    return array_key_first($counts);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> Dragon Folllow - منصة الخدمات الاحترافية</title>
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>

*{margin:0;padding:0;box-sizing:border-box;-webkit-touch-callout:none;-webkit-user-select:none;user-select:none;-webkit-tap-highlight-color:transparent;}
img,.stat-card-3d,.product-card-3d,.section-card-3d,.buy-btn,.submit-btn,.claim-btn,.menu-button{-webkit-user-drag:none;}
.menu-button,.stat-card-3d,.submit-btn,.claim-btn,.back-link,button{cursor:pointer;}

:root{
  --bg:            

  --surface:       

  --surface-high:  

  --surface-low:   

  --border:        rgba(73,72,71,0.35);
  --border-accent: rgba(0,227,253,0.25);
  --cyan:          

  --cyan-dim:      

  --cyan-glow:     rgba(0,227,253,0.15);
  --cyan-glow-md:  rgba(0,227,253,0.25);
  --on-surface:    

  --on-surface-v:  

  --on-surface-m:  

  --danger:        

  --warning:       

  --success:       

  --r-sm:  4px;
  --r-md:  8px;
  --r-lg:  16px;
  --r-xl:  24px;
}

html,body{
  font-family:'Cairo',sans-serif;
  background-color:var(--bg);
  color:var(--on-surface);
  min-height:100vh;
  overflow-x:hidden;
}

body *:not(i):not([class^="fa"]):not([class*=" fa"]){
  font-family:'Cairo',sans-serif;
}

.num,.hero-balance-value,.stat-value-3d,.invite-stat-value,.gift-amount,
.balance-amount,.hero-balance-row [dir="ltr"]{
  font-family:'Cairo',sans-serif!important;
  font-feature-settings:"tnum";
  direction:ltr;
}

.background-3d{position:fixed;inset:0;z-index:-1;overflow:hidden;pointer-events:none;}
.grid-3d{display:none;}
.blob1,.blob2{position:absolute;border-radius:50%;filter:blur(120px);opacity:0.6;}
.blob1{top:-10%;right:-10%;width:50%;height:50%;background:rgba(0,227,253,0.05);}
.blob2{bottom:-5%;left:20%;width:35%;height:35%;background:rgba(0,206,219,0.04);}

::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:rgba(0,227,253,0.25);border-radius:2px;}
::-webkit-scrollbar-thumb:hover{background:var(--cyan);}

.sidebar{
  position:fixed;right:-300px;top:0;width:288px;height:100vh;
  background:rgba(11,11,12,0.97);
  border-left:1px solid var(--border);
  box-shadow:-24px 0 80px rgba(0,0,0,0.7);
  transition:right 0.38s cubic-bezier(0.4,0,0.2,1);
  z-index:1000;display:flex;flex-direction:column;
  backdrop-filter:blur(40px);-webkit-backdrop-filter:blur(40px);
}
.sidebar.active{right:0;}

.sidebar-toggle{display:none;}

.sidebar-header{
  padding:28px 20px 18px;
  border-bottom:1px solid var(--border);flex-shrink:0;
  display:flex;align-items:center;gap:14px;
}
.sidebar-logo{
  width:44px;height:44px;border-radius:13px;
  background:linear-gradient(135deg,rgba(0,227,253,0.18),rgba(0,227,253,0.06));
  border:1px solid rgba(0,227,253,0.3);
  display:flex;align-items:center;justify-content:center;
  font-size:1.3em;color:var(--cyan);
  box-shadow:0 0 18px rgba(0,227,253,0.15);
  flex-shrink:0;
}
.sidebar-header-text{}
.sidebar-header h3{
  font-size:1.1em;font-weight:900;
  color:var(--on-surface);letter-spacing:-0.3px;
}
.sidebar-header p{color:var(--on-surface-m);font-size:0.68em;margin-top:2px;letter-spacing:0.5px;}

.sidebar-menu-container{flex:1;overflow-y:auto;padding:10px 10px;scrollbar-width:none;}
.sidebar-menu-container::-webkit-scrollbar{display:none;}
.sidebar-menu{list-style:none;}
.sidebar-menu li{margin-bottom:2px;}

.menu-section-label{
  font-size:0.58em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:2px;
  padding:14px 14px 6px;font-weight:700;
}

.menu-button{
  width:100%;padding:10px 12px;
  background:transparent;border:none;border-radius:11px;
  color:var(--on-surface-v);
  transition:all 0.18s;display:flex;align-items:center;gap:12px;
  font-size:0.88em;font-family:'Cairo',sans-serif;text-align:right;
}
.menu-button .icon-chip{
  width:34px;height:34px;border-radius:9px;
  background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);
  display:flex;justify-content:center;align-items:center;
  font-size:13px;flex-shrink:0;transition:all 0.2s;
  color:var(--on-surface-m);
}
.menu-button .menu-label{flex:1;font-weight:600;}
.menu-button .menu-arrow{font-size:9px;color:rgba(255,255,255,0.15);transition:all 0.2s;flex-shrink:0;}

.menu-button:hover{background:rgba(255,255,255,0.04);color:var(--on-surface);}
.menu-button:hover .icon-chip{background:rgba(0,227,253,0.08);border-color:rgba(0,227,253,0.2);color:var(--cyan);}
.menu-button:hover .menu-arrow{color:var(--cyan);transform:translateX(-2px);}

.menu-button.active{background:rgba(0,227,253,0.07);color:var(--cyan);}
.menu-button.active .icon-chip{
  background:rgba(0,227,253,0.15);border-color:rgba(0,227,253,0.35);
  color:var(--cyan);box-shadow:0 0 14px rgba(0,227,253,0.2);
}
.menu-button.active .menu-label{font-weight:800;}
.menu-button.active .menu-arrow{color:var(--cyan);transform:translateX(-2px);}

.sidebar-footer{
  padding:14px 20px;border-top:1px solid var(--border);
  text-align:center;font-size:0.68em;color:var(--on-surface-m);flex-shrink:0;
  letter-spacing:1px;display:flex;align-items:center;justify-content:center;gap:6px;
}

.main-container{max-width:560px;margin:0 auto;padding:72px 14px 120px;}

.user-profile-3d{
  background:rgba(26,25,25,0.6);
  backdrop-filter:blur(32px);
  border:1px solid var(--border);
  border-radius:var(--r-xl);padding:20px;margin-bottom:16px;
  position:relative;overflow:hidden;
}
.user-profile-3d::after{
  content:'';position:absolute;top:-60px;right:-60px;
  width:200px;height:200px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,227,253,0.06) 0%,transparent 70%);
}
.user-profile-content{display:flex;align-items:center;gap:16px;position:relative;z-index:1;}
.user-avatar-3d{
  width:72px;height:72px;border-radius:50%;object-fit:cover;flex-shrink:0;
  border:2px solid var(--border-accent);
  box-shadow:0 0 0 4px rgba(0,227,253,0.06);
  transition:all 0.3s;
}
.user-avatar-3d:hover{box-shadow:0 0 0 4px rgba(0,227,253,0.15),0 0 24px var(--cyan-glow);}
.user-info-3d h2{font-size:1.2em;font-weight:800;color:var(--on-surface);margin-bottom:2px;letter-spacing:-0.3px;}
.user-info-3d p{color:var(--on-surface-v);font-size:0.78em;margin-bottom:1px;}
.user-stats-3d{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;}
.stat-badge{
  background:var(--surface-high);border:1px solid var(--border);
  padding:5px 12px;border-radius:50px;font-weight:600;font-size:0.75em;
  display:flex;align-items:center;gap:6px;color:var(--on-surface-v);
}
.stat-badge i{color:var(--cyan);font-size:11px;}

.balance-card-3d{
  background:rgba(0,227,253,0.08);
  border:1px solid rgba(0,227,253,0.2);
  border-radius:var(--r-xl);padding:24px 22px;margin-bottom:16px;
  position:relative;overflow:hidden;
}
.balance-card-3d::before{
  content:'';position:absolute;top:-50px;left:-50px;
  width:180px;height:180px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,227,253,0.1) 0%,transparent 70%);
}
.balance-content{display:flex;justify-content:space-between;align-items:center;position:relative;z-index:1;}
.balance-label{font-size:0.78em;color:var(--on-surface-v);font-weight:600;text-transform:uppercase;letter-spacing:1.5px;}
.balance-label i{margin-left:6px;color:var(--cyan);}
.balance-amount{font-size:2.6em;font-weight:900;color:var(--cyan);text-shadow:0 0 30px var(--cyan-glow-md);letter-spacing:-1px;}

.stats-grid-3d{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:16px;}
.stat-card-3d{
  background:rgba(26,25,25,0.6);backdrop-filter:blur(16px);
  border:1px solid var(--border);border-radius:var(--r-lg);
  padding:20px 14px;text-align:center;
  transition:all 0.25s;position:relative;overflow:hidden;
  color:var(--on-surface);font-family:'Cairo',sans-serif;
  width:100%;
}
.stat-card-3d:hover{border-color:var(--border-accent);box-shadow:0 0 25px var(--cyan-glow);}
.stat-icon-3d{font-size:1.8em;color:var(--cyan);margin-bottom:8px;}
.stat-value-3d{
  font-family:'Cairo',sans-serif;
  font-feature-settings:"tnum";
  font-size:1.7em;font-weight:800;color:var(--on-surface);
  line-height:1.1;letter-spacing:-0.5px;direction:ltr;
}
.stat-label-3d{color:var(--on-surface-v);font-size:0.7em;margin-top:6px;letter-spacing:0;font-family:'Cairo',sans-serif;font-weight:400;}

.order-form-3d{
  background:rgba(26,25,25,0.6);
  backdrop-filter:blur(32px);
  border:1px solid var(--border);
  border-radius:var(--r-xl);padding:24px 20px;margin-top:12px;
}
.form-title{
  font-size:1.5em;font-weight:800;color:var(--on-surface);
  margin-bottom:6px;display:flex;align-items:center;gap:10px;letter-spacing:-0.5px;
}
.form-title i{color:var(--cyan);}
.form-subtitle{
  font-size:0.72em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:2px;margin-bottom:24px;
}

.form-group{margin-bottom:20px;}
.form-label{
  display:block;margin-bottom:12px;
  color:var(--on-surface-v);font-weight:700;
  font-size:0.72em;letter-spacing:2px;text-transform:uppercase;
}
.form-label i{color:var(--cyan);margin-left:6px;}

.form-control{
  width:100%;padding:15px 18px;
  background:#0c0c0c;
  border:1px solid var(--border);
  border-radius:var(--r-md);
  color:var(--on-surface);font-size:0.95em;
  transition:all 0.22s;font-family:'Cairo',sans-serif;
  box-sizing:border-box;
}
.form-control::placeholder{color:var(--on-surface-m);font-size:0.9em;}
.form-control:focus{
  outline:none;
  border-color:rgba(0,227,253,0.5);
  background:#0e0e0e;
  box-shadow:0 0 0 3px rgba(0,227,253,0.07), 0 4px 16px rgba(0,0,0,0.3);
}
select.form-control{
  appearance:none;-webkit-appearance:none;cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2300E3FD' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:left 16px center;
  padding-left:40px;
}

.service-details-3d{
  background:var(--surface-low);border:1px solid var(--border);
  border-radius:var(--r-lg);padding:16px;margin:16px 0;display:none;
}
.service-details-3d.active{display:block;animation:fadeInUp 0.35s ease;}
@keyframes fadeInUp{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}

.total-price-3d{
  background:rgba(0,227,253,0.06);border:1px solid rgba(0,227,253,0.18);
  border-radius:var(--r-lg);padding:18px;margin:16px 0;
  display:none;text-align:center;font-size:1em;font-weight:700;color:var(--on-surface-v);
}
.total-price-3d span{font-size:2em;font-weight:900;color:var(--cyan);display:block;margin-top:4px;letter-spacing:-1px;}

.detail-row{
  display:flex;justify-content:space-between;align-items:center;
  padding:10px 0;border-bottom:1px solid var(--border);font-size:0.88em;
}
.detail-row:last-child{border-bottom:none;}
.detail-label{color:var(--on-surface-v);text-transform:uppercase;letter-spacing:0.5px;font-size:0.8em;}
.detail-value{color:var(--cyan);font-weight:800;font-size:1em;}

.submit-btn{
  width:100%;padding:17px 24px;border:none;
  border-radius:14px;
  background:linear-gradient(135deg,#00E3FD 0%,#00a8c4 100%);
  color:#002530;font-size:0.9em;font-weight:900;
  cursor:pointer;transition:all 0.28s;
  margin-top:20px;font-family:'Cairo',sans-serif;
  letter-spacing:0.5px;
  position:relative;overflow:hidden;
  display:flex;align-items:center;justify-content:center;gap:9px;
  box-shadow:0 6px 24px rgba(0,227,253,0.32),0 2px 8px rgba(0,0,0,0.25);
}
.submit-btn::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,0.16),transparent 60%);
  transform:translateX(-100%);transition:0.55s;
}
.submit-btn:hover::before{transform:translateX(100%);}
.submit-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 12px 36px rgba(0,227,253,0.44),0 4px 12px rgba(0,0,0,0.25);
}
.submit-btn:active{transform:scale(0.97) translateY(0);}
.submit-btn i{font-size:1em;}

.claim-btn{
  display:inline-flex;align-items:center;gap:8px;text-decoration:none;
  padding:15px 32px;border:none;border-radius:14px;
  background:linear-gradient(135deg,#00E3FD 0%,#00a8c4 100%);
  color:#002530;font-size:0.88em;font-weight:900;
  cursor:pointer;transition:all 0.28s;
  margin:12px 0;font-family:'Cairo',sans-serif;
  letter-spacing:0.5px;
  box-shadow:0 6px 24px rgba(0,227,253,0.32);
  position:relative;overflow:hidden;
}
.claim-btn::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,0.14),transparent 60%);
  transform:translateX(-100%);transition:0.55s;
}
.claim-btn:hover::before{transform:translateX(100%);}
.claim-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 10px 32px rgba(0,227,253,0.44);
}
.claim-btn:active{transform:scale(0.97) translateY(0);}
.claim-btn:disabled{opacity:0.28;cursor:not-allowed;transform:none;box-shadow:none;}

.back-link{
  display:inline-flex;align-items:center;gap:8px;margin-top:16px;
  color:var(--on-surface-m);text-decoration:none;
  font-size:0.78em;transition:all 0.2s;
  background:none;border:none;font-family:'Cairo',sans-serif;
  letter-spacing:0.5px;padding:10px 0;
}
.back-link:hover{color:var(--cyan);}
.back-link i{font-size:13px;}

.copy-btn{
  background:rgba(0,227,253,0.06);color:var(--cyan);
  border:1px solid rgba(0,227,253,0.25);
  border-radius:12px;padding:12px 24px;margin-top:12px;
  cursor:pointer;transition:all 0.25s;font-size:0.82em;
  font-family:'Cairo',sans-serif;letter-spacing:0.5px;
  display:inline-flex;align-items:center;gap:8px;
}
.copy-btn:hover{background:rgba(0,227,253,0.12);box-shadow:0 0 20px rgba(0,227,253,0.15);transform:translateY(-1px);}
.copy-btn:active{transform:scale(0.97);}

.custom-dropdown{position:relative;width:100%;}

.dropdown-selected{
  background:#0c0c0c;
  border:1px solid var(--border);
  border-radius:var(--r-md);
  padding:0 18px;
  height:52px;
  color:var(--on-surface);font-size:0.95em;
  cursor:pointer;display:flex;justify-content:space-between;align-items:center;
  transition:all 0.22s;font-family:'Cairo',sans-serif;
  user-select:none;
}
.dropdown-selected:hover{
  border-color:rgba(0,227,253,0.35);
  background:#0e0e0e;
}
.dropdown-selected.active{
  border-color:rgba(0,227,253,0.5);
  border-radius:var(--r-md) var(--r-md) 0 0;
  border-bottom-color:transparent;
  background:#0e0e0e;
  box-shadow:0 0 0 3px rgba(0,227,253,0.07);
}
.dropdown-selected-text{
  flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.dropdown-selected-placeholder{color:var(--on-surface-m);font-size:0.9em;}
.dropdown-chevron{
  width:28px;height:28px;border-radius:50%;flex-shrink:0;
  background:rgba(0,227,253,0.08);border:1px solid rgba(0,227,253,0.15);
  display:flex;justify-content:center;align-items:center;
  color:var(--cyan);font-size:10px;
  transition:transform 0.3s,background 0.2s;
}
.dropdown-selected.active .dropdown-chevron{
  transform:rotate(180deg);
  background:rgba(0,227,253,0.15);
}

@keyframes dropDown{
  from{opacity:0;transform:translateY(-6px);}
  to{opacity:1;transform:translateY(0);}
}
.dropdown-items{
  position:absolute;top:100%;left:0;right:0;
  background:#0e0e0e;
  border:1px solid rgba(0,227,253,0.3);
  border-top:none;
  border-radius:0 0 var(--r-md) var(--r-md);
  max-height:240px;overflow-y:auto;
  z-index:1000;display:none;
  box-shadow:0 16px 48px rgba(0,0,0,0.8),0 0 0 1px rgba(0,227,253,0.05);
}
.dropdown-items.show{display:block;animation:dropDown 0.18s ease;}

.dropdown-item{
  padding:14px 18px;
  cursor:pointer;transition:all 0.15s;
  border-bottom:1px solid rgba(73,72,71,0.2);
  color:#e0dede;font-size:0.92em;
  font-family:'Cairo',sans-serif;
  display:flex;align-items:center;justify-content:space-between;
}
.dropdown-item:last-child{border-bottom:none;}
.dropdown-item::after{
  content:'';width:6px;height:6px;border-radius:50%;
  background:transparent;flex-shrink:0;
  transition:background 0.15s;
}
.dropdown-item:hover{
  background:rgba(0,227,253,0.05);
  color:var(--on-surface);
  padding-right:22px;
}
.dropdown-item:hover::after{background:rgba(0,227,253,0.4);}
.dropdown-item.selected{
  background:rgba(0,227,253,0.08);
  color:var(--cyan);font-weight:700;
}
.dropdown-item.selected::after{background:var(--cyan);box-shadow:0 0 6px var(--cyan);}

.dropdown-items::-webkit-scrollbar{width:3px;}
.dropdown-items::-webkit-scrollbar-track{background:transparent;}
.dropdown-items::-webkit-scrollbar-thumb{background:rgba(0,227,253,0.25);border-radius:2px;}

.modal-overlay{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.75);
  backdrop-filter:blur(12px);
  z-index:10000;display:none;
  justify-content:center;align-items:flex-end;
  padding:0;
}
@keyframes modalSlideUp{
  0%{transform:translateY(100%);opacity:0;}
  100%{transform:translateY(0);opacity:1;}
}
@keyframes iconPop{
  0%{transform:scale(0.5);opacity:0;}
  70%{transform:scale(1.1);}
  100%{transform:scale(1);opacity:1;}
}
.modal-3d{
  background:var(--surface);
  border:1px solid var(--border);
  border-top:1px solid var(--modal-accent,var(--border));
  border-radius:var(--r-xl) var(--r-xl) 0 0;
  width:100%;max-width:480px;
  text-align:center;
  animation:modalSlideUp 0.32s cubic-bezier(0.16,1,0.3,1) forwards;
  box-shadow:0 -24px 80px rgba(0,0,0,0.6);
  position:relative;overflow:hidden;
  padding-bottom:max(28px, env(safe-area-inset-bottom));
}
.modal-drag-handle{
  width:40px;height:4px;border-radius:2px;
  background:var(--border);margin:14px auto 0;
}
.modal-icon-wrap{
  width:72px;height:72px;border-radius:50%;
  background:var(--modal-icon-bg,rgba(0,227,253,0.1));
  border:1.5px solid var(--modal-icon-border,rgba(0,227,253,0.25));
  display:flex;justify-content:center;align-items:center;
  margin:24px auto 18px;
  font-size:1.8em;
  color:var(--modal-icon-color,var(--cyan));
  animation:iconPop 0.4s cubic-bezier(0.34,1.56,0.64,1) 0.1s both;
  box-shadow:0 0 0 8px var(--modal-icon-glow,rgba(0,227,253,0.06));
}
.modal-title{
  font-size:1.25em;font-weight:900;
  color:var(--on-surface);
  letter-spacing:-0.3px;margin-bottom:10px;
  padding:0 24px;
}
.modal-message{
  color:var(--on-surface-v);
  line-height:1.8;font-size:0.88em;
  padding:0 28px;margin-bottom:28px;
  white-space:pre-line;
}
.modal-actions{padding:0 20px;}
.modal-button{
  width:100%;padding:16px;border:none;
  border-radius:var(--r-md);
  background:var(--modal-btn-bg,var(--cyan));
  color:var(--modal-btn-color,#003a45);
  font-size:0.82em;font-weight:800;
  cursor:pointer;transition:all 0.25s;
  font-family:'Cairo',sans-serif;
  letter-spacing:2px;text-transform:uppercase;
  box-shadow:0 6px 24px var(--modal-btn-glow,rgba(0,227,253,0.2));
}
.modal-button:active{transform:scale(0.98);}
.modal-close{
  position:absolute;top:16px;left:16px;
  width:30px;height:30px;
  background:var(--surface-high);border:1px solid var(--border);
  border-radius:50%;display:flex;justify-content:center;align-items:center;
  cursor:pointer;transition:all 0.25s;color:var(--on-surface-m);font-size:11px;
}
.modal-close:hover{border-color:var(--danger);color:var(--danger);transform:rotate(90deg);}

.modal-3d.state-ok{
  --modal-accent:rgba(52,211,153,0.35);
  --modal-icon-bg:rgba(52,211,153,0.1);
  --modal-icon-border:rgba(52,211,153,0.25);
  --modal-icon-color:#34d399;
  --modal-icon-glow:rgba(52,211,153,0.06);
  --modal-btn-bg:#34d399;
  --modal-btn-color:#022c22;
  --modal-btn-glow:rgba(52,211,153,0.25);
}
.modal-3d.state-error{
  --modal-accent:rgba(248,113,113,0.35);
  --modal-icon-bg:rgba(248,113,113,0.1);
  --modal-icon-border:rgba(248,113,113,0.25);
  --modal-icon-color:#f87171;
  --modal-icon-glow:rgba(248,113,113,0.06);
  --modal-btn-bg:#f87171;
  --modal-btn-color:#2d0000;
  --modal-btn-glow:rgba(248,113,113,0.25);
}
.modal-3d.state-warn{
  --modal-accent:rgba(251,191,36,0.35);
  --modal-icon-bg:rgba(251,191,36,0.1);
  --modal-icon-border:rgba(251,191,36,0.25);
  --modal-icon-color:#fbbf24;
  --modal-icon-glow:rgba(251,191,36,0.06);
  --modal-btn-bg:#fbbf24;
  --modal-btn-color:#1a0a00;
  --modal-btn-glow:rgba(251,191,36,0.2);
}

.loading-3d{
  position:fixed;inset:0;background:rgba(14,14,14,0.93);backdrop-filter:blur(16px);
  z-index:9999;display:none;justify-content:center;align-items:center;flex-direction:column;gap:16px;
}
.loading-3d.active{display:flex;}
.spinner-3d{
  width:44px;height:44px;border:2px solid rgba(0,227,253,0.12);
  border-top:2px solid var(--cyan);border-radius:50%;
  animation:spin 0.8s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg);}}
.loading-3d p{color:var(--on-surface-v);font-size:0.78em;letter-spacing:2px;text-transform:uppercase;}

.footer-3d{
  text-align:center;padding:24px 0;margin-top:20px;
  color:var(--on-surface-m);border-top:1px solid var(--border);
  font-size:0.72em;letter-spacing:1px;text-transform:uppercase;
}
.footer-3d button{background:none;border:none;color:var(--cyan);font-family:'Cairo',sans-serif;font-size:inherit;cursor:pointer;padding:0;letter-spacing:inherit;}
.footer-3d button:hover{text-decoration:underline;}

.gift-card{
  background:rgba(26,25,25,0.6);backdrop-filter:blur(16px);
  border:1px solid var(--border);border-radius:var(--r-xl);
  padding:32px 24px;text-align:center;margin:12px 0;
  position:relative;overflow:hidden;
}
.gift-card::before{content:'';position:absolute;top:-60px;right:-60px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(0,227,253,0.06) 0%,transparent 70%);}
.gift-icon{font-size:52px;color:var(--cyan);margin-bottom:12px;animation:iconFloat 3s ease-in-out infinite;}
.gift-amount{font-family:'Cairo',sans-serif;font-feature-settings:"tnum";direction:ltr;font-size:3em;font-weight:800;color:var(--cyan);margin:12px 0;letter-spacing:-2px;text-shadow:0 0 30px var(--cyan-glow-md);}
.gift-timer{
  background:var(--surface-high);border:1px solid var(--border);
  border-radius:50px;padding:12px 20px;margin:14px 0;
  font-size:0.82em;color:var(--on-surface-v);letter-spacing:0.5px;
}

.charge-hero{
  position:relative;overflow:hidden;
  background:linear-gradient(135deg,rgba(0,227,253,0.08) 0%,rgba(0,0,0,0) 60%);
  border:1px solid rgba(0,227,253,0.18);
  border-radius:20px;padding:32px 20px 28px;
  text-align:center;margin-bottom:18px;
}
.charge-hero::before{
  content:'';position:absolute;top:-60px;left:50%;transform:translateX(-50%);
  width:220px;height:220px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,227,253,0.13) 0%,transparent 70%);
  pointer-events:none;
}
.charge-hero-icon{
  width:62px;height:62px;border-radius:50%;
  background:rgba(0,227,253,0.1);border:1.5px solid rgba(0,227,253,0.3);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 14px;font-size:1.6em;color:var(--cyan);
}
.charge-hero-label{font-size:0.82em;color:var(--on-surface-v);margin-bottom:6px;letter-spacing:0.5px;}
.charge-hero-amount{
  font-size:2.8em;font-weight:800;color:#fff;line-height:1;
  text-shadow:0 0 24px rgba(0,227,253,0.35);
}
.charge-hero-currency{font-size:0.88em;color:var(--cyan);margin-top:6px;letter-spacing:1px;}

.charge-steps-title{
  display:flex;align-items:center;gap:8px;
  font-size:0.82em;color:var(--on-surface-v);
  text-transform:uppercase;letter-spacing:1px;
  margin:20px 0 12px;
}
.charge-steps-title::after{content:'';flex:1;height:1px;background:var(--border);}

.charge-instructions{
  background:var(--surface-low);border:1px solid var(--border);
  border-radius:16px;padding:0;overflow:hidden;margin-bottom:18px;
}
.charge-inst-header{
  background:rgba(0,227,253,0.05);border-bottom:1px solid var(--border);
  padding:12px 16px;display:flex;align-items:center;gap:10px;
  font-size:0.88em;color:var(--cyan);font-weight:600;
}
.charge-inst-body{
  padding:18px 16px;font-size:0.9em;line-height:2.1;
  white-space:pre-line;color:var(--on-surface);direction:rtl;
}

.charge-support-btn{
  width:100%;padding:15px;border-radius:14px;border:1px solid rgba(0,227,253,0.25);
  background:rgba(0,227,253,0.06);color:var(--cyan);
  font-size:0.95em;font-family:'Cairo',sans-serif;font-weight:600;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;
  transition:all .2s;
}
.charge-support-btn:active{background:rgba(0,227,253,0.12);}

.invite-card{
  background:rgba(26,25,25,0.6);backdrop-filter:blur(16px);
  border:1px solid var(--border);border-radius:var(--r-xl);
  padding:28px 20px;text-align:center;margin:12px 0;position:relative;overflow:hidden;
}
.invite-link-box{
  background:#000;border:1px solid var(--border-accent);
  border-radius:var(--r-md);padding:14px;margin:16px 0;
  direction:ltr;text-align:left;font-family:monospace;
  font-size:0.82em;word-break:break-all;color:var(--cyan);
}
.invite-stats{display:flex;justify-content:space-around;margin:16px 0;padding:16px;background:var(--surface-low);border:1px solid var(--border);border-radius:var(--r-lg);}
.invite-stat-item{text-align:center;}
.invite-stat-value{font-family:'Cairo',sans-serif;font-feature-settings:"tnum";direction:ltr;font-size:2em;font-weight:800;color:var(--cyan);letter-spacing:-1px;}
.invite-stat-label{color:var(--on-surface-v);font-size:0.7em;margin-top:2px;text-transform:uppercase;letter-spacing:1px;}

.page-card{
  background:var(--surface);
  border:1px solid var(--border);
  border-top:3px solid var(--ph-color,var(--cyan));
  border-radius:var(--r-xl);
  overflow:hidden;
  margin-bottom:12px;
}
.page-card-header{
  display:flex;align-items:center;gap:14px;
  padding:20px 20px 18px;
  border-bottom:1px solid var(--border);
  background:rgba(0,0,0,0.15);
}
.page-card-icon{
  width:46px;height:46px;border-radius:var(--r-sm);flex-shrink:0;
  background:rgba(0,227,253,0.1);border:1px solid rgba(0,227,253,0.2);
  display:flex;justify-content:center;align-items:center;
  font-size:1.1em;color:var(--cyan);
}
.page-card-title{font-size:1em;font-weight:900;color:var(--on-surface);letter-spacing:-0.2px;}
.page-card-sub{font-size:0.65em;color:var(--on-surface-m);letter-spacing:0;margin-top:2px;font-weight:400;}

.field-block{padding:18px 20px 0;}
.field-label{
  font-size:0.62em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;
}
.page-card .form-control{margin:0;}
.page-card .form-group{padding:18px 20px 0;margin:0;}
.page-card .form-label{
  font-size:0.62em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;
}
.page-card .custom-dropdown{margin:0;}
.page-card .submit-btn{
  width:calc(100% - 40px);
  margin-left:20px;
  margin-right:20px;
}

.info-strip{
  display:flex;
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  margin-top:16px;
}
.info-strip-cell{
  flex:1;padding:14px 12px;text-align:center;
  border-left:1px solid var(--border);
}
.info-strip-cell:last-child{border-left:none;}
.info-strip-val{
  font-size:1.2em;font-weight:800;color:var(--on-surface);
  letter-spacing:-0.5px;direction:ltr;
}
.info-strip-lbl{
  font-size:0.58em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:1.5px;margin-top:4px;
}

.order-summary{
  display:flex;align-items:stretch;
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  margin-top:16px;
  background:rgba(0,0,0,0.15);
  transition:all 0.3s;
}
.order-summary-cell{
  flex:1;display:flex;flex-direction:column;align-items:center;
  justify-content:center;padding:18px 10px;text-align:center;
  gap:4px;transition:all 0.3s;
}
.order-summary-sep{
  width:1px;background:var(--border);flex-shrink:0;
}
.order-summary-icon{
  font-size:0.75em;color:var(--on-surface-m);margin-bottom:2px;
}
.order-summary-val{
  font-size:1.6em;font-weight:800;
  letter-spacing:-1px;line-height:1;
  transition:color 0.3s;direction:ltr;
}
.order-summary-curr{
  font-size:0.55em;font-weight:700;
  color:var(--on-surface-m);letter-spacing:1px;
  text-transform:uppercase;
  background:var(--surface-high);border:1px solid var(--border);
  border-radius:4px;padding:2px 6px;
}
.order-summary-lbl{
  font-size:0.58em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:1.5px;margin-top:2px;
}

.notice-box{
  display:flex;align-items:flex-start;gap:10px;
  padding:14px 20px;
  background:rgba(0,0,0,0.2);
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  font-size:0.82em;color:var(--on-surface-v);line-height:1.6;
}
.notice-box i{flex-shrink:0;margin-top:1px;}
.notice-warning{border-color:rgba(251,191,36,0.25);background:rgba(251,191,36,0.05);color:#fbbf24;}

.content-text{
  padding:20px;
  font-size:0.88em;color:var(--on-surface-v);
  line-height:1.9;white-space:pre-wrap;
  border-top:1px solid var(--border);
}

.invite-link-box{
  background:#000;
  border-radius:var(--r-sm);
  padding:14px 16px;
  font-family:'Cairo',monospace;
  font-size:0.78em;color:var(--cyan);
  letter-spacing:0.5px;direction:ltr;
  border:1px solid rgba(0,227,253,0.2);
  word-break:break-all;
}

.gift-showcase{
  display:flex;flex-direction:column;align-items:center;
  padding:32px 20px 24px;
  border-top:1px solid rgba(251,191,36,0.1);
  position:relative;
}
.gift-icon-wrap{
  position:relative;width:96px;height:96px;
  display:flex;align-items:center;justify-content:center;margin-bottom:22px;
}
.gift-icon-wrap i{
  font-size:2.8em;color:#fbbf24;position:relative;z-index:2;
  filter:drop-shadow(0 0 12px rgba(251,191,36,0.5));
}
.gift-glow-ring{
  position:absolute;inset:-8px;border-radius:50%;
  border:1px solid rgba(251,191,36,0.25);
  background:radial-gradient(circle,rgba(251,191,36,0.1) 0%,transparent 70%);
  animation:gift-pulse 2.5s ease-in-out infinite;
}
.gift-glow-ring::after{
  content:'';position:absolute;inset:10px;border-radius:50%;
  background:radial-gradient(circle,rgba(251,191,36,0.08) 0%,transparent 70%);
}
@keyframes gift-pulse{
  0%,100%{transform:scale(1);opacity:0.8;}
  50%{transform:scale(1.08);opacity:1;}
}

.gift-amount-row{
  display:flex;align-items:baseline;gap:8px;direction:ltr;
  margin-bottom:8px;
}
.gift-amount-val{
  font-family:'Cairo',sans-serif!important;
  font-size:3.2em;font-weight:800;color:#fbbf24;
  letter-spacing:-2px;line-height:1;
  text-shadow:0 0 30px rgba(251,191,36,0.35);
}
.gift-amount-curr{
  font-size:0.9em;font-weight:700;color:rgba(251,191,36,0.6);
  letter-spacing:1px;
}
.gift-amount-desc{
  font-size:0.7em;color:var(--on-surface-m);
  letter-spacing:0.5px;margin-bottom:20px;
}
.gift-perks{
  display:flex;gap:8px;flex-wrap:wrap;justify-content:center;
}
.gift-perk{
  display:inline-flex;align-items:center;gap:5px;
  background:rgba(251,191,36,0.07);border:1px solid rgba(251,191,36,0.18);
  border-radius:50px;padding:5px 12px;
  font-size:0.68em;font-weight:700;color:rgba(251,191,36,0.8);
  letter-spacing:0.3px;
}
.gift-perk i{font-size:0.85em;}

.timer-block{
  display:flex;flex-direction:column;align-items:center;
  padding:20px;margin:0 20px 20px;
  border:1px solid rgba(251,191,36,0.15);
  border-radius:16px;
  background:rgba(251,191,36,0.04);
}
.timer-label{
  font-size:0.6em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:2px;margin-bottom:14px;
  display:flex;align-items:center;gap:6px;
}
.timer-label i{color:#fbbf24;}
.timer-segs{
  display:flex;align-items:center;gap:6px;direction:ltr;
}
.timer-seg{
  display:flex;flex-direction:column;align-items:center;
  background:rgba(0,0,0,0.3);border:1px solid rgba(251,191,36,0.2);
  border-radius:12px;padding:10px 14px;min-width:56px;
}
.timer-seg-val{
  font-family:'Cairo',sans-serif!important;
  font-size:1.8em;font-weight:800;color:#fbbf24;
  letter-spacing:-1px;line-height:1;
  text-shadow:0 0 16px rgba(251,191,36,0.4);
}
.timer-seg-lbl{
  font-size:0.5em;color:var(--on-surface-m);
  text-transform:uppercase;letter-spacing:1.5px;margin-top:4px;
}
.timer-colon{
  font-size:1.6em;font-weight:800;color:rgba(251,191,36,0.4);
  line-height:1;padding-bottom:14px;
  animation:blink-colon 1s step-end infinite;
}
@keyframes blink-colon{0%,100%{opacity:1;}50%{opacity:0.2;}}

.code-input{
  text-align:center!important;
  font-family:'Cairo',monospace!important;
  font-size:1.15em!important;
  letter-spacing:4px!important;
  direction:ltr!important;
  text-transform:uppercase!important;
}

.hero-card{
  background:linear-gradient(160deg,rgba(22,22,24,0.97) 0%,rgba(14,14,16,0.99) 100%);
  border:1px solid rgba(0,227,253,0.14);
  border-radius:22px;
  overflow:hidden;
  margin-bottom:12px;
  position:relative;
}
.hero-card::before{
  content:'';position:absolute;top:-90px;right:-70px;
  width:260px;height:260px;border-radius:50%;pointer-events:none;
  background:radial-gradient(circle,rgba(0,227,253,0.07) 0%,transparent 65%);
}
.hero-card::after{
  content:'';position:absolute;bottom:-60px;left:-50px;
  width:200px;height:200px;border-radius:50%;pointer-events:none;
  background:radial-gradient(circle,rgba(167,139,250,0.05) 0%,transparent 65%);
}

.hero-user-row{
  display:flex;align-items:center;gap:14px;
  padding:20px 20px 18px;position:relative;z-index:1;
}
.hero-avatar-wrap{position:relative;flex-shrink:0;}
.hero-avatar{
  width:62px;height:62px;border-radius:50%;object-fit:cover;
  border:2px solid rgba(0,227,253,0.4);
  box-shadow:0 0 0 5px rgba(0,227,253,0.08),0 0 22px rgba(0,227,253,0.14);
}
.hero-online-dot{
  position:absolute;bottom:2px;right:2px;
  width:13px;height:13px;border-radius:50%;
  background:#34d399;border:2.5px solid 

  box-shadow:0 0 8px rgba(52,211,153,0.8);
  animation:pulse-dot 2.2s ease-in-out infinite;
}
@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.6;transform:scale(0.85);}}

.hero-user-info{flex:1;min-width:0;}
.hero-name{
  font-size:1.2em;font-weight:900;color:var(--on-surface);
  letter-spacing:-0.3px;line-height:1.2;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.hero-username-tag{
  font-size:0.7em;color:var(--on-surface-v);margin-top:4px;
  display:flex;align-items:center;gap:4px;
}
.hero-username-tag i{font-size:0.9em;color:var(--on-surface-m);}

.hero-plat-chip{
  flex-shrink:0;
  display:flex;flex-direction:column;align-items:center;gap:3px;
  background:rgba(0,227,253,0.07);
  border:1px solid rgba(0,227,253,0.18);
  border-radius:12px;padding:9px 11px;
  color:var(--cyan);
}
.hero-plat-chip i{font-size:1.25em;}
.hero-plat-chip span{font-size:0.5em;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;}

.hero-balance-strip{
  border-top:1px solid rgba(0,227,253,0.08);
  padding:18px 20px 16px;
  position:relative;z-index:1;
  background:rgba(0,227,253,0.025);
  display:flex;align-items:center;justify-content:space-between;
}
.hero-balance-left{}
.hero-balance-label{
  font-size:0.6em;color:var(--on-surface-m);
  letter-spacing:2px;margin-bottom:6px;
  display:flex;align-items:center;gap:5px;text-transform:uppercase;
}
.hero-balance-label i{color:var(--cyan);}
.hero-balance-main{display:flex;align-items:baseline;gap:8px;direction:ltr;}
.hero-balance-value{
  font-family:'Cairo',sans-serif!important;
  font-feature-settings:"tnum";
  font-size:2.8em;font-weight:800;color:var(--cyan);
  letter-spacing:-2px;line-height:1;
  text-shadow:0 0 36px rgba(0,227,253,0.28);
}
.hero-balance-curr{
  font-family:'Cairo',sans-serif!important;
  font-size:0.78em;font-weight:700;
  color:var(--on-surface-m);
  
}
.hero-balance-right{
  display:flex;flex-direction:column;align-items:center;gap:4px;
  background:rgba(0,227,253,0.06);border:1px solid rgba(0,227,253,0.15);
  border-radius:14px;padding:12px 14px;
}
.hero-balance-right i{font-size:1.6em;color:var(--cyan);opacity:0.8;}
.hero-balance-right span{font-size:0.5em;color:var(--on-surface-m);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;}

.hero-stats-strip{
  display:flex;border-top:1px solid rgba(255,255,255,0.05);
}
.hero-stat-cell{
  flex:1;padding:13px 10px 11px;text-align:center;
  border-left:1px solid rgba(255,255,255,0.05);
}
.hero-stat-cell:last-child{border-left:none;}
.hero-stat-icon{
  font-size:0.85em;margin-bottom:5px;
  color:var(--on-surface-m);
}
.hero-stat-val{
  font-family:'Cairo',sans-serif!important;
  font-feature-settings:"tnum";
  font-size:1.35em;font-weight:800;color:var(--on-surface);
  letter-spacing:-0.5px;direction:ltr;
}
.hero-stat-lbl{
  font-size:0.58em;color:var(--on-surface-m);
  letter-spacing:0.5px;margin-top:2px;
}

.cta-order-btn{
  width:100%;padding:0;border:none;border-radius:18px;
  background:linear-gradient(135deg,#00E3FD 0%,#00a0bb 100%);
  display:flex;align-items:center;
  cursor:pointer;transition:all 0.28s;margin-bottom:12px;
  overflow:hidden;position:relative;height:72px;
  box-shadow:0 8px 32px rgba(0,227,253,0.3),0 0 0 1px rgba(0,227,253,0.2);
}
.cta-order-btn::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(105deg,transparent 35%,rgba(255,255,255,0.12),transparent 65%);
  transform:translateX(-100%);transition:0.55s;
}
.cta-order-btn:hover::before{transform:translateX(100%);}
.cta-order-btn:hover{box-shadow:0 14px 44px rgba(0,227,253,0.45);transform:translateY(-2px);}
.cta-order-btn:active{transform:scale(0.98) translateY(0);}
.cta-icon-block{
  width:72px;height:72px;flex-shrink:0;
  background:rgba(0,0,0,0.12);
  display:flex;justify-content:center;align-items:center;
  font-size:1.6em;color:rgba(0,38,50,0.85);
  border-left:1px solid rgba(0,0,0,0.08);
}
.cta-text-block{flex:1;padding:0 18px;text-align:right;}
.cta-title{
  font-family:'Cairo',sans-serif!important;
  font-size:1.05em;font-weight:900;color:#002530;
  letter-spacing:-0.3px;display:block;
}
.cta-sub{
  font-family:'Cairo',sans-serif!important;
  font-size:0.68em;color:rgba(0,38,50,0.65);
  letter-spacing:0;margin-top:3px;font-weight:600;
}
.cta-arrow{
  width:52px;height:72px;flex-shrink:0;
  display:flex;justify-content:center;align-items:center;
  color:rgba(0,38,50,0.45);font-size:0.9em;
  transition:transform 0.25s;
}
.cta-order-btn:hover .cta-arrow{transform:translateX(-3px);}

.section-header{
  display:flex;align-items:center;gap:10px;
  padding:18px 2px 12px;
}
.section-header-line{flex:1;height:1px;background:linear-gradient(90deg,var(--border),transparent);}
.section-header-text{
  font-family:'Cairo',sans-serif!important;
  font-size:0.65em;color:var(--on-surface-m);
  letter-spacing:2px;white-space:nowrap;font-weight:700;
  text-transform:uppercase;
  background:var(--surface);border:1px solid var(--border);
  border-radius:20px;padding:4px 14px;
}

.services-grid{
  display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:10px;
}
.svc-card{
  background:rgba(22,22,22,0.8);border:1px solid var(--border);
  border-radius:18px;padding:20px 16px 18px;
  cursor:pointer;transition:all 0.25s;
  display:flex;flex-direction:column;align-items:flex-start;gap:14px;
  position:relative;overflow:hidden;
}
.svc-card::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse at top right,var(--svc-color,var(--cyan)) 0%,transparent 70%);
  opacity:0;transition:opacity 0.3s;
}
.svc-card:hover::before{opacity:0.06;}
.svc-card::after{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,transparent,var(--svc-color,var(--cyan)),transparent);
  opacity:0;transition:opacity 0.3s;
}
.svc-card:hover::after{opacity:1;}
.svc-card:hover{
  border-color:color-mix(in srgb,var(--svc-color,var(--cyan)) 28%,transparent);
  transform:translateY(-3px);
  box-shadow:0 12px 32px rgba(0,0,0,0.4),0 0 0 1px color-mix(in srgb,var(--svc-color,var(--cyan)) 18%,transparent);
}
.svc-card:active{transform:scale(0.97) translateY(0);}
.svc-icon{
  width:50px;height:50px;border-radius:13px;
  background:color-mix(in srgb,var(--svc-color,var(--cyan)) 12%,rgba(0,0,0,0.3));
  border:1px solid color-mix(in srgb,var(--svc-color,var(--cyan)) 28%,transparent);
  display:flex;justify-content:center;align-items:center;
  font-size:1.25em;color:var(--svc-color,var(--cyan));
  box-shadow:0 0 16px color-mix(in srgb,var(--svc-color,var(--cyan)) 14%,transparent);
  transition:all 0.25s;position:relative;z-index:1;
}
.svc-card:hover .svc-icon{
  transform:scale(1.1);
  box-shadow:0 0 28px color-mix(in srgb,var(--svc-color,var(--cyan)) 30%,transparent);
}
.svc-name{
  font-family:'Cairo',sans-serif!important;
  font-size:0.88em;font-weight:800;color:var(--on-surface);
  letter-spacing:-0.2px;position:relative;z-index:1;
}
.svc-desc{
  font-family:'Cairo',sans-serif!important;
  font-size:0.67em;color:var(--on-surface-m);
  letter-spacing:0;margin-top:2px;font-weight:400;position:relative;z-index:1;
}

.wide-card{
  width:100%;background:rgba(22,22,22,0.8);border:1px solid var(--border);
  border-radius:16px;padding:16px 18px;
  cursor:pointer;transition:all 0.25s;
  display:flex;align-items:center;gap:16px;
  position:relative;overflow:hidden;margin-bottom:10px;
}
.wide-card::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse at right,var(--svc-color,var(--cyan)) 0%,transparent 65%);
  opacity:0;transition:opacity 0.3s;
}
.wide-card:hover::before{opacity:0.04;}
.wide-card::after{
  content:'';position:absolute;right:0;top:0;bottom:0;width:2px;
  background:linear-gradient(180deg,transparent,var(--svc-color,var(--cyan)),transparent);
  opacity:0;transition:opacity 0.3s;
}
.wide-card:hover::after{opacity:1;}
.wide-card:hover{
  border-color:color-mix(in srgb,var(--svc-color,var(--cyan)) 28%,transparent);
  transform:translateY(-2px);
  box-shadow:0 10px 28px rgba(0,0,0,0.35);
}
.wide-card:active{transform:scale(0.99) translateY(0);}
.wide-card-icon{
  width:48px;height:48px;border-radius:13px;flex-shrink:0;
  background:color-mix(in srgb,var(--svc-color,var(--cyan)) 12%,rgba(0,0,0,0.3));
  border:1px solid color-mix(in srgb,var(--svc-color,var(--cyan)) 28%,transparent);
  display:flex;justify-content:center;align-items:center;
  font-size:1.15em;color:var(--svc-color,var(--cyan));
  box-shadow:0 0 14px color-mix(in srgb,var(--svc-color,var(--cyan)) 12%,transparent);
  transition:all 0.25s;position:relative;z-index:1;
}
.wide-card:hover .wide-card-icon{transform:scale(1.08);}
.wide-card-text{flex:1;position:relative;z-index:1;}
.wide-card-title{font-family:'Cairo',sans-serif!important;font-size:0.92em;font-weight:800;color:var(--on-surface);letter-spacing:-0.2px;}
.wide-card-sub{font-family:'Cairo',sans-serif!important;font-size:0.67em;color:var(--on-surface-m);letter-spacing:0;margin-top:3px;font-weight:400;}
.wide-card-arrow{color:var(--on-surface-m);font-size:0.85em;flex-shrink:0;transition:all 0.25s;position:relative;z-index:1;}
.wide-card:hover .wide-card-arrow{color:var(--svc-color,var(--cyan));transform:translateX(-4px);}

@media(max-width:480px){
  .hero-balance-value{font-size:2.4em;}
  .hero-avatar{width:56px;height:56px;}
  .modal-3d{padding:32px 18px;}
  .main-container{padding:68px 10px 120px;}
  .order-form-3d{padding:20px 14px;}
  .primary-action-icon{width:44px;height:44px;font-size:1.2em;}
  .primary-action-title{font-size:0.95em;}
}

.platforms-section{padding:20px 18px 16px;}
.platforms-grid{
  display:grid;grid-template-columns:repeat(5,1fr);gap:8px;
}
.platform-card{
  display:flex;flex-direction:column;align-items:center;gap:6px;
  padding:12px 4px 10px;
  background:color-mix(in srgb, var(--plat-clr,var(--cyan)) 7%, var(--surface-high));
  border:1.5px solid color-mix(in srgb, var(--plat-clr,var(--cyan)) 30%, transparent);
  border-radius:14px;cursor:pointer;
  transition:all 0.22s cubic-bezier(0.4,0,0.2,1);
  position:relative;overflow:hidden;
  -webkit-tap-highlight-color:transparent;
}
.platform-card:active{transform:scale(0.91);}
.platform-card.active{
  border-color:var(--plat-clr,var(--cyan));
  background:color-mix(in srgb, var(--plat-clr,var(--cyan)) 18%, rgba(0,0,0,0.5));
  box-shadow:0 0 0 1px var(--plat-clr,var(--cyan)), 0 0 14px color-mix(in srgb, var(--plat-clr,var(--cyan)) 40%, transparent);
}
.platform-card.active::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:2.5px;
  background:var(--plat-clr,var(--cyan));
}
.platform-card-icon{
  width:34px;height:34px;border-radius:9px;
  background:color-mix(in srgb, var(--plat-clr,var(--cyan)) 15%, rgba(0,0,0,0.35));
  display:flex;justify-content:center;align-items:center;
  font-size:1.1em;
  color:var(--plat-clr,var(--cyan));
  filter:drop-shadow(0 0 4px color-mix(in srgb, var(--plat-clr,var(--cyan)) 45%, transparent));
  transition:all 0.22s;
}
.platform-card.active .platform-card-icon{
  color:var(--plat-clr,var(--cyan));
  filter:drop-shadow(0 0 8px color-mix(in srgb, var(--plat-clr,var(--cyan)) 80%, transparent));
}
.platform-card-name{
  font-size:0.48em;
  color:color-mix(in srgb, var(--plat-clr,var(--cyan)) 70%, 

  text-align:center;line-height:1.2;
  transition:color 0.22s;
}
.platform-card.active .platform-card-name{
  color:var(--plat-clr,var(--cyan));font-weight:700;
}

.form-sec{
  border-top:1px solid var(--border);
  padding:16px 18px 14px;
}
.form-sec-hdr{
  display:flex;align-items:center;gap:8px;
  margin-bottom:12px;
}
.form-sec-icon{
  width:28px;height:28px;border-radius:8px;
  background:rgba(0,227,253,0.08);
  border:1px solid rgba(0,227,253,0.15);
  display:flex;justify-content:center;align-items:center;
  font-size:0.75em;color:var(--cyan);flex-shrink:0;
}
.form-sec-label{
  font-size:0.65em;font-weight:800;
  color:var(--on-surface-v);
  text-transform:uppercase;letter-spacing:1.5px;
}

.step-block{
  overflow:hidden;
  transition:max-height 0.38s cubic-bezier(0.4,0,0.2,1),opacity 0.3s;
  max-height:0;opacity:0;
}
.step-block.open{max-height:800px;opacity:1;overflow:visible;}

.btn-cyan {
    display:inline-flex;align-items:center;gap:6px;
    background:var(--cyan);color:#003a45;
    border:none;border-radius:8px;
    padding:10px 18px;font-size:14px;font-weight:700;
    font-family:'Cairo',sans-serif;cursor:pointer;
    transition:all 0.2s;flex-shrink:0;
}
.btn-cyan:hover { background:var(--cyan-dim);box-shadow:0 0 16px var(--cyan-glow); }
.btn-cyan:active { transform:scale(0.97); }

.coupon-toggle-btn {
    width:100%; display:flex; align-items:center; gap:10px;
    background:rgba(0,227,253,0.06); border:1px solid rgba(0,227,253,0.2);
    border-radius:10px; padding:12px 16px; color:var(--cyan); font-size:14px;
    font-family:'Cairo',sans-serif; font-weight:600; cursor:pointer;
    transition:all 0.25s; margin-bottom:4px;
}
.coupon-toggle-btn:hover { background:rgba(0,227,253,0.12); border-color:rgba(0,227,253,0.4); }
.coupon-toggle-btn.applied { background:rgba(52,211,153,0.08); border-color:rgba(52,211,153,0.3); color:#34d399; }

.store-balance-strip{display:flex;align-items:center;justify-content:space-between;background:rgba(167,139,250,0.07);border:1px solid rgba(167,139,250,0.2);border-radius:14px;padding:14px 18px;margin:0 20px 18px;}
.store-balance-label{font-size:0.75em;color:var(--on-surface-m);margin-bottom:3px;}
.store-balance-val{font-family:'Cairo',sans-serif;font-size:1.2em;font-weight:900;color:#a78bfa;}
.store-balance-icon{width:42px;height:42px;border-radius:50%;background:rgba(167,139,250,0.12);border:1px solid rgba(167,139,250,0.25);display:flex;align-items:center;justify-content:center;font-size:1.2em;}
.store-tabs-bar{display:flex;gap:8px;overflow-x:auto;padding:0 20px 14px;scrollbar-width:none;-ms-overflow-style:none;}
.store-tabs-bar::-webkit-scrollbar{display:none;}
.store-tab-btn{flex-shrink:0;padding:9px 22px;border-radius:22px;border:1px solid rgba(167,139,250,0.22);background:rgba(167,139,250,0.04);color:var(--on-surface-m);font-size:0.83em;font-family:'Cairo',sans-serif;font-weight:700;transition:all 0.22s;cursor:pointer;white-space:nowrap;letter-spacing:0.2px;}
.store-tab-btn.active{background:rgba(167,139,250,0.16);border-color:rgba(167,139,250,0.7);color:#a78bfa;box-shadow:0 0 14px rgba(167,139,250,0.18);}
.store-section-panel{display:none;}
.store-section-panel.active{display:block;}
.store-items-list{display:flex;flex-direction:column;gap:14px;padding:0 20px 22px;}
.store-item-card{background:rgba(167,139,250,0.03);border:1px solid rgba(167,139,250,0.13);border-radius:16px;padding:16px;transition:all 0.2s;}
.store-item-card:hover{border-color:rgba(167,139,250,0.28);}
.store-item-card.selected{border-color:rgba(167,139,250,0.6);background:rgba(167,139,250,0.07);}
.store-item-top{display:flex;align-items:center;gap:14px;}
.store-item-meta{flex:1;min-width:0;}
.store-item-name{font-family:'Cairo',sans-serif;font-weight:800;font-size:0.95em;color:var(--on-surface);margin-bottom:4px;line-height:1.3;}
.store-item-desc{font-size:0.78em;color:var(--on-surface-m);line-height:1.45;}
.store-item-side{display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0;}
.store-item-price-box{text-align:center;background:rgba(167,139,250,0.1);border:1px solid rgba(167,139,250,0.22);border-radius:10px;padding:8px 14px;min-width:80px;}
.store-item-price-val{font-family:'Cairo',sans-serif;font-size:1em;font-weight:900;color:#a78bfa;white-space:nowrap;}
.store-item-price-lbl{font-size:0.62em;color:var(--on-surface-m);margin-top:2px;}
.store-buy-toggle{padding:7px 16px;border-radius:20px;border:1px solid rgba(167,139,250,0.35);background:rgba(167,139,250,0.1);color:#a78bfa;font-family:'Cairo',sans-serif;font-size:0.78em;font-weight:700;transition:all 0.2s;display:flex;align-items:center;gap:5px;white-space:nowrap;cursor:pointer;}
.store-buy-toggle:hover{background:rgba(167,139,250,0.2);border-color:rgba(167,139,250,0.6);}
.store-buy-toggle.cancel{background:rgba(248,113,113,0.08);border-color:rgba(248,113,113,0.3);color:#f87171;}
.store-buy-toggle.cancel:hover{background:rgba(248,113,113,0.15);border-color:rgba(248,113,113,0.6);}
.store-buy-panel{display:none;margin-top:14px;padding-top:14px;border-top:1px solid rgba(167,139,250,0.12);}
.store-delivery-label{font-size:0.78em;color:var(--on-surface-m);margin-bottom:8px;font-family:'Cairo',sans-serif;}
.store-delivery-hint{font-size:0.68em;color:var(--on-surface-m);margin-top:6px;padding-right:2px;}
.store-confirm-btn{width:100%;margin-top:12px;padding:11px;border-radius:12px;background:#a78bfa;color:#1a0a2e;font-family:'Cairo',sans-serif;font-size:0.88em;font-weight:800;letter-spacing:0.3px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s;box-shadow:0 4px 18px rgba(167,139,250,0.35);}
.store-confirm-btn:hover{background:#c4b5fd;box-shadow:0 6px 24px rgba(167,139,250,0.45);}
.store-confirm-btn:active{transform:scale(0.98);}

.bottom-nav{
  position:fixed;bottom:0;left:0;right:0;z-index:999;
  background:rgba(11,11,12,0.98);
  backdrop-filter:blur(32px);-webkit-backdrop-filter:blur(32px);
  border-top:1px solid rgba(255,255,255,0.06);
  display:flex;height:76px;
  padding-bottom:env(safe-area-inset-bottom,0px);
  box-shadow:0 -8px 40px rgba(0,0,0,0.65);
}
.bnav-item{
  flex:1;display:flex;flex-direction:column;align-items:center;
  justify-content:center;gap:4px;
  background:none;border:none;cursor:pointer;
  color:rgba(255,255,255,0.35);transition:color 0.22s;
  font-family:'Cairo',sans-serif;position:relative;overflow:hidden;
  -webkit-tap-highlight-color:transparent;
}
.bnav-item.active{color:var(--cyan);}
.bnav-item.active .bnav-icon-wrap{
  background:rgba(0,227,253,0.12);
  border-color:rgba(0,227,253,0.25);
}
.bnav-item.active .bnav-icon{
  filter:drop-shadow(0 0 10px rgba(0,227,253,0.65));
}
.bnav-item.active::before{
  content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);
  width:46px;height:2px;
  background:linear-gradient(90deg,transparent,var(--cyan),transparent);
  border-radius:0 0 6px 6px;
  box-shadow:0 0 14px rgba(0,227,253,0.7);
}
.bnav-icon-wrap{
  width:52px;height:36px;border-radius:13px;
  background:transparent;border:1px solid transparent;
  display:flex;align-items:center;justify-content:center;
  transition:all 0.22s;
}
.bnav-icon{font-size:1.55em;transition:transform 0.25s,filter 0.25s;}
.bnav-label{font-size:0.56em;font-weight:700;letter-spacing:0.2px;transition:color 0.22s;}

.main-container{animation:pageEnter 0.38s cubic-bezier(0.4,0,0.2,1) both;}
@keyframes pageEnter{
  from{opacity:0;transform:translateY(16px);}
  to{opacity:1;transform:translateY(0);}
}

.ripple-wave{
  position:absolute;border-radius:50%;
  background:rgba(255,255,255,0.15);
  transform:scale(0);animation:ripple-go 0.55s ease-out forwards;
  pointer-events:none;
}
@keyframes ripple-go{to{transform:scale(4);opacity:0;}}

.toast-shelf{
  position:fixed;bottom:80px;left:50%;transform:translateX(-50%);
  z-index:10000;display:flex;flex-direction:column;align-items:center;gap:8px;
  pointer-events:none;
}
.toast-pill{
  background:rgba(30,30,30,0.97);
  border:1px solid rgba(255,255,255,0.09);
  color:var(--on-surface);
  padding:10px 20px;border-radius:50px;
  font-size:0.82em;font-weight:700;
  box-shadow:0 8px 32px rgba(0,0,0,0.55),0 0 0 1px rgba(255,255,255,0.03) inset;
  animation:toastPop 0.32s cubic-bezier(0.34,1.56,0.64,1) both;
  display:flex;align-items:center;gap:8px;
  white-space:nowrap;pointer-events:all;
  font-family:'Cairo',sans-serif;
}
.toast-pill.t-success{border-color:rgba(52,211,153,0.35);color:#34d399;}
.toast-pill.t-error{border-color:rgba(248,113,113,0.35);color:#f87171;}
.toast-pill.t-warn{border-color:rgba(251,191,36,0.35);color:#fbbf24;}
.toast-pill.t-info{border-color:rgba(0,227,253,0.35);color:var(--cyan);}
@keyframes toastPop{
  from{opacity:0;transform:translateY(10px) scale(0.88);}
  to{opacity:1;transform:translateY(0) scale(1);}
}
@keyframes toastFade{
  to{opacity:0;transform:translateY(-6px) scale(0.92);}
}

@keyframes shimmer{
  0%{background-position:200% center;}
  100%{background-position:-200% center;}
}
.skeleton{
  background:linear-gradient(90deg,var(--surface) 25%,var(--surface-high) 50%,var(--surface) 75%);
  background-size:200% 100%;
  animation:shimmer 1.6s ease-in-out infinite;
  border-radius:6px;
}
</style>
</head>
<body oncontextmenu="return false;" onselectstart="return false;" ondragstart="return false;">
<div class="background-3d">
<div class="grid-3d"></div>
<div class="blob1"></div>
<div class="blob2"></div>
</div>

<div class="loading-3d" id="loading">
<div class="spinner-3d"></div>
<p>معالجة الطلب...</p>
</div>

<div class="modal-overlay" id="modalOverlay" style="display:none;">
<div class="modal-3d" id="modalCard">
    <div class="modal-drag-handle"></div>
    <div class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></div>
    <div class="modal-icon-wrap" id="modalIconWrap"><i id="modalIconFA" class="fas fa-check"></i></div>
    <h2 class="modal-title" id="modalTitle"></h2>
    <div class="modal-message" id="modalMessage"></div>
    <div class="modal-actions">
        <button class="modal-button" onclick="closeModal()">حسناً</button>
    </div>
    <div style="height:8px;"></div>
</div>
</div>

<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo"><i class="fas fa-dragon"></i></div>
        <div class="sidebar-header-text">
            <h3>Dragon Follow</h3>
            <p>منصة الخدمات الاحترافية</p>
        </div>
    </div>
    
    <div class="sidebar-menu-container">
        <ul class="sidebar-menu">
            <div class="menu-section-label">التنقل الرئيسي</div>
            <li>
                <button onclick="navigateToPage('main')" class="menu-button <?php echo $page == 'main' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-house"></i></span>
                    <span class="menu-label">الرئيسية</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('services')" class="menu-button <?php echo $page == 'services' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-bolt"></i></span>
                    <span class="menu-label">طلب خدمة</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('store')" class="menu-button <?php echo $page == 'store' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-store"></i></span>
                    <span class="menu-label">قسم المتجر</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('invite')" class="menu-button <?php echo $page == 'invite' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-user-plus"></i></span>
                    <span class="menu-label">رابط الدعوة</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>

            <div class="menu-section-label">الخدمات</div>
            <li>
                <button onclick="navigateToPage('charge')" class="menu-button <?php echo $page == 'charge' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-coins"></i></span>
                    <span class="menu-label">شحن النقاط</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('daily_gift')" class="menu-button <?php echo $page == 'daily_gift' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-gift"></i></span>
                    <span class="menu-label">الهدية اليومية</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>
            <li>
                <button onclick="navigateToPage('redeem_gift')" class="menu-button <?php echo $page == 'redeem_gift' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-ticket-alt"></i></span>
                    <span class="menu-label">استبدال كود</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>

            <div class="menu-section-label">أخرى</div>
            <li>
                <button onclick="navigateToPage('terms')" class="menu-button <?php echo $page == 'terms' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-file-lines"></i></span>
                    <span class="menu-label">شروط البوت</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>
            <li>
                <button onclick="window.open('<?php echo $channel_link; ?>', '_blank')" class="menu-button">
                    <span class="icon-chip"><i class="fab fa-telegram"></i></span>
                    <span class="menu-label">قناة البوت</span>
                    <i class="fas fa-arrow-up-right-from-square menu-arrow"></i>
                </button>
            </li>
            <?php if ($api_enabled_flag): ?>
            <li>
                <button onclick="navigateToPage('developer')" class="menu-button <?php echo $page == 'developer' ? 'active' : ''; ?>">
                    <span class="icon-chip"><i class="fas fa-code"></i></span>
                    <span class="menu-label">API للمطوّرين</span>
                    <i class="fas fa-chevron-left menu-arrow"></i>
                </button>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
</div>

<!-- ═══ BOTTOM NAVIGATION ═══ -->
<nav class="bottom-nav">
    <button class="bnav-item <?php echo $page == 'main' ? 'active' : ''; ?>" onclick="navigateToPage('main')">
        <span class="bnav-icon-wrap"><i class="fas fa-house bnav-icon"></i></span>
        <span class="bnav-label">الرئيسية</span>
    </button>
    <button class="bnav-item <?php echo $page == 'services' ? 'active' : ''; ?>" onclick="navigateToPage('services')">
        <span class="bnav-icon-wrap"><i class="fas fa-bolt bnav-icon"></i></span>
        <span class="bnav-label">خدمات</span>
    </button>
    <button class="bnav-item <?php echo $page == 'store' ? 'active' : ''; ?>" onclick="navigateToPage('store')">
        <span class="bnav-icon-wrap"><i class="fas fa-store bnav-icon"></i></span>
        <span class="bnav-label">المتجر</span>
    </button>
    <button class="bnav-item <?php echo $page == 'invite' ? 'active' : ''; ?>" onclick="navigateToPage('invite')">
        <span class="bnav-icon-wrap"><i class="fas fa-user-plus bnav-icon"></i></span>
        <span class="bnav-label">دعوة</span>
    </button>
    <button class="bnav-item <?php echo in_array($page, ['daily_gift','charge','terms','redeem_gift','developer']) ? 'active' : ''; ?>" onclick="toggleSidebar()">
        <span class="bnav-icon-wrap"><i class="fas fa-bars-staggered bnav-icon"></i></span>
        <span class="bnav-label">المزيد</span>
    </button>
</nav>

<!-- ═══ CONFETTI CANVAS ═══ -->
<canvas id="confettiCanvas"></canvas>

<!-- ═══ TOAST SHELF ═══ -->
<div class="toast-shelf" id="toastShelf"></div>

<div class="main-container">

    <?php if ($page !== 'main'): ?>
    <!-- Mini Header for sub-pages -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:10px;">
            <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="" style="width:36px;height:36px;border-radius:var(--r-md);object-fit:cover;border:1px solid var(--border);">
            <div>
                <div style="font-size:0.8em;font-weight:800;color:var(--on-surface);"><?php echo htmlspecialchars($first_name); ?></div>
                <div style="font-size:0.65em;color:var(--cyan);letter-spacing:1px;text-transform:uppercase;"><?php echo number_format($coin,4); ?> <?php echo $currency; ?></div>
            </div>
        </div>
        <button onclick="navigateToPage('main')" style="display:flex;align-items:center;gap:6px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r-sm);padding:8px 14px;color:var(--on-surface-v);font-family:'Cairo',sans-serif;font-size:0.7em;letter-spacing:1px;text-transform:uppercase;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--border-accent)';this.style.color='var(--cyan)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--on-surface-v)';">
            <i class="fas fa-home" style="font-size:0.9em;"></i> الرئيسية
        </button>
    </div>
    <?php endif; ?>

    <?php if ($page == 'main'): ?>

    <!-- ═══ HERO CARD ═══ -->
    <div class="hero-card">

        <!-- صف المستخدم -->
        <div class="hero-user-row">
            <div class="hero-avatar-wrap">
                <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Avatar" class="hero-avatar">
                <div class="hero-online-dot"></div>
            </div>
            <div class="hero-user-info">
                <div class="hero-name"><?php echo htmlspecialchars($first_name); ?></div>
                <div class="hero-username-tag">
                    <i class="fas fa-at"></i><?php echo htmlspecialchars($username ?: 'لا يوجد'); ?>
                </div>
            </div>
            <div class="hero-plat-chip">
                <i class="fas fa-dragon"></i>
                <span>Dragon</span>
            </div>
        </div>

        <!-- شريط الرصيد -->
        <div class="hero-balance-strip">
            <div class="hero-balance-left">
                <div class="hero-balance-label">
                    <i class="fas fa-wallet"></i> رصيدك الحالي
                </div>
                <div class="hero-balance-main">
                    <div class="hero-balance-value"><?php echo number_format($coin, 4); ?></div>
                    <div class="hero-balance-curr"><?php echo $currency; ?></div>
                </div>
            </div>
            <div class="hero-balance-right">
                <i class="fas fa-coins"></i>
                <span>رصيد</span>
            </div>
        </div>

        <!-- شريط الإحصاء -->
        <div class="hero-stats-strip">
            <div class="hero-stat-cell">
                <div class="hero-stat-icon"><i class="fas fa-cart-shopping"></i></div>
                <div class="hero-stat-val"><?php echo number_format($user_orders_count); ?></div>
                <div class="hero-stat-lbl">الطلبات</div>
            </div>
            <div class="hero-stat-cell">
                <div class="hero-stat-icon" style="color:var(--cyan);"><i class="fas fa-user-plus"></i></div>
                <div class="hero-stat-val" style="color:var(--cyan);"><?php echo number_format($share ?? 0); ?></div>
                <div class="hero-stat-lbl">الدعوات</div>
            </div>
            <div class="hero-stat-cell">
                <div class="hero-stat-icon" style="color:#34d399;"><i class="fas fa-circle-check"></i></div>
                <div class="hero-stat-val" style="color:#34d399;font-size:0.82em;">ACTIVE</div>
                <div class="hero-stat-lbl">الحالة</div>
            </div>
        </div>
    </div>

    <!-- ═══ CTA — طلب خدمة جديدة ═══ -->
    <button onclick="navigateToPage('services')" class="cta-order-btn">
        <div class="cta-icon-block"><i class="fas fa-shopping-cart"></i></div>
        <div class="cta-text-block">
            <span class="cta-title">طلب خدمة جديدة</span>
            <span class="cta-sub">متابعين · تفاعل · مشاهدات</span>
        </div>
        <div class="cta-arrow"><i class="fas fa-chevron-left"></i></div>
    </button>

    <!-- ═══ خدمات سريعة ═══ -->
    <div class="section-header">
        <div class="section-header-line"></div>
        <div class="section-header-text">الخدمات</div>
        <div class="section-header-line"></div>
    </div>

    <div class="services-grid">
        <!-- قسم المتجر -->
        <button onclick="navigateToPage('store')" class="svc-card"
            style="--svc-color:#a78bfa;--svc-bg:rgba(167,139,250,0.08);--svc-border:rgba(167,139,250,0.15);">
            <div class="svc-icon"><i class="fas fa-store"></i></div>
            <div>
                <div class="svc-name">قسم المتجر</div>
                <div class="svc-desc">منتجات حصرية</div>
            </div>
        </button>
        <!-- رابط الدعوة -->
        <button onclick="navigateToPage('invite')" class="svc-card"
            style="--svc-color:#34d399;--svc-bg:rgba(52,211,153,0.08);--svc-border:rgba(52,211,153,0.15);">
            <div class="svc-icon"><i class="fas fa-link"></i></div>
            <div>
                <div class="svc-name">رابط الدعوة</div>
                <div class="svc-desc">اكسب نقاط</div>
            </div>
        </button>
        <!-- الهدية اليومية -->
        <button onclick="navigateToPage('daily_gift')" class="svc-card"
            style="--svc-color:#fbbf24;--svc-bg:rgba(251,191,36,0.08);--svc-border:rgba(251,191,36,0.15);">
            <div class="svc-icon"><i class="fas fa-gift"></i></div>
            <div>
                <div class="svc-name">الهدية اليومية</div>
                <div class="svc-desc">مجانية كل يوم</div>
            </div>
        </button>
        <!-- شحن النقاط -->
        <button onclick="navigateToPage('charge')" class="svc-card"
            style="--svc-color:#00E3FD;--svc-bg:rgba(0,227,253,0.08);--svc-border:rgba(0,227,253,0.15);">
            <div class="svc-icon"><i class="fas fa-coins"></i></div>
            <div>
                <div class="svc-name">شحن النقاط</div>
                <div class="svc-desc">أضف رصيداً</div>
            </div>
        </button>
    </div>

    <!-- ═══ استبدال كود ═══ -->
    <button onclick="navigateToPage('redeem_gift')" class="wide-card"
        style="--svc-color:#fb923c;--svc-bg:rgba(251,146,60,0.08);--svc-border:rgba(251,146,60,0.15);">
        <div class="wide-card-icon"><i class="fas fa-ticket-alt"></i></div>
        <div class="wide-card-text">
            <div class="wide-card-title">استبدال كود هدية</div>
            <div class="wide-card-sub">اشحن رصيدك فوراً بالكود</div>
        </div>
        <i class="fas fa-chevron-left wide-card-arrow"></i>
    </button>

    <!-- ═══ شروط البوت ═══ -->
    <button onclick="navigateToPage('terms')" class="wide-card"
        style="--svc-color:#64748b;--svc-bg:rgba(100,116,139,0.08);--svc-border:rgba(100,116,139,0.15);">
        <div class="wide-card-icon"><i class="fas fa-file-alt"></i></div>
        <div class="wide-card-text">
            <div class="wide-card-title">شروط وتعليمات البوت</div>
            <div class="wide-card-sub">اطّلع على الشروط</div>
        </div>
        <i class="fas fa-chevron-left wide-card-arrow"></i>
    </button>

    <?php elseif ($page == 'services'): ?>
    <!-- ══════════ طلب خدمة ══════════ -->
    <div class="page-card" style="overflow:visible;">

        <!-- رأس البطاقة -->
        <div class="page-card-header" style="--ph-color:#00E3FD;">
            <div class="page-card-icon"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="page-card-title">طلب خدمة جديدة</div>
                <div class="page-card-sub">اتبع الخطوات بالترتيب</div>
            </div>
        </div>

        <form id="servicesForm" onsubmit="submitAjaxForm(event,'servicesForm','?id=<?php echo $chat_id;?>&key=<?php echo $key;?>&page=services','submit_order')" method="POST">

            <!-- ══════ 1. المنصات ══════ -->
            <div class="platforms-section">
                <div class="form-sec-hdr">
                    <div class="form-sec-icon"><i class="fas fa-globe"></i></div>
                    <div class="form-sec-label">المنصة</div>
                </div>
                <div class="platforms-grid" id="platformsGrid">
                    <div class="platform-card active" id="plat-all" style="--plat-clr:var(--cyan)" onclick="selectPlatform('all')">
                        <div class="platform-card-icon"><i class="fas fa-border-all"></i></div>
                        <div class="platform-card-name">الكل</div>
                    </div>
                    <div class="platform-card" id="plat-facebook" style="--plat-clr:#1877F2" onclick="selectPlatform('facebook')">
                        <div class="platform-card-icon"><i class="fab fa-facebook-f"></i></div>
                        <div class="platform-card-name">فيسبوك</div>
                    </div>
                    <div class="platform-card" id="plat-youtube" style="--plat-clr:#FF0000" onclick="selectPlatform('youtube')">
                        <div class="platform-card-icon"><i class="fab fa-youtube"></i></div>
                        <div class="platform-card-name">يوتيوب</div>
                    </div>
                    <div class="platform-card" id="plat-tiktok" style="--plat-clr:#EE1D52" onclick="selectPlatform('tiktok')">
                        <div class="platform-card-icon"><i class="fab fa-tiktok"></i></div>
                        <div class="platform-card-name">تيك توك</div>
                    </div>
                    <div class="platform-card" id="plat-instagram" style="--plat-clr:#E1306C" onclick="selectPlatform('instagram')">
                        <div class="platform-card-icon"><i class="fab fa-instagram"></i></div>
                        <div class="platform-card-name">انستقرام</div>
                    </div>
                    <div class="platform-card" id="plat-telegram" style="--plat-clr:#29A8E0" onclick="selectPlatform('telegram')">
                        <div class="platform-card-icon"><i class="fab fa-telegram"></i></div>
                        <div class="platform-card-name">تيليجرام</div>
                    </div>
                    <div class="platform-card" id="plat-snapchat" style="--plat-clr:#F7C700" onclick="selectPlatform('snapchat')">
                        <div class="platform-card-icon"><i class="fab fa-snapchat"></i></div>
                        <div class="platform-card-name">سناب</div>
                    </div>
                    <div class="platform-card" id="plat-twitter" style="--plat-clr:#e2e2e2" onclick="selectPlatform('twitter')">
                        <div class="platform-card-icon"><i class="fab fa-x-twitter"></i></div>
                        <div class="platform-card-name">تويتر X</div>
                    </div>
                    <div class="platform-card" id="plat-threads" style="--plat-clr:#cccccc" onclick="selectPlatform('threads')">
                        <div class="platform-card-icon"><i class="fab fa-threads"></i></div>
                        <div class="platform-card-name">ثريدز</div>
                    </div>
                    <div class="platform-card" id="plat-whatsapp" style="--plat-clr:#25D366" onclick="selectPlatform('whatsapp')">
                        <div class="platform-card-icon"><i class="fab fa-whatsapp"></i></div>
                        <div class="platform-card-name">واتساب</div>
                    </div>
                </div>
            </div>

            <!-- ══════ 2. القسم ══════ -->
            <div class="form-sec">
                <div class="form-sec-hdr">
                    <div class="form-sec-icon"><i class="fas fa-folder"></i></div>
                    <div class="form-sec-label">القسم</div>
                </div>
                <div class="custom-dropdown" id="categoryDropdown">
                    <div class="dropdown-selected" onclick="toggleDropdown('category')">
                        <span class="dropdown-selected-text"><span id="selectedCategory" class="dropdown-selected-placeholder">اضغط لاختيار القسم</span></span>
                        <span class="dropdown-chevron"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <div class="dropdown-items" id="categoryItems">
                        <?php foreach ($sections as $uid => $section): ?>
                        <div class="dropdown-item"
                             data-uid="<?php echo $uid;?>"
                             data-name="<?php echo htmlspecialchars($section['name']);?>"
                             data-platform="<?php echo getSectionPlatform($section);?>"
                             onclick="selectCategory('<?php echo $uid;?>','<?php echo htmlspecialchars($section['name'],ENT_QUOTES);?>')">
                            <?php echo htmlspecialchars($section['name']);?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ══════ 3. الخدمة ══════ -->
            <div class="form-sec step-block" id="stepService">
                <div class="form-sec-hdr">
                    <div class="form-sec-icon"><i class="fas fa-tag"></i></div>
                    <div class="form-sec-label">الخدمة</div>
                </div>
                <div class="custom-dropdown" id="serviceDropdown">
                    <div class="dropdown-selected" onclick="toggleDropdown('service')">
                        <span class="dropdown-selected-text"><span id="selectedService" class="dropdown-selected-placeholder">اختر القسم أولاً</span></span>
                        <span class="dropdown-chevron"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <div class="dropdown-items" id="serviceItems"></div>
                </div>
            </div>

            <input type="hidden" name="section_uid" id="sectionUid">
            <input type="hidden" name="service_uid" id="serviceUid">

            <!-- شريط معلومات الخدمة -->
            <div class="info-strip" id="serviceInfo" style="display:none;">
                <div class="info-strip-cell">
                    <div class="info-strip-val" id="serviceMin" style="font-family:'Cairo',sans-serif;">—</div>
                    <div class="info-strip-lbl">أقل كمية</div>
                </div>
                <div class="info-strip-cell">
                    <div class="info-strip-val" id="serviceMax" style="font-family:'Cairo',sans-serif;">—</div>
                    <div class="info-strip-lbl">أقصى كمية</div>
                </div>
                <div class="info-strip-cell">
                    <div class="info-strip-val" id="servicePrice" style="font-family:'Cairo',sans-serif;color:var(--cyan);">—</div>
                    <div class="info-strip-lbl">سعر / ألف</div>
                </div>
            </div>

            <!-- ══════ 4. الرابط ══════ -->
            <div class="form-sec">
                <div class="form-sec-hdr">
                    <div class="form-sec-icon"><i class="fas fa-link"></i></div>
                    <div class="form-sec-label" id="linkStepLabel">رابط الحساب أو المنشور</div>
                </div>
                <input type="text" name="link" id="linkInput" class="form-control" placeholder="https://...">
            </div>

            <!-- ══════ 5. الكمية ══════ -->
            <div class="form-sec">
                <div class="form-sec-hdr">
                    <div class="form-sec-icon"><i class="fas fa-hashtag"></i></div>
                    <div class="form-sec-label">الكمية المطلوبة</div>
                </div>
                <input type="number" name="quantity" id="quantityInput" class="form-control" min="1" value="1000" step="any" required onkeyup="calculateTotalPrice()" onchange="calculateTotalPrice()">
            </div>

            <!-- ملخص التكلفة -->
            <div class="order-summary" id="totalPriceDisplay" style="display:none;">
                <div class="order-summary-cell" id="costCell">
                    <div class="order-summary-icon"><i class="fas fa-receipt"></i></div>
                    <div class="order-summary-val" id="totalPriceValue" style="font-family:'Cairo',sans-serif;">0</div>
                    <div class="order-summary-curr"><?php echo $currency;?></div>
                    <div class="order-summary-lbl">التكلفة</div>
                </div>
                <div class="order-summary-sep"></div>
                <div class="order-summary-cell" id="balanceCell">
                    <div class="order-summary-icon"><i class="fas fa-wallet"></i></div>
                    <div class="order-summary-val" id="balanceAfterValue" style="font-family:'Cairo',sans-serif;">0</div>
                    <div class="order-summary-curr"><?php echo $currency;?></div>
                    <div class="order-summary-lbl">رصيدك بعد الطلب</div>
                </div>
            </div>

            <!-- ══════ خصم اختياري ══════ -->
            <?php if (!empty($api_settings['coupons'] ?? [])): ?>
            <div class="form-sec" id="couponSection" style="padding:0 18px 4px;">
                <button type="button" class="coupon-toggle-btn" id="couponToggleBtn" onclick="toggleCouponBox()">
                    <i class="fas fa-ticket-alt"></i> خصم اختياري
                    <i class="fas fa-chevron-down" id="couponChevron" style="margin-right:auto;font-size:11px;transition:transform 0.3s;"></i>
                </button>
                <div id="couponBox" style="display:none;margin-top:10px;">
                    <!-- حقل الإدخال -->
                    <div id="couponInputRow" style="display:flex;gap:8px;align-items:center;padding:14px;background:rgba(0,227,253,0.04);border:1px solid rgba(0,227,253,0.15);border-radius:10px;">
                        <input type="text" id="couponInput" class="form-control" placeholder="أدخل كود الخصم..." style="flex:1;text-transform:uppercase;margin:0;" oninput="resetCouponField()">
                        <button type="button" class="btn-cyan" onclick="applyCoupon()">
                            <i class="fas fa-check"></i> تطبيق
                        </button>
                    </div>
                    <!-- رسالة الخطأ -->
                    <div id="couponMsg" style="margin-top:8px;font-size:13px;display:none;padding:8px 12px;border-radius:8px;"></div>
                    <!-- بادج الكوبون المطبّق -->
                    <div id="couponAppliedBadge" style="display:none;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;background:rgba(52,211,153,0.08);border:1px solid rgba(52,211,153,0.25);border-radius:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:8px;background:rgba(52,211,153,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-ticket-alt" style="color:#34d399;font-size:14px;"></i>
                            </div>
                            <div>
                                <div id="couponBadgeCode" style="font-weight:800;color:#34d399;font-family:'Cairo',sans-serif;letter-spacing:1px;font-size:14px;"></div>
                                <div id="couponBadgeDiscount" style="font-size:11px;color:rgba(52,211,153,0.7);margin-top:2px;"></div>
                            </div>
                        </div>
                        <button type="button" onclick="removeCoupon()" style="width:32px;height:32px;border-radius:8px;background:rgba(248,113,113,0.12);border:1px solid rgba(248,113,113,0.25);color:#f87171;font-size:16px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:all 0.2s;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="coupon_code" id="couponCodeHidden" value="">
                <input type="hidden" name="coupon_discount" id="couponDiscountHidden" value="0">
            </div>
            <?php endif; ?>

            <!-- زر الإرسال -->
            <div style="padding:16px 18px 20px;">
                <button type="submit" name="submit_order" class="submit-btn" style="margin:0;width:100%;">
                    <i class="fas fa-paper-plane"></i> إرسال الطلب
                </button>
            </div>

        </form>
    </div>

    <?php elseif ($page == 'store'): ?>
    <!-- ══════════ قسم المتجر ══════════ -->
    <div class="page-card">
        <div class="page-card-header" style="--ph-color:#a78bfa;">
            <div class="page-card-icon" style="background:rgba(167,139,250,0.1);border-color:rgba(167,139,250,0.2);color:#a78bfa;"><i class="fas fa-store"></i></div>
            <div>
                <div class="page-card-title">قسم المتجر</div>
                <div class="page-card-sub">استبدل نقاطك بمنتجات حصرية</div>
            </div>
        </div>

        <!-- رصيد المستخدم -->
        <div class="store-balance-strip">
            <div>
                <div class="store-balance-label">رصيدك الحالي</div>
                <div class="store-balance-val"><?php echo number_format($coin, 4); ?> <span style="font-size:0.62em;font-weight:600;color:var(--on-surface-m);"><?php echo htmlspecialchars($currency); ?></span></div>
            </div>
            <div class="store-balance-icon"><i class="fas fa-coins" style="color:#a78bfa;"></i></div>
        </div>

        <?php if (empty($store_sections)): ?>
        <div class="notice-box notice-warning" style="margin:0 20px 20px;">
            <i class="fas fa-info-circle"></i>
            لا توجد منتجات متاحة حالياً، تابعنا للمزيد قريباً
        </div>
        <?php else: ?>

        <!-- تبويبات الأقسام -->
        <div class="store-tabs-bar" id="storeTabsBar">
            <?php $first_sec = true; foreach ($store_sections as $sec_uid => $sec_data): ?>
            <button type="button" class="store-tab-btn<?php echo $first_sec ? ' active' : ''; ?>"
                    id="storeTabBtn_<?php echo htmlspecialchars($sec_uid); ?>"
                    onclick="switchStoreSection('<?php echo htmlspecialchars($sec_uid); ?>')">
                <?php echo htmlspecialchars($sec_data['name']); ?>
            </button>
            <?php $first_sec = false; endforeach; ?>
        </div>

        <!-- محتوى كل قسم -->
        <?php $first_sec = true; foreach ($store_sections as $sec_uid => $sec_data): ?>
        <div class="store-section-panel<?php echo $first_sec ? ' active' : ''; ?>" id="storePanel_<?php echo htmlspecialchars($sec_uid); ?>">
            <?php $sec_items = $sec_data['items'] ?? []; ?>
            <?php if (empty($sec_items)): ?>
            <div class="notice-box" style="margin:12px 20px 20px;">
                <i class="fas fa-box-open"></i> لا توجد منتجات في هذا القسم حالياً
            </div>
            <?php else: ?>
            <div class="store-items-list">
                <?php foreach ($sec_items as $item_uid => $item): ?>
                <?php $item_price = floatval($item['price'] ?? 0); ?>
                <div class="store-item-card" id="storeCard_<?php echo htmlspecialchars($item_uid); ?>">
                    <div class="store-item-top">
                        <div class="store-item-meta">
                            <div class="store-item-name"><?php echo htmlspecialchars($item['name'] ?? 'منتج'); ?></div>
                            <?php if (!empty($item['description'])): ?>
                            <div class="store-item-desc"><?php echo htmlspecialchars($item['description']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="store-item-side">
                            <div class="store-item-price-box">
                                <div class="store-item-price-val"><?php echo number_format($item_price, 4); ?></div>
                                <div class="store-item-price-lbl"><?php echo htmlspecialchars($currency); ?></div>
                            </div>
                            <button type="button" class="store-buy-toggle"
                                    id="storeBuyToggle_<?php echo htmlspecialchars($item_uid); ?>"
                                    onclick="toggleStoreBuyPanel('<?php echo htmlspecialchars($item_uid); ?>')">
                                <i class="fas fa-shopping-cart"></i> شراء
                            </button>
                        </div>
                    </div>

                    <div class="store-buy-panel" id="storeBuyPanel_<?php echo htmlspecialchars($item_uid); ?>">
                        <form id="storeItemForm_<?php echo htmlspecialchars($item_uid); ?>"
                              onsubmit="submitAjaxForm(event,'storeItemForm_<?php echo htmlspecialchars($item_uid); ?>','?id=<?php echo $chat_id;?>&key=<?php echo $key;?>&page=store','confirm_store_buy')"
                              method="POST">
                            <input type="hidden" name="store_section_uid" value="<?php echo htmlspecialchars($sec_uid); ?>">
                            <input type="hidden" name="store_item_uid" value="<?php echo htmlspecialchars($item_uid); ?>">
                            <div class="store-delivery-label">حساب الاستلام</div>
                            <input type="text" name="delivery_info" class="form-control"
                                   placeholder="@username أو رقم الهاتف" required>
                            <div class="store-delivery-hint">سيتم إرسال هذا الحساب للمشرف لتسليم المنتج</div>
                            <button type="submit" class="store-confirm-btn">
                                <i class="fas fa-check-circle"></i> تأكيد الشراء
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php $first_sec = false; endforeach; ?>

        <?php endif; ?>
    </div>

    <?php elseif ($page == 'invite'): ?>
    <!-- ══════════ رابط الدعوة ══════════ -->
    <div class="page-card">
        <div class="page-card-header" style="--ph-color:#34d399;">
            <div class="page-card-icon" style="background:rgba(52,211,153,0.1);border-color:rgba(52,211,153,0.2);color:#34d399;"><i class="fas fa-link"></i></div>
            <div>
                <div class="page-card-title">رابط الدعوة</div>
                <div class="page-card-sub">شارك واكسب نقاط مجانية</div>
            </div>
        </div>

        <?php if ($invite_link_status != 'on'): ?>
        <div class="notice-box notice-warning">
            <i class="fas fa-exclamation-triangle"></i>
            ميزة الدعوة معطلة حالياً من قبل الإدارة
        </div>
        <?php else: ?>

        <div class="info-strip" style="margin-bottom:20px;">
            <div class="info-strip-cell">
                <div class="info-strip-val" style="font-family:'Cairo',sans-serif;color:var(--cyan);"><?php echo number_format($share);?></div>
                <div class="info-strip-lbl">عدد الدعوات</div>
            </div>
            <div class="info-strip-cell">
                <div class="info-strip-val" style="font-family:'Cairo',sans-serif;color:#34d399;"><?php echo number_format($invite_reward);?></div>
                <div class="info-strip-lbl">نقاط / دعوة</div>
            </div>
        </div>

        <div class="field-block">
            <div class="field-label">رابطك الخاص</div>
            <div class="invite-link-box" id="inviteLink">https:

        </div>

        <button class="submit-btn" onclick="copyInviteLink()" style="background:#34d399;color:#022c22;">
            <i class="fas fa-copy"></i> نسخ الرابط
        </button>

        <div class="notice-box" style="margin-top:16px;">
            <i class="fas fa-info-circle" style="color:var(--cyan);"></i>
            كل شخص يدخل البوت عبر رابطك ستحصل على <strong style="color:var(--cyan);"><?php echo $invite_reward;?> <?php echo $currency;?></strong>
        </div>
        <?php endif; ?>
    </div>

    <?php elseif ($page == 'terms'): ?>
    <!-- ══════════ الشروط ══════════ -->
    <div class="page-card">
        <div class="page-card-header" style="--ph-color:#64748b;">
            <div class="page-card-icon" style="background:rgba(100,116,139,0.1);border-color:rgba(100,116,139,0.2);color:#94a3b8;"><i class="fas fa-file-alt"></i></div>
            <div>
                <div class="page-card-title">شروط وتعليمات البوت</div>
                <div class="page-card-sub">يرجى الاطلاع والالتزام</div>
            </div>
        </div>
        <div class="content-text"><?php echo nl2br(htmlspecialchars($terms_text));?></div>
    </div>

    <?php elseif ($page == 'charge'): ?>
    <!-- ══════════ شحن النقاط ══════════ -->

    <!-- تعليمات الشحن -->
    <div class="charge-steps-title"><i class="fas fa-receipt" style="color:var(--cyan);"></i> طريقة الشحن</div>
    <div class="charge-instructions">
        <div class="charge-inst-header">
            <i class="fas fa-info-circle"></i> اقرأ التعليمات بعناية قبل الإرسال
        </div>
        <div class="charge-inst-body"><?php echo nl2br(htmlspecialchars($charge_cliche)); ?></div>
    </div>

    <!-- زر الدعم -->
    <button onclick="window.open('https://t.me/Dragon_Supor','_blank')" class="charge-support-btn">
        <i class="fas fa-headset"></i> تواصل مع الدعم الفني
    </button>

    <?php elseif ($page == 'daily_gift'): ?>
    <!-- ══════════ الهدية اليومية ══════════ -->
    <?php
    $now = time();
    $last_claim = $_user_last_gift;
    $seconds_remaining = 86400 - ($now - $last_claim);
    $can_claim = ($seconds_remaining <= 0);
    $hours = max(0, floor($seconds_remaining / 3600));
    $minutes = max(0, floor(($seconds_remaining % 3600) / 60));
    ?>
    <div class="page-card" style="border-color:rgba(251,191,36,0.18);overflow:hidden;position:relative;">

        <!-- خلفية توهج ذهبي -->
        <div style="position:absolute;top:-80px;right:-80px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(251,191,36,0.07) 0%,transparent 70%);pointer-events:none;"></div>

        <div class="page-card-header" style="--ph-color:#fbbf24;">
            <div class="page-card-icon" style="background:rgba(251,191,36,0.1);border-color:rgba(251,191,36,0.22);color:#fbbf24;width:48px;height:48px;border-radius:14px;font-size:1.25em;">
                <i class="fas fa-gift"></i>
            </div>
            <div>
                <div class="page-card-title">الهدية اليومية</div>
                <div class="page-card-sub">مجانية لك كل 24 ساعة</div>
            </div>
        </div>

        <!-- عرض الهدية -->
        <div class="gift-showcase">
            <div class="gift-icon-wrap">
                <i class="fas fa-gift"></i>
                <div class="gift-glow-ring"></div>
            </div>
            <div class="gift-amount-row">
                <span class="gift-amount-val"><?php
                $gv = (float)$daily_gift_amount;
                echo ($gv == floor($gv)) ? number_format($gv) : rtrim(rtrim(number_format($gv,8),'0'),'.');
                ?></span>
                <span class="gift-amount-curr"><?php echo $currency;?></span>
            </div>
            <div class="gift-amount-desc">تُضاف لرصيدك فوراً عند الاستلام</div>
            <div class="gift-perks">
                <span class="gift-perk"><i class="fas fa-check-circle"></i> مجانية 100%</span>
                <span class="gift-perk"><i class="fas fa-rotate"></i> كل 24 ساعة</span>
                <span class="gift-perk"><i class="fas fa-bolt"></i> فوري</span>
            </div>
        </div>

        <?php if (!$can_claim): ?>
        <!-- مؤقت العد التنازلي -->
        <div class="timer-block">
            <div class="timer-label"><i class="fas fa-clock"></i> الوقت المتبقي للهدية التالية</div>
            <div class="timer-segs">
                <div class="timer-seg">
                    <span class="timer-seg-val" id="giftHH"><?php echo str_pad($hours,2,'0',STR_PAD_LEFT);?></span>
                    <span class="timer-seg-lbl">ساعة</span>
                </div>
                <div class="timer-colon">:</div>
                <div class="timer-seg">
                    <span class="timer-seg-val" id="giftMM"><?php echo str_pad($minutes,2,'0',STR_PAD_LEFT);?></span>
                    <span class="timer-seg-lbl">دقيقة</span>
                </div>
                <div class="timer-colon">:</div>
                <div class="timer-seg">
                    <span class="timer-seg-val" id="giftSS">00</span>
                    <span class="timer-seg-lbl">ثانية</span>
                </div>
            </div>
        </div>
        <div style="padding:0 20px 20px;">
            <button class="submit-btn" disabled style="background:rgba(251,191,36,0.12);color:rgba(251,191,36,0.4);box-shadow:none;cursor:not-allowed;border:1px solid rgba(251,191,36,0.15);">
                <i class="fas fa-lock"></i> مغلقة حتى الموعد
            </button>
        </div>
        <?php else: ?>
        <div style="padding:0 20px 20px;">
            <button onclick="claimDailyGift()" class="submit-btn" id="claimGiftBtn"
                style="background:linear-gradient(135deg,#fbbf24 0%,#f59e0b 100%);color:#1a0a00;box-shadow:0 6px 24px rgba(251,191,36,0.38);animation:gift-btn-pulse 2s ease-in-out infinite;">
                <i class="fas fa-gift"></i> احصل على هديتك الآن
            </button>
        </div>
        <?php endif; ?>
    </div>

    <style>
    @keyframes gift-btn-pulse{
        0%,100%{box-shadow:0 6px 24px rgba(251,191,36,0.38);}
        50%{box-shadow:0 8px 36px rgba(251,191,36,0.65),0 0 0 6px rgba(251,191,36,0.08);}
    }
    </style>
    <script>
    (function(){
        var hEl=document.getElementById('giftHH'),mEl=document.getElementById('giftMM'),sEl=document.getElementById('giftSS');
        if(!hEl)return;
        var total=<?php echo max(0,$seconds_remaining);?>;
        function tick(){
            if(total<=0){location.reload();return;}
            total--;
            var h=Math.floor(total/3600),m=Math.floor((total%3600)/60),s=total%60;
            hEl.textContent=String(h).padStart(2,'0');
            mEl.textContent=String(m).padStart(2,'0');
            sEl.textContent=String(s).padStart(2,'0');
        }
        setInterval(tick,1000);
    })();
    </script>

    <?php elseif ($page == 'redeem_gift'): ?>
    <!-- ══════════ استبدال كود ══════════ -->
    <div class="page-card">
        <div class="page-card-header" style="--ph-color:#fb923c;">
            <div class="page-card-icon" style="background:rgba(251,146,60,0.1);border-color:rgba(251,146,60,0.2);color:#fb923c;"><i class="fas fa-ticket-alt"></i></div>
            <div>
                <div class="page-card-title">استبدال كود هدية</div>
                <div class="page-card-sub">اشحن رصيدك فوراً بالكود</div>
            </div>
        </div>

        <div class="notice-box" style="margin-bottom:20px;">
            <i class="fas fa-info-circle" style="color:#fb923c;"></i>
            أدخل كود الهدية الذي حصلت عليه لشحن رصيدك فوراً
        </div>

        <form id="redeemForm" onsubmit="submitAjaxForm(event,'redeemForm','?id=<?php echo $chat_id;?>&key=<?php echo $key;?>&page=redeem_gift','redeem_gift_code')" method="POST">
            <div class="field-block">
                <div class="field-label">كود الهدية</div>
                <input type="text" name="gift_code" class="form-control code-input" placeholder="XXXX-XXXX-XXXX" required>
            </div>
            <button type="submit" name="redeem_gift_code" class="submit-btn" style="background:#fb923c;color:#1a0500;margin-top:24px;">
                <i class="fas fa-check-circle"></i> استبدل الكود
            </button>
        </form>
    </div>
    <?php elseif ($page == 'developer' && $api_enabled_flag): ?>
    <!-- ══════════ API Developer Page ══════════ -->
    <?php
    $api_base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . preg_replace('/\/service\.php.*$/', '/api.php', $_SERVER['REQUEST_URI']);
    $api_base = preg_replace('/\?.*$/', '', $api_base);
    

    if (isset($_GET['regen_key']) && !empty($chat_id)) {
        

        foreach ($Namero_data['api_keys'] ?? [] as $k => $uid) {
            if ((string)$uid === (string)$chat_id) { unset($Namero_data['api_keys'][$k]); break; }
        }
        $user_api_key = hash('sha256', $chat_id . time() . rand(1000,9999));
        $Namero_data['api_keys'][$user_api_key] = (string)$chat_id;
        db_save_namero($Namero_data);
    }
    ?>
    <div class="page-card" style="overflow:visible;">
        <div class="page-card-header" style="--ph-color:#00E3FD;">
            <div class="page-card-icon" style="background:rgba(0,227,253,0.1);border-color:rgba(0,227,253,0.2);color:#00E3FD;"><i class="fas fa-code"></i></div>
            <div>
                <div class="page-card-title">API للمطوّرين</div>
                <div class="page-card-sub">استخدم خدماتنا في موقعك أو بوتك</div>
            </div>
        </div>

        <!-- مفتاح API -->
        <div style="background:rgba(0,227,253,0.04);border:1px solid rgba(0,227,253,0.18);border-radius:12px;padding:16px;margin-bottom:18px;">
            <div style="font-size:11px;color:var(--cyan);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:10px;font-weight:700;"><i class="fas fa-key"></i> مفتاح API الخاص بك</div>
            <div style="display:flex;gap:8px;align-items:center;">
                <input type="text" id="userApiKey" readonly value="<?php echo htmlspecialchars($user_api_key ?? ''); ?>"
                    style="flex:1;background:var(--surface-high);border:1px solid var(--border);border-radius:8px;padding:10px 12px;color:var(--cyan);font-family:monospace;font-size:12px;direction:ltr;letter-spacing:0.5px;outline:none;-webkit-user-select:text;user-select:text;">
                <button onclick="copyApiKey()" style="background:rgba(0,227,253,0.1);border:1px solid rgba(0,227,253,0.25);color:var(--cyan);padding:10px 14px;border-radius:8px;cursor:pointer;white-space:nowrap;font-size:12px;" title="نسخ">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <div style="display:flex;gap:8px;margin-top:10px;">
                <div style="font-size:11px;color:var(--on-surface-v);flex:1;line-height:1.6;">⚠️ لا تشارك هذا المفتاح مع أحد — يمنح وصولاً كاملاً لحسابك.</div>
                <a href="?id=<?php echo $chat_id;?>&key=<?php echo $key;?>&page=developer&regen_key=1"
                   onclick="return confirm('تجديد المفتاح سيُلغي المفتاح القديم نهائياً. متأكد؟')"
                   style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:#f87171;padding:8px 12px;border-radius:8px;font-size:11px;text-decoration:none;white-space:nowrap;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-sync-alt"></i> تجديد
                </a>
            </div>
        </div>

        <!-- Base URL -->
        <div style="margin-bottom:18px;">
            <div style="font-size:11px;color:var(--on-surface-v);letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;font-weight:600;"><i class="fas fa-globe"></i> رابط الـ API</div>
            <div style="background:var(--surface-high);border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:monospace;font-size:12px;color:var(--on-surface);direction:ltr;word-break:break-all;"><?php echo htmlspecialchars($api_base); ?></div>
        </div>

        <!-- Endpoints -->
        <div style="font-size:11px;color:var(--on-surface-v);letter-spacing:1px;text-transform:uppercase;margin-bottom:12px;font-weight:600;"><i class="fas fa-list"></i> الإجراءات المتاحة</div>

        <!-- balance -->
        <div class="api-endpoint-card">
            <div class="api-ep-header" onclick="toggleEp('ep1')">
                <span class="api-method">GET</span>
                <span class="api-ep-name">balance — رصيد الحساب</span>
                <i class="fas fa-chevron-down api-chevron" id="chev_ep1"></i>
            </div>
            <div class="api-ep-body" id="ep1" style="display:none;">
                <div class="api-code-wrap">
                    <div class="api-code-block"><?php echo htmlspecialchars($api_base); ?>?key=<span class="api-hl">YOUR_KEY</span>&amp;action=<span class="api-hl">balance</span></div>
                    <button class="api-copy-btn" onclick="copyCode(this)" data-text="<?php echo htmlspecialchars($api_base); ?>?key=YOUR_KEY&action=balance" title="نسخ"><i class="fas fa-copy"></i></button>
                </div>
                <div class="api-resp-label">نموذج الاستجابة:</div>
                <div class="api-code-block">{"balance": "1250", "currency": "<?php echo htmlspecialchars($currency); ?>"}</div>
            </div>
        </div>

        <!-- services -->
        <div class="api-endpoint-card">
            <div class="api-ep-header" onclick="toggleEp('ep2')">
                <span class="api-method">GET</span>
                <span class="api-ep-name">services — قائمة الخدمات</span>
                <i class="fas fa-chevron-down api-chevron" id="chev_ep2"></i>
            </div>
            <div class="api-ep-body" id="ep2" style="display:none;">
                <div class="api-code-wrap">
                    <div class="api-code-block"><?php echo htmlspecialchars($api_base); ?>?key=<span class="api-hl">YOUR_KEY</span>&amp;action=<span class="api-hl">services</span></div>
                    <button class="api-copy-btn" onclick="copyCode(this)" data-text="<?php echo htmlspecialchars($api_base); ?>?key=YOUR_KEY&action=services" title="نسخ"><i class="fas fa-copy"></i></button>
                </div>
                <div class="api-resp-label">نموذج الاستجابة:</div>
                <div class="api-code-block">[{"service":1,"name":"اسم الخدمة","rate":5,"min":100,"max":10000,"category":"القسم"}]</div>
            </div>
        </div>

        <!-- add -->
        <div class="api-endpoint-card">
            <div class="api-ep-header" onclick="toggleEp('ep3')">
                <span class="api-method api-method-post">POST</span>
                <span class="api-ep-name">add — إضافة طلب</span>
                <i class="fas fa-chevron-down api-chevron" id="chev_ep3"></i>
            </div>
            <div class="api-ep-body" id="ep3" style="display:none;">
                <div class="api-code-wrap">
                    <div class="api-code-block"><?php echo htmlspecialchars($api_base); ?>?key=<span class="api-hl">YOUR_KEY</span>&amp;action=<span class="api-hl">add</span>&amp;service=<span class="api-hl">1</span>&amp;link=<span class="api-hl">https:

                    <button class="api-copy-btn" onclick="copyCode(this)" data-text="<?php echo htmlspecialchars($api_base); ?>?key=YOUR_KEY&action=add&service=1&link=https://instagram.com/user&quantity=100" title="نسخ"><i class="fas fa-copy"></i></button>
                </div>
                <div style="font-size:11px;color:var(--on-surface-v);margin:10px 0 6px;">الحقول المطلوبة:</div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <div class="api-param"><span class="api-param-name">service</span><span class="api-param-desc">رقم الخدمة من قائمة services</span></div>
                    <div class="api-param"><span class="api-param-name">link</span><span class="api-param-desc">رابط الحساب أو المحتوى</span></div>
                    <div class="api-param"><span class="api-param-name">quantity</span><span class="api-param-desc">الكمية المطلوبة</span></div>
                </div>
                <div class="api-resp-label">نموذج الاستجابة:</div>
                <div class="api-code-block">{"order": 12345678}</div>
            </div>
        </div>

        <!-- status -->
        <div class="api-endpoint-card">
            <div class="api-ep-header" onclick="toggleEp('ep4')">
                <span class="api-method">GET</span>
                <span class="api-ep-name">status — حالة الطلب</span>
                <i class="fas fa-chevron-down api-chevron" id="chev_ep4"></i>
            </div>
            <div class="api-ep-body" id="ep4" style="display:none;">
                <div class="api-code-wrap">
                    <div class="api-code-block"><?php echo htmlspecialchars($api_base); ?>?key=<span class="api-hl">YOUR_KEY</span>&amp;action=<span class="api-hl">status</span>&amp;order=<span class="api-hl">12345678</span></div>
                    <button class="api-copy-btn" onclick="copyCode(this)" data-text="<?php echo htmlspecialchars($api_base); ?>?key=YOUR_KEY&action=status&order=12345678" title="نسخ"><i class="fas fa-copy"></i></button>
                </div>
                <div class="api-resp-label">نموذج الاستجابة:</div>
                <div class="api-code-block">{"charge":"50","status":"Pending","remains":"0","currency":"<?php echo htmlspecialchars($currency); ?>"}</div>
                <div style="font-size:11px;color:var(--on-surface-v);margin-top:8px;">حالات ممكنة: <span style="color:var(--success);">Completed</span> · <span style="color:var(--warning);">Pending</span> · <span style="color:var(--danger);">Canceled</span></div>
            </div>
        </div>

        <!-- orders -->
        <div class="api-endpoint-card">
            <div class="api-ep-header" onclick="toggleEp('ep5')">
                <span class="api-method">GET</span>
                <span class="api-ep-name">orders — حالات متعددة</span>
                <i class="fas fa-chevron-down api-chevron" id="chev_ep5"></i>
            </div>
            <div class="api-ep-body" id="ep5" style="display:none;">
                <div class="api-code-wrap">
                    <div class="api-code-block"><?php echo htmlspecialchars($api_base); ?>?key=<span class="api-hl">YOUR_KEY</span>&amp;action=<span class="api-hl">orders</span>&amp;orders=<span class="api-hl">1,2,3</span></div>
                    <button class="api-copy-btn" onclick="copyCode(this)" data-text="<?php echo htmlspecialchars($api_base); ?>?key=YOUR_KEY&action=orders&orders=1,2,3" title="نسخ"><i class="fas fa-copy"></i></button>
                </div>
                <div class="api-resp-label">نموذج الاستجابة:</div>
                <div class="api-code-block">{"1":{"status":"Completed"},"2":{"status":"Pending"}}</div>
            </div>
        </div>
    </div>

    <style>
    .api-endpoint-card{background:var(--surface-high);border:1px solid var(--border);border-radius:10px;margin-bottom:10px;overflow:hidden;}
    .api-ep-header{display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer;transition:background 0.2s;}
    .api-ep-header:hover{background:rgba(255,255,255,0.03);}
    .api-method{background:rgba(0,227,253,0.12);color:var(--cyan);border:1px solid rgba(0,227,253,0.25);padding:2px 8px;border-radius:4px;font-size:10px;font-family:monospace;font-weight:700;letter-spacing:1px;flex-shrink:0;}
    .api-method-post{background:rgba(251,191,36,0.1);color:#fbbf24;border-color:rgba(251,191,36,0.3);}
    .api-ep-name{flex:1;font-size:13px;font-weight:600;color:var(--on-surface);}
    .api-chevron{color:var(--on-surface-v);font-size:11px;transition:transform 0.25s;flex-shrink:0;}
    .api-ep-body{padding:0 14px 14px;border-top:1px solid var(--border);}
    .api-code-wrap{position:relative;margin-top:10px;}
    .api-code-wrap .api-code-block{margin-top:0;padding-left:36px;}
    .api-copy-btn{position:absolute;top:6px;left:6px;background:rgba(0,227,253,0.08);border:1px solid rgba(0,227,253,0.2);color:var(--cyan);width:26px;height:26px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:11px;transition:background 0.2s,transform 0.1s;padding:0;}
    .api-copy-btn:hover{background:rgba(0,227,253,0.18);}
    .api-copy-btn.copied{background:rgba(34,197,94,0.15);border-color:rgba(34,197,94,0.35);color:#22c55e;}
    .api-code-block{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px 12px;font-family:monospace;font-size:11px;color:var(--on-surface-v);direction:ltr;word-break:break-all;margin-top:10px;line-height:1.7;}
    .api-hl{color:var(--cyan);}
    .api-resp-label{font-size:10px;color:var(--on-surface-v);margin-top:10px;letter-spacing:1px;text-transform:uppercase;}
    .api-param{display:flex;gap:8px;align-items:baseline;padding:4px 0;border-bottom:1px solid var(--border);}
    .api-param:last-child{border:none;}
    .api-param-name{font-family:monospace;font-size:11px;color:var(--cyan);min-width:80px;flex-shrink:0;}
    .api-param-desc{font-size:11px;color:var(--on-surface-v);}
    </style>
    <script>
    function toggleEp(id){
        var b=document.getElementById(id);
        var c=document.getElementById('chev_'+id);
        var open=b.style.display!=='none';
        b.style.display=open?'none':'block';
        if(c) c.style.transform=open?'':'rotate(180deg)';
    }
    function copyApiKey(){
        var inp=document.getElementById('userApiKey');
        inp.select(); inp.setSelectionRange(0,999);
        if(navigator.clipboard){
            navigator.clipboard.writeText(inp.value).then(function(){ showToast('تم نسخ المفتاح ✓'); });
        } else { document.execCommand('copy'); showToast('تم نسخ المفتاح ✓'); }
    }
    function copyCode(btn){
        var text=btn.getAttribute('data-text');
        var icon=btn.querySelector('i');
        var copy=function(){
            btn.classList.add('copied');
            if(icon){icon.className='fas fa-check';}
            showToast('تم النسخ ✓');
            setTimeout(function(){btn.classList.remove('copied');if(icon)icon.className='fas fa-copy';},1800);
        };
        if(navigator.clipboard){
            navigator.clipboard.writeText(text).then(copy).catch(function(){
                var t=document.createElement('textarea');
                t.value=text; document.body.appendChild(t);
                t.select(); document.execCommand('copy'); document.body.removeChild(t); copy();
            });
        } else {
            var t=document.createElement('textarea');
            t.value=text; document.body.appendChild(t);
            t.select(); document.execCommand('copy'); document.body.removeChild(t); copy();
        }
    }
    function showToast(msg, type){
        var shelf = document.getElementById('toastShelf');
        if (!shelf) return;
        type = type || 'info';
        var icons = {success:'✓', error:'✗', warn:'⚠', info:'ℹ'};
        var pill = document.createElement('div');
        pill.className = 'toast-pill t-' + type;
        pill.innerHTML = '<span style="font-size:1.1em;">' + (icons[type]||'ℹ') + '</span><span>' + msg + '</span>';
        shelf.appendChild(pill);
        setTimeout(function(){
            pill.style.animation = 'toastFade 0.3s forwards';
            setTimeout(function(){ pill.remove(); }, 310);
        }, 2600);
    }
    </script>

    <?php endif; ?>

</div>

<?php

$safe_sectionsData = [];
if (isset($sections) && is_array($sections)) {
    foreach ($sections as $section_uid => $section_data) {
        $safe_services = [];
        if (isset($section_data['services']) && is_array($section_data['services'])) {
            foreach ($section_data['services'] as $service_uid => $service_info) {
                

                $safe_services[$service_uid] = [
                    'name'  => $service_info['name'] ?? 'بدون اسم',
                    'min'   => $service_info['min'] ?? 0,
                    'max'   => $service_info['max'] ?? 0,
                    'price' => floatval($service_info['price'] ?? 0)
                ];
            }
        }
        $safe_sectionsData[$section_uid] = [
            'name'     => $section_data['name'] ?? 'قسم غير معروف',
            'services' => $safe_services
        ];
    }
}

$safe_storeSectionsData = [];
if (isset($store_sections) && is_array($store_sections)) {
    foreach ($store_sections as $section_uid => $section_data) {
        $safe_items = [];
        if (isset($section_data['items']) && is_array($section_data['items'])) {
            foreach ($section_data['items'] as $item_uid => $item_info) {
                $safe_items[$item_uid] = [
                    'name' => $item_info['name'] ?? 'بدون اسم',
                    'price' => floatval($item_info['price'] ?? 0),
                    'description' => $item_info['description'] ?? ''
                ];
            }
        }
        $safe_storeSectionsData[$section_uid] = [
            'name' => $section_data['name'] ?? 'قسم غير معروف',
            'items' => $safe_items
        ];
    }
}
?>

<script>

const sectionsData = <?php echo json_encode($safe_sectionsData, JSON_UNESCAPED_UNICODE); ?>;

const storeSectionsData = <?php echo json_encode($safe_storeSectionsData, JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

document.addEventListener('touchstart', function(e) {
    const target = e.target;
    if (target.tagName === 'BUTTON' || target.closest('button') || target.classList.contains('stat-card-3d')) {
        return true;
    }
    
    let timer = setTimeout(() => {
        e.preventDefault();
    }, 300);
    
    const touchEndHandler = () => clearTimeout(timer);
    const touchMoveHandler = () => clearTimeout(timer);
    
    target.addEventListener('touchend', touchEndHandler, { once: true });
    target.addEventListener('touchmove', touchMoveHandler, { once: true });
}, { passive: false });

function navigateToPage(page) {
    showLoading();
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page);
    window.location.href = '?' + urlParams.toString();
}

function claimDailyGift() {
    sessionStorage.setItem('triggerConfetti', '1');
    showLoading();
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('claim', '1');
    window.location.href = '?' + urlParams.toString();
}

function showModal(ok, title, message) {
    var card = document.getElementById('modalCard');
    var iconEl = document.getElementById('modalIconFA');
    var state = 'error';
    var faIcon = 'fa-times-circle';

    if (ok === true || ok === 'ok' || ok === 'success') {
        state = 'ok';
        faIcon = 'fa-check-circle';
    } else if (ok === 'warn' || ok === 'warning') {
        state = 'warn';
        faIcon = 'fa-exclamation-circle';
    }

    card.className = 'modal-3d state-' + state;
    iconEl.className = 'fas ' + faIcon;

    var cleanTitle = (title || '').replace(/[❌✅⚠💔🔒🎁💰📦📝🔑🎫⭐🔢💬🔗]/g, '').trim();
    document.getElementById('modalTitle').textContent = cleanTitle;
    document.getElementById('modalMessage').innerHTML = (message || '').replace(/\n/g, '<br>');
    document.getElementById('modalOverlay').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
}

function submitAjaxForm(event, formId, action, actionKey) {
    event.preventDefault();
    showLoading();
    var form = document.getElementById(formId);
    var data = new FormData(form);
    if (actionKey) data.append(actionKey, '1');
    fetch(action, { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            document.getElementById('loading').classList.remove('active');
            showModal(res.ok ? 'ok' : 'error', res.title || '', res.message || '');
        })
        .catch(function() {
            document.getElementById('loading').classList.remove('active');
            showModal('error', 'خطأ في الاتصال', 'تعذّر الاتصال بالخادم. حاول مجدداً.');
        });
}

document.addEventListener('click', function(e) {
    const modalOverlay = document.getElementById('modalOverlay');
    if (e.target === modalOverlay) {
        closeModal();
    }
});

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

function showLoading() {
    document.getElementById('loading').classList.add('active');
}

window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('loading').classList.remove('active');
    }, 500);
    <?php if ($show_modal && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
    setTimeout(function() {
        showModal(
            <?php echo json_encode($modal_type === 'success' ? 'ok' : ($modal_type === 'warning' ? 'warn' : 'error')); ?>,
            <?php echo json_encode($modal_title ?: ''); ?>,
            <?php echo json_encode($modal_message ?: ''); ?>
        );
    }, 600);
    <?php endif; ?>
});

function copyInviteLink() {
    var linkText = document.getElementById('inviteLink').innerText;
    navigator.clipboard.writeText(linkText).then(function() {
        showModal('ok', 'تم النسخ', 'تم نسخ رابط الدعوة بنجاح!');
    }, function() {
        showModal('error', 'خطأ', 'فشل نسخ الرابط، حاول مجدداً.');
    });
}

function toggleDropdown(type) {
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

function setDropdownLabel(spanId, text, isPlaceholder) {
    var el = document.getElementById(spanId);
    if (!el) return;
    el.textContent = text;
    el.className = isPlaceholder ? 'dropdown-selected-placeholder' : '';
}

var currentPlatform = 'all';

function selectPlatform(platformId) {
    currentPlatform = platformId;

    

    document.querySelectorAll('.platform-card').forEach(function(card) {
        card.classList.remove('active');
    });
    var activeCard = document.getElementById('plat-' + platformId);
    if (activeCard) activeCard.classList.add('active');

    

    filterCategoryItems(platformId);

    

    setDropdownLabel('selectedCategory', 'اضغط لاختيار القسم', true);
    document.getElementById('sectionUid').value = '';
    closeAllDropdowns();
    resetServiceStep();
}

function filterCategoryItems(platformId) {
    var items = document.querySelectorAll('#categoryItems .dropdown-item');
    var anyVisible = false;
    items.forEach(function(item) {
        var itemPlatform = item.getAttribute('data-platform') || 'all';
        var show = (platformId === 'all') || (itemPlatform === platformId) || (itemPlatform === 'all');
        item.style.display = show ? '' : 'none';
        if (show) anyVisible = true;
    });

    

    var noSectionsMsg = document.getElementById('noSectionsMsg');
    if (!noSectionsMsg) {
        noSectionsMsg = document.createElement('div');
        noSectionsMsg.id = 'noSectionsMsg';
        noSectionsMsg.style.cssText = 'padding:14px;text-align:center;color:var(--on-surface-m);font-size:0.82em;display:none;';
        noSectionsMsg.textContent = 'لا توجد أقسام لهذه المنصة';
        document.getElementById('categoryItems').appendChild(noSectionsMsg);
    }
    noSectionsMsg.style.display = anyVisible ? 'none' : '';
}

function resetServiceStep() {
    var stepSvc = document.getElementById('stepService');
    if (stepSvc) stepSvc.classList.remove('open');
    document.getElementById('serviceItems').innerHTML = '';
    setDropdownLabel('selectedService', 'اختر القسم أولاً', true);
    document.getElementById('serviceUid').value = '';
    var si = document.getElementById('serviceInfo');
    if (si) si.style.display = 'none';
    var td = document.getElementById('totalPriceDisplay');
    if (td) td.style.display = 'none';
}

function selectCategory(uid, name) {
    setDropdownLabel('selectedCategory', name, false);
    document.getElementById('sectionUid').value = uid;
    closeAllDropdowns();
    resetServiceStep();
    loadServicesCustom(uid);
    

    var stepSvc = document.getElementById('stepService');
    if (stepSvc) {
        stepSvc.classList.add('open');
        setTimeout(function() {
            stepSvc.scrollIntoView({ behavior:'smooth', block:'nearest' });
        }, 100);
    }
}

function loadServicesCustom(sectionUid) {
    var serviceItems = document.getElementById('serviceItems');
    serviceItems.innerHTML = '';
    if (sectionUid && sectionsData[sectionUid] && sectionsData[sectionUid].services) {
        var services = sectionsData[sectionUid].services;
        for (var uid in services) {
            var svc = services[uid];
            var div = document.createElement('div');
            div.className = 'dropdown-item';
            div.textContent = svc.name;
            (function(u, s) {
                div.onclick = function() {
                    selectService(u, s.name, s.min||0, s.max||0, s.price||0);
                };
            })(uid, svc);
            serviceItems.appendChild(div);
        }
    }
}

function selectService(uid, name, min, max, price) {
    setDropdownLabel('selectedService', name, false);
    document.getElementById('serviceUid').value = uid;
    document.getElementById('serviceMin').textContent = min;
    document.getElementById('serviceMax').textContent = max;
    document.getElementById('servicePrice').textContent = price.toFixed(4);
    var si = document.getElementById('serviceInfo');
    if (si) si.style.display = 'flex';
    closeAllDropdowns();
    calculateTotalPrice();
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-dropdown')) {
        closeAllDropdowns();
    }
});

var currentUserBalance = <?php echo floatval($coin); ?>;

function calculateTotalPrice() {
    var serviceUid = document.getElementById('serviceUid').value;
    var quantityInput = document.getElementById('quantityInput');
    var totalPriceDiv = document.getElementById('totalPriceDisplay');
    var totalPriceSpan = document.getElementById('totalPriceValue');
    var balanceAfterSpan = document.getElementById('balanceAfterValue');

    if (!serviceUid || !quantityInput) return;

    var pricePerThousand = 0;
    for (var sectionUid in sectionsData) {
        if (sectionsData[sectionUid].services && sectionsData[sectionUid].services[serviceUid]) {
            pricePerThousand = sectionsData[sectionUid].services[serviceUid].price || 0;
            break;
        }
    }

    var quantity = parseFloat(quantityInput.value) || 0;

    if (quantity > 0 && pricePerThousand > 0) {
        var totalPrice = (quantity / 1000) * pricePerThousand;
        

        var discountPct = parseFloat(document.getElementById('couponDiscountHidden') ? document.getElementById('couponDiscountHidden').value : 0) || 0;
        if (discountPct > 0) { totalPrice = totalPrice * (1 - discountPct / 100); }
        var balanceAfter = currentUserBalance - totalPrice;
        var insufficient = balanceAfter < 0;

        

        totalPriceSpan.textContent = totalPrice.toFixed(4);
        totalPriceSpan.style.color = 'var(--cyan)';

        

        balanceAfterSpan.textContent = balanceAfter.toFixed(4);
        var balanceColor = insufficient ? '#f87171' : '#34d399';
        balanceAfterSpan.style.color = balanceColor;

        

        var balanceCell = document.getElementById('balanceCell');
        if (balanceCell) {
            balanceCell.querySelector('.order-summary-icon').style.color = balanceColor;
            balanceCell.style.background = insufficient
                ? 'rgba(248,113,113,0.04)'
                : 'rgba(52,211,153,0.04)';
        }
        

        var costCell = document.getElementById('costCell');
        if (costCell) {
            costCell.querySelector('.order-summary-icon').style.color = 'var(--cyan)';
        }

        totalPriceDiv.style.display = 'flex';
    } else {
        totalPriceDiv.style.display = 'none';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
    }
});

var couponApplied = false;

function toggleCouponBox() {
    var box = document.getElementById('couponBox');
    var chevron = document.getElementById('couponChevron');
    if (!box) return;
    var open = box.style.display !== 'none';
    box.style.display = open ? 'none' : 'block';
    if (chevron) chevron.style.transform = open ? '' : 'rotate(180deg)';
}

function applyCoupon() {
    var code = document.getElementById('couponInput').value.trim().toUpperCase();
    if (!code) { showCouponMsg('يرجى إدخال كود الخصم.', false); return; }

    var formData = new FormData();
    formData.append('apply_coupon', '1');
    formData.append('coupon_code', code);

    fetch(window.location.href, { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                couponApplied = true;
                document.getElementById('couponCodeHidden').value = code;
                document.getElementById('couponDiscountHidden').value = data.discount;

                

                document.getElementById('couponInputRow').style.display = 'none';
                document.getElementById('couponMsg').style.display = 'none';

                var badge = document.getElementById('couponAppliedBadge');
                badge.style.display = 'flex';
                document.getElementById('couponBadgeCode').textContent = code;
                document.getElementById('couponBadgeDiscount').textContent = 'خصم ' + data.discount + '% مطبّق على طلبك';

                

                var toggleBtn = document.getElementById('couponToggleBtn');
                if (toggleBtn) {
                    toggleBtn.classList.add('applied');
                    toggleBtn.innerHTML = '<i class="fas fa-check-circle"></i> كوبون مطبّق — ' + data.discount + '%'
                        + '<i class="fas fa-chevron-down" id="couponChevron" style="margin-right:auto;font-size:11px;"></i>';
                }
                calculateTotalPrice();
            } else {
                couponApplied = false;
                document.getElementById('couponCodeHidden').value = '';
                document.getElementById('couponDiscountHidden').value = '0';
                showCouponMsg(data.message, false);
                calculateTotalPrice();
            }
        })
        .catch(function() { showCouponMsg('❌ حدث خطأ في الاتصال.', false); });
}

function removeCoupon() {
    couponApplied = false;
    document.getElementById('couponCodeHidden').value = '';
    document.getElementById('couponDiscountHidden').value = '0';
    document.getElementById('couponInput').value = '';

    

    document.getElementById('couponInputRow').style.display = 'flex';
    document.getElementById('couponAppliedBadge').style.display = 'none';
    document.getElementById('couponMsg').style.display = 'none';

    

    var toggleBtn = document.getElementById('couponToggleBtn');
    if (toggleBtn) {
        toggleBtn.classList.remove('applied');
        toggleBtn.innerHTML = '<i class="fas fa-ticket-alt"></i> خصم اختياري'
            + '<i class="fas fa-chevron-down" id="couponChevron" style="margin-right:auto;font-size:11px;transition:transform 0.3s;"></i>';
    }
    calculateTotalPrice();
}

function resetCouponField() {
    

    document.getElementById('couponMsg').style.display = 'none';
}

function showCouponMsg(msg, ok) {
    var div = document.getElementById('couponMsg');
    if (!div) return;
    div.textContent = msg;
    div.style.display = 'block';
    div.style.background = ok ? 'rgba(52,211,153,0.1)' : 'rgba(248,113,113,0.1)';
    div.style.color = ok ? '#34d399' : '#f87171';
    div.style.border = ok ? '1px solid rgba(52,211,153,0.3)' : '1px solid rgba(248,113,113,0.3)';
}

function switchStoreSection(uid) {
    document.querySelectorAll('.store-section-panel').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.store-tab-btn').forEach(function(el) { el.classList.remove('active'); });
    var panel = document.getElementById('storePanel_' + uid);
    if (panel) panel.classList.add('active');
    var tab = document.getElementById('storeTabBtn_' + uid);
    if (tab) tab.classList.add('active');
}

function toggleStoreBuyPanel(itemUid) {
    var panel = document.getElementById('storeBuyPanel_' + itemUid);
    var btn = document.getElementById('storeBuyToggle_' + itemUid);
    var card = document.getElementById('storeCard_' + itemUid);
    if (!panel) return;
    var isOpen = panel.style.display === 'block';
    panel.style.display = isOpen ? 'none' : 'block';
    if (isOpen) {
        btn.classList.remove('cancel');
        btn.innerHTML = '<i class="fas fa-shopping-cart"></i> شراء';
        card.classList.remove('selected');
    } else {
        btn.classList.add('cancel');
        btn.innerHTML = '<i class="fas fa-times"></i> إلغاء';
        card.classList.add('selected');
    }
}

document.querySelectorAll('.stat-card-3d, .product-card-3d, .section-card-3d, .buy-btn, .submit-btn, .claim-btn, .back-link, .menu-button').forEach(el => {
    el.addEventListener('contextmenu', (e) => e.preventDefault());
    el.addEventListener('touchstart', (e) => {
        let timer = setTimeout(() => {
            e.preventDefault();
        }, 300);
        
        el.addEventListener('touchend', () => clearTimeout(timer), { once: true });
        el.addEventListener('touchmove', () => clearTimeout(timer), { once: true });
    }, { passive: false });
});

function addRipple(e) {
    var el = this;
    var rect = el.getBoundingClientRect();
    var size = Math.max(rect.width, rect.height) * 2;
    var clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : rect.left + rect.width/2);
    var clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : rect.top + rect.height/2);
    var x = clientX - rect.left - size/2;
    var y = clientY - rect.top - size/2;
    var ripple = document.createElement('span');
    ripple.className = 'ripple-wave';
    ripple.style.cssText = 'width:'+size+'px;height:'+size+'px;left:'+x+'px;top:'+y+'px;';
    if(getComputedStyle(el).position === 'static') el.style.position = 'relative';
    el.appendChild(ripple);
    setTimeout(function(){ ripple.remove(); }, 600);
}
function bindRipples() {
    document.querySelectorAll('.submit-btn, .claim-btn, .cta-order-btn, .bnav-item, .store-confirm-btn, .btn-cyan, .copy-btn, .coupon-toggle-btn').forEach(function(el){
        el.removeEventListener('click', addRipple);
        el.addEventListener('click', addRipple);
    });
}
bindRipples();

function animateCount(el, target, duration) {
    if (!el || isNaN(target)) return;
    var decimals = (target.toString().split('.')[1] || '').length || 0;
    var startTime = null;
    function step(ts) {
        if (!startTime) startTime = ts;
        var progress = Math.min((ts - startTime) / duration, 1);
        var ease = 1 - Math.pow(1 - progress, 3);
        var current = target * ease;
        el.textContent = current.toFixed(decimals);
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = target.toFixed(decimals);
    }
    requestAnimationFrame(step);
}

document.addEventListener('DOMContentLoaded', function() {
    

    var balEl = document.querySelector('.hero-balance-value');
    if (balEl) {
        var raw = parseFloat(balEl.textContent.replace(/,/g,'')) || 0;
        balEl.textContent = '0.0000';
        setTimeout(function(){ animateCount(balEl, raw, 1100); }, 250);
    }
    

    document.querySelectorAll('.hero-stat-val').forEach(function(el) {
        var t = parseFloat(el.textContent.replace(/,/g,''));
        if (!isNaN(t) && t > 0) {
            el.textContent = '0';
            setTimeout(function(){ animateCount(el, t, 900); }, 350);
        }
    });
    

    if (sessionStorage.getItem('triggerConfetti')) {
        sessionStorage.removeItem('triggerConfetti');
        setTimeout(launchConfetti, 400);
    }
    

    bindRipples();
});

function launchConfetti() {
    var canvas = document.getElementById('confettiCanvas');
    if (!canvas) return;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    var ctx = canvas.getContext('2d');
    var colors = ['#00E3FD','#a78bfa','#fbbf24','#34d399','#f87171','#60a5fa','#ffffff'];
    var pieces = [];
    for (var i = 0; i < 140; i++) {
        pieces.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height * 0.4 - canvas.height * 0.2,
            w: Math.random() * 9 + 4,
            h: Math.random() * 5 + 2,
            color: colors[Math.floor(Math.random() * colors.length)],
            rot: Math.random() * Math.PI * 2,
            rotS: (Math.random() - 0.5) * 0.18,
            vx: (Math.random() - 0.5) * 3.5,
            vy: Math.random() * 3.5 + 1.5,
            opacity: 1
        });
    }
    var frame = 0, maxF = 200;
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces.forEach(function(p) {
            p.x += p.vx; p.y += p.vy; p.rot += p.rotS;
            if (frame > maxF * 0.55) p.opacity = Math.max(0, p.opacity - 0.018);
            ctx.save();
            ctx.globalAlpha = p.opacity;
            ctx.translate(p.x + p.w/2, p.y + p.h/2);
            ctx.rotate(p.rot);
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
            ctx.restore();
        });
        frame++;
        if (frame < maxF) requestAnimationFrame(draw);
        else ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    draw();
    showToast('🎉 تم استلام هديتك!', 'success');
}

document.addEventListener('click', function(event) {
    var sidebar = document.getElementById('sidebar');
    var bnav = document.querySelector('.bottom-nav');
    if (!sidebar || !bnav) return;
    if (!sidebar.contains(event.target) && sidebar.classList.contains('active')) {
        

        var moreBtn = bnav.querySelector('button:last-child');
        if (!moreBtn || !moreBtn.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>
</body>
</html>