<?php
/**
 * Admin screen — Google-signed-in admins only (see viewerAdmins() in includes/app-config.php:
 * yesudas.rs@gmail.com plus the emails in config.php 'admins'; nobody else gets in).
 * Views: ?view=tracking (default) | users | sitemap
 */
require_once __DIR__ . '/includes/google-auth-client.php';
require_once __DIR__ . '/includes/counters.php';
require_once __DIR__ . '/includes/books.php';
require_once __DIR__ . '/includes/admin-users.php';

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

$user = viewerGoogleUser();
$isAdmin = viewerIsAdmin($user);
$logoutUrl = $user ? viewerGoogleLogoutUrl(viewerCurrentUrl()) : null;
if ($user) {
    viewerUserTouch($user, $isAdmin); // Users tab: remember who opened (or tried to open) this screen
}

$views = ['tracking' => ['Tracking', 'bi-bar-chart-line'], 'users' => ['Users', 'bi-people'], 'sitemap' => ['Sitemap', 'bi-diagram-3']];
$view = isset($views[$_GET['view'] ?? '']) ? (string) $_GET['view'] : 'tracking';

/** Page shell (head + top bar); $bodyHtml-free so the caller echoes content then calls adminFoot(). */
function adminHead(string $title, ?array $user, ?string $logoutUrl): void
{
    ?><!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= h($title) ?></title>
    <link rel="icon" type="image/png" href="images/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .metric { text-align: center; padding: .9rem .5rem; }
        .metric-n { display: block; font-size: 1.7rem; font-weight: 600; line-height: 1.1; }
        .metric-l { display: block; font-size: .8rem; color: var(--bs-secondary-color); margin-top: .25rem; }
        .metric-s { display: block; font-size: .72rem; color: var(--bs-secondary-color); }
        .bar-cell { width: 38%; }
        .bar { height: .55rem; border-radius: .3rem; background: var(--bs-primary); min-width: 2px; }
        .table-scroll { max-height: 26rem; overflow: auto; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .test-badge { background: #b45309; color: #fff; font-size: .7rem; padding: .15rem .45rem; border-radius: .3rem; }

        /* ── Indigo style ── */
        :root {
            --a-accent: #4f46e5; --a-on-accent: #ffffff;
            --bs-body-bg: #f3f2ff; --bs-body-color: #1e1b4b; --bs-emphasis-color: #1e1b4b;
            --bs-secondary-color: #6366a1; --bs-border-color: #dcdaf7;
            --bs-primary: #4f46e5; --bs-primary-rgb: 79,70,229;
            --bs-link-color: #4f46e5; --bs-link-color-rgb: 79,70,229;
            --bs-link-hover-color: #4338ca; --bs-link-hover-color-rgb: 67,56,202;
            --bs-secondary-bg: #ffffff; --bs-tertiary-bg: #ffffff;
        }
        body { background: var(--bs-body-bg); color: var(--bs-body-color); }
        .card { --bs-card-bg: #ffffff; --bs-card-border-color: var(--bs-border-color); border-radius: 1rem; box-shadow: 0 4px 12px rgba(79,70,229,.12); }
        .table { --bs-table-bg: transparent; --bs-table-color: var(--bs-body-color); --bs-table-border-color: var(--bs-border-color); }
        .navbar { background: linear-gradient(90deg, #4338ca, #7c3aed); color: #ffffff; border-color: transparent !important; }
        .navbar .navbar-brand, .navbar .text-secondary { color: #ffffff !important; }
        .navbar .btn-outline-secondary { --bs-btn-color: #ffffff; --bs-btn-border-color: rgba(255,255,255,.6);
            --bs-btn-hover-color: #ffffff; --bs-btn-hover-bg: rgba(255,255,255,.2); --bs-btn-hover-border-color: rgba(255,255,255,.6);
            --bs-btn-active-color: #ffffff; --bs-btn-active-bg: rgba(255,255,255,.3); }
        .btn-primary { --bs-btn-bg: #4f46e5; --bs-btn-border-color: #4f46e5; --bs-btn-color: #ffffff;
            --bs-btn-hover-bg: #4338ca; --bs-btn-hover-border-color: #4338ca; --bs-btn-hover-color: #ffffff; }
        .nav-tabs { --bs-nav-tabs-link-active-bg: #ffffff; --bs-nav-tabs-link-active-color: #4f46e5;
            --bs-nav-tabs-link-active-border-color: var(--bs-border-color) var(--bs-border-color) #ffffff; --bs-nav-tabs-border-color: var(--bs-border-color); }
        .metric-n { color: #4f46e5; }
        .form-control { background: #ffffff; border-color: var(--bs-border-color); }
    </style>
</head>
<body>
<nav class="navbar border-bottom mb-3">
    <div class="container-xl">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-shield-lock me-1"></i>Online Bibles · Admin</span>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if (viewerTestMode()): ?><span class="test-badge" title="Testing mode: signed in automatically">TEST MODE</span><?php endif; ?>
            <?php if ($user): ?>
                <span class="small text-secondary d-none d-sm-inline"><?php if (!empty($user['picture'])): ?><img src="<?= h($user['picture']) ?>" alt="" width="22" height="22" class="rounded-circle me-1" referrerpolicy="no-referrer"><?php endif; ?><?= h($user['name'] ?? $user['email']) ?></span>
            <?php endif; ?>
            <a class="btn btn-sm btn-outline-secondary" href="./"><i class="bi bi-arrow-left"></i> Site</a>
            <?php if ($logoutUrl): ?><a class="btn btn-sm btn-outline-secondary" href="<?= h($logoutUrl) ?>"><i class="bi bi-box-arrow-right"></i> Sign out</a><?php endif; ?>
        </div>
    </div>
</nav>
<main class="container-xl pb-5">
<?php
}

function adminFoot(): void
{
    ?></main>
</body>
</html>
<?php
}

// ── Access control: signed in AND on the admin list (config.php) ───────────
if (!$isAdmin) {
    http_response_code($user ? 403 : 401);
    adminHead('Admin - Online Bibles', $user, $logoutUrl);
    echo '<div class="text-center py-5"><i class="bi bi-shield-lock display-4 text-secondary"></i>';
    if (!$user) {
        $login = viewerGoogleLoginUrl(viewerCurrentUrl());
        echo '<p class="mt-3">Please sign in with an admin Google account.</p>';
        echo $login
            ? '<a class="btn btn-primary" href="' . h($login) . '"><i class="bi bi-google me-1"></i> Sign in with Google</a>'
            : '<p class="text-secondary small">The sign-in service (../auth) is not available on this server.</p>';
    } else {
        echo '<p class="mt-3">' . h($user['email']) . (viewerIsBlocked((string) $user['email']) ? ' has been blocked from this page.' : ' does not have access to this page.') . '</p>';
    }
    echo '</div>';
    adminFoot();
    exit;
}

// ── Helpers for the views ──────────────────────────────────────────────────
/** Table for a keyed counter; $labels maps key → display text. */
function adminMapTable(string $title, array $items, int $limit = 15, array $labels = [], string $note = ''): void
{
    $total = array_sum($items);
    $max = $items ? max($items) : 0;
    ?><div class="col-12 col-md-6 col-xl-4"><div class="card h-100"><div class="card-body">
        <h2 class="h6 card-title"><?= h($title) ?> <?= $total ? '<span class="text-secondary fw-normal">(' . number_format($total) . ')</span>' : '' ?></h2>
    <?php if (!$items): ?><p class="text-secondary small mb-0">No data yet.</p><?php else: ?>
        <div class="table-scroll"><table class="table table-sm align-middle mb-0"><tbody>
        <?php foreach (array_slice($items, 0, $limit, true) as $k => $n): ?>
            <tr><td class="text-truncate" style="max-width:11rem"><?= h((string) ($labels[$k] ?? $k)) ?></td>
                <td class="bar-cell d-none d-sm-table-cell"><div class="bar" style="width:<?= $max ? max(2, round($n * 100 / $max)) : 0 ?>%"></div></td>
                <td class="num"><?= number_format($n) ?></td><td class="num text-secondary small"><?= $total ? round($n * 100 / $total) : 0 ?>%</td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php if (count($items) > $limit): ?><p class="text-secondary small mb-0 mt-1">Top <?= $limit ?> of <?= count($items) ?>.</p><?php endif; ?>
    <?php endif; ?>
    <?php if ($note !== ''): ?><p class="text-secondary small mb-0 mt-2"><?= h($note) ?></p><?php endif; ?>
    </div></div></div><?php
}

function adminMetric(string $label, $value, string $sub = ''): void
{
    ?><div class="col-6 col-md-4 col-xl-3"><div class="card h-100 metric"><span class="metric-n"><?= is_numeric($value) ? number_format((int) $value) : h((string) $value) ?></span><span class="metric-l"><?= h($label) ?></span><?php if ($sub !== ''): ?><span class="metric-s"><?= h($sub) ?></span><?php endif; ?></div></div><?php
}

$csrf = viewerCsrf();
$go = static function (string $to, string $msg = '', bool $err = false): void {
    header('Location: a.php?view=' . $to . ($msg !== '' ? '&' . ($err ? 'err' : 'msg') . '=' . urlencode($msg) : ''));
    exit;
};

// ── POST actions (all CSRF-checked) ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!viewerCheckCsrf($_POST['csrf'] ?? null)) {
        $go($view, 'Session expired — please try again.', true);
    }
    $target = viewerUserRead((string) preg_replace('/[^a-f0-9]/', '', (string) ($_POST['uid'] ?? '')));
    $isSelf = $target && strtolower((string) $target['email']) === strtolower((string) $user['email']);
    switch ((string) ($_POST['action'] ?? '')) {
        case 'user_block':
        case 'user_unblock':
            if (!$target) { $go('users', 'User not found.', true); }
            if ($isSelf) { $go('users', 'You cannot block your own account.', true); }
            if (viewerIsDefaultAdmin((string) $target['email'])) { $go('users', 'The default admin cannot be blocked.', true); }
            $block = $_POST['action'] === 'user_block';
            $go('users', viewerUserSetBlocked(viewerUserId((string) $target['email']), $block) ? ($block ? 'User blocked.' : 'User unblocked.') : 'Could not update the user.', false);
        case 'user_delete':
            if (!$target) { $go('users', 'User not found.', true); }
            if ($isSelf) { $go('users', 'You cannot delete your own record.', true); }
            $go('users', viewerUserDelete(viewerUserId((string) $target['email'])) ? 'Record deleted. (Accounts listed in config.php reappear when they next visit.)' : 'Could not delete the record.', false);
    }
    $go($view);
}

$msg = (string) ($_GET['msg'] ?? '');
$err = (string) ($_GET['err'] ?? '');
$hidden = static function (string $action, string $uid = '') use ($csrf): string {
    return '<input type="hidden" name="action" value="' . h($action) . '"><input type="hidden" name="csrf" value="' . h($csrf) . '">'
        . ($uid !== '' ? '<input type="hidden" name="uid" value="' . h($uid) . '">' : '');
};

adminHead('Admin - Online Bibles', $user, $logoutUrl);
?>
<ul class="nav nav-tabs mb-3">
<?php foreach ($views as $k => [$label, $icon]): ?>
    <li class="nav-item"><a class="nav-link<?= $k === $view ? ' active' : '' ?>" href="a.php?view=<?= $k ?>"><i class="bi <?= $icon ?> me-1"></i><?= h($label) ?></a></li>
<?php endforeach; ?>
</ul>
<?php if ($msg !== ''): ?><div class="alert alert-success py-2"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err !== ''): ?><div class="alert alert-danger py-2"><?= h($err) ?></div><?php endif; ?>
<?php

// ═══ Tracking ══════════════════════════════════════════════════════════════
if ($view === 'tracking'):
    $loadsByDay = viewerReadDayMap('visits-by-day');
    $uniqueByDay = viewerReadDayMap('unique-by-day');
    $chaptersByDay = viewerReadDayMap('chapters-by-day');
    $today = date('Y-m-d');
    $sumDays = static function (array $map, int $days): int {
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        return array_sum(array_filter($map, static function ($k) use ($from) { return $k >= $from; }, ARRAY_FILTER_USE_KEY));
    };
    $new = viewerReadCounter('visitors-new');
    $returning = viewerReadCounter('visitors-returning');
    $bookLabels = [];
    foreach (VIEWER_BOOK_NAMES as $no => $name) { $bookLabels[(string) $no] = $name; }
    $featureLabels = [
        'verse-copy' => 'Copy button clicked', 'verse-study' => 'Study button clicked (next to verse)', 'verse-quiz' => 'Quiz button clicked (next to verse)',
        'word-study' => 'Word clicked (concordance opened)', 'crossref-navigate' => 'Cross-reference jump',
        'zoom' => 'Zoom / font size', 'theme-change' => 'Theme change', 'install-app' => 'App installed',
        'tab-concordance' => 'Modal tab clicked: Concordance', 'tab-dictionary' => 'Modal tab clicked: Dictionary', 'tab-devotions' => 'Modal tab clicked: Devotions',
        'tab-commentary' => 'Modal tab clicked: Commentary', 'tab-crossreferences' => 'Modal tab clicked: Cross references', 'tab-quiz' => 'Modal tab clicked: Quiz',
    ];
?>
<div class="row g-2 mb-3">
<?php
    adminMetric('Page loads (all-time)', viewerReadCounter('counter'), 'shown as “Visitors” in the site footer');
    adminMetric('Unique visitors (all-time)', viewerReadCounter('unique-visitors'), 'a browser counts once per day · since tracking began');
    adminMetric('Page loads today', $loadsByDay[$today] ?? 0);
    adminMetric('Unique visitors today', $uniqueByDay[$today] ?? 0);
    adminMetric('Unique visitors, last 7 days', $sumDays($uniqueByDay, 7));
    adminMetric('Unique visitors, last 30 days', $sumDays($uniqueByDay, 30));
    adminMetric('Chapters viewed', viewerReadCounter('chapters-viewed'), 'last 30 days: ' . number_format($sumDays($chaptersByDay, 30)));
    adminMetric('New / returning visitors', number_format($new) . ' / ' . number_format($returning), 'daily-unique visits by first-time vs known browsers');
    adminMetric('Crawler hits', viewerReadCounter('bots'), 'not included in any visitor number');
    $fu = viewerReadMap('feature-usage');
    adminMetric('Copy button clicks', $fu['verse-copy'] ?? 0);
    adminMetric('Study button clicks', $fu['verse-study'] ?? 0, 'next to each verse');
    adminMetric('Quiz button clicks', $fu['verse-quiz'] ?? 0, 'next to each verse');
    adminMetric('Quiz tab clicks', $fu['tab-quiz'] ?? 0, 'inside the modal panel');
?>
</div>

<div class="card mb-3"><div class="card-body">
    <h2 class="h6 card-title">Last 30 days</h2>
    <div class="table-scroll"><table class="table table-sm align-middle mb-0">
        <thead><tr><th>Date</th><th class="num">Page loads</th><th class="num">Unique visitors</th><th class="bar-cell d-none d-sm-table-cell"></th><th class="num">Chapters viewed</th></tr></thead>
        <tbody>
        <?php
        $maxU = 0;
        $rows = [];
        for ($i = 0; $i < 30; $i++) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $rows[$d] = [$loadsByDay[$d] ?? 0, $uniqueByDay[$d] ?? 0, $chaptersByDay[$d] ?? 0];
            $maxU = max($maxU, $rows[$d][1]);
        }
        foreach ($rows as $d => [$l, $u, $c]): ?>
            <tr><td><?= h($d) ?><?= $d === $today ? ' <span class="badge text-bg-secondary">today</span>' : '' ?></td><td class="num"><?= number_format($l) ?></td><td class="num"><?= number_format($u) ?></td>
                <td class="bar-cell d-none d-sm-table-cell"><div class="bar" style="width:<?= $maxU ? max($u ? 2 : 0, round($u * 100 / $maxU)) : 0 ?>%"></div></td><td class="num"><?= number_format($c) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
</div></div>

<div class="card mb-3"><div class="card-body">
    <h2 class="h6 card-title">By month</h2>
    <?php
    $months = [];
    foreach ([['loads', $loadsByDay], ['unique', $uniqueByDay], ['chapters', $chaptersByDay]] as [$key, $map]) {
        foreach ($map as $d => $n) { $months[substr($d, 0, 7)][$key] = ($months[substr($d, 0, 7)][$key] ?? 0) + $n; }
    }
    krsort($months);
    ?>
    <?php if (!$months): ?><p class="text-secondary small mb-0">No data yet.</p><?php else: ?>
    <div class="table-scroll"><table class="table table-sm mb-0"><thead><tr><th>Month</th><th class="num">Page loads</th><th class="num">Unique visitors</th><th class="num">Chapters viewed</th></tr></thead><tbody>
    <?php foreach (array_slice($months, 0, 12, true) as $m => $v): ?>
        <tr><td><?= h($m) ?></td><td class="num"><?= number_format($v['loads'] ?? 0) ?></td><td class="num"><?= number_format($v['unique'] ?? 0) ?></td><td class="num"><?= number_format($v['chapters'] ?? 0) ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
</div></div>

<div class="row g-3">
<?php
    adminMapTable('Top books read', viewerReadMap('chapters-by-book'), 15, $bookLabels, 'Chapters viewed per book.');
    adminMapTable('Translations used', viewerReadMap('bibles-used'), 15, [], 'Counted each time a chapter is shown with that translation.');
    adminMapTable('Translation languages', viewerReadMap('bible-languages'), 15);
    adminMapTable('Translations per view', viewerReadMap('bibles-per-view'), 6, [], 'How many translations were shown side by side.');
    adminMapTable('Features used', viewerReadMap('feature-usage'), 15, $featureLabels);
    adminMapTable('Quiz opened by book', viewerReadMap('quiz-by-book'), 15, $bookLabels, 'Quiz button + Quiz tab clicks (the quiz itself runs on wordofgod.in).');
    adminMapTable('Concordance: words opened', viewerReadMap('concordance-words'), 25, [], 'Word (or Strong’s number) clicked. Top 25.');
    adminMapTable('Dictionaries expanded', viewerReadMap('dictionary-expands'), 15, [], 'A dictionary section opened from collapsed.');
    adminMapTable('Devotions expanded (by brand)', viewerReadMap('devotion-expands'), 15, [], 'A devotion opened from collapsed.');
    adminMapTable('Commentaries viewed', viewerReadMap('commentary-views'), 15, [], 'Counted when the tab is opened (default commentary) and when another is selected.');
    adminMapTable('Cross-reference sources', viewerReadMap('crossref-views'), 10, [], 'Counted when the tab is opened (default source) and when another is selected.');
    adminMapTable('Themes chosen', viewerReadMap('themes-used'), 10);
    adminMapTable('Devices', viewerReadMap('visits-by-device'), 5, [], 'Per unique daily visitor.');
    adminMapTable('Browsers', viewerReadMap('visits-by-browser'), 10);
    adminMapTable('Operating systems', viewerReadMap('visits-by-os'), 10);
    adminMapTable('Traffic sources', viewerReadMap('visits-by-referrer'), 15, [], '“direct” = no referrer; “utm:…” = ?utm_source= links.');
    adminMapTable('Crawlers', viewerReadMap('bots-by-name'), 15);
?>
</div>
<p class="text-secondary small mt-3">Counters live in <code>counters/</code> as JSON. They are anonymous (no IP addresses or user ids are stored); visitors are told apart only by a cookie holding two dates. Counting started when this feature was deployed — earlier history only exists as the all-time page-load total.</p>

<?php
// ═══ Users ═════════════════════════════════════════════════════════════════
elseif ($view === 'users'):
    $q = trim((string) ($_GET['q'] ?? ''));
    $all = viewerUsersAll();
    $list = $q === '' ? $all : array_values(array_filter($all, static function ($u) use ($q) {
        return stripos((string) $u['email'] . ' ' . (string) ($u['name'] ?? ''), $q) !== false;
    }));
    $sel = isset($_GET['u']) ? viewerUserRead((string) preg_replace('/[^a-f0-9]/', '', (string) $_GET['u'])) : null;
    $badge = static function (string $status): string {
        $cls = ['Admin (default)' => 'success', 'Admin (config)' => 'primary', 'Blocked' => 'danger', 'No access' => 'secondary'][$status] ?? 'secondary';
        return '<span class="badge text-bg-' . $cls . '">' . h($status) . '</span>';
    };
    $actions = static function (array $u) use ($hidden, $user): string {
        $id = viewerUserId((string) $u['email']);
        $self = strtolower((string) $u['email']) === strtolower((string) $user['email']);
        $html = '';
        if (!$self && !viewerIsDefaultAdmin((string) $u['email'])) {
            $blocked = !empty($u['blocked']);
            $html .= '<form method="post" class="d-inline" onsubmit="return confirm(\'' . ($blocked ? 'Unblock' : 'Block') . ' this account?\')">' . $hidden($blocked ? 'user_unblock' : 'user_block', $id)
                . '<button class="btn btn-sm btn-outline-' . ($blocked ? 'success' : 'warning') . '" type="submit"><i class="bi ' . ($blocked ? 'bi-unlock' : 'bi-slash-circle') . '"></i> ' . ($blocked ? 'Unblock' : 'Block') . '</button></form> ';
        }
        if (!$self) {
            $html .= '<form method="post" class="d-inline" onsubmit="return confirm(\'Delete this record?\')">' . $hidden('user_delete', $id)
                . '<button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> Delete</button></form>';
        }
        return $html;
    };
?>
<p class="small text-secondary">Google accounts that have opened or tried to open this admin screen. <strong>Access is decided only by <code>config.php</code></strong> (<code>admins</code>, plus the built-in default admin); here you can block a listed admin or clear a record.</p>
<form method="get" class="d-flex gap-2 align-items-center mb-3"><input type="hidden" name="view" value="users">
    <input class="form-control form-control-sm" style="max-width:20rem" type="search" name="q" value="<?= h($q) ?>" placeholder="Search name or email">
    <button class="btn btn-sm btn-outline-secondary" type="submit">Search</button>
    <span class="text-secondary small"><?= count($list) ?> account<?= count($list) === 1 ? '' : 's' ?></span></form>

<?php if ($sel): $st = viewerUserStatus($sel); ?>
<div class="card mb-3"><div class="card-body">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <?php if (!empty($sel['picture'])): ?><img src="<?= h((string) $sel['picture']) ?>" alt="" width="44" height="44" class="rounded-circle" referrerpolicy="no-referrer"><?php endif; ?>
        <div class="me-auto"><strong><?= h((string) ($sel['name'] ?? '') ?: (string) $sel['email']) ?></strong> <?= $badge($st) ?><br><span class="text-secondary small"><?= h((string) $sel['email']) ?></span></div>
        <a class="btn btn-sm btn-outline-secondary" href="a.php?view=users<?= $q !== '' ? '&amp;q=' . h(urlencode($q)) : '' ?>">Close</a>
    </div>
    <dl class="row small mb-2 mt-3">
        <dt class="col-sm-3">First seen</dt><dd class="col-sm-9"><?= h((string) ($sel['firstSeen'] ?? '—')) ?></dd>
        <dt class="col-sm-3">Last seen</dt><dd class="col-sm-9"><?= h((string) ($sel['lastSeen'] ?? '—')) ?></dd>
        <dt class="col-sm-3">Visits to admin</dt><dd class="col-sm-9"><?= number_format((int) ($sel['visits'] ?? 0)) ?></dd>
        <dt class="col-sm-3">Denied attempts</dt><dd class="col-sm-9"><?= number_format((int) ($sel['denied'] ?? 0)) ?></dd>
        <?php if (!empty($sel['blockedAt'])): ?><dt class="col-sm-3">Blocked on</dt><dd class="col-sm-9"><?= h((string) $sel['blockedAt']) ?></dd><?php endif; ?>
    </dl>
    <?= $actions($sel) ?>
</div></div>
<?php endif; ?>

<?php if (!$list): ?><p class="text-secondary">No accounts recorded yet.</p><?php else: ?>
<div class="table-responsive"><table class="table table-sm align-middle">
    <thead><tr><th>User</th><th>Email</th><th>Status</th><th class="num">Visits</th><th class="num">Denied</th><th>First seen</th><th>Last seen</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $u): $isSel = $sel && strtolower((string) $sel['email']) === strtolower((string) $u['email']); ?>
        <tr<?= $isSel ? ' class="table-active"' : '' ?>>
            <td><?php if (!empty($u['picture'])): ?><img src="<?= h((string) $u['picture']) ?>" alt="" width="24" height="24" class="rounded-circle me-1" referrerpolicy="no-referrer"><?php endif; ?>
                <a href="a.php?view=users&amp;u=<?= h(viewerUserId((string) $u['email'])) ?><?= $q !== '' ? '&amp;q=' . h(urlencode($q)) : '' ?>"><?= h((string) ($u['name'] ?? '') ?: '—') ?></a></td>
            <td class="small"><?= h((string) $u['email']) ?></td>
            <td><?= $badge(viewerUserStatus($u)) ?></td>
            <td class="num"><?= number_format((int) ($u['visits'] ?? 0)) ?></td><td class="num"><?= number_format((int) ($u['denied'] ?? 0)) ?></td>
            <td class="small"><?= h(substr((string) ($u['firstSeen'] ?? ''), 0, 16)) ?></td><td class="small"><?= h(substr((string) ($u['lastSeen'] ?? ''), 0, 16)) ?></td>
            <td class="text-nowrap"><?= $actions($u) ?></td>
        </tr>
    <?php endforeach; ?></tbody></table></div>
<?php endif; ?>

<?php
// ═══ Sitemap ═══════════════════════════════════════════════════════════════
else:
    $files = glob(__DIR__ . '/sitemap*.xml') ?: [];
    usort($files, static function ($a, $b) {
        $ia = basename($a) === 'sitemap.xml';
        $ib = basename($b) === 'sitemap.xml';
        return $ia === $ib ? strcmp($a, $b) : ($ia ? -1 : 1); // the index first
    });
    $indexFile = __DIR__ . '/sitemap.xml';
?>
<div class="card mb-3"><div class="card-body">
    <h2 class="h6 card-title">Generate sitemaps</h2>
    <p class="small text-secondary">Rebuilds <code>sitemap.xml</code> (the index), <code>sitemap-general.xml</code> and one <code>sitemap-&lt;BIBLE&gt;.xml</code> per visible Bible, with book, chapter and verse URLs. It reads every chapter file, so it can take a minute or more — leave the page open until it says “Done!”.</p>
    <form method="post" action="sitemap.php" class="d-inline"><input type="hidden" name="t" value="<?= h(viewerCsrf()) ?>"><button class="btn btn-primary" type="submit"><i class="bi bi-arrow-repeat me-1"></i> Generate all sitemaps</button></form>
    <p class="small text-secondary mt-3 mb-0">Last generated: <?= is_file($indexFile) ? h(date('Y-m-d H:i', filemtime($indexFile))) : 'never' ?>
        · Submit <code>https://www.wordofgod.in/bibles/sitemap.xml</code> in <a href="https://search.google.com/search-console/sitemaps" target="_blank" rel="noopener">Google Search Console</a> and <a href="https://www.bing.com/webmasters" target="_blank" rel="noopener">Bing Webmaster Tools</a>.</p>
</div></div>
<div class="card"><div class="card-body">
    <h2 class="h6 card-title">Generated files <span class="text-secondary fw-normal">(<?= count($files) ?>)</span></h2>
    <?php if (!$files): ?><p class="text-secondary small mb-0">No sitemap files yet — generate them above.</p><?php else: ?>
    <div class="table-scroll"><table class="table table-sm align-middle mb-0"><thead><tr><th>File</th><th class="num">Size</th><th>Modified</th></tr></thead><tbody>
    <?php foreach ($files as $f): ?>
        <tr><td><a href="<?= h(basename($f)) ?>" target="_blank" rel="noopener"><?= h(basename($f)) ?></a></td><td class="num"><?= number_format(filesize($f) / 1024 / 1024, 2) ?> MB</td><td><?= h(date('Y-m-d H:i', filemtime($f))) ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
</div></div>
<?php endif;
adminFoot();
