<?php
// admin_login.php - صفحة دخول المدير
session_start();
ob_start();

// التحقق من وجود توكن البوت في الجلسة
if (isset($_SESSION['bot_token']) && !empty($_SESSION['bot_token'])) {
    header("Location: admin_panel.php");
    exit();
}

// معالجة تسجيل الدخول
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $bot_token = $_POST['bot_token'] ?? '';
    
    if (!empty($admin_id) && !empty($bot_token)) {
        // التحقق من صحة التوكن
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/getMe");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $bot_info = json_decode($response, true);
        
        if ($bot_info && $bot_info['ok']) {
            // التحقق من أن المستخدم هو أدمن البوت
            $ch2 = curl_init();
            curl_setopt($ch2, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/getChatAdministrators?chat_id={$admin_id}");
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
            $admin_check = curl_exec($ch2);
            curl_close($ch2);
            
            // تخزين معلومات الدخول في الجلسة
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['bot_token'] = $bot_token;
            $_SESSION['bot_username'] = $bot_info['result']['username'];
            $_SESSION['bot_name'] = $bot_info['result']['first_name'];
            
            header("Location: admin_panel.php");
            exit();
        } else {
            $login_error = "❌ توكن البوت غير صحيح!";
        }
    } else {
        $login_error = "❌ يرجى ملء جميع الحقول!";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المدير - سميث ماتريكس</title>
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
        }

        body {
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
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
                linear-gradient(90deg, var(--accent-purple-glow) 1px, transparent 1px),
                linear-gradient(0deg, var(--accent-purple-glow) 1px, transparent 1px);
            background-size: 50px 50px;
            transform: perspective(500px) rotateX(60deg) translateY(-20%);
            animation: moveGrid 20s linear infinite;
            opacity: 0.2;
        }

        @keyframes moveGrid {
            0% { transform: perspective(500px) rotateX(60deg) translateY(-20%) translateX(0); }
            100% { transform: perspective(500px) rotateX(60deg) translateY(-20%) translateX(50px); }
        }

        .login-container {
            background: linear-gradient(145deg, var(--bg-secondary), #000000);
            border: 2px solid var(--accent-purple);
            border-radius: 40px;
            padding: 50px;
            max-width: 500px;
            width: 100%;
            transform: perspective(1000px) rotateX(2deg);
            box-shadow: 0 30px 60px rgba(138,43,226,0.3);
            position: relative;
            overflow: hidden;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: perspective(1000px) rotateX(2deg) translateY(0); }
            50% { transform: perspective(1000px) rotateX(2deg) translateY(-10px); }
        }

        .login-container::before {
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

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .login-icon {
            font-size: 80px;
            color: var(--accent-purple);
            margin-bottom: 20px;
            filter: drop-shadow(0 0 20px var(--accent-purple-glow));
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .login-title {
            font-size: 2.5em;
            color: var(--accent-purple);
            text-shadow: 0 0 20px var(--accent-purple-glow);
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .form-label i {
            color: var(--accent-purple);
            margin-left: 8px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            background: var(--bg-tertiary);
            border: 2px solid var(--glass-border);
            border-radius: 20px;
            color: var(--text-primary);
            font-size: 1.1em;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-purple);
            box-shadow: 0 0 30px var(--accent-purple-glow);
            transform: perspective(500px) translateZ(10px);
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(145deg, var(--accent-purple), var(--accent-purple-dark));
            border: none;
            border-radius: 20px;
            color: white;
            font-size: 1.3em;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.3s;
            transform: perspective(500px) translateZ(0);
            box-shadow: 0 10px 30px rgba(138,43,226,0.3);
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .login-btn:hover {
            transform: perspective(500px) translateZ(20px) scale(1.02);
            box-shadow: 0 20px 40px rgba(138,43,226,0.5);
        }

        .error-alert {
            background: rgba(255,68,68,0.1);
            border: 2px solid var(--danger);
            border-radius: 20px;
            padding: 15px 20px;
            margin-bottom: 25px;
            color: var(--danger);
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            z-index: 1;
        }

        .error-alert i {
            font-size: 1.5em;
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: var(--text-secondary);
            position: relative;
            z-index: 1;
        }

        .footer-text a {
            color: var(--accent-purple);
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="background-3d">
        <div class="grid-3d"></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-crown"></i>
            </div>
            <h1 class="login-title">لوحة التحكم</h1>
            <p class="login-subtitle">سميث ماتريكس - منصة إدارة الخدمات</p>
        </div>

        <?php if (!empty($login_error)): ?>
        <div class="error-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo $login_error; ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-id-card"></i> ايدي الأدمن</label>
                <input type="text" name="admin_id" class="form-control" placeholder="7816487928" required>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-key"></i> توكن البوت</label>
                <input type="password" name="bot_token" class="form-control" placeholder="8575984011:AAGk4WNw26C3zuXKMMAS2TWMLjJdZ3WzqIA" required>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> دخول
            </button>
        </form>

        <div class="footer-text">
            <p>⚡ برمجة وتطوير <a href="https://t.me/ypiu5" target="_blank">@ypiu5</a></p>
        </div>
    </div>
</body>
</html>