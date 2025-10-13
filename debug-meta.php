<?php
// Debug Meta Tags Implementation

echo "<h2>Meta Tags Debug</h2>";

// Get URL parameters from query string
$selectedBibles = isset($_GET['bibles']) ? explode(',', $_GET['bibles']) : [];
$selectedBook = isset($_GET['book']) ? intval($_GET['book']) : 1;
$selectedChapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;

echo "<h3>Parameters:</h3>";
echo "Selected Bibles: " . implode(', ', $selectedBibles) . "<br>";
echo "Selected Book: $selectedBook<br>";
echo "Selected Chapter: $selectedChapter<br><br>";

// Load languages data
$languagesData = json_decode(file_get_contents('data/languages.json'), true);
$biblesByLanguage = $languagesData['biblesByLanguage'];

echo "<h3>Languages Data Loaded:</h3>";
echo "<pre>";
print_r($biblesByLanguage);
echo "</pre>";

// Get default bible if no bibles selected
if (empty($selectedBibles)) {
    echo "<h3>No Bibles Selected - Finding Default:</h3>";
    foreach ($biblesByLanguage as $langKey => $langData) {
        foreach ($langData['bibles'] as $bible) {
            if ($bible['isDefault']) {
                $selectedBibles[] = $bible['abbr'];
                echo "Found default: {$bible['abbr']} in $langKey<br>";
                break 2;
            }
        }
    }
}

echo "<h3>Final Selected Bibles:</h3>";
echo implode(', ', $selectedBibles) . "<br><br>";

// Get books from first selected bible
$firstBible = !empty($selectedBibles) ? $selectedBibles[0] : 'TOV2017';
$booksData = [];
$bookNames = [];

echo "<h3>Loading Books from: $firstBible</h3>";

if (file_exists("data/{$firstBible}/bibles.json")) {
    $bibleData = json_decode(file_get_contents("data/{$firstBible}/bibles.json"), true);
    if (isset($bibleData['bibles'][0]['books'])) {
        $booksData = $bibleData['bibles'][0]['books'];
        foreach ($booksData as $book) {
            $bookNames[$book['bookNo']] = $book['longName'];
        }
        echo "Loaded " . count($booksData) . " books<br>";
    }
} else {
    echo "ERROR: Bible data file not found: data/{$firstBible}/bibles.json<br>";
}

// Get current book name for SEO
$currentBookName = isset($bookNames[$selectedBook]) ? $bookNames[$selectedBook] : 'Genesis';
echo "Current Book Name: $currentBookName<br><br>";

// Get selected Bible information for meta tags
$selectedBibleInfo = [];
echo "<h3>Processing Selected Bibles for Meta Tags:</h3>";

foreach ($selectedBibles as $bibleAbbr) {
    echo "Looking for: $bibleAbbr<br>";
    foreach ($biblesByLanguage as $langKey => $langData) {
        foreach ($langData['bibles'] as $bible) {
            if ($bible['abbr'] === $bibleAbbr) {
                $selectedBibleInfo[] = [
                    'abbr' => $bible['abbr'],
                    'commonName' => $bible['commonName'],
                    'language' => $langKey
                ];
                echo "Found: {$bible['commonName']} ({$bible['abbr']}) in $langKey<br>";
                break 2;
            }
        }
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
echo "Languages: " . implode(', ', $languages) . "<br><br>";

// Create formatted strings for meta tags
$bibleNamesStr = !empty($bibleNames) ? implode(', ', array_slice($bibleNames, 0, 3)) : 'Bible';
$bibleAbbrStr = !empty($bibleAbbreviations) ? '(' . implode(', ', array_slice($bibleAbbreviations, 0, 3)) . ')' : '';
$languagesStr = !empty($languages) ? implode(', ', $languages) : '';

// Add "and more" if there are more than 3 Bibles selected
if (count($bibleNames) > 3) {
    $bibleNamesStr .= ' and ' . (count($bibleNames) - 3) . ' more versions';
}
if (count($bibleAbbreviations) > 3) {
    $bibleAbbrStr = '(' . implode(', ', array_slice($bibleAbbreviations, 0, 3)) . ' +' . (count($bibleAbbreviations) - 3) . ')';
}

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
echo htmlspecialchars($pageKeywords) . "<br><br>";

echo "<h3>Test URLs:</h3>";
echo '<a href="debug-meta.php?bibles=TOV2017&book=1&chapter=1">Single Bible Test</a><br>';
echo '<a href="debug-meta.php?bibles=TOV2017,TCVIN2022&book=19&chapter=23">Multiple Bible Test</a><br>';
echo '<a href="debug-meta.php">Default Test</a><br>';
?>