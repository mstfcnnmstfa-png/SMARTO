<?php
/**
 * ملف الإصلاح التلقائي الشامل لـ "دراجون فولو"
 * قم برفعه إلى مجلد البوت ثم افتحه عبر المتصفح.
 */

// بياناتك الجديدة (عدلها هنا حسب رغبتك)
$NEW_BOT_TOKEN = "8076347498:AAFf19F-V1HVsRsJPuq3_S2L0s63dwaS7_I";
$NEW_OWNER_ID  = "7816487928";
$NEW_CHANNEL   = "https://t.me/TJUI9";   // رابط قناة البوت

echo "<!DOCTYPE html><html dir='rtl'><head><meta charset='UTF-8'><title>إصلاح البوت</title><style>body{font-family:Tahoma;background:#0a0a0a;color:#0f0;padding:20px;}</style></head><body>";
echo "<h2>🔧 جاري الإصلاح التلقائي ...</h2><pre>";

// 1. تحديث التوكن في الملفات الأساسية
$files_with_token = ['index.php', 'admin_login.php', 'polling.php', 'service.php', 'index.php', 'fix.php'];
foreach ($files_with_token as $file) {
    if (file_exists($file)) {
        $old_content = file_get_contents($file);
        // استبدال التوكن القديم بالجديد (أي توكن كان)
        $new_content = preg_replace('/[0-9]{9,12}:[A-Za-z0-9_\-]{35,}/', $NEW_BOT_TOKEN, $old_content);
        if ($new_content !== $old_content) {
            file_put_contents($file, $new_content);
            echo "✅ تم تحديث التوكن في: $file\n";
        } else {
            echo "⚠️ لم يتغير التوكن في: $file (قد يكون صحيحاً بالفعل)\n";
        }
    } else {
        echo "⏩ ملف غير موجود: $file\n";
    }
}

// 2. تحديث $SALEH و $admins و ADMIN_PANEL_PASS في admins.php
$admins_content = "<?php
define('ADMIN_PANEL_PASS', '999888');
\$SALEH = {$NEW_OWNER_ID};
\$admins = [];
return \$admins;
?>";
file_put_contents('admins.php', $admins_content);
echo "✅ تم تحديث admins.php (المطور: $NEW_OWNER_ID)\n";

// 3. تحديث رابط القناة في db_settings إذا أمكن (أو في متغيرات Namero)
$settings_file = __DIR__ . '/NAMERO/*/botdata.sqlite'; // سيتم تحديثه عبر db.php
// لكن نضبط القناة عبر db مباشرةً إذا كانت db.php موجودة
if (file_exists('db.php')) {
    require_once 'db.php';
    try {
        db_init();
        _db_set_setting('Ch', $NEW_CHANNEL);
        echo "✅ تم تحديث قناة البوت في قاعدة البيانات إلى: $NEW_CHANNEL\n";
    } catch (Exception $e) {
        echo "⚠️ لم نتمكن من تحديث القناة في قاعدة البيانات (تأكد من وجود مجلد NAMERO قابل للكتابة)\n";
    }
}

// 4. إنشاء ملف Dockerfile (لحل مشكلة Build على Koyeb)
$dockerfile = <<<DOCKER
FROM php:8.2-apache
RUN a2enmod rewrite
RUN docker-php-ext-install pdo_sqlite
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html && chmod -R 777 /var/www/html/NAMERO
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!\${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
EXPOSE 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf
CMD ["apache2-foreground"]
DOCKER;
file_put_contents('Dockerfile', $dockerfile);
echo "✅ تم إنشاء ملف Dockerfile (لضمان عمل PHP على Koyeb)\n";

// 5. إنشاء composer.json
$composer = <<<JSON
{
    "name": "namero/bot",
    "type": "project",
    "require": {
        "php": ">=7.4"
    }
}
JSON;
file_put_contents('composer.json', $composer);
echo "✅ تم إنشاء composer.json\n";

// 6. تصحيح admin.php (حذف إعادة تعريف $update، إضافة bot_id)
if (file_exists('admin.php')) {
    $admin_content = file_get_contents('admin.php');
    // إزالة السطر $update = json_decode($_raw_input);
    $admin_content = preg_replace('/\$update\s*=\s*json_decode\(\$_raw_input\);/', '// تم إزالة السطر المكرر', $admin_content);
    // إصلاح $NAMERO = "NAMERO/". $bot_id;
    $admin_content = preg_replace(
        '/\$NAMERO\s*=\s*"NAMERO\/"\s*\.\s*\$bot_id\s*;/',
        'global $bot_id; if(!isset($bot_id)){$bot_info=bot("getMe");$bot_id=$bot_info->result->id??"";} $NAMERO = "NAMERO/".$bot_id;',
        $admin_content
    );
    // إصلاح mkdir
    $admin_content = str_replace(
        'mkdir ("NAMERO"); mkdir ("NAMERO/$bot_id");',
        'if(!is_dir("NAMERO")) mkdir("NAMERO",0777,true); if(!is_dir("NAMERO/$bot_id")) mkdir("NAMERO/$bot_id",0777,true);',
        $admin_content
    );
    file_put_contents('admin.php', $admin_content);
    echo "✅ تم إصلاح admin.php (حذف تكرار update, إصلاح bot_id, mkdir)\n";
} else {
    echo "⚠️ admin.php غير موجود\n";
}

// 7. تصحيح admin_login.php (عرض الأخطاء + نقطة)
if (file_exists('admin_login.php')) {
    $login_content = file_get_contents('admin_login.php');
    // إضافة عرض الأخطاء بعد <?php
    if (strpos($login_content, 'ini_set') === false) {
        $login_content = preg_replace('/<\?php/', "<?php\nini_set('display_errors',1);\nerror_reporting(E_ALL);", $login_content);
    }
    // إزالة النقطة الزائدة بعد شرط كلمة السر
    $login_content = str_replace("if (\$pass === '999888').", "if (\$pass === '999888')", $login_content);
    file_put_contents('admin_login.php', $login_content);
    echo "✅ تم إصلاح admin_login.php (عرض الأخطاء، النقطة)\n";
} else {
    echo "⚠️ admin_login.php غير موجود\n";
}

// 8. التأكد من وجود مجلد NAMERO وقابل للكتابة
if (!is_dir('NAMERO')) mkdir('NAMERO', 0777, true);
echo "✅ مجلد NAMERO موجود (أو تم إنشاؤه)\n";

// 9. ضبط الـ Webhook تلقائياً (اختياري)
$webhook_url = "https://api.telegram.org/bot{$NEW_BOT_TOKEN}/setWebhook?url=" . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/Namero.php&drop_pending_updates=true';
$wh_response = @file_get_contents($webhook_url);
if ($wh_response) {
    $json = json_decode($wh_response);
    if ($json && $json->ok) {
        echo "✅ تم ضبط الـ Webhook بنجاح على: " . $json->result->url . "\n";
    } else {
        echo "⚠️ فشل ضبط Webhook تلقائياً (يمكنك تنفيذه يدوياً لاحقاً)\n";
    }
} else {
    echo "⚠️ تعذر الاتصال بتليجرام لضبط Webhook (قد تحتاج لتنفيذه يدوياً)\n";
}

echo "\n✨ تم الإصلاح بنجاح!\n";
echo "🔹 الآن أعد تحميل التطبيق على Koyeb (أو أعد البناء).\n";
echo "🔹 بعد ذلك، أرسل /start إلى البوت.\n";
echo "</pre></body></html>";
