<?php
// Simple direct test of the meta tags issue
echo "<h2>Direct Meta Tags Test</h2>";

// Simulate the exact same conditions
$_GET['bibles'] = 'TOV2017,TCB1973';
$_GET['langs'] = 'தமிழ்,English';
$_GET['book'] = '1';
$_GET['chapter'] = '1';

// Copy the exact logic from index.php
$selectedBibles = isset($_GET['bibles']) ? explode(',', $_GET['bibles']) : [];
$selectedBook = isset($_GET['book']) ? intval($_GET['book']) : 1;
$selectedChapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;

echo "Selected Bibles: " . implode(', ', $selectedBibles) . "<br>";

// Load languages data
$languagesData = json_decode(file_get_contents('data/languages.json'), true);
$biblesByLanguage = $languagesData['biblesByLanguage'];

// Get selected Bible information for meta tags (using the FIXED logic)
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

echo "<br>Selected Bible Info:<br>";
print_r($selectedBibleInfo);

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

echo "<br>Bible Names: " . implode(', ', $bibleNames) . "<br>";
echo "Bible Abbreviations: " . implode(', ', $bibleAbbreviations) . "<br>";

// Create formatted strings for meta tags
$bibleNamesStr = !empty($bibleNames) ? implode(', ', array_slice($bibleNames, 0, 3)) : 'Bible';
$bibleAbbrStr = !empty($bibleAbbreviations) ? '(' . implode(', ', array_slice($bibleAbbreviations, 0, 3)) . ')' : '';

echo "<br>Bible Names String: $bibleNamesStr<br>";
echo "Bible Abbreviations String: $bibleAbbrStr<br>";

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

echo "<br>Current Book Name: $currentBookName<br>";

// Page Meta data with Bible information
$pageTitle = "Online Bibles - {$currentBookName} Chapter {$selectedChapter} | {$bibleNamesStr} {$bibleAbbrStr} | WordOfGod.in";
$pageDescription = "Read {$currentBookName} Chapter {$selectedChapter} in {$bibleNamesStr} {$bibleAbbrStr}. Compare different Bible translations side by side online.";

echo "<br><strong>Final Meta Tags:</strong><br>";
echo "Title: " . htmlspecialchars($pageTitle) . "<br>";
echo "Description: " . htmlspecialchars($pageDescription) . "<br>";

// Now test what you're seeing in the browser
echo "<br><h3>What you reported seeing:</h3>";
echo "Title: Online Bibles - ஆதியாகமம் Chapter 1 | WordOfGod.in<br>";
echo "Description: Read ஆதியாகமம் Chapter 1 in multiple Bible versions online. Compare different translations side by side.<br>";

echo "<br><h3>Comparison:</h3>";
if (strpos($pageTitle, 'Tamil One Version 2017') !== false) {
    echo "✅ Generated title includes Bible names<br>";
} else {
    echo "❌ Generated title is missing Bible names<br>";
}

if (strpos($pageDescription, 'Tamil One Version 2017') !== false) {
    echo "✅ Generated description includes Bible names<br>";
} else {
    echo "❌ Generated description is missing Bible names<br>";
}
?>