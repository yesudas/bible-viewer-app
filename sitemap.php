<?php
$baseUrl = "https://wordofgod.in/bibles/";
$languagesFile = __DIR__ . "/data/languages.json";

// Load JSON
$json = file_get_contents($languagesFile);
$data = json_decode($json, true);

if (!$data || !isset($data['biblesByLanguage'])) {
    die("Invalid languages.json");
}

$sitemapFiles = [];
$today = date('c'); // ISO 8601 date, e.g., 2025-08-27T10:30:00+05:30

// Loop through each language and its bibles
foreach ($data['biblesByLanguage'] as $language => $langData) {
    if (!isset($langData['bibles'])) {
        continue;
    }
    
    foreach ($langData['bibles'] as $bible) {
        $abbr = $bible['abbr'];
        
        // Skip hidden bibles
        if (isset($bible['hide']) && $bible['hide']) {
            continue;
        }
        
        $filename = "sitemap-$abbr.xml";
        $sitemapFiles[] = $filename;

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . 
                                     '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        // Load the bible's books data
        $bibleFile = __DIR__ . "/data/$language/$abbr/bibles.json";
        
        if (!file_exists($bibleFile)) {
            echo "Warning: Bible file not found for $abbr ($language)\n";
            continue;
        }
        
        $bibleData = json_decode(file_get_contents($bibleFile), true);
        
        if (!isset($bibleData['bibles'][0]['books'])) {
            echo "Warning: Books data not found for $abbr\n";
            continue;
        }
        
        $books = $bibleData['bibles'][0]['books'];

        foreach ($books as $book) {
            $bookNo = $book['bookNo'];
            $bookName = $book['longName'];
            $chapters = $book['chapterCount'];

            for ($ch = 1; $ch <= $chapters; $ch++) {
                // New URL format: ?bibles=ABBR&langs=LANGUAGE&book=BOOKNO&chapter=CH
                $url = $baseUrl . "?bibles=" . urlencode($abbr) . 
                       "&langs=" . urlencode($language) . 
                       "&book=" . $bookNo . 
                       "&chapter=" . $ch;

                $u = $xml->addChild('url');
                $u->addChild('loc', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
                $u->addChild('lastmod', $today);
                $u->addChild('changefreq', 'weekly');
                $u->addChild('priority', '0.8');
            }
        }

        $xml->asXML($filename);
        echo "Generated $filename\n<br>";
    }
}

echo "\n<br>";

// Create master sitemap.xml
$index = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . 
                              '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');

foreach ($sitemapFiles as $file) {
    $sm = $index->addChild('sitemap');
    $sm->addChild('loc', $baseUrl . $file);
    $sm->addChild('lastmod', $today);
}

$index->asXML("sitemap.xml");
echo "Generated sitemap.xml (index of " . count($sitemapFiles) . " sitemaps)\n<br>";
echo "Done!\n<br>";
