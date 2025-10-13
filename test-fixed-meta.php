<?php
// Test the fixed meta tags logic

echo "<h2>Testing Fixed Meta Tags</h2>";

$_GET['bibles'] = 'TOV2017,TCB1973';
$_GET['langs'] = 'தமிழ்,English';
$_GET['book'] = '1';
$_GET['chapter'] = '1';

$selectedBibles = explode(',', $_GET['bibles']);
$selectedBook = intval($_GET['book']);
$selectedChapter = intval($_GET['chapter']);

echo "<h3>Parameters:</h3>";
echo "Selected Bibles: " . implode(', ', $selectedBibles) . "<br>";
echo "Selected Book: $selectedBook<br>";
echo "Selected Chapter: $selectedChapter<br><br>";

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
                echo "Found: {$bible['commonName']} ({$bible['abbr']}) in $langKey<br>";
                $found = true;
                break;
            }
        }
        if ($found) break;
    }
}

echo "<h3>Selected Bible Info:</h3>";
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
$languagesStr = !empty($languages) ? implode(', ', $languages) : '';

echo "<h3>Final Meta Tag Strings:</h3>";
echo "Bible Names String: $bibleNamesStr<br>";
echo "Bible Abbreviations String: $bibleAbbrStr<br>";

// Get current book name
$firstBible = $selectedBibles[0];
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

echo "<h3>Current Book Name: $currentBookName</h3>";

// Page Meta data with Bible information
$pageTitle = "Online Bibles - {$currentBookName} Chapter {$selectedChapter} | {$bibleNamesStr} {$bibleAbbrStr} | WordOfGod.in";
$pageDescription = "Read {$currentBookName} Chapter {$selectedChapter} in {$bibleNamesStr} {$bibleAbbrStr}. Compare different Bible translations side by side online.";
$pageKeywords = "bible, online bible, {$currentBookName}, scripture, biblical text, {$bibleNamesStr}, " . implode(', ', $bibleAbbreviations);
if (!empty($languagesStr)) {
    $pageKeywords .= ", {$languagesStr} bible";
}

echo "<h3>Generated Meta Tags:</h3>";
echo "<strong>Title:</strong><br>";
echo htmlspecialchars($pageTitle) . "<br><br>";
echo "<strong>Description:</strong><br>";
echo htmlspecialchars($pageDescription) . "<br><br>";
echo "<strong>Keywords:</strong><br>";
echo htmlspecialchars($pageKeywords) . "<br>";
?>