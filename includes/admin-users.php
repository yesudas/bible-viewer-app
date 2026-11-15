<?php

/**
 * Accounts that have opened (or tried to open) the admin screen a.php — one JSON file per Google account in users/
 * (gitignored, blocked from the web by users/.htaccess). Who is *allowed* in is decided only by viewerAdmins()
 * (built-in default admin + config.php 'admins'); a record here can additionally block a listed admin.
 */
require_once __DIR__ . '/app-config.php';

function viewerUserId(string $email): string
{
    return substr(sha1(strtolower(trim($email))), 0, 16);
}

function viewerUserFile(string $id): string
{
    return __DIR__ . '/../users/' . preg_replace('/[^a-f0-9]/', '', $id) . '.json';
}

/** @return array<string,mixed>|null */
function viewerUserRead(string $id): ?array
{
    $f = viewerUserFile($id);
    $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    return is_array($d) && !empty($d['email']) ? $d : null;
}

function viewerUserWrite(array $u): bool
{
    $dir = __DIR__ . '/../users';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return @file_put_contents(viewerUserFile(viewerUserId((string) $u['email'])), json_encode($u, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false;
}

function viewerIsDefaultAdmin(string $email): bool
{
    return strtolower(trim($email)) === viewerAdmins()[0];
}

/** Blocked accounts are turned away even if listed in config.php. The default admin can never be blocked (no lock-out). */
function viewerIsBlocked(?string $email): bool
{
    if (!$email || viewerIsDefaultAdmin($email)) {
        return false;
    }
    $u = viewerUserRead(viewerUserId($email));
    return $u !== null && !empty($u['blocked']);
}

/** Records a visit to a.php. A "visit" = first request, or one 30+ minutes after the previous. */
function viewerUserTouch(array $user, bool $allowed): void
{
    $email = strtolower(trim((string) $user['email']));
    $id = viewerUserId($email);
    $now = time();
    $existing = viewerUserRead($id);
    if ($existing === null && !$allowed && count(glob(__DIR__ . '/../users/*.json') ?: []) >= 500) {
        return; // don't let unknown accounts create unlimited records
    }
    $u = $existing ?? ['email' => $email, 'firstSeen' => date('Y-m-d H:i:s'), 'visits' => 0, 'denied' => 0, 'blocked' => false, 'lastSeenTs' => 0];
    $u['name'] = (string) ($user['name'] ?? $u['name'] ?? '');
    $u['picture'] = (string) ($user['picture'] ?? $u['picture'] ?? '');
    if ($now - (int) ($u['lastSeenTs'] ?? 0) > 1800) {
        $u[$allowed ? 'visits' : 'denied'] = (int) ($u[$allowed ? 'visits' : 'denied'] ?? 0) + 1;
    }
    $u['lastSeen'] = date('Y-m-d H:i:s', $now);
    $u['lastSeenTs'] = $now;
    viewerUserWrite($u);
}

/** Every recorded account, most recently seen first. @return list<array<string,mixed>> */
function viewerUsersAll(): array
{
    $out = [];
    foreach (glob(__DIR__ . '/../users/*.json') ?: [] as $f) {
        $d = json_decode((string) file_get_contents($f), true);
        if (is_array($d) && !empty($d['email'])) {
            $out[] = $d;
        }
    }
    usort($out, static function (array $a, array $b): int { return (int) ($b['lastSeenTs'] ?? 0) <=> (int) ($a['lastSeenTs'] ?? 0); });
    return $out;
}

function viewerUserSetBlocked(string $id, bool $blocked): bool
{
    $u = viewerUserRead($id);
    if (!$u || viewerIsDefaultAdmin((string) $u['email'])) {
        return false;
    }
    $u['blocked'] = $blocked;
    if ($blocked) { $u['blockedAt'] = date('Y-m-d H:i:s'); } else { unset($u['blockedAt']); }
    return viewerUserWrite($u);
}

function viewerUserDelete(string $id): bool
{
    $f = viewerUserFile($id);
    return is_file($f) && @unlink($f);
}

/** Status shown in the Users tab. */
function viewerUserStatus(array $u): string
{
    $email = (string) $u['email'];
    if (!empty($u['blocked']) && !viewerIsDefaultAdmin($email)) { return 'Blocked'; }
    if (viewerIsDefaultAdmin($email)) { return 'Admin (default)'; }
    return in_array(strtolower($email), viewerAdmins(), true) ? 'Admin (config)' : 'No access';
}
