<?php
chdir(__DIR__);
set_time_limit(60);

if ($argc < 2 || !file_exists($argv[1])) {
    exit(1);
}

$GLOBALS['_polling_input'] = file_get_contents($argv[1]);

$origBot = null;
include __DIR__ . '/Namero.php';
