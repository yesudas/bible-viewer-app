<?php

/**
 * Thin bridge to the shared wordofgod.in/auth service (sibling folder of this app, hence the ../../ path).
 * Same approach as bible-tracker-app / bible-quiz-app.
 *
 * Testing mode (config.php 'test_mode' => true, localhost only) skips Google
 * and returns a fixed user, so the admin screen can be exercised without signing in.
 */
require_once __DIR__ . '/app-config.php';

function viewerCurrentUrl(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
}

function viewerAuthSessionFile(): ?string
{
    $f = __DIR__ . '/../../auth/session.php';
    return is_file($f) ? $f : null;
}

/** @return array{sub:string,email:string,name:string,picture:string}|null */
function viewerGoogleUser(): ?array
{
    if (viewerTestMode()) {
        $u = viewerConfig()['test_user'] ?? [];
        return [
            'sub' => (string) ($u['sub'] ?? 'test-user'),
            'email' => (string) ($u['email'] ?? 'yesudas.rs@gmail.com'),
            'name' => (string) ($u['name'] ?? 'Yesudas Solomon'),
            'picture' => (string) ($u['picture'] ?? ''),
        ];
    }
    $file = viewerAuthSessionFile();
    if (!$file) {
        return null;
    }
    require_once $file;
    try {
        $u = authUser();
        return $u && !empty($u['email']) ? $u : null;
    } catch (Throwable $e) {
        return null;
    }
}

function viewerGoogleLoginUrl(string $returnTo): ?string
{
    $file = viewerAuthSessionFile();
    if (!$file) {
        return null;
    }
    require_once $file;
    return authLoginUrl($returnTo);
}

function viewerGoogleLogoutUrl(string $returnTo): ?string
{
    if (viewerTestMode()) {
        return null; // nothing to sign out of
    }
    $file = viewerAuthSessionFile();
    if (!$file) {
        return null;
    }
    require_once $file;
    return authLogoutUrl($returnTo);
}

/** Per-session CSRF token (the shared auth session is already running when signed in; test mode starts its own). */
function viewerCsrf(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('VIEWERADMSESS');
        session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
    if (empty($_SESSION['viewer_csrf'])) {
        $_SESSION['viewer_csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['viewer_csrf'];
}

function viewerCheckCsrf(?string $token): bool
{
    return is_string($token) && $token !== '' && hash_equals(viewerCsrf(), $token);
}
