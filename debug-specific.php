<?php
// Debug the meta tags issue

$selectedBibles = ['TOV2017', 'TCB1973'];
$selectedBook = 1;
$selectedChapter = 1;

// Load languages data
$languagesData = json_decode(file_get_contents('data/languages.json'), true);
$biblesByLanguage = $languagesData['biblesByLanguage'];

echo "<h2>Debug: Meta Tags Issue</h2>";
echo "<h3>Selected Bibles: " . implode(', ', $selectedBibles) . "</h3>";

// Get selected Bible information for meta tags
$selectedBibleInfo = [];
echo "<h3>Looking for Bible information:</h3>";

foreach ($selectedBibles as $bibleAbbr) {
    echo "<br><strong>Looking for: $bibleAbbr</strong><br>";
    $found = false;
    
    foreach ($biblesByLanguage as $langKey => $langData) {
        echo "Checking language: $langKey<br>";
        foreach ($langData['bibles'] as $bible) {
            echo "  - Bible: {$bible['abbr']} ({$bible['commonName']})<br>";
            if ($bible['abbr'] === $bibleAbbr) {
                $selectedBibleInfo[] = [
                    'abbr' => $bible['abbr'],
                    'commonName' => $bible['commonName'],
                    'language' => $langKey
                ];
                echo "  ✅ FOUND: {$bible['commonName']} ({$bible['abbr']}) in $langKey<br>";
                $found = true;
                break 2;
            }
        }
    }
    
    if (!$found) {
        echo "  ❌ NOT FOUND: $bibleAbbr<br>";
    }
}

echo "<h3>Final Selected Bible Info:</h3>";
echo "<pre>";
print_r($selectedBibleInfo);
echo "</pre>";

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

echo "<h3>Extracted Data:</h3>";
echo "Bible Names: " . implode(', ', $bibleNames) . "<br>";
echo "Bible Abbreviations: " . implode(', ', $bibleAbbreviations) . "<br>";
echo "Languages: " . implode(', ', $languages) . "<br>";

// Create formatted strings for meta tags
$bibleNamesStr = !empty($bibleNames) ? implode(', ', array_slice($bibleNames, 0, 3)) : 'Bible';
$bibleAbbrStr = !empty($bibleAbbreviations) ? '(' . implode(', ', array_slice($bibleAbbreviations, 0, 3)) . ')' : '';

echo "<h3>Final Meta Tag Strings:</h3>";
echo "Bible Names String: $bibleNamesStr<br>";
echo "Bible Abbreviations String: $bibleAbbrStr<br>";

// Get current book name for SEO
$firstBible = $selectedBibles[0];
$currentBookName = 'Genesis'; // Default

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

echo "<h3>Current Book Name: $currentBookName</h3>";

// Page Meta data with Bible information
$pageTitle = "Online Bibles - {$currentBookName} Chapter {$selectedChapter} | {$bibleNamesStr} {$bibleAbbrStr} | WordOfGod.in";
$pageDescription = "Read {$currentBookName} Chapter {$selectedChapter} in {$bibleNamesStr} {$bibleAbbrStr}. Compare different Bible translations side by side online.";

echo "<h3>Generated Meta Tags:</h3>";
echo "<strong>Title:</strong><br>";
echo htmlspecialchars($pageTitle) . "<br><br>";
echo "<strong>Description:</strong><br>";
echo htmlspecialchars($pageDescription) . "<br>";
?>