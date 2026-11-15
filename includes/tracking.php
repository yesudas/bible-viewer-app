<?php

/**
 * Anonymous usage tracking → JSON counters (see includes/counters.php).
 * Nothing personal is stored: no IP addresses, no user ids, only aggregate counts.
 * Visitors are told apart by a first-party cookie holding just two dates (first visit | last visit).
 *
 * Counter files written (all in counters/):
 *   counter                 page loads, all-time           bots / bots-by-name       crawler hits
 *   visits-by-day           page loads per day             unique-visitors           unique visitors, all-time (a visitor counts once per day)
 *   unique-by-day           unique visitors per day        visitors-new / visitors-returning
 *   visits-by-device|browser|os|referrer   once per unique visitor per day
 *   chapters-viewed / chapters-by-day / chapters-by-book   reading activity (sent by js/app.js → track.php)
 *   bibles-used / bible-languages / bibles-per-view        translation usage
 *   feature-usage / themes-used / quiz-by-book             feature usage (verse-copy/study/quiz = buttons next to a verse, tab-* = modal tabs clicked)
 *   concordance-words / dictionary-expands / devotion-expands / commentary-views / crossref-views   per-item counts
 */
require_once __DIR__ . '/counters.php';

const VIEWER_DAY_MAP_MAX_KEYS = 800; // ~2 years of daily history

/** Same keyword list the old counter.php used. */
function viewerBotKeywords(): array
{
    return ['bot', 'crawl', 'slurp', 'spider', 'mediapartners', 'curl', 'python', 'wget', 'baiduspider', 'bingpreview', 'facebookexternalhit', 'pingdom'];
}

/** Crawler name (lower-case, safe as a map key) or null for a normal visitor. */
function viewerBotName(string $userAgent): ?string
{
    $ua = strtolower(trim($userAgent));
    if ($ua === '') {
        return 'no-user-agent';
    }
    foreach (viewerBotKeywords() as $kw) {
        if (strpos($ua, $kw) !== false) {
            if (preg_match('/([a-z0-9._-]*(?:bot|crawl|spider|slurp)[a-z0-9._-]*)/', $ua, $m)) {
                return substr($m[1], 0, 30);
            }
            return $kw;
        }
    }
    return null;
}

function viewerDeviceType(string $ua): string
{
    if (preg_match('/ipad|tablet|kindle|silk|playbook/i', $ua) || (stripos($ua, 'android') !== false && stripos($ua, 'mobile') === false)) {
        return 'Tablet';
    }
    if (preg_match('/mobi|iphone|ipod|android|windows phone/i', $ua)) {
        return 'Mobile';
    }
    return 'Desktop';
}

function viewerBrowserName(string $ua): string
{
    if (preg_match('/SamsungBrowser/i', $ua)) { return 'Samsung Internet'; }
    if (preg_match('/Edg(e|A|iOS)?\//i', $ua)) { return 'Edge'; }
    if (preg_match('/OPR\/|Opera/i', $ua)) { return 'Opera'; }
    if (preg_match('/Firefox\/|FxiOS/i', $ua)) { return 'Firefox'; }
    if (preg_match('/Chrome\/|CriOS/i', $ua)) { return 'Chrome'; }
    if (preg_match('/Safari\//i', $ua)) { return 'Safari'; }
    return 'Other';
}

function viewerOsName(string $ua): string
{
    if (preg_match('/Windows/i', $ua)) { return 'Windows'; }
    if (preg_match('/Android/i', $ua)) { return 'Android'; }
    if (preg_match('/iPhone|iPad|iPod/i', $ua)) { return 'iOS'; }
    if (preg_match('/Mac OS X|Macintosh/i', $ua)) { return 'macOS'; }
    if (preg_match('/CrOS/i', $ua)) { return 'ChromeOS'; }
    if (preg_match('/Linux/i', $ua)) { return 'Linux'; }
    return 'Other';
}

/** Where the visitor came from: utm_source, else the referring host, else "direct". Safe as a map key. */
function viewerReferrerName(): string
{
    $utm = strtolower(preg_replace('/[^a-z0-9._-]/i', '', (string) ($_GET['utm_source'] ?? '')));
    if ($utm !== '') {
        return 'utm:' . substr($utm, 0, 30);
    }
    $host = strtolower((string) parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST));
    $host = preg_replace('/^www\./', '', preg_replace('/[^a-z0-9.-]/', '', $host));
    $own = strtolower(preg_replace('/^www\.|:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    if ($host === '' || $host === $own) {
        return 'direct';
    }
    return substr($host, 0, 60);
}

/** Fallback import of the old root counter.txt (pre-counters/ layout); only used while counters/counter.txt does not exist yet. */
function viewerSeedLegacyCounter(): void
{
    if (is_file(viewerCounterFile('counter'))) {
        return;
    }
    $legacy = __DIR__ . '/../counter.txt';
    $n = is_file($legacy) ? (int) trim((string) file_get_contents($legacy)) : 0;
    if ($n > 0) {
        viewerIncrementCounter('counter', $n);
    }
}

/**
 * Records one page load (call before any output — it may set a cookie). Returns the all-time page-load total.
 * Crawlers are counted separately and never touch the visitor numbers.
 */
function viewerTrackPageLoad(): int
{
    viewerSeedLegacyCounter();
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

    $bot = viewerBotName($ua);
    if ($bot !== null) {
        viewerIncrementCounter('bots');
        viewerIncrementMap('bots-by-name', [$bot => 1], 300, true); // names come from the User-Agent: keep the map bounded
        return viewerReadCounter('counter');
    }

    $today = date('Y-m-d');
    $total = viewerIncrementCounter('counter');
    viewerIncrementMap('visits-by-day', [$today => 1], VIEWER_DAY_MAP_MAX_KEYS);

    $first = $last = '';
    if (preg_match('/^(\d{4}-\d{2}-\d{2})\|(\d{4}-\d{2}-\d{2})$/', (string) ($_COOKIE['bv_seen'] ?? ''), $m)) {
        [$first, $last] = [$m[1], $m[2]];
    }
    $isNew = $first === '';
    if ($isNew || $last !== $today) { // first load of the day for this browser = one unique visitor
        viewerIncrementCounter('unique-visitors');
        viewerIncrementMap('unique-by-day', [$today => 1], VIEWER_DAY_MAP_MAX_KEYS);
        viewerIncrementCounter($isNew ? 'visitors-new' : 'visitors-returning');
        viewerIncrementMap('visits-by-device', [viewerDeviceType($ua) => 1]);
        viewerIncrementMap('visits-by-browser', [viewerBrowserName($ua) => 1]);
        viewerIncrementMap('visits-by-os', [viewerOsName($ua) => 1]);
        viewerIncrementMap('visits-by-referrer', [viewerReferrerName() => 1], 500, true); // Referer / utm_source are client-controlled: keep the map bounded
        if (!headers_sent()) {
            setcookie('bv_seen', ($isNew ? $today : $first) . '|' . $today, [
                'expires' => time() + 400 * 86400,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }
    return $total;
}

/** abbr => language, for every Bible in data/languages.json (the whitelist for client-sent Bible names). */
function viewerBibleLanguages(): array
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $d = json_decode((string) @file_get_contents(__DIR__ . '/../data/languages.json'), true);
        foreach ((array) ($d['biblesByLanguage'] ?? []) as $lang => $info) {
            foreach ((array) ($info['bibles'] ?? []) as $b) {
                if (!empty($b['abbr'])) {
                    $map[(string) $b['abbr']] = (string) $lang;
                }
            }
        }
    }
    return $map;
}

/** Feature names the client may report (anything else is ignored). */
function viewerFeatureNames(): array
{
    return ['word-study', 'verse-copy', 'verse-study', 'verse-quiz', 'crossref-navigate', 'zoom', 'theme-change', 'install-app',
        'tab-concordance', 'tab-dictionary', 'tab-devotions', 'tab-commentary', 'tab-crossreferences', 'tab-quiz'];
}

function viewerThemeNames(): array
{
    return ['light', 'light-gray', 'gray', 'sepia', 'dark', 'warm-dark', 'true-black', 'auto'];
}

/** Translations in one view: "1", "2", "3+" … @param list<string> $bibles */
function viewerRecordBibles(array $bibles): void
{
    if (!$bibles) {
        return;
    }
    $languages = viewerBibleLanguages();
    $used = [];
    $langs = [];
    foreach ($bibles as $abbr) {
        $used[$abbr] = 1;
        $langs[$languages[$abbr]] = ($langs[$languages[$abbr]] ?? 0) + 1;
    }
    viewerIncrementMap('bibles-used', $used);
    viewerIncrementMap('bible-languages', $langs);
    viewerIncrementMap('bibles-per-view', [count($bibles) >= 4 ? '4+' : (string) count($bibles) => 1]);
}

/**
 * Simple per-IP rate limit for public endpoints: true when $bucket was hit more than $max times by this client in the
 * current $window seconds. Uses short-lived files in the system temp dir named by a salted hash (no IP is stored);
 * if the temp dir isn't writable it fails open.
 */
function viewerRateLimited(string $bucket, int $max, int $window = 60): bool
{
    $dir = sys_get_temp_dir() . '/viewer-rate';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
        return false;
    }
    $slot = intdiv(time(), $window);
    $file = $dir . '/' . hash('sha256', __DIR__ . '|' . $bucket . '|' . (string) ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $slot);
    $fh = @fopen($file, 'c+');
    if (!$fh) {
        return false;
    }
    flock($fh, LOCK_EX);
    $n = (int) stream_get_contents($fh) + 1;
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, (string) $n);
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    if (mt_rand(1, 100) === 1) { // occasional tidy-up of expired slots
        foreach (glob($dir . '/*') ?: [] as $f) {
            if (filemtime($f) < time() - 3 * $window) { @unlink($f); }
        }
    }
    return $n > $max;
}

/** Validates one client event (decoded JSON) and records it. Returns false if it was rejected. */
function viewerRecordEvent(array $e): bool
{
    $type = (string) ($e['type'] ?? '');
    $languages = viewerBibleLanguages();
    $bibles = [];
    foreach (array_slice((array) ($e['bibles'] ?? []), 0, 8) as $abbr) {
        if (is_string($abbr) && isset($languages[$abbr]) && !isset($bibles[$abbr])) {
            $bibles[$abbr] = $abbr;
        }
    }
    $bibles = array_values($bibles);

    if ($type === 'chapter') {
        $book = (int) ($e['book'] ?? 0);
        $chapter = (int) ($e['chapter'] ?? 0);
        if ($book < 1 || $book > 66 || $chapter < 1 || $chapter > 150) {
            return false;
        }
        viewerIncrementCounter('chapters-viewed');
        viewerIncrementMap('chapters-by-day', [date('Y-m-d') => 1], VIEWER_DAY_MAP_MAX_KEYS);
        viewerIncrementMap('chapters-by-book', [(string) $book => 1]);
        viewerRecordBibles($bibles);
        return true;
    }
    if ($type === 'bibles') {
        viewerRecordBibles($bibles);
        return (bool) $bibles;
    }
    if ($type === 'feature') {
        $name = (string) ($e['name'] ?? '');
        if (!in_array($name, viewerFeatureNames(), true)) {
            return false;
        }
        viewerIncrementMap('feature-usage', [$name => 1]);
        if ($name === 'theme-change' && in_array((string) ($e['value'] ?? ''), viewerThemeNames(), true)) {
            viewerIncrementMap('themes-used', [(string) $e['value'] => 1]);
        }
        $book = (int) ($e['book'] ?? 0);
        if (in_array($name, ['tab-quiz', 'verse-quiz'], true) && $book >= 1 && $book <= 66) {
            viewerIncrementMap('quiz-by-book', [(string) $book => 1]);
        }
        return true;
    }
    if ($type === 'item') {
        return viewerRecordItem((string) ($e['kind'] ?? ''), (string) ($e['key'] ?? ''));
    }
    return false;
}

/** kind => [counter file, max keys kept]. Keys come from the client, so they are sanitised and the maps are bounded. */
function viewerItemKinds(): array
{
    return [
        'concordance-word' => ['concordance-words', 3000],
        'dictionary' => ['dictionary-expands', 200],
        'devotion' => ['devotion-expands', 200],
        'commentary' => ['commentary-views', 200],
        'crossref' => ['crossref-views', 50],
    ];
}

/** Counts one use of a word / dictionary / devotion brand / commentary / cross-reference source. */
function viewerRecordItem(string $kind, string $key): bool
{
    $kinds = viewerItemKinds();
    if (!isset($kinds[$kind])) {
        return false;
    }
    $key = trim($key);
    if ($kind === 'concordance-word') {
        // A word or a Strong's number: letters/marks/digits (any script), apostrophe, hyphen; max 40 chars.
        $key = mb_strtolower(preg_replace('/[^\p{L}\p{M}\p{N}\'-]/u', '', $key));
        if ($key === '' || mb_strlen($key) > 40) {
            return false;
        }
        if (preg_match('/^[hg]\d{1,5}$/', $key)) {
            $key = strtoupper($key); // Strong's number, e.g. H7225
        }
    } elseif (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,59}$/', $key)) { // a slug such as naves-bible-dictionary, GNTBC, OB
        return false;
    }
    viewerIncrementMap($kinds[$kind][0], [$key => 1], $kinds[$kind][1], true);
    return true;
}
