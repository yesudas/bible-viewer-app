<?php
// track.php — receives small anonymous usage events from js/app.js (chapter viewed, feature used…).
// POST only, same-origin only, tiny JSON body; crawlers are ignored. Always answers 204 (the client never waits on it).
require_once __DIR__ . '/includes/tracking.php';

header('Cache-Control: no-store');
http_response_code(204);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    exit;
}
// Same-origin check: the Origin (or, failing that, Referer) host must be this site.
$from = (string) ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
if ($from === '' || strcasecmp((string) parse_url($from, PHP_URL_HOST), preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? ''))) !== 0) {
    exit;
}
if (viewerBotName((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')) !== null) {
    exit;
}
if (viewerRateLimited('track', 120)) { // 120 events per minute per client is far beyond real use
    exit;
}
$body = (string) file_get_contents('php://input', false, null, 0, 2048);
$event = json_decode($body, true);
if (is_array($event)) {
    viewerRecordEvent($event);
}
