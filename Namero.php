<?php
// التوكن النظيف لـ ypiu3
$API_KEY = "8076347498:AAEq520a0raqgxY0kQW7_fiYM23khnxSKNU";
define('API_KEY', $API_KEY);

function bot($method, $datas = []){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    return curl_exec($ch);
}

$update = json_decode(file_get_contents('php://input'));
if($update->message){
    $chat_id = $update->message->chat->id;
    $text = $update->message->text;

    if($text == "/start"){
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "أهلاً يا مصطفى (ypiu3)! البوت الآن متصل ومستقر ✅"
        ]);
    }
}
