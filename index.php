<?php

include 'counter.php';

$version = "2026.07";


// Get URL parameters from query string
$selectedLanguages = isset($_GET['langs']) ? explode(',', $_GET['langs']) : [];
$selectedBibles = isset($_GET['bibles']) ? explode(',', $_GET['bibles']) : [];
$selectedBook = isset($_GET['book']) ? intval($_GET['book']) : 1;
$selectedChapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;
$selectedVerse = isset($_GET['verse']) ? intval($_GET['verse']) : null;

// Load languages data
$languagesData = json_decode(file_get_contents('data/languages.json'), true);
$supportedLanguages = $languagesData['metadata']['supportedLanguages'];
$biblesByLanguage = $languagesData['biblesByLanguage'];

# Get default bibles if no bibles selected - ENHANCED to support multiple defaults
if (empty($selectedBibles)) {
    // Collect all bibles with isDefault: true AND hide: false across all languages
    foreach ($biblesByLanguage as $langKey => $langData) {
        foreach ($langData['bibles'] as $bible) {
            if ($bible['isDefault'] && empty($bible['hide'])) {
                $selectedBibles[] = $bible['abbr'];
                // Also add the language to selectedLanguages
                if (!in_array($langKey, $selectedLanguages)) {
                    $selectedLanguages[] = $langKey;
                }
            }
        }
    }
    
    // If still no default found, select the first visible bible as fallback
    if (empty($selectedBibles)) {
        foreach ($biblesByLanguage as $langKey => $langData) {
            foreach ($langData['bibles'] as $bible) {
                if (empty($bible['hide'])) {
                    $selectedBibles[] = $bible['abbr'];
                    if (!in_array($langKey, $selectedLanguages)) {
                        $selectedLanguages[] = $langKey;
                    }
                    break 2;
                }
            }
        }
    }
}

// Get books from first selected bible
$firstBible = !empty($selectedBibles) ? $selectedBibles[0] : 'TOV2017';
$booksData = [];
$bookNames = [];
$chapterCounts = [];

// Find the language for the first Bible
$firstBibleLanguage = 'தமிழ்'; // Default to Tamil
foreach ($biblesByLanguage as $langKey => $langData) {
    foreach ($langData['bibles'] as $bible) {
        if ($bible['abbr'] === $firstBible) {
            $firstBibleLanguage = $langKey;
            break 2;
        }
    }
}

if (file_exists("data/{$firstBibleLanguage}/{$firstBible}/bibles.json")) {
    $bibleData = json_decode(file_get_contents("data/{$firstBibleLanguage}/{$firstBible}/bibles.json"), true);
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

// Add "and more" if there are more than 3 Bibles selected
if (count($bibleNames) > 3) {
    $bibleNamesStr .= ' and ' . (count($bibleNames) - 3) . ' more versions';
}
if (count($bibleAbbreviations) > 3) {
    $bibleAbbrStr = '(' . implode(', ', array_slice($bibleAbbreviations, 0, 3)) . ' +' . (count($bibleAbbreviations) - 3) . ')';
}

// Page Meta data with Bible information
$chapterVerseLabel = $selectedChapter . ($selectedVerse ? ":{$selectedVerse}" : '');
$pageTitle = "Online Bibles - {$currentBookName} Chapter {$chapterVerseLabel} | {$bibleNamesStr} {$bibleAbbrStr} | WordOfGod.in";
$pageDescription = "Read {$currentBookName} Chapter {$chapterVerseLabel} in {$bibleNamesStr} {$bibleAbbrStr}. Compare different Bible translations side by side online.";
$pageKeywords = "bible, online bible, {$currentBookName}, scripture, biblical text, {$bibleNamesStr}, " . implode(', ', $bibleAbbreviations);
if (!empty($languagesStr)) {
    $pageKeywords .= ", {$languagesStr} bible";
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <script>
      (function() {
        function isDarkTheme(t) {
          return t === 'dark' || t === 'warm-dark' || t === 'true-black';
        }
        function resolveAutoTheme() {
          if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
          }
          var h = new Date().getHours();
          return (h >= 18 || h < 6) ? 'dark' : 'light';
        }
        function getEffectiveTheme(stored) {
          if (stored === 'auto') return resolveAutoTheme();
          return stored;
        }
        var stored = localStorage.getItem('bibleViewerTheme');
        if (!stored && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
          stored = 'dark';
        }
        stored = stored || 'light';
        var effective = getEffectiveTheme(stored);
        document.documentElement.setAttribute('data-theme', effective);
        document.documentElement.setAttribute('data-bs-theme', isDarkTheme(effective) ? 'dark' : 'light');
      })();
    </script>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    
    <!-- PWA Support -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2196f3">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Online Bibles">
    <link rel="apple-touch-icon" href="images/icon-192.png">
    
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

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-8ZYHRZG9B8"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-8ZYHRZG9B8');
    </script>

</head>
<body>
    <!-- PWA Install Banner (Top Pulldown) -->
    <div id="pwaInstallBanner" class="pwa-install-banner" style="display: none;">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between py-2">
                <div class="d-flex align-items-center flex-grow-1">
                    <i class="bi bi-phone me-2 text-primary" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Install Online Bibles App</strong>
                        <p class="mb-0 small">Get quick access to Bible study tools. Needs internet!</p>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-primary btn-sm install-app-btn" id="installAppBtnBanner">
                        <i class="bi bi-download me-1"></i>Install
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="dismissBannerBtn">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <button class="btn btn-link btn-sm text-muted" id="dontShowAgainBtn">
                        Don't show again
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-light app-navbar border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="https://www.wordofgod.in/bibles/">
                <i class="bi bi-book me-2"></i>Online Bibles
            </a>
            <div class="d-flex align-items-center gap-2 ms-auto order-lg-last">
                <div class="dropdown">
                    <button class="theme-selector-btn dropdown-toggle" type="button" id="themeDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Change theme">
                        <i class="bi bi-palette"></i>
                        <span class="d-none d-sm-inline" id="themeLabel">Light</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown">
                        <li><button class="dropdown-item" type="button" data-theme="light" onclick="setTheme('light')"><span class="theme-swatch theme-swatch-light me-2"></span>Light</button></li>
                        <li><button class="dropdown-item" type="button" data-theme="light-gray" onclick="setTheme('light-gray')"><span class="theme-swatch theme-swatch-light-gray me-2"></span>Light Gray</button></li>
                        <li><button class="dropdown-item" type="button" data-theme="gray" onclick="setTheme('gray')"><span class="theme-swatch theme-swatch-gray me-2"></span>Gray</button></li>
                        <li><button class="dropdown-item" type="button" data-theme="dark" onclick="setTheme('dark')"><span class="theme-swatch theme-swatch-dark me-2"></span>Dark</button></li>
                        <li><button class="dropdown-item" type="button" data-theme="warm-dark" onclick="setTheme('warm-dark')"><span class="theme-swatch theme-swatch-warm-dark me-2"></span>Warm Dark</button></li>
                        <li><button class="dropdown-item" type="button" data-theme="true-black" onclick="setTheme('true-black')"><span class="theme-swatch theme-swatch-true-black me-2"></span>True Black</button></li>
                        <li><button class="dropdown-item" type="button" data-theme="sepia" onclick="setTheme('sepia')"><span class="theme-swatch theme-swatch-sepia me-2"></span>Sepia</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button class="dropdown-item" type="button" data-theme="auto" onclick="setTheme('auto')"><span class="theme-swatch theme-swatch-auto me-2"></span>Auto <small class="text-muted">(sunset / system)</small></button></li>
                    </ul>
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                            <a class="nav-link" href="https://wordofgod.in/good-news-collections/" target="_blank"><i class="bi bi-box-seam me-1"></i>Good News Collections</a> </li>
                    <li class="nav-item">
                            <a class="nav-link" href="https://wordofgod.in/bibledictionary/" target="_blank"><i class="bi bi-collection me-1"></i>Bible Dictionaries</a> </li>
                    <li class="nav-item">
                            <a class="nav-link" href="https://wordofgod.in/bible-concordance/" target="_blank"><i class="bi bi-search me-1"></i>Bible Concordances</a></li>
                    <li class="nav-item">
                            <a class="nav-link" href="https://wordofgod.in/bible-wallpapers/" target="_blank"><i class="bi bi-card-image me-1"></i>Bible Wallpapers</a></li>
                    <li class="nav-item">
                            <a class="nav-link" href="https://wordofgod.in/bible-app-modules/" target="_blank"><i class="bi bi-phone me-1"></i>Bible App Modules</a></li>
                    <li class="nav-item">
                            <a class="nav-link" href="https://wordofgod.in/" target="_blank"><i class="bi bi-gift me-1"></i>Free Christian Resources</a></li>
                    <li class="nav-item">
                            <button class="btn btn-primary btn-sm ms-2 install-app-btn" id="installAppBtnHeader" style="display: none;">
                                <i class="bi bi-download me-1"></i>Install App
                            </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <!-- Bible Selection Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bible-selection-card">
                    <div class="card-body p-0">
                        <button class="btn bible-selection-toggle w-100 text-start d-flex justify-content-between align-items-center collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#bibleSelectionCollapse"
                                aria-expanded="false"
                                aria-controls="bibleSelectionCollapse">
                            <span class="d-flex flex-column flex-md-row align-items-md-center gap-1 gap-md-3">
                                <span class="fw-semibold">
                                    <i class="bi bi-journals me-1"></i>Bible Selection
                                </span>
                                <small class="text-muted" id="bibleSelectionSummary">Select language &amp; translation</small>
                            </span>
                            <i class="bi bi-chevron-down bible-selection-toggle-icon"></i>
                        </button>

                        <div id="bibleSelectionCollapse" class="collapse">
                            <div class="p-3 border-top">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="languageSelect" class="form-label mb-1">
                                            <i class="bi bi-translate me-1"></i>Language
                                        </label>
                                        <select class="form-select" id="languageSelect" onchange="selectLanguage(this.value)">
                                            <?php foreach ($supportedLanguages as $langKey): ?>
                                                <?php if (isset($biblesByLanguage[$langKey])): ?>
                                                    <option value="<?php echo htmlspecialchars($langKey); ?>">
                                                        <?php echo htmlspecialchars($langKey); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bibleSelect" class="form-label mb-1">
                                            <i class="bi bi-book me-1"></i>Translation
                                        </label>
                                        <select class="form-select" id="bibleSelect" onchange="onBibleSelectChange(this)">
                                            <option value="">Select a Bible translation...</option>
                                        </select>
                                        <small class="text-muted">Choose a translation to add or remove it</small>
                                    </div>
                                </div>

                                <!-- Selected Bibles Display (Compact) -->
                                <div class="mt-3" id="selectedBiblesContainer" style="display: none;">
                                    <div class="d-flex flex-column">
                                        <h6 class="text-muted mb-2">Selected:</h6>
                                        <div id="selectedBiblesList" class="d-flex flex-wrap gap-1"></div>
                                    </div>
                                </div>
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
                <div class="book-navigation-container">
                    <button class="book-nav-btn" id="prevBookBtn" onclick="previousBook()" title="Previous Book">
                        <span class="d-none d-md-inline"><< Prev</span>
                        <span class="d-md-none"><<</span>
                    </button>
                    <select class="form-select book-select-with-nav" id="bookSelect" onchange="updateChapters()">
                        <?php foreach ($bookNames as $bookNo => $bookName): ?>
                            <option value="<?php echo $bookNo; ?>" <?php echo ($bookNo == $selectedBook) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bookName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="book-nav-btn" id="nextBookBtn" onclick="nextBook()" title="Next Book">
                        <span class="d-none d-md-inline">Next >></span>
                        <span class="d-md-none">>></span>
                    </button>
                </div>
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
                    
                    <!-- Zoom Controls -->
                    <button class="chapter-nav-btn ms-2" onclick="zoomOut()" title="Zoom Out">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button class="chapter-nav-btn" onclick="resetZoom()" title="Reset Zoom">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button class="chapter-nav-btn" onclick="zoomIn()" title="Zoom In">
                        <i class="bi bi-zoom-in"></i>
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
        <button class="btn btn-success" onclick="scrollToTop()" title="Go to Top">
            <i class="bi bi-arrow-up"></i>
        </button>
    </div>

    <!-- Footer -->
    <footer class="app-footer text-center py-4 mt-5">
        <div class="container">
            <p class="mb-2 text-muted">No Copyright, Freely Copy and Distribute (as per Matthew 10:8)</p>
            <div class="mb-3">
                <button class="btn btn-primary btn-sm install-app-btn" id="installAppBtnFooter" style="display: none;">
                    <i class="bi bi-download me-1"></i>Install as App
                </button>
            </div>
            <p class="mb-0 text-muted">
                <a href="https://wordofgod.in/good-news-collections/" target="_blank" class="text-decoration-none"><i class="bi bi-box-seam me-1"></i>Good News Collections</a> | 
                <a href="https://wordofgod.in/bibledictionary/" target="_blank" class="text-decoration-none"><i class="bi bi-collection me-1"></i>Bible Dictionaries</a> | 
                <a href="https://wordofgod.in/bible-concordance/" target="_blank" class="text-decoration-none"><i class="bi bi-search me-1"></i>Bible Concordances</a> | 
                <a href="https://wordofgod.in/bible-wallpapers/" target="_blank" class="text-decoration-none"><i class="bi bi-card-image me-1"></i>Bible Wallpapers</a> | 
                <a href="https://wordofgod.in/bible-app-modules/" target="_blank" class="text-decoration-none"><i class="bi bi-phone me-1"></i>Bible App Modules</a> | 
                <a href="https://wordofgod.in" target="_blank" class="text-decoration-none"><i class="bi bi-gift me-1"></i>Free Christian Resources</a> | 
                <span class="text-primary"><i class="bi bi-emoji-heart-eyes me-1"></i>Visitors: <?= $visitors2 ?></span>
            </p>    
            <div style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; opacity: 0; pointer-events: none;" aria-hidden="true">
                <a href="./bot.php" tabindex="-1">.</a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Script -->
    <script src="js/script.js"></script>
    
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
                // Set the initial chapter/verse selection before initializeGlobalVariables runs,
                // since it synchronously calls updateChapters() which reads these globals.
                window.initialSelectedChapter = <?php echo $selectedChapter; ?>;
                window.initialSelectedVerse = <?php echo $selectedVerse ? $selectedVerse : 'null'; ?>;

                initializeGlobalVariables({
                    selectedBibles: <?php echo json_encode($selectedBibles); ?>,
                    selectedLanguages: <?php echo json_encode($selectedLanguages); ?>,
                    biblesByLanguage: <?php echo json_encode($biblesByLanguage); ?>,
                    booksData: <?php echo json_encode($booksData); ?>,
                    chapterCounts: <?php echo json_encode($chapterCounts); ?>
                });

                if (typeof initTheme === 'function') {
                    initTheme();
                }
            } else {
                // JavaScript may not have loaded properly
            }
        });
    </script>

    <!-- Concordance Modal -->
    <div class="modal fade" id="concordanceModal" tabindex="-1" aria-labelledby="concordanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="concordanceModalLabel">Word Concordance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="concordanceTabs" role="tablist">
                        <li class="nav-item nav-item-concordance" role="presentation">
                            <button class="nav-link active" id="concordance-tab" data-bs-toggle="tab" data-bs-target="#concordance" type="button" role="tab" aria-controls="concordance" aria-selected="true">
                                Concordance
                            </button>
                        </li>
                        <li class="nav-item nav-item-dictionary" role="presentation">
                            <button class="nav-link" id="dictionary-tab" data-bs-toggle="tab" data-bs-target="#dictionary" type="button" role="tab" aria-controls="dictionary" aria-selected="false">
                                Dictionary
                            </button>
                        </li>
                        <li class="nav-item nav-item-devotions" role="presentation">
                            <button class="nav-link" id="devotions-tab" data-bs-toggle="tab" data-bs-target="#devotions" type="button" role="tab" aria-controls="devotions" aria-selected="false">
                                Devotions
                            </button>
                        </li>
                        <li class="nav-item nav-item-commentary" role="presentation">
                            <button class="nav-link" id="commentary-tab" data-bs-toggle="tab" data-bs-target="#commentary" type="button" role="tab" aria-controls="commentary" aria-selected="false">
                                Commentary
                            </button>
                        </li>
                        <li class="nav-item nav-item-crossreferences" role="presentation">
                            <button class="nav-link" id="crossreferences-tab" data-bs-toggle="tab" data-bs-target="#crossreferences" type="button" role="tab" aria-controls="crossreferences" aria-selected="false">
                                Cross References
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="concordanceTabContent">
                        <div class="tab-pane fade show active" id="concordance" role="tabpanel" aria-labelledby="concordance-tab">
                            <div id="concordanceContent">
                                <!-- Concordance content will be loaded here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="dictionary" role="tabpanel" aria-labelledby="dictionary-tab">
                            <div id="dictionaryContent">
                                <!-- Dictionary content will be loaded here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="devotions" role="tabpanel" aria-labelledby="devotions-tab">
                            <div id="devotionsContent">
                                <!-- Devotions content will be loaded here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="commentary" role="tabpanel" aria-labelledby="commentary-tab">
                            <div id="commentaryContent">
                                <div class="mb-3">
                                    <select id="commentarySourceSelect" class="form-select form-select-sm">
                                        <!-- Options populated by JS -->
                                    </select>
                                </div>
                                <div id="commentarySections">
                                    <!-- Commentary sections will be loaded here -->
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="crossreferences" role="tabpanel" aria-labelledby="crossreferences-tab">
                            <div id="crossreferencesContent">
                                <div class="mb-3">
                                    <select id="crossrefSourceSelect" class="form-select form-select-sm">
                                        <!-- Options populated by JS -->
                                    </select>
                                </div>
                                <div id="crossrefPanels">
                                    <!-- Collapsible per-bible panels will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>