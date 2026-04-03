<?php
// إعدادات الإصلاح الشامل لملفات البوت
$newToken = "8076347498:AAEq520a0raqgxY0kQW7_fiYM23khnxSKNU";
$files = ['index.php', 'polling.php', 'service.php', 'admin_login.php', 'Namero.php'];

echo "<h3>جاري إصلاح ملفات ypiu3...</h3>";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // 1. إصلاح التوكن بجميع أشكاله (سواء كان فيه حرف Ø أو ترميز UTF-8 مشوه)
        $fixed = preg_replace('/8076347498:AAEq520a0raqgxY.{1,5}kQW7_fiYM23khnxSKNU/', $newToken, $content);
        
        // 2. إصلاح خطأ النقطة الزائدة في ملف تسجيل الدخول
        if ($file == 'admin_login.php') {
            $fixed = str_replace("if (\$pass === '999888').", "if (\$pass === '999888')", $fixed);
        }

        // 3. التأكد من وجود وسم PHP في بداية الملف إذا فُقد
        if (strpos($fixed, '<?php') === false) {
            $fixed = "<?php\n" . $fixed;
        }

        if (file_put_contents($file, $fixed)) {
            echo "✅ تم إصلاح: <b>$file</b><br>";
        } else {
            echo "❌ فشل الكتابة في: $file (تحقق من الصلاحيات)<br>";
        }
    } else {
        echo "⚠️ الملف غير موجود: $file (تخطيت)<br>";
    }
}

// 4. محاولة تفعيل الويب هوك تلقائياً بعد الإصلاح
$webhookUrl = "https://api.telegram.org/bot$newToken/setWebhook?url=https://eldest-joletta-baken123-5f29ef2c.koyeb.app/index.php&drop_pending_updates=true";
$response = file_get_contents($webhookUrl);

echo "<br><b>الحالة النهائية:</b><br>";
echo "استجابة التليجرام: <pre>$response</pre>";
echo "<br><br><b>الآن يا مصطفى، اذهب للبوت وجرب أرسل /start</b>";
?>
