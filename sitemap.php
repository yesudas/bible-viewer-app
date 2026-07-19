<?php
// Generating book+chapter+verse level URLs for every Bible means reading every chapter file
// (not just bibles.json) for every bible - this can take a while, so don't let the default
// 30s PHP execution limit cut it short. Run this from the CLI (php sitemap.php) rather than
// through a browser/web server where a request timeout may still apply regardless.
set_time_limit(0);

$baseUrl = "https://wordofgod.in/bibles/";
// The general sitemap has no bibles/langs of its own, so its URLs use the bare app URL as
// given in the request for this feature.
$generalUrlBase = "https://www.wordofgod.in/bibles/";
$languagesFile = __DIR__ . "/data/languages.json";

// Reference bible used only to determine the book/chapter/verse structure (how many chapters
// per book, how many verses per chapter) for the bible-agnostic general sitemap. KJV1769+ is a
// complete, visible 66-book Bible, so it's a reasonable canonical reference. Change here if a
// different version should define that structure instead.
$generalLanguage = 'English';
$generalBibleAbbr = 'KJV1769+';

// Load JSON
$json = file_get_contents($languagesFile);
$data = json_decode($json, true);

if (!$data || !isset($data['biblesByLanguage'])) {
    die("Invalid languages.json");
}

$sitemapFiles = [];
$today = date('c'); // ISO 8601 date, e.g., 2025-08-27T10:30:00+05:30

// Sitemap protocol caps a single file at 50,000 URLs - warn rather than silently truncate
// or split, since none of the current bibles are anywhere near that size.
const SITEMAP_URL_WARNING_THRESHOLD = 50000;

function addSitemapUrl($xml, $loc, $lastmod, $changefreq, $priority) {
    $u = $xml->addChild('url');
    $u->addChild('loc', htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'));
    $u->addChild('lastmod', $lastmod);
    $u->addChild('changefreq', $changefreq);
    $u->addChild('priority', $priority);
}

// Verse count for a chapter, read from its own data file (bibles.json only has chapter counts).
// Returns 0 (silently skipping verse-level URLs for that chapter) if the file is missing or
// malformed, rather than failing the whole sitemap run.
function getVerseCount($language, $abbr, $bookFolder, $chapterNo) {
    $verseFile = "data/{$language}/{$abbr}/{$bookFolder}/{$chapterNo}.json";
    if (!file_exists($verseFile)) {
        return 0;
    }
    $verseData = json_decode(file_get_contents($verseFile), true);
    if (!isset($verseData['verses']) || !is_array($verseData['verses'])) {
        return 0;
    }
    return count($verseData['verses']);
}

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
        $urlCount = 0;

        foreach ($books as $book) {
            $bookNo = $book['bookNo'];
            $bookFolder = "{$bookNo}-{$book['longName']}";
            $chapters = $book['chapterCount'];

            // Book-level URL: ?bibles=ABBR&langs=LANGUAGE&book=BOOKNO
            addSitemapUrl(
                $xml,
                $baseUrl . "?bibles=" . urlencode($abbr) . "&langs=" . urlencode($language) . "&book=" . $bookNo,
                $today, 'yearly', '0.8'
            );
            $urlCount++;

            for ($ch = 1; $ch <= $chapters; $ch++) {
                // Chapter-level URL: ?bibles=ABBR&langs=LANGUAGE&book=BOOKNO&chapter=CH
                addSitemapUrl(
                    $xml,
                    $baseUrl . "?bibles=" . urlencode($abbr) . "&langs=" . urlencode($language) . "&book=" . $bookNo . "&chapter=" . $ch,
                    $today, 'yearly', '0.8'
                );
                $urlCount++;

                // Verse-level URLs: ?bibles=ABBR&langs=LANGUAGE&book=BOOKNO&chapter=CH&verse=V
                $verseCount = getVerseCount($language, $abbr, $bookFolder, $ch);
                for ($v = 1; $v <= $verseCount; $v++) {
                    addSitemapUrl(
                        $xml,
                        $baseUrl . "?bibles=" . urlencode($abbr) . "&langs=" . urlencode($language) . "&book=" . $bookNo . "&chapter=" . $ch . "&verse=" . $v,
                        $today, 'yearly', '0.8'
                    );
                    $urlCount++;
                }
            }
        }

        if ($urlCount > SITEMAP_URL_WARNING_THRESHOLD) {
            echo "Warning: $filename has $urlCount URLs, over the 50,000 sitemap protocol limit - consider splitting it\n<br>";
        }

        $xml->asXML($filename);
        echo "Generated $filename ($urlCount URLs)\n<br>";
    }
}

echo "\n<br>";

// General sitemap: book/chapter/verse-level URLs with no bibles/langs, so any visitor lands on
// whichever default bible(s) the app itself picks for that book/chapter/verse. The reference
// bible above only supplies how many chapters/verses to enumerate.
$generalFilename = "sitemap-general.xml";
$generalXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' .
                                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

$generalBibleFile = __DIR__ . "/data/$generalLanguage/$generalBibleAbbr/bibles.json";
if (!file_exists($generalBibleFile)) {
    die("Reference bible file not found for general sitemap: $generalBibleFile");
}

$generalBibleData = json_decode(file_get_contents($generalBibleFile), true);
$generalBooks = $generalBibleData['bibles'][0]['books'] ?? [];
$generalUrlCount = 0;

foreach ($generalBooks as $book) {
    $bookNo = $book['bookNo'];
    $bookFolder = "{$bookNo}-{$book['longName']}";
    $chapters = $book['chapterCount'];

    // Book-level URL: ?book=BOOKNO
    addSitemapUrl($generalXml, $generalUrlBase . "?book=" . $bookNo, $today, 'yearly', '0.8');
    $generalUrlCount++;

    for ($ch = 1; $ch <= $chapters; $ch++) {
        // Chapter-level URL: ?book=BOOKNO&chapter=CH
        addSitemapUrl($generalXml, $generalUrlBase . "?book=" . $bookNo . "&chapter=" . $ch, $today, 'yearly', '0.8');
        $generalUrlCount++;

        // Verse-level URL: ?book=BOOKNO&chapter=CH&verse=V
        $verseCount = getVerseCount($generalLanguage, $generalBibleAbbr, $bookFolder, $ch);
        for ($v = 1; $v <= $verseCount; $v++) {
            addSitemapUrl($generalXml, $generalUrlBase . "?book=" . $bookNo . "&chapter=" . $ch . "&verse=" . $v, $today, 'yearly', '0.8');
            $generalUrlCount++;
        }
    }
}

if ($generalUrlCount > SITEMAP_URL_WARNING_THRESHOLD) {
    echo "Warning: $generalFilename has $generalUrlCount URLs, over the 50,000 sitemap protocol limit - consider splitting it\n<br>";
}

$generalXml->asXML($generalFilename);
$sitemapFiles[] = $generalFilename;
echo "Generated $generalFilename ($generalUrlCount URLs)\n<br>";

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
