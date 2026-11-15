<?php
/**
 * Shared WordOfGod.in menu links, maintained by the site owner
 * (sibling folder ../shared/menus.json). If that file is missing or
 * invalid, the built-in list below is used so the site never loses its menu.
 * Used by index.php for the header nav and the footer.
 */

/** URL folder name of this app on the server (wordofgod.in/bibles/). Menu links to it are left out. */
const VIEWER_SELF_SLUG = 'bibles';

/** True when the URL's last path segment is this app, e.g. https://wordofgod.in/bibles/ */
function viewerIsSelfLink(string $url): bool
{
    $path = (string) parse_url($url, PHP_URL_PATH);
    return strtolower(basename(rtrim($path, '/'))) === VIEWER_SELF_SLUG;
}

/** @return list<array{label:string,url:string,icon:string,newTab:bool}> */
function viewerSharedMenus(): array
{
    static $items = null;
    if ($items !== null) {
        return $items;
    }
    $raw = [];
    $file = __DIR__ . '/../shared/menus.json';
    if (is_file($file)) {
        $j = json_decode((string) @file_get_contents($file), true);
        if (is_array($j) && is_array($j['items'] ?? null)) {
            $raw = $j['items'];
        }
    }
    $items = [];
    foreach ($raw as $i) {
        if (!is_array($i)) {
            continue;
        }
        if (array_key_exists('enabled', $i) && empty($i['enabled'])) {
            continue; // switched off by the site owner
        }
        $label = trim((string) ($i['label'] ?? ''));
        $url = trim((string) ($i['url'] ?? ''));
        $icon = (string) ($i['icon'] ?? '');
        // Re-validate: only http(s) or single-slash paths; icon must be a bi-* class.
        if ($label === '' || !preg_match('#^(https?://[^\s"\'<>]+|/[^/\s"\'<>]\S*|/)$#i', $url)) {
            continue;
        }
        if (viewerIsSelfLink($url)) {
            continue; // don't link the app to itself
        }
        $items[] = [
            'label' => $label,
            'url' => $url,
            'icon' => preg_match('/^bi-[a-z0-9-]{1,40}$/', $icon) ? $icon : '',
            'newTab' => !empty($i['newTab']),
        ];
    }
    if (!$items) {
        foreach ([
            ['About Us', 'https://www.wordofgodteam.com/', 'bi-people'],
            ['Good News Collections', 'https://wordofgod.in/good-news-collections/', 'bi-box-seam'],
            ['Bible Dictionaries', 'https://wordofgod.in/bibledictionary/', 'bi-collection'],
            ['Bible Concordances', 'https://wordofgod.in/bible-concordance/', 'bi-search'],
            ['Bible Wallpapers', 'https://wordofgod.in/bible-wallpapers/', 'bi-card-image'],
            ['Bible App Modules', 'https://wordofgod.in/bible-app-modules/', 'bi-phone'],
            ['Free Christian Resources', 'https://wordofgod.in/', 'bi-gift'],
        ] as [$l, $u, $ic]) {
            $items[] = ['label' => $l, 'url' => $u, 'icon' => $ic, 'newTab' => true];
        }
    }
    return $items;
}

/** One escaped <a> for a menu item; $class is the link class, $iconClass the icon spacing (e.g. 'me-1'). */
function viewerMenuLink(array $it, string $class, string $iconClass = 'me-1'): string
{
    return '<a class="' . htmlspecialchars($class) . '" href="' . htmlspecialchars($it['url']) . '"'
        . ($it['newTab'] ? ' target="_blank" rel="noopener"' : '') . '>'
        . ($it['icon'] !== '' ? '<i class="bi ' . htmlspecialchars($it['icon']) . ' ' . $iconClass . '"></i>' : '')
        . htmlspecialchars($it['label']) . '</a>';
}
