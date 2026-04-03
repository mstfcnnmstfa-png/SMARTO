<?php
$files = ['index.php', 'polling.php', 'service.php', 'admin_login.php'];
foreach ($files as $file) {
    $content = file_get_contents($file);
    $fixed = str_replace(
        "8076347498:AAEq520a0raqgxY\xc3\x980kQW7_fiYM23khnxSKNU",
        "YOUR_NEW_TOKEN_HERE",
        $content
    );
    file_put_contents($file, $fixed);
    echo "Fixed: $file\n";
}
