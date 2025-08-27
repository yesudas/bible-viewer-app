<?php
// counter.php

// List of common bot keywords in User-Agent
$botKeywords = [
    'bot', 'crawl', 'slurp', 'spider', 'mediapartners', 'curl', 'python', 'wget', 'baiduspider', 'bingpreview', 'facebookexternalhit', 'pingdom'
];

// Get lowercase user agent
$userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

// Check if it's a bot
$isBot = false;
foreach ($botKeywords as $keyword) {
    if (strpos($userAgent, $keyword) !== false) {
        $isBot = true;
        break;
    }
}

$visitors2 = '1';

// If not a bot, increment the counter
if (!$isBot) {
    $dbFile = __DIR__ . '/data/counter.db';
    
    $db = new SQLite3($dbFile);

    $db->exec('CREATE TABLE IF NOT EXISTS visits (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        count TEXT
    )');

    $result = $db->querySingle('SELECT count FROM visits WHERE id=1');

    if ($result === null) {
        $visitors2 = '1';
        $stmt = $db->prepare('INSERT INTO visits (id, count) VALUES (1, :count)');
        $stmt->bindValue(':count', $visitors2, SQLITE3_TEXT);
        $stmt->execute();
    } else {
        $visitors2 = bcadd($result, '1');
        $stmt = $db->prepare('UPDATE visits SET count = :count WHERE id=1');
        $stmt->bindValue(':count', $visitors2, SQLITE3_TEXT);
        $stmt->execute();
    }

    $db->close();
} else {
    // For bots, show the last count without incrementing
    $dbFile = __DIR__ . '/data/counter.db';
    $visitors2 = '0';
    if (file_exists($dbFile)) {
        $db = new SQLite3($dbFile);
        $visitors2 = $db->querySingle('SELECT count FROM visits WHERE id=1');
        $db->close();
    }
}

?>