<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$API_KEY = "8575984011:AAGk4WNw26C3zuXKMMAS2TWMLjJdZ3WzqIA";

function bot($method, $datas = []) {
    global $API_KEY;
    $url = "https://api.telegram.org/bot" . $API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res);
}

$update = json_decode(file_get_contents('php://input'));

if ($update && isset($update->message)) {
    $chat_id = $update->message->chat->id;
    $text = $update->message->text;
    
    if ($text == "/start") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ البوت يعمل! 🎉\n\nتم التطوير بواسطة @ypui5"
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "أرسلت: $text"
        ]);
    }
}

echo "OK";
