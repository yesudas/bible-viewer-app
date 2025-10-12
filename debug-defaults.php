<?php
// Debug script to check default values
$selectedLanguages = isset($_GET['langs']) ? explode(',', $_GET['langs']) : [];
$selectedBibles = isset($_GET['bibles']) ? explode(',', $_GET['bibles']) : [];

// Load languages data
$languagesData = json_decode(file_get_contents('data/languages.json'), true);
$biblesByLanguage = $languagesData['biblesByLanguage'];

echo "<h2>Debug Information</h2>";
echo "<h3>Before Default Selection:</h3>";
echo "Selected Languages: " . json_encode($selectedLanguages) . "<br>";
echo "Selected Bibles: " . json_encode($selectedBibles) . "<br>";

// Get default bible if no bibles selected
if (empty($selectedBibles)) {
    foreach ($biblesByLanguage as $langKey => $langData) {
        foreach ($langData['bibles'] as $bible) {
            if ($bible['isDefault']) {
                $selectedBibles[] = $bible['abbr'];
                // Also add the language to selectedLanguages
                if (!in_array($langKey, $selectedLanguages)) {
                    $selectedLanguages[] = $langKey;
                }
                echo "Found default bible: {$bible['abbr']} in language: {$langKey}<br>";
                break 2;
            }
        }
    }
    
    // If still no default found, select the first available bible
    if (empty($selectedBibles)) {
        foreach ($biblesByLanguage as $langKey => $langData) {
            if (!empty($langData['bibles'])) {
                $selectedBibles[] = $langData['bibles'][0]['abbr'];
                if (!in_array($langKey, $selectedLanguages)) {
                    $selectedLanguages[] = $langKey;
                }
                echo "Using first available bible: {$langData['bibles'][0]['abbr']} in language: {$langKey}<br>";
                break;
            }
        }
    }
}

echo "<h3>After Default Selection:</h3>";
echo "Selected Languages: " . json_encode($selectedLanguages) . "<br>";
echo "Selected Bibles: " . json_encode($selectedBibles) . "<br>";

echo "<h3>Available Bibles by Language:</h3>";
foreach ($biblesByLanguage as $langKey => $langData) {
    echo "<strong>{$langKey}:</strong><br>";
    foreach ($langData['bibles'] as $bible) {
        $default = $bible['isDefault'] ? ' (DEFAULT)' : '';
        echo "- {$bible['abbr']}: {$bible['commonName']}{$default}<br>";
    }
    echo "<br>";
}

echo "<p><a href='index.php'>Back to Main App</a></p>";
?>