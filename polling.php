<?php
chdir(__DIR__);
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$API_KEY = "8076347498:AAEq520aOraqgxY0kQW7_fiyM23khnxSKNU";

function polling_request($method, $data = []) {
    global $API_KEY;
    $url = "https://api.telegram.org/bot{$API_KEY}/{$method}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 35);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        echo "[CURL ERROR] " . curl_error($ch) . "\n";
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    return json_decode($res, true);
}

echo "=== Dragon Follow Bot - Long Polling Mode ===\n";
echo "Removing webhook...\n";
$del = polling_request('deleteWebhook');
echo "deleteWebhook: " . json_encode($del) . "\n";

$me = polling_request('getMe');
echo "Bot: @" . ($me['result']['username'] ?? '?') . " (ID: " . ($me['result']['id'] ?? '?') . ")\n";
echo "Starting polling loop...\n\n";

$offset = 0;

while (true) {
    $updates = polling_request('getUpdates', [
        'offset' => $offset,
        'timeout' => 30,
        'allowed_updates' => json_encode(['message', 'callback_query', 'inline_query'])
    ]);

    if (!$updates || !$updates['ok'] || empty($updates['result'])) {
        continue;
    }

    foreach ($updates['result'] as $update) {
        $offset = $update['update_id'] + 1;
        $json = json_encode($update, JSON_UNESCAPED_UNICODE);
        echo "[UPDATE " . $update['update_id'] . "] ";

        if (isset($update['message']['text'])) {
            echo "From: " . ($update['message']['from']['first_name'] ?? '?')
                . " | Text: " . $update['message']['text'] . "\n";
        } elseif (isset($update['callback_query'])) {
            echo "Callback: " . ($update['callback_query']['data'] ?? '?') . "\n";
        } else {
            echo "Other update type\n";
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'tg_update_');
        file_put_contents($tmpFile, $json);

        $cmd = sprintf(
            'php %s/process_update.php %s 2>&1',
            escapeshellarg(__DIR__),
            escapeshellarg($tmpFile)
        );

        $output = shell_exec($cmd);
        if ($output !== null && trim($output) !== '') {
            echo "  [OUTPUT] " . trim($output) . "\n";
        } else {
            echo "  [OK]\n";
        }

        @unlink($tmpFile);
    }
}
