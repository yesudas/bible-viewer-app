<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chapter Navigation Test - Online Bibles</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        <?php echo file_get_contents('css/styles.css'); ?>
        
        .feature-demo {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .keyboard-shortcut {
            background: #e7f3ff;
            border: 1px solid #b6d7ff;
            border-radius: 6px;
            padding: 4px 8px;
            font-family: monospace;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">📖 Chapter Navigation Features</h2>
                
                <!-- Feature Overview -->
                <div class="feature-demo">
                    <h4>🎯 New Chapter Navigation System</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>✅ Features Added:</h6>
                            <ul>
                                <li><strong>Book Navigation Buttons:</strong> Navigate between books with << and >> buttons</li>
                                <li><strong>Chapter Navigation Buttons:</strong> Navigate between chapters with < and > buttons</li>
                                <li><strong>Smart Disabled States:</strong> Buttons disable at Bible boundaries</li>
                                <li><strong>Cross-Book Navigation:</strong> Automatically move to next/previous book from chapters</li>
                                <li><strong>Responsive Labels:</strong> Full text on desktop, symbols only on mobile</li>
                                <li><strong>Enhanced Keyboard Shortcuts:</strong> Arrow keys, comma/period, and Shift+Arrow for books</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>🎮 Keyboard Shortcuts:</h6>
                            <ul>
                                <li><span class="keyboard-shortcut">←</span> or <span class="keyboard-shortcut">,</span> = Previous Chapter</li>
                                <li><span class="keyboard-shortcut">→</span> or <span class="keyboard-shortcut">.</span> = Next Chapter</li>
                                <li><span class="keyboard-shortcut">Shift+←</span> = Previous Book</li>
                                <li><span class="keyboard-shortcut">Shift+→</span> = Next Book</li>
                                <li><strong>Note:</strong> Shortcuts work when not typing in input fields</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Demo Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">🔧 Live Demo - Chapter Navigation</h5>
                    </div>
                    <div class="card-body">
                        <!-- Simulated Books Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="demoBookSelect" class="form-label">
                                    <i class="bi bi-book me-1"></i>Select Book:
                                </label>
                                <div class="book-navigation-container">
                                    <button class="book-nav-btn" id="demoPrevBookBtn" onclick="demoPreviousBook()" title="Previous Book">
                                        <span class="d-none d-md-inline"><< Prev</span>
                                        <span class="d-md-none"><<</span>
                                    </button>
                                    <select class="form-select book-select-with-nav" id="demoBookSelect" onchange="updateDemoChapters()">
                                        <option value="1">Genesis (50 chapters)</option>
                                        <option value="2">Exodus (40 chapters)</option>
                                        <option value="3">Leviticus (27 chapters)</option>
                                        <option value="19">Psalms (150 chapters)</option>
                                        <option value="40">Matthew (28 chapters)</option>
                                        <option value="41">Mark (16 chapters)</option>
                                        <option value="66">Revelation (22 chapters)</option>
                                    </select>
                                    <button class="book-nav-btn" id="demoNextBookBtn" onclick="demoNextBook()" title="Next Book">
                                        <span class="d-none d-md-inline">Next >></span>
                                        <span class="d-md-none">>></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="demoChapterSelect" class="form-label">
                                    <i class="bi bi-list-ol me-1"></i>Select Chapter:
                                </label>
                                <div class="chapter-navigation-container">
                                    <button class="chapter-nav-btn" id="demoPrevBtn" onclick="demoPreviousChapter()" title="Previous Chapter">
                                        <span class="d-none d-md-inline">< Prev</span>
                                        <span class="d-md-none"><</span>
                                    </button>
                                    <select class="form-select chapter-select-with-nav" id="demoChapterSelect" onchange="updateDemoStatus()">
                                        <!-- Dynamically populated -->
                                    </select>
                                    <button class="chapter-nav-btn" id="demoNextBtn" onclick="demoNextChapter()" title="Next Chapter">
                                        <span class="d-none d-md-inline">Next ></span>
                                        <span class="d-md-none">></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Display -->
                        <div class="alert alert-info" id="demoStatus">
                            Current: Genesis Chapter 1
                        </div>
                        
                        <!-- Test Instructions -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6>🖱️ Button Testing:</h6>
                                <ol>
                                    <li>Click the "< Prev" or "Next >" buttons</li>
                                    <li>Notice buttons disable at book boundaries</li>
                                    <li>Test cross-book navigation (Genesis → Exodus)</li>
                                    <li>Resize window to see mobile labels ("< >")</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6>⌨️ Keyboard Testing:</h6>
                                <ol>
                                    <li>Click anywhere on this page (not in input)</li>
                                    <li>Use arrow keys (← →) to navigate</li>
                                    <li>Try comma (,) and period (.) keys</li>
                                    <li>Notice shortcuts don't work in input fields</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Details -->
                <div class="feature-demo">
                    <h4>⚙️ Technical Implementation</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>🎨 CSS Features:</h6>
                            <ul>
                                <li><strong>Flexbox Layout:</strong> chapter-navigation-container</li>
                                <li><strong>Responsive Design:</strong> d-none d-md-inline for labels</li>
                                <li><strong>Disabled States:</strong> opacity and cursor changes</li>
                                <li><strong>Hover Effects:</strong> transform and box-shadow</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>💻 JavaScript Features:</h6>
                            <ul>
                                <li><strong>Smart Navigation:</strong> Cross-book chapter switching</li>
                                <li><strong>State Management:</strong> Button enable/disable logic</li>
                                <li><strong>Event Handling:</strong> Keyboard shortcut detection</li>
                                <li><strong>URL Updates:</strong> Maintains bookmarkable URLs</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Responsive Behavior Demo -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📱 Responsive Behavior</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">🖥️ Desktop View (>768px)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex gap-2 align-items-center">
                                            <button class="chapter-nav-btn">< Prev</button>
                                            <select class="form-select flex-grow-1">
                                                <option>Chapter 1</option>
                                            </select>
                                            <button class="chapter-nav-btn">Next ></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">📱 Mobile View (≤768px)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex gap-1 align-items-center">
                                            <button class="chapter-nav-btn" style="min-width: 40px; font-size: 0.875rem;"><</button>
                                            <select class="form-select flex-grow-1" style="font-size: 0.875rem;">
                                                <option>Chapter 1</option>
                                            </select>
                                            <button class="chapter-nav-btn" style="min-width: 40px; font-size: 0.875rem;">></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary me-2">
                        <i class="bi bi-arrow-left me-2"></i>Try in Main App
                    </a>
                    <a href="test-complete.php" class="btn btn-secondary">
                        <i class="bi bi-list-check me-2"></i>All Tests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Demo data structure
        const demoBooks = {
            1: { name: "Genesis", chapters: 50 },
            2: { name: "Exodus", chapters: 40 },
            3: { name: "Leviticus", chapters: 27 },
            19: { name: "Psalms", chapters: 150 },
            40: { name: "Matthew", chapters: 28 },
            41: { name: "Mark", chapters: 16 },
            66: { name: "Revelation", chapters: 22 }
        };
        
        const bookOrder = [1, 2, 3, 19, 40, 41, 66];
        
        function updateDemoChapters() {
            const bookSelect = document.getElementById('demoBookSelect');
            const chapterSelect = document.getElementById('demoChapterSelect');
            const selectedBookId = parseInt(bookSelect.value);
            const book = demoBooks[selectedBookId];
            
            chapterSelect.innerHTML = '';
            
            for (let i = 1; i <= book.chapters; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `Chapter ${i}`;
                if (i === 1) option.selected = true;
                chapterSelect.appendChild(option);
            }
            
            updateDemoStatus();
            updateDemoButtons();
            updateDemoBookButtons();
        }
        
        function updateDemoStatus() {
            const bookSelect = document.getElementById('demoBookSelect');
            const chapterSelect = document.getElementById('demoChapterSelect');
            const statusDiv = document.getElementById('demoStatus');
            
            const selectedBookId = parseInt(bookSelect.value);
            const selectedChapter = parseInt(chapterSelect.value);
            const book = demoBooks[selectedBookId];
            
            statusDiv.textContent = `Current: ${book.name} Chapter ${selectedChapter}`;
            updateDemoButtons();
            updateDemoBookButtons();
        }
        
        function updateDemoButtons() {
            const bookSelect = document.getElementById('demoBookSelect');
            const chapterSelect = document.getElementById('demoChapterSelect');
            const prevBtn = document.getElementById('demoPrevBtn');
            const nextBtn = document.getElementById('demoNextBtn');
            
            const selectedBookId = parseInt(bookSelect.value);
            const selectedChapter = parseInt(chapterSelect.value);
            const bookIndex = bookOrder.indexOf(selectedBookId);
            const book = demoBooks[selectedBookId];
            
            // Disable prev if first chapter of first book
            prevBtn.disabled = (bookIndex === 0 && selectedChapter === 1);
            
            // Disable next if last chapter of last book
            nextBtn.disabled = (bookIndex === bookOrder.length - 1 && selectedChapter === book.chapters);
        }
        
        function demoPreviousChapter() {
            const bookSelect = document.getElementById('demoBookSelect');
            const chapterSelect = document.getElementById('demoChapterSelect');
            const selectedBookId = parseInt(bookSelect.value);
            const selectedChapter = parseInt(chapterSelect.value);
            
            if (selectedChapter > 1) {
                chapterSelect.value = selectedChapter - 1;
            } else {
                // Go to previous book
                const bookIndex = bookOrder.indexOf(selectedBookId);
                if (bookIndex > 0) {
                    const prevBookId = bookOrder[bookIndex - 1];
                    bookSelect.value = prevBookId;
                    updateDemoChapters();
                    // Set to last chapter
                    const prevBook = demoBooks[prevBookId];
                    document.getElementById('demoChapterSelect').value = prevBook.chapters;
                }
            }
            updateDemoStatus();
        }
        
        function demoNextChapter() {
            const bookSelect = document.getElementById('demoBookSelect');
            const chapterSelect = document.getElementById('demoChapterSelect');
            const selectedBookId = parseInt(bookSelect.value);
            const selectedChapter = parseInt(chapterSelect.value);
            const book = demoBooks[selectedBookId];
            
            if (selectedChapter < book.chapters) {
                chapterSelect.value = selectedChapter + 1;
            } else {
                // Go to next book
                const bookIndex = bookOrder.indexOf(selectedBookId);
                if (bookIndex < bookOrder.length - 1) {
                    const nextBookId = bookOrder[bookIndex + 1];
                    bookSelect.value = nextBookId;
                    updateDemoChapters();
                    // Chapter 1 is already selected by updateDemoChapters
                }
            }
            updateDemoStatus();
        }
        
        function demoPreviousBook() {
            const bookSelect = document.getElementById('demoBookSelect');
            const selectedBookId = parseInt(bookSelect.value);
            const bookIndex = bookOrder.indexOf(selectedBookId);
            
            if (bookIndex > 0) {
                const prevBookId = bookOrder[bookIndex - 1];
                bookSelect.value = prevBookId;
                updateDemoChapters();
                updateDemoStatus();
            }
        }
        
        function demoNextBook() {
            const bookSelect = document.getElementById('demoBookSelect');
            const selectedBookId = parseInt(bookSelect.value);
            const bookIndex = bookOrder.indexOf(selectedBookId);
            
            if (bookIndex < bookOrder.length - 1) {
                const nextBookId = bookOrder[bookIndex + 1];
                bookSelect.value = nextBookId;
                updateDemoChapters();
                updateDemoStatus();
            }
        }
        
        function updateDemoBookButtons() {
            const bookSelect = document.getElementById('demoBookSelect');
            const prevBookBtn = document.getElementById('demoPrevBookBtn');
            const nextBookBtn = document.getElementById('demoNextBookBtn');
            
            const selectedBookId = parseInt(bookSelect.value);
            const bookIndex = bookOrder.indexOf(selectedBookId);
            
            // Disable prev if first book
            prevBookBtn.disabled = (bookIndex === 0);
            
            // Disable next if last book
            nextBookBtn.disabled = (bookIndex === bookOrder.length - 1);
        }
        
        // Initialize demo
        document.addEventListener('DOMContentLoaded', function() {
            updateDemoChapters();
            
            // Add keyboard shortcuts for demo
            document.addEventListener('keydown', function(event) {
                if (event.target.tagName.toLowerCase() === 'input' || 
                    event.target.tagName.toLowerCase() === 'textarea' ||
                    event.target.tagName.toLowerCase() === 'select') {
                    return;
                }
                
                if (event.key === 'ArrowLeft' || event.key === ',') {
                    event.preventDefault();
                    const prevBtn = document.getElementById('demoPrevBtn');
                    if (!prevBtn.disabled) {
                        demoPreviousChapter();
                    }
                }
                
                if (event.key === 'ArrowRight' || event.key === '.') {
                    event.preventDefault();
                    const nextBtn = document.getElementById('demoNextBtn');
                    if (!nextBtn.disabled) {
                        demoNextChapter();
                    }
                }
                
                // Shift + Left arrow for previous book
                if (event.key === 'ArrowLeft' && event.shiftKey) {
                    event.preventDefault();
                    const prevBookBtn = document.getElementById('demoPrevBookBtn');
                    if (!prevBookBtn.disabled) {
                        demoPreviousBook();
                    }
                }
                
                // Shift + Right arrow for next book
                if (event.key === 'ArrowRight' && event.shiftKey) {
                    event.preventDefault();
                    const nextBookBtn = document.getElementById('demoNextBookBtn');
                    if (!nextBookBtn.disabled) {
                        demoNextBook();
                    }
                }
            });
        });
    </script>
</body>
</html>