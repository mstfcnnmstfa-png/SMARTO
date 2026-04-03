<?php
session_start();
ob_start();
require_once __DIR__ . '/admins.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

if (!empty($_SESSION['bot_token'])) {
    header("Location: admin_panel.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = trim($_POST['password'] ?? '');
    if ($pass === '999888') 
 {
        $_SESSION['admin_id']     = '7816487928';
        $_SESSION['bot_token']    = "8076347498:AAEq520a0raqgxYØkQW7_fiYM23khnxSKNU";
        $_SESSION['bot_username'] = 'rc3BOT';
        $_SESSION['bot_name']     = 'Dragon Follow';
        header("Location: admin_panel.php");
        exit();
    } else {
        $error = 'كلمة السر غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>دراجون فولو — دخول الأدمن</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --bg:#0b0b0b;--surface:#141414;--surface-high:#1e1e1e;
  --border:rgba(70,69,68,0.4);--border-accent:rgba(0,227,253,0.28);
  --cyan:#00E3FD;--cyan-glow:rgba(0,227,253,0.18);
  --on-surface:#f0f0f0;--on-surface-v:#a8a5a5;--on-surface-m:#666;
  --danger:#ff6e84;--r-md:12px;--r-lg:18px;--r-xl:28px;
}
html,body{
  min-height:100vh;background:var(--bg);color:var(--on-surface);
  font-family:'Cairo',sans-serif;
  display:flex;align-items:center;justify-content:center;padding:24px 16px;
}
.bg-wrap{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;}
.bg-grid{
  position:absolute;inset:0;
  background-image:linear-gradient(rgba(0,227,253,0.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,227,253,0.03) 1px,transparent 1px);
  background-size:40px 40px;
  mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 30%,transparent 100%);
}
.blob{position:absolute;border-radius:50%;filter:blur(100px);pointer-events:none;}
.blob-a{top:-15%;right:-10%;width:55%;height:55%;background:rgba(0,227,253,0.05);}
.blob-b{bottom:-10%;left:-5%;width:40%;height:40%;background:rgba(0,200,240,0.04);}

.wrap{position:relative;z-index:1;width:100%;max-width:380px;display:flex;flex-direction:column;gap:0;}

.brand{text-align:center;margin-bottom:28px;}
.brand-ring{
  width:68px;height:68px;margin:0 auto 16px;border-radius:50%;
  border:1px solid var(--border-accent);
  background:radial-gradient(circle at 40% 40%,rgba(0,227,253,0.14),rgba(0,227,253,0.04));
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 0 1px rgba(0,227,253,0.08),0 0 40px rgba(0,227,253,0.1);
}
.brand-ring i{font-size:26px;color:var(--cyan);}
.brand-name{font-size:2em;font-weight:800;color:var(--cyan);text-shadow:0 0 30px rgba(0,227,253,0.3);line-height:1;}
.brand-sub{margin-top:6px;font-size:0.72em;color:var(--on-surface-m);text-transform:uppercase;letter-spacing:2.5px;}

.card{
  background:rgba(20,20,20,0.88);backdrop-filter:blur(32px);
  border:1px solid var(--border);border-radius:var(--r-xl);
  padding:32px 28px;
  box-shadow:0 0 0 1px rgba(0,227,253,0.05),0 32px 80px rgba(0,0,0,0.6);
  position:relative;overflow:hidden;
}
.card::before{
  content:'';position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,227,253,0.06) 0%,transparent 70%);pointer-events:none;
}
.card-title{font-size:1.05em;font-weight:800;margin-bottom:4px;}
.card-sub{font-size:0.78em;color:var(--on-surface-m);margin-bottom:24px;}

.error-box{
  background:rgba(255,110,132,0.07);border:1px solid rgba(255,110,132,0.3);
  border-radius:var(--r-md);padding:11px 14px;margin-bottom:16px;
  display:flex;align-items:center;gap:10px;font-size:0.87em;color:var(--danger);
  animation:fadeIn .25s ease;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}

.field{position:relative;margin-bottom:14px;}
.field-label{display:block;font-size:0.77em;font-weight:700;color:var(--on-surface-v);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px;}
.field input{
  width:100%;padding:14px 46px 14px 46px;
  background:var(--surface-high);border:1px solid var(--border);
  border-radius:var(--r-md);color:var(--on-surface);font-size:1em;
  font-family:'Cairo',sans-serif;transition:border-color .2s,box-shadow .2s;
  letter-spacing:2px;
}
.field input::placeholder{color:var(--on-surface-m);letter-spacing:0;}
.field input:focus{outline:none;border-color:var(--border-accent);box-shadow:0 0 0 3px rgba(0,227,253,0.07),0 0 20px rgba(0,227,253,0.08);}
.field-icon-r{position:absolute;top:50%;right:15px;transform:translateY(-50%);color:var(--on-surface-m);font-size:15px;pointer-events:none;margin-top:12px;}
.field-icon-l{position:absolute;top:50%;left:14px;transform:translateY(-50%);color:var(--on-surface-m);font-size:13px;cursor:pointer;margin-top:12px;background:none;border:none;padding:2px 4px;transition:color .2s;}
.field-icon-l:hover{color:var(--cyan);}

.login-btn{
  width:100%;margin-top:4px;padding:14px 20px;
  background:linear-gradient(135deg,rgba(0,227,253,0.2) 0%,rgba(0,200,240,0.1) 100%);
  border:1px solid var(--border-accent);border-radius:var(--r-md);
  color:var(--cyan);font-size:1.05em;font-weight:800;font-family:'Cairo',sans-serif;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;
  transition:all .22s cubic-bezier(.4,0,.2,1);
  box-shadow:0 2px 12px rgba(0,0,0,0.3),inset 0 1px 0 rgba(0,227,253,0.15);
}
.login-btn:hover{
  background:linear-gradient(135deg,rgba(0,227,253,0.3) 0%,rgba(0,200,240,0.18) 100%);
  box-shadow:0 0 24px rgba(0,227,253,0.2),0 6px 18px rgba(0,0,0,0.35);
  transform:translateY(-2px);
}
.login-btn:active{transform:translateY(0);}

@media(max-width:480px){
  .card{padding:24px 18px;border-radius:var(--r-lg);}
  .brand-name{font-size:1.7em;}
  .brand-ring{width:58px;height:58px;}
  .brand-ring i{font-size:22px;}
}
</style>
</head>
<body>
<div class="bg-wrap">
  <div class="bg-grid"></div>
  <div class="blob blob-a"></div>
  <div class="blob blob-b"></div>
</div>

<div class="wrap">
  <div class="brand">
    <div class="brand-ring"><i class="fas fa-crown"></i></div>
    <div class="brand-name">دراجون فولو</div>
    <div class="brand-sub">Admin Panel</div>
  </div>

  <div class="card">
    <div class="card-title">تسجيل الدخول</div>
    <div class="card-sub">أدخل كلمة السر للوصول إلى لوحة التحكم</div>

    <?php if ($error): ?>
    <div class="error-box">
      <i class="fas fa-circle-exclamation"></i>
      <span><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="field">
        <label class="field-label">كلمة السر</label>
        <i class="fas fa-lock field-icon-r"></i>
        <input type="password" name="password" id="passInput" placeholder="أدخل كلمة السر" required autofocus>
        <button type="button" class="field-icon-l" onclick="togglePass()" tabindex="-1">
          <i class="fas fa-eye" id="eyeIcon"></i>
        </button>
      </div>
      <button type="submit" class="login-btn">
        <i class="fas fa-right-to-bracket"></i>
        <span>دخول إلى اللوحة</span>
      </button>
    </form>
  </div>
</div>

<script>
function togglePass() {
  var inp = document.getElementById('passInput');
  var ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.className = 'fas fa-eye-slash';
  } else {
    inp.type = 'password';
    ico.className = 'fas fa-eye';
  }
}
</script>
</body>
</html>
