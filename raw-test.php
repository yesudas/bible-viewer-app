<?php
// Direct output test - no HTML, just the meta tags
header('Content-Type: text/plain');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$_GET['bibles'] = 'TOV2017,TCB1973';
$_GET['langs'] = 'தமிழ்,English';
$_GET['book'] = '1';
$_GET['chapter'] = '1';

// Copy exact logic from index.php
$selectedBibles = isset($_GET['bibles']) ? explode(',', $_GET['bibles']) : [];
$selectedBook = isset($_GET['book']) ? intval($_GET['book']) : 1;
$selectedChapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;

// Load languages data
$languagesData = json_decode(file_get_contents('data/languages.json'), true);
$biblesByLanguage = $languagesData['biblesByLanguage'];

// Get selected Bible information for meta tags
$selectedBibleInfo = [];
foreach ($selectedBibles as $bibleAbbr) {
    $found = false;
    foreach ($biblesByLanguage as $langKey => $langData) {
        foreach ($langData['bibles'] as $bible) {
            if ($bible['abbr'] === $bibleAbbr) {
                $selectedBibleInfo[] = [
                    'abbr' => $bible['abbr'],
                    'commonName' => $bible['commonName'],
                    'language' => $langKey
                ];
                $found = true;
                break;
            }
        }
        if ($found) break;
    }
}

// Build meta tag content with Bible information
$bibleNames = [];
$bibleAbbreviations = [];
$languages = [];

foreach ($selectedBibleInfo as $info) {
    $bibleNames[] = $info['commonName'];
    $bibleAbbreviations[] = $info['abbr'];
    if (!in_array($info['language'], $languages)) {
        $languages[] = $info['language'];
    }
}

// Create formatted strings for meta tags
$bibleNamesStr = !empty($bibleNames) ? implode(', ', array_slice($bibleNames, 0, 3)) : 'Bible';
$bibleAbbrStr = !empty($bibleAbbreviations) ? '(' . implode(', ', array_slice($bibleAbbreviations, 0, 3)) . ')' : '';
$languagesStr = !empty($languages) ? implode(', ', $languages) : '';

// Get current book name
$firstBible = !empty($selectedBibles) ? $selectedBibles[0] : 'TOV2017';
$currentBookName = 'Genesis';

if (file_exists("data/{$firstBible}/bibles.json")) {
    $bibleData = json_decode(file_get_contents("data/{$firstBible}/bibles.json"), true);
    if (isset($bibleData['bibles'][0]['books'])) {
        $booksData = $bibleData['bibles'][0]['books'];
        foreach ($booksData as $book) {
            if ($book['bookNo'] == $selectedBook) {
                $currentBookName = $book['longName'];
                break;
            }
        }
    }
}

// Page Meta data with Bible information
$pageTitle = "Online Bibles - {$currentBookName} Chapter {$selectedChapter} | {$bibleNamesStr} {$bibleAbbrStr} | WordOfGod.in";
$pageDescription = "Read {$currentBookName} Chapter {$selectedChapter} in {$bibleNamesStr} {$bibleAbbrStr}. Compare different Bible translations side by side online.";
$pageKeywords = "bible, online bible, {$currentBookName}, scripture, biblical text, {$bibleNamesStr}, " . implode(', ', $bibleAbbreviations);
if (!empty($languagesStr)) {
    $pageKeywords .= ", {$languagesStr} bible";
}

echo "TITLE: " . $pageTitle . "\n";
echo "DESCRIPTION: " . $pageDescription . "\n";
echo "KEYWORDS: " . $pageKeywords . "\n";
echo "\nRAW DATA:\n";
echo "Bible Names: " . implode(', ', $bibleNames) . "\n";
echo "Bible Abbreviations: " . implode(', ', $bibleAbbreviations) . "\n";
echo "Bible Names String: " . $bibleNamesStr . "\n";
echo "Bible Abbreviations String: " . $bibleAbbrStr . "\n";
echo "Current Book Name: " . $currentBookName . "\n";
?>