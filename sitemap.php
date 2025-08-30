<?php
$baseUrl = "https://wordofgod.in/bibles/";
$biblesFile = __DIR__ . "/data/bibles.json";

// Load JSON
$json = file_get_contents($biblesFile);
$data = json_decode($json, true);

if (!$data || !isset($data['bibles'])) {
    die("Invalid bibles.json");
}

$sitemapFiles = [];
$today = date('c'); // ISO 8601 date, e.g., 2025-08-27T10:30:00+05:30

foreach ($data['bibles'] as $bible) {
    $abbr = $bible['info']['abbr'];
    $shortName = $bible['info']['shortName'];
    $filename = "sitemap-$abbr.xml";
    $sitemapFiles[] = $filename;

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . 
                                 '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

    foreach ($bible['books'] as $book) {
        $bookName = $book['longName'];
        $chapters = $book['chapterCount'];

        for ($ch = 1; $ch <= $chapters; $ch++) {
            $url = $baseUrl . "?bible=" . urlencode($shortName) . 
                   "&book=" . urlencode($bookName) . 
                   "&chapter=" . $ch;

            $u = $xml->addChild('url');
            $u->addChild('loc', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
            $u->addChild('lastmod', $today);
            $u->addChild('changefreq', 'weekly');
            $u->addChild('priority', '0.8');
        }
    }

    $xml->asXML($filename);
    echo "Generated $filename\n";
}

// Create master sitemap.xml
$index = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . 
                              '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');

foreach ($sitemapFiles as $file) {
    $sm = $index->addChild('sitemap');
    $sm->addChild('loc', $baseUrl . $file);
    $sm->addChild('lastmod', $today);
}

$index->asXML("sitemap.xml");
echo "Generated sitemap.xml\n";
