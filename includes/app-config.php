<?php

/** Loads config.php (gitignored) if present, else built-in defaults. */
function viewerConfig(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $file = __DIR__ . '/../config.php';
        $loaded = is_file($file) ? require $file : [];
        $cfg = is_array($loaded) ? $loaded : [];
    }
    return $cfg;
}

function viewerIsLocalhost(): bool
{
    // The Host header is client-controlled, so also require the connection itself to come from the local machine.
    $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    // Behind a reverse proxy REMOTE_ADDR is the proxy, so any forwarding header means this is not a direct local request.
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_HOST', 'HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP'] as $h) {
        if (!empty($_SERVER[$h])) {
            return false;
        }
    }
    $loopback = in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true);
    return $loopback && in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
}

/** Testing mode = flag on in config.php AND request is really on localhost. */
function viewerTestMode(): bool
{
    return !empty(viewerConfig()['test_mode']) && viewerIsLocalhost();
}

/** Admin emails: yesudas.rs@gmail.com always, plus any listed in config.php 'admins'. Nobody else. */
function viewerAdmins(): array
{
    $extra = array_map('strval', (array) (viewerConfig()['admins'] ?? []));
    return array_values(array_unique(array_map(
        static function (string $e): string { return strtolower(trim($e)); },
        array_merge(['yesudas.rs@gmail.com'], $extra)
    )));
}

function viewerIsAdmin(?array $user): bool
{
    if (!$user || empty($user['email']) || !in_array(strtolower(trim((string) $user['email'])), viewerAdmins(), true)) {
        return false;
    }
    require_once __DIR__ . '/admin-users.php';
    return !viewerIsBlocked((string) $user['email']); // an admin can be blocked from the Users tab
}
