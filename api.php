<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getBooks':
        getBooks();
        break;
    case 'getVerses':
        getVerses();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function getBooks() {
    $bibleAbbr = $_GET['bible'] ?? '';
    
    if (empty($bibleAbbr)) {
        echo json_encode(['success' => false, 'error' => 'Bible abbreviation required']);
        return;
    }
    
    $bibleFile = "data/{$bibleAbbr}/bibles.json";
    
    if (!file_exists($bibleFile)) {
        echo json_encode(['success' => false, 'error' => 'Bible data not found']);
        return;
    }
    
    try {
        $bibleData = json_decode(file_get_contents($bibleFile), true);
        
        if (!isset($bibleData['bibles'][0]['books'])) {
            echo json_encode(['success' => false, 'error' => 'Books data not found']);
            return;
        }
        
        $books = $bibleData['bibles'][0]['books'];
        echo json_encode(['success' => true, 'books' => $books]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error reading bible data: ' . $e->getMessage()]);
    }
}

function getVerses() {
    $bibleAbbr = $_GET['bible'] ?? '';
    $bookNo = intval($_GET['book'] ?? 0);
    $chapterNo = intval($_GET['chapter'] ?? 0);
    
    if (empty($bibleAbbr) || $bookNo <= 0 || $chapterNo <= 0) {
        echo json_encode(['success' => false, 'error' => 'Bible, book and chapter required']);
        return;
    }
    
    try {
        // First get the book information to find the folder name
        $bibleFile = "data/{$bibleAbbr}/bibles.json";
        
        if (!file_exists($bibleFile)) {
            echo json_encode(['success' => false, 'error' => 'Bible data not found']);
            return;
        }
        
        $bibleData = json_decode(file_get_contents($bibleFile), true);
        $books = $bibleData['bibles'][0]['books'] ?? [];
        
        $book = null;
        foreach ($books as $b) {
            if ($b['bookNo'] == $bookNo) {
                $book = $b;
                break;
            }
        }
        
        if (!$book) {
            echo json_encode(['success' => false, 'error' => 'Book not found']);
            return;
        }
        
        // Check if chapter exists
        if ($chapterNo > $book['chapterCount']) {
            echo json_encode(['success' => false, 'error' => 'Chapter not found']);
            return;
        }
        
        // Construct the verse file path
        $bookFolder = "{$bookNo}-{$book['longName']}";
        $verseFile = "data/{$bibleAbbr}/{$bookFolder}/{$chapterNo}.json";
        
        if (!file_exists($verseFile)) {
            echo json_encode(['success' => false, 'error' => 'Verse file not found: ' . $verseFile]);
            return;
        }
        
        $verseData = json_decode(file_get_contents($verseFile), true);
        
        if (!isset($verseData['verses'])) {
            echo json_encode(['success' => false, 'error' => 'Verses data not found']);
            return;
        }
        
        echo json_encode([
            'success' => true, 
            'verses' => $verseData['verses'],
            'bibleAbbr' => $verseData['bibleAbbr'] ?? $bibleAbbr,
            'bookName' => $verseData['bookName'] ?? $book['longName'],
            'chapterNumber' => $verseData['chapterNumber'] ?? $chapterNo
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error reading verse data: ' . $e->getMessage()]);
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateBibleAbbr($abbr) {
    // Only allow alphanumeric characters and limited special characters
    return preg_match('/^[A-Za-z0-9_-]+$/', $abbr);
}

function validateNumber($num, $min = 1, $max = 999) {
    $number = intval($num);
    return $number >= $min && $number <= $max;
}
?>