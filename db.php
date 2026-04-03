<?php

declare(strict_types=1);

$_db_pdo = null;

$_setting_cache = [];

function db_init(string $db_file = ''): PDO {
    global $_db_pdo;
    if ($_db_pdo) return $_db_pdo;

    if (empty($db_file)) {
        $db_file = __DIR__ . '/botdata.sqlite';
    }

    $dir = dirname($db_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $_db_pdo = new PDO("sqlite:{$db_file}", null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ]);

    $_db_pdo->exec('PRAGMA journal_mode=WAL');
    $_db_pdo->exec('PRAGMA busy_timeout=5000');
    $_db_pdo->exec('PRAGMA synchronous=NORMAL');

    $_db_flag = $db_file . '.tables_ok';
    if (!file_exists($_db_flag)) {
        _db_create_tables($_db_pdo);
        file_put_contents($_db_flag, time());
    }

    return $_db_pdo;
}

function db(): PDO {
    global $_db_pdo;
    if (!$_db_pdo) throw new \RuntimeException('db_init() not called');
    return $_db_pdo;
}

function _db_create_tables(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            key   TEXT PRIMARY KEY,
            value TEXT NOT NULL DEFAULT ''
        );
        CREATE TABLE IF NOT EXISTS sections (
            uid        TEXT PRIMARY KEY,
            name       TEXT NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS services (
            uid         TEXT PRIMARY KEY,
            section_uid TEXT NOT NULL,
            name        TEXT NOT NULL DEFAULT '',
            price       REAL NOT NULL DEFAULT 0,
            min_order   INTEGER NOT NULL DEFAULT 10,
            max_order   INTEGER NOT NULL DEFAULT 1000,
            delay       INTEGER NOT NULL DEFAULT 0,
            service_id  TEXT NOT NULL DEFAULT '',
            domain      TEXT NOT NULL DEFAULT '',
            api_key     TEXT NOT NULL DEFAULT '',
            platform    TEXT NOT NULL DEFAULT 'tiktok',
            sort_order  INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS api_providers (
            uid        TEXT PRIMARY KEY,
            name       TEXT NOT NULL DEFAULT '',
            domain     TEXT NOT NULL DEFAULT '',
            api_key    TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL DEFAULT ''
        );
        CREATE TABLE IF NOT EXISTS store_sections (
            uid        TEXT PRIMARY KEY,
            name       TEXT NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS store_items (
            uid         TEXT PRIMARY KEY,
            section_uid TEXT NOT NULL,
            name        TEXT NOT NULL DEFAULT '',
            price       REAL NOT NULL DEFAULT 0,
            description TEXT NOT NULL DEFAULT '',
            sort_order  INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS coupons (
            code     TEXT PRIMARY KEY,
            amount   REAL NOT NULL DEFAULT 0,
            uses     INTEGER NOT NULL DEFAULT 0,
            max_uses INTEGER NOT NULL DEFAULT 1
        );
        CREATE TABLE IF NOT EXISTS users (
            user_id   INTEGER PRIMARY KEY,
            balance   REAL NOT NULL DEFAULT 0,
            mode      TEXT NOT NULL DEFAULT '',
            step      TEXT NOT NULL DEFAULT '',
            temp      TEXT NOT NULL DEFAULT '',
            joined_at INTEGER NOT NULL DEFAULT 0,
            banned    INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS api_keys (
            api_key TEXT PRIMARY KEY,
            user_id INTEGER NOT NULL
        );
        CREATE TABLE IF NOT EXISTS daily_gifts (
            user_id   INTEGER PRIMARY KEY,
            last_gift INTEGER NOT NULL DEFAULT 0
        );
    ");
}

function _db_preload_settings(array $keys): void {
    global $_setting_cache;
    $missing = array_filter($keys, fn($k) => !array_key_exists($k, $_setting_cache));
    if (empty($missing)) return;
    $ph = implode(',', array_fill(0, count($missing), '?'));
    $st = db()->prepare("SELECT key,value FROM settings WHERE key IN ($ph)");
    $st->execute(array_values($missing));
    foreach ($st->fetchAll() as $row)
        $_setting_cache[$row['key']] = $row['value'];
}

function _db_get_setting(string $key, string $default = ''): string {
    global $_setting_cache;
    if (array_key_exists($key, $_setting_cache)) return $_setting_cache[$key];
    $st = db()->prepare('SELECT value FROM settings WHERE key=?');
    $st->execute([$key]);
    $row = $st->fetch();
    $val = ($row !== false) ? $row['value'] : $default;
    $_setting_cache[$key] = $val;
    return $val;
}

function _db_set_setting(string $key, $value): void {
    global $_setting_cache;
    if (is_array($value) || is_object($value))
        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    db()->prepare('INSERT INTO settings (key,value) VALUES (?,?) ON CONFLICT (key) DO UPDATE SET value=excluded.value')
       ->execute([$key, (string)$value]);
    unset($_setting_cache[$key]);
}

function _db_json_setting(string $key, $default = []) {
    $raw = _db_get_setting($key, '');
    if ($raw === '') return $default;
    $decoded = json_decode($raw, true);
    return ($decoded !== null) ? $decoded : $default;
}

$_db_settings_cache = null;

function db_settings_invalidate(): void {
    global $_db_settings_cache;
    $_db_settings_cache = null;
    $f = __DIR__ . '/.settings_cache.json';
    if (file_exists($f)) @unlink($f);
}

function db_get_settings(): array {
    global $_db_settings_cache;

    if ($_db_settings_cache !== null) return $_db_settings_cache;

    $cache_file = __DIR__ . '/.settings_cache.json';
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 30) {
        $cached = @json_decode(file_get_contents($cache_file), true);
        if (is_array($cached) && !empty($cached)) {
            $_db_settings_cache = $cached;
            return $cached;
        }
    }

    $s = [];

    $flat = ['currency','invite_reward','min_order_quantity','daily_gift',
             'daily_gift_status','invite_link_status','transfer_status',
             'starss','Market','Ch','user_price','domain','token',
             'rshaq','api_enabled','maintenance_mode','site_url'];
    $flat_ph = implode(',', array_fill(0, count($flat), '?'));
    $st = db()->prepare("SELECT key,value FROM settings WHERE key IN ($flat_ph)");
    $st->execute($flat);
    $rows = $st->fetchAll();
    $kv   = array_column($rows, 'value', 'key');
    foreach ($flat as $k) {
        if (array_key_exists($k, $kv)) {
            $decoded = json_decode($kv[$k], true);
            $s[$k]   = ($decoded !== null && (is_array($decoded) || is_numeric($kv[$k]))) ? $decoded : $kv[$k];
        }
    }

    $pdo = db();
    $sec_rows = $pdo->query(
        'SELECT s.uid AS sec_uid, s.name AS sec_name, s.sort_order AS sec_sort,
                sv.uid, sv.name, sv.price, sv.min_order, sv.max_order,
                sv.delay, sv.service_id, sv.domain, sv.api_key, sv.platform, sv.sort_order
         FROM sections s
         LEFT JOIN services sv ON sv.section_uid = s.uid
         ORDER BY s.sort_order, sv.sort_order'
    )->fetchAll();
    foreach ($sec_rows as $r) {
        $sid = $r['sec_uid'];
        if (!isset($s['sections'][$sid]))
            $s['sections'][$sid] = ['name' => $r['sec_name'], 'services' => []];
        if ($r['uid'] !== null) {
            $s['sections'][$sid]['services'][$r['uid']] = [
                'name'       => $r['name'],
                'price'      => (float)$r['price'],
                'min'        => (int)$r['min_order'],
                'max'        => (int)$r['max_order'],
                'delay'      => (int)$r['delay'],
                'service_id' => $r['service_id'],
                'domain'     => $r['domain'],
                'api'        => $r['api_key'],
                'platform'   => $r['platform'],
            ];
        }
    }

    $store_rows = $pdo->query(
        'SELECT ss.uid AS ss_uid, ss.name AS ss_name, ss.sort_order AS ss_sort,
                si.uid, si.name, si.price, si.description, si.sort_order
         FROM store_sections ss
         LEFT JOIN store_items si ON si.section_uid = ss.uid
         ORDER BY ss.sort_order, si.sort_order'
    )->fetchAll();
    foreach ($store_rows as $r) {
        $sid = $r['ss_uid'];
        if (!isset($s['store']['sections'][$sid]))
            $s['store']['sections'][$sid] = ['name' => $r['ss_name'], 'items' => []];
        if ($r['uid'] !== null) {
            $s['store']['sections'][$sid]['items'][$r['uid']] = [
                'name'        => $r['name'],
                'price'       => (float)$r['price'],
                'description' => $r['description'],
            ];
        }
    }

    foreach (db()->query('SELECT * FROM api_providers')->fetchAll() as $p) {
        $s['api_providers'][$p['uid']] = [
            'name'       => $p['name'],
            'domain'     => $p['domain'],
            'api_key'    => $p['api_key'],
            'created_at' => $p['created_at'],
        ];
    }

    foreach (db()->query('SELECT * FROM coupons')->fetchAll() as $c) {
        $s['coupons'][$c['code']] = [
            'amount'   => (float)$c['amount'],
            'uses'     => (int)$c['uses'],
            'max_uses' => (int)$c['max_uses'],
        ];
    }

    $s['step'] = [];
    $s['temp'] = [];

    $_db_settings_cache = $s;
    @file_put_contents(__DIR__ . '/.settings_cache.json',
        json_encode($s, JSON_UNESCAPED_UNICODE), LOCK_EX);

    return $s;
}

function db_save_settings(array $s): void {
    $db = db();
    $db->beginTransaction();
    try {
        $flat = ['currency','invite_reward','min_order_quantity','daily_gift',
                 'daily_gift_status','invite_link_status','transfer_status',
                 'starss','Market','Ch','user_price','domain','token',
                 'rshaq','api_enabled','maintenance_mode','site_url'];
        foreach ($flat as $k) {
            if (array_key_exists($k, $s)) _db_set_setting($k, $s[$k]);
        }

        if (array_key_exists('sections', $s)) {
            $existing = array_column($db->query('SELECT uid FROM sections')->fetchAll(), 'uid');
            $new_uids = array_keys($s['sections'] ?? []);
            foreach (array_diff($existing, $new_uids) as $del)
                $db->prepare('DELETE FROM sections WHERE uid=?')->execute([$del]);

            $i = 0;
            foreach (($s['sections'] ?? []) as $uid => $sec) {
                $db->prepare('INSERT INTO sections (uid,name,sort_order) VALUES (?,?,?)
                    ON CONFLICT (uid) DO UPDATE SET name=excluded.name, sort_order=excluded.sort_order')
                   ->execute([$uid, $sec['name'] ?? '', $i++]);

                $st = $db->prepare('SELECT uid FROM services WHERE section_uid=?');
                $st->execute([$uid]);
                $existing_svcs = array_column($st->fetchAll(), 'uid');
                $new_svcs      = array_keys($sec['services'] ?? []);
                foreach (array_diff($existing_svcs, $new_svcs) as $del)
                    $db->prepare('DELETE FROM services WHERE uid=?')->execute([$del]);

                $j = 0;
                foreach (($sec['services'] ?? []) as $suid => $svc) {
                    $db->prepare('INSERT INTO services
                        (uid,section_uid,name,price,min_order,max_order,delay,service_id,domain,api_key,platform,sort_order)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                        ON CONFLICT (uid) DO UPDATE SET
                        section_uid=excluded.section_uid, name=excluded.name, price=excluded.price,
                        min_order=excluded.min_order, max_order=excluded.max_order, delay=excluded.delay,
                        service_id=excluded.service_id, domain=excluded.domain, api_key=excluded.api_key,
                        platform=excluded.platform, sort_order=excluded.sort_order')
                       ->execute([
                           $suid, $uid,
                           $svc['name'] ?? '', (float)($svc['price'] ?? 0),
                           (int)($svc['min'] ?? 10), (int)($svc['max'] ?? 1000),
                           (int)($svc['delay'] ?? 0), $svc['service_id'] ?? '',
                           $svc['domain'] ?? '', $svc['api'] ?? '',
                           $svc['platform'] ?? 'tiktok', $j++,
                       ]);
                }
            }
        }

        if (array_key_exists('store', $s)) {
            $i = 0;
            foreach (($s['store']['sections'] ?? []) as $uid => $ss) {
                $db->prepare('INSERT INTO store_sections (uid,name,sort_order) VALUES (?,?,?)
                    ON CONFLICT (uid) DO UPDATE SET name=excluded.name, sort_order=excluded.sort_order')
                   ->execute([$uid, $ss['name'] ?? '', $i++]);
                $j = 0;
                foreach (($ss['items'] ?? []) as $iuid => $item) {
                    $db->prepare('INSERT INTO store_items
                        (uid,section_uid,name,price,description,sort_order) VALUES (?,?,?,?,?,?)
                        ON CONFLICT (uid) DO UPDATE SET
                        name=excluded.name, price=excluded.price,
                        description=excluded.description, sort_order=excluded.sort_order')
                       ->execute([$iuid, $uid, $item['name'] ?? '', (float)($item['price'] ?? 0),
                                  $item['description'] ?? '', $j++]);
                }
            }
        }

        if (array_key_exists('api_providers', $s)) {
            $db->exec('DELETE FROM api_providers');
            foreach (($s['api_providers'] ?? []) as $uid => $p) {
                $db->prepare('INSERT INTO api_providers (uid,name,domain,api_key,created_at) VALUES (?,?,?,?,?)')
                   ->execute([$uid, $p['name'] ?? '', $p['domain'] ?? '',
                              $p['api_key'] ?? '', $p['created_at'] ?? date('Y-m-d')]);
            }
        }

        if (array_key_exists('coupons', $s)) {
            $db->exec('DELETE FROM coupons');
            foreach (($s['coupons'] ?? []) as $code => $c) {
                $db->prepare('INSERT INTO coupons (code,amount,uses,max_uses) VALUES (?,?,?,?)')
                   ->execute([$code, (float)($c['amount'] ?? 0),
                              (int)($c['uses'] ?? 0), (int)($c['max_uses'] ?? 1)]);
            }
        }

        foreach (($s['step'] ?? []) as $uid => $step) {
            $db->prepare('INSERT INTO users (user_id) VALUES (?) ON CONFLICT (user_id) DO NOTHING')->execute([(int)$uid]);
            $db->prepare('UPDATE users SET step=? WHERE user_id=?')->execute([$step ?? '', (int)$uid]);
        }
        foreach (($s['temp'] ?? []) as $uid => $temp) {
            $db->prepare('INSERT INTO users (user_id) VALUES (?) ON CONFLICT (user_id) DO NOTHING')->execute([(int)$uid]);
            $enc = is_array($temp) ? json_encode($temp, JSON_UNESCAPED_UNICODE) : (string)$temp;
            $db->prepare('UPDATE users SET temp=? WHERE user_id=?')->execute([$enc, (int)$uid]);
        }

        $db->commit();
        db_settings_invalidate();
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function db_get_namero(bool $with_api_keys = false): array {
    $N = ['mode' => [], 'coin' => [], 'id' => [], 'step' => [], 'temp' => [], 'rshaq' => 'on'];

    foreach (db()->query("SELECT user_id, mode FROM users WHERE mode IS NOT NULL AND mode != ''")->fetchAll() as $u) {
        $uid = (string)$u['user_id'];
        $N['mode'][$uid] = $u['mode'];
    }

    if ($with_api_keys) {
        $N['api_keys'] = [];
        foreach (db()->query('SELECT api_key,user_id FROM api_keys')->fetchAll() as $k)
            $N['api_keys'][$k['api_key']] = (int)$k['user_id'];
    }

    $N['rshaq'] = _db_get_setting('_namero_rshaq', 'on');

    return $N;
}

function db_ensure_namero_coin(array &$Namero, $uid): void {
    $key = (string)$uid;
    if (!array_key_exists($key, $Namero['coin'])) {
        $Namero['coin'][$key] = db_get_user_coin((int)$uid);
    }
}

function db_save_namero(array $N): void {
    $has_mode    = !empty($N['mode']);
    $has_coin    = !empty($N['coin']);
    $has_id      = !empty($N['id']);
    $has_api     = array_key_exists('api_keys', $N);
    $has_rshaq   = array_key_exists('rshaq', $N);

    if (!$has_mode && !$has_coin && !$has_id && !$has_api && !$has_rshaq) return;

    $db = db();
    $db->beginTransaction();
    try {
        if ($has_mode) {
            $st = $db->prepare('INSERT INTO users (user_id,mode) VALUES (?,?)
                ON CONFLICT (user_id) DO UPDATE SET mode=excluded.mode');
            foreach ($N['mode'] as $uid => $mode)
                $st->execute([(int)$uid, $mode ?? '']);
        }
        if ($has_coin) {
            $st = $db->prepare('INSERT INTO users (user_id,balance) VALUES (?,?)
                ON CONFLICT (user_id) DO UPDATE SET balance=excluded.balance');
            foreach ($N['coin'] as $uid => $bal)
                $st->execute([(int)$uid, (float)$bal]);
        }
        if ($has_id) {
            $st = $db->prepare('INSERT INTO users (user_id,joined_at) VALUES (?,?)
                ON CONFLICT (user_id) DO NOTHING');
            foreach ($N['id'] as $uid => $_)
                $st->execute([(int)$uid, time()]);
        }
        if ($has_api) {
            $db->exec('DELETE FROM api_keys');
            if (!empty($N['api_keys'])) {
                $st = $db->prepare('INSERT INTO api_keys (api_key,user_id) VALUES (?,?)
                    ON CONFLICT (api_key) DO UPDATE SET user_id=excluded.user_id');
                foreach ($N['api_keys'] as $key => $uid)
                    $st->execute([$key, (int)$uid]);
            }
        }
        if ($has_rshaq) _db_set_setting('_namero_rshaq', $N['rshaq']);

        $db->commit();
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function db_get_daily_gifts(): array {
    $result = [];
    foreach (db()->query('SELECT user_id,last_gift FROM daily_gifts')->fetchAll() as $row)
        $result[(string)$row['user_id']] = (int)$row['last_gift'];
    return $result;
}

function db_save_daily_gifts(array $gifts): void {
    $db = db();
    $db->beginTransaction();
    try {
        foreach ($gifts as $uid => $ts)
            $db->prepare('INSERT INTO daily_gifts (user_id,last_gift) VALUES (?,?)
                ON CONFLICT (user_id) DO UPDATE SET last_gift=excluded.last_gift')
               ->execute([(int)$uid, (int)$ts]);
        $db->commit();
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function db_get_users_list(): array {
    return _db_json_setting('users_list', []);
}

function db_save_users_list(array $data): void {
    _db_set_setting('users_list', json_encode($data, JSON_UNESCAPED_UNICODE));
}

function db_get_user_profile(string $uid): ?array {
    $raw = _db_get_setting('users_list', '');
    if ($raw === '' || $raw === '[]') return null;
    $pat = '/"' . preg_quote($uid, '/') . '"\s*:\s*(\{[^}]+\})/';
    if (!preg_match($pat, $raw, $m)) return null;
    $obj = @json_decode($m[1], true);
    return is_array($obj) ? $obj : null;
}

function db_save_user_profile(string $uid, string $name, string $username): void {
    $all = _db_json_setting('users_list', []);
    $all[$uid] = ['name' => $name, 'username' => $username ?: 'غير معروف', 'id' => $uid];
    _db_set_setting('users_list', json_encode($all, JSON_UNESCAPED_UNICODE));
}

function db_get_stats(): array {
    return _db_json_setting('stats_data', []);
}

function db_save_stats(array $data): void {
    _db_set_setting('stats_data', json_encode($data, JSON_UNESCAPED_UNICODE));
}

function db_get_saleh(): array {
    $raw = _db_get_setting('saleh_data', '');
    if ($raw === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function db_save_saleh(array $data): void {
    _db_set_setting('saleh_data', json_encode($data, JSON_UNESCAPED_UNICODE));
}

function db_get_user_coin(int $uid): float {
    $st = db()->prepare('SELECT balance FROM users WHERE user_id=?');
    $st->execute([$uid]);
    $row = $st->fetch();
    return $row ? (float)$row['balance'] : 0.0;
}

function db_set_user_coin(int $uid, float $balance): void {
    db()->prepare('INSERT INTO users (user_id,balance) VALUES (?,?)
        ON CONFLICT (user_id) DO UPDATE SET balance=excluded.balance')
       ->execute([$uid, $balance]);
}

function db_get_user_row(int $uid): array {
    $st = db()->prepare('SELECT balance,mode,step,temp FROM users WHERE user_id=?');
    $st->execute([$uid]);
    $row = $st->fetch();
    if (!$row) return ['balance'=>0.0,'mode'=>'','step'=>'','temp'=>''];
    $row['balance'] = (float)$row['balance'];
    return $row;
}

function db_set_user_mode(int $uid, string $mode): void {
    db()->prepare('INSERT INTO users (user_id,mode) VALUES (?,?)
        ON CONFLICT (user_id) DO UPDATE SET mode=excluded.mode')
       ->execute([$uid, $mode]);
}

function db_set_user_step(int $uid, string $step): void {
    db()->prepare('INSERT INTO users (user_id,step) VALUES (?,?)
        ON CONFLICT (user_id) DO UPDATE SET step=excluded.step')
       ->execute([$uid, $step]);
}

function db_set_user_temp(int $uid, $temp): void {
    $val = is_array($temp) ? json_encode($temp, JSON_UNESCAPED_UNICODE) : (string)$temp;
    db()->prepare('INSERT INTO users (user_id,temp) VALUES (?,?)
        ON CONFLICT (user_id) DO UPDATE SET temp=excluded.temp')
       ->execute([$uid, $val]);
}

function db_ban_user(int $uid): void {
    db()->prepare('INSERT INTO users (user_id, banned) VALUES (?, 1)
        ON CONFLICT (user_id) DO UPDATE SET banned=1')
       ->execute([$uid]);
}

function db_unban_user(int $uid): void {
    db()->prepare('INSERT INTO users (user_id, banned) VALUES (?, 0)
        ON CONFLICT (user_id) DO UPDATE SET banned=0')
       ->execute([$uid]);
}

function db_is_banned(int $uid): bool {
    $st = db()->prepare('SELECT banned FROM users WHERE user_id=?');
    $st->execute([$uid]);
    $row = $st->fetch();
    return $row && (int)$row['banned'] === 1;
}

function db_get_user_daily_gift(int $uid): int {
    $st = db()->prepare('SELECT last_gift FROM daily_gifts WHERE user_id=?');
    $st->execute([$uid]);
    $row = $st->fetch();
    return $row ? (int)$row['last_gift'] : 0;
}

function db_set_user_daily_gift(int $uid, int $ts): void {
    db()->prepare('INSERT INTO daily_gifts (user_id,last_gift) VALUES (?,?)
        ON CONFLICT (user_id) DO UPDATE SET last_gift=excluded.last_gift')
       ->execute([$uid, $ts]);
}

function db_get_bot_status(): string {
    return _db_get_setting('_bot_status', 'enabled');
}

function db_set_bot_status(string $status): void {
    _db_set_setting('_bot_status', $status);
}

function db_get_force_channels(): array {
    $raw = _db_get_setting('force_sub_channels', '[]');
    $arr = json_decode($raw, true);
    return is_array($arr) ? array_values(array_filter($arr)) : [];
}

function db_set_force_channels(array $channels): void {
    _db_set_setting('force_sub_channels',
        json_encode(array_values(array_filter($channels)), JSON_UNESCAPED_UNICODE));
}

function db_ensure_user(int $uid): void {
    db()->prepare('INSERT INTO users (user_id) VALUES (?) ON CONFLICT (user_id) DO NOTHING')
       ->execute([$uid]);
}
