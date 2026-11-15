<?php
// counter.php — included at the top of index.php, before any output.
// Records the page load (visits, unique visitors, device/browser/OS/referrer, crawlers) into counters/*.txt
// — see includes/tracking.php. $visitors2 is the all-time page-load total shown in the footer.
require_once __DIR__ . '/includes/tracking.php';

$visitors2 = viewerTrackPageLoad();
