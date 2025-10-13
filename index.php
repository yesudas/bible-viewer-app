<?php
// Get URL parameters from query string
$selectedLanguages = isset($_GET['langs']) ? explode(',', $_GET['langs']) : [];
$selectedBibles = isset($_GET['bibles']) ? explode(',', $_GET['bibles']) : [];
$selectedBook = isset($_GET['book']) ? intval($_GET['book']) : 1;
$selectedChapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;

// Load languages data
$languagesData = json_decode(file_get_contents('data/languages.json'), true);
$supportedLanguages = $languagesData['metadata']['supportedLanguages'];
$biblesByLanguage = $languagesData['biblesByLanguage'];

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
                break;
            }
        }
    }
}

// Get books from first selected bible
$firstBible = !empty($selectedBibles) ? $selectedBibles[0] : 'TOV2017';
$booksData = [];
$bookNames = [];
$chapterCounts = [];

if (file_exists("data/{$firstBible}/bibles.json")) {
    $bibleData = json_decode(file_get_contents("data/{$firstBible}/bibles.json"), true);
    if (isset($bibleData['bibles'][0]['books'])) {
        $booksData = $bibleData['bibles'][0]['books'];
        foreach ($booksData as $book) {
            $bookNames[$book['bookNo']] = $book['longName'];
            $chapterCounts[$book['bookNo']] = $book['chapterCount'];
        }
    }
}

// Get current book name for SEO
$currentBookName = isset($bookNames[$selectedBook]) ? $bookNames[$selectedBook] : 'Genesis';

// Page Meta data
$pageTitle = "Online Bibles - {$currentBookName} Chapter {$selectedChapter} | WordOfGod.in";
$pageDescription = "Read {$currentBookName} Chapter {$selectedChapter} in multiple Bible versions online. Compare different translations side by side.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="bible, online bible, <?php echo htmlspecialchars($currentBookName); ?>, scripture, biblical text">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <?php
    // Check if CSS file exists and embed it directly to avoid server issues
    $cssPath = 'css/styles.css';
    
    if (file_exists($cssPath)) {
        echo '<style type="text/css">';
        echo file_get_contents($cssPath);
        echo '</style>';
    } else {
        echo '<link href="css/styles.css" rel="stylesheet" type="text/css">';
    }
    ?>
</head>
<body>
    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="https://www.wordofgod.in/bibles/">
                <i class="bi bi-book me-2"></i>Online Bibles
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="https://wordofgod.in" target="_blank">
                            <i class="bi bi-globe me-1"></i>WordOfGod.in
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://christianpdf.com" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i>ChristianPDF.com
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <!-- Bible Selection Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <!-- Languages Tabs (First Row) -->
                        <div class="border-bottom">
                            <ul class="nav nav-tabs" id="languagesTabs" role="tablist">
                                <?php $firstLanguage = true; ?>
                                <?php foreach ($supportedLanguages as $langKey): ?>
                                    <?php if (isset($biblesByLanguage[$langKey])): ?>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link language-tab" 
                                                    id="lang-<?php echo htmlspecialchars($langKey); ?>-tab"
                                                    data-language="<?php echo htmlspecialchars($langKey); ?>"
                                                    type="button" 
                                                    role="tab"
                                                    onclick="selectLanguage('<?php echo htmlspecialchars($langKey); ?>')">
                                                <?php echo htmlspecialchars($langKey); ?>
                                            </button>
                                        </li>
                                        <?php $firstLanguage = false; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Bibles Tabs (Second Row) -->
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-muted mb-0">Available Bibles:</h6>
                                <small class="text-muted">Click to select/deselect</small>
                            </div>
                            <div id="biblesTabsContainer" class="d-flex flex-wrap gap-2">
                                <!-- Dynamically populated based on selected language -->
                            </div>
                        </div>
                        
                        <!-- Selected Bibles Display (Compact) -->
                        <div class="border-top p-3" id="selectedBiblesContainer" style="display: none;">
                            <div class="d-flex flex-column">
                                <h6 class="text-muted mb-2">Selected:</h6>
                                <div id="selectedBiblesList" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Selection Section -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label for="bookSelect" class="form-label">
                    <i class="bi bi-book me-1"></i>Select Book:
                </label>
                <select class="form-select" id="bookSelect" onchange="updateChapters()">
                    <?php foreach ($bookNames as $bookNo => $bookName): ?>
                        <option value="<?php echo $bookNo; ?>" <?php echo ($bookNo == $selectedBook) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($bookName); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="chapterSelect" class="form-label">
                    <i class="bi bi-list-ol me-1"></i>Select Chapter:
                </label>
                <div class="chapter-navigation-container">
                    <button class="chapter-nav-btn" id="prevChapterBtn" onclick="previousChapter()" title="Previous Chapter">
                        <span class="d-none d-md-inline">< Prev</span>
                        <span class="d-md-none"><</span>
                    </button>
                    <select class="form-select chapter-select-with-nav" id="chapterSelect" onchange="loadVerses()">
                        <!-- Dynamically populated based on selected book -->
                    </select>
                    <button class="chapter-nav-btn" id="nextChapterBtn" onclick="nextChapter()" title="Next Chapter">
                        <span class="d-none d-md-inline">Next ></span>
                        <span class="d-md-none">></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Verses Display Section -->
        <div class="row">
            <div class="col-12">
                <div id="versesContainer">
                    <!-- Verses will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Controls -->
    <div class="floating-controls">
        <button class="btn btn-primary" onclick="zoomIn()" title="Zoom In">
            <i class="bi bi-zoom-in"></i>
        </button>
        <button class="btn btn-primary" onclick="zoomOut()" title="Zoom Out">
            <i class="bi bi-zoom-out"></i>
        </button>
        <button class="btn btn-secondary" onclick="resetZoom()" title="Reset Zoom">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button class="btn btn-success" onclick="scrollToTop()" title="Go to Top">
            <i class="bi bi-arrow-up"></i>
        </button>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1">&copy; <?php echo date('Y'); ?> Online Bibles. All rights reserved.</p>
                    <p class="text-muted mb-0">Powered by WordOfGod.in</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="https://wordofgod.in" class="text-decoration-none me-3" target="_blank">
                        <i class="bi bi-globe me-1"></i>WordOfGod.in
                    </a>
                    <a href="https://christianpdf.com" class="text-decoration-none" target="_blank">
                        <i class="bi bi-file-pdf me-1"></i>ChristianPDF.com
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php
    // Check if JS file exists and embed it directly to avoid server issues
    $jsPath = 'js/app.js';
    
    if (file_exists($jsPath)) {
        echo '<script type="application/javascript">';
        echo file_get_contents($jsPath);
        echo '</script>';
    } else {
        echo '<script src="js/app.js" type="application/javascript"></script>';
    }
    ?>
    
    <script>
        // Initialize global variables from PHP data
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure the initialization function exists
            if (typeof initializeGlobalVariables === 'function') {
                initializeGlobalVariables({
                    selectedBibles: <?php echo json_encode($selectedBibles); ?>,
                    selectedLanguages: <?php echo json_encode($selectedLanguages); ?>,
                    biblesByLanguage: <?php echo json_encode($biblesByLanguage); ?>,
                    booksData: <?php echo json_encode($booksData); ?>,
                    chapterCounts: <?php echo json_encode($chapterCounts); ?>
                });
                
                // Set the initial chapter selection for the updateChapters function
                window.initialSelectedChapter = <?php echo $selectedChapter; ?>;
                
                // Update UI components after initialization in correct order
                if (typeof updateLanguageButtons === 'function') {
                    updateLanguageButtons();
                }
                
                // Load Bibles for the default selected language
                if (typeof loadBiblesForLanguage === 'function' && selectedLanguages.length > 0) {
                    loadBiblesForLanguage(selectedLanguages[0]);
                }
                
                if (typeof updateBibleButtons === 'function') {
                    updateBibleButtons();
                }
                
                if (typeof updateSelectedBiblesDisplay === 'function') {
                    updateSelectedBiblesDisplay();
                }
                
                if (typeof updateChapters === 'function') {
                    updateChapters();
                }
                
                // Load verses after all UI is updated
                if (typeof loadVerses === 'function') {
                    loadVerses();
                }
                
                // Initialize mobile controls stabilization
                if (typeof initializeMobileControls === 'function') {
                    initializeMobileControls();
                }
            } else {
                console.error('initializeGlobalVariables function not found. JavaScript may not have loaded properly.');
            }
        });
    </script>
</body>
</html>