// Global variables
let selectedBibles = [];
let selectedLanguages = [];
let currentLanguage = '';
let biblesByLanguage = {};
let booksData = [];
let chapterCounts = {};
let currentFontSize = 16;

// Initialize the app
document.addEventListener('DOMContentLoaded', function() {
    // Don't call these functions here - they will be called after PHP data is loaded
    // initializeSelections();
    // updateChapters();
    // loadVerses();
});

function initializeSelections() {
    // Set default bible if none selected
    if (selectedBibles.length === 0) {
        for (let langKey in biblesByLanguage) {
            for (let bible of biblesByLanguage[langKey].bibles) {
                if (bible.isDefault) {
                    selectedBibles.push(bible.abbr);
                    break;
                }
            }
            if (selectedBibles.length > 0) break;
        }
    }
    
    updateSelectedBiblesDisplay();
    
    // If we have selected bibles, find their languages
    selectedBibles.forEach(bibleAbbr => {
        for (let langKey in biblesByLanguage) {
            for (let bible of biblesByLanguage[langKey].bibles) {
                if (bible.abbr === bibleAbbr) {
                    if (!selectedLanguages.includes(langKey)) {
                        selectedLanguages.push(langKey);
                    }
                }
            }
        }
    });
    
    updateLanguageButtons();
}

function selectLanguage(language) {
    currentLanguage = language;
    updateLanguageButtons();
    loadBiblesForLanguage(language);
}

function updateLanguageButtons() {
    // Update tab-style language buttons
    document.querySelectorAll('.language-tab').forEach(btn => {
        btn.classList.remove('active');
        if (selectedLanguages.includes(btn.dataset.language)) {
            btn.classList.add('active');
        }
    });
    
    // Also update old-style buttons if they exist
    document.querySelectorAll('.language-btn').forEach(btn => {
        btn.classList.remove('active');
        if (selectedLanguages.includes(btn.dataset.language)) {
            btn.classList.add('active');
        }
    });
}

function loadBiblesForLanguage(language) {
    const biblesContainer = document.getElementById('biblesTabsContainer');
    if (!biblesContainer) return;
    
    biblesContainer.innerHTML = '';
    
    if (biblesByLanguage[language] && biblesByLanguage[language].bibles) {
        biblesByLanguage[language].bibles.forEach(bible => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'bible-tab-btn';
            button.textContent = bible.abbr;
            button.title = bible.longName || bible.abbr;
            button.onclick = () => toggleBible(bible.abbr);
            
            if (selectedBibles.includes(bible.abbr)) {
                button.classList.add('active');
            }
            
            biblesContainer.appendChild(button);
        });
    }
}

function toggleBible(bibleAbbr) {
    const index = selectedBibles.indexOf(bibleAbbr);
    if (index > -1) {
        selectedBibles.splice(index, 1);
        
        // Remove language if no more bibles from that language are selected
        updateSelectedLanguages();
    } else {
        selectedBibles.push(bibleAbbr);
        
        // Add language of this bible to selectedLanguages
        for (let langKey in biblesByLanguage) {
            for (let bible of biblesByLanguage[langKey].bibles) {
                if (bible.abbr === bibleAbbr) {
                    if (!selectedLanguages.includes(langKey)) {
                        selectedLanguages.push(langKey);
                    }
                    break;
                }
            }
        }
    }
    
    updateSelectedBiblesDisplay();
    updateBibleButtons();
    updateLanguageButtons();
    updateURL();
    
    // If this is the first bible, update book/chapter dropdowns
    if (selectedBibles.length > 0) {
        loadBooksForBible(selectedBibles[0]);
    }
    
    // Refresh the verses display
    loadVerses();
}

function updateSelectedLanguages() {
    // Rebuild selectedLanguages based on selectedBibles
    selectedLanguages = [];
    selectedBibles.forEach(bibleAbbr => {
        for (let langKey in biblesByLanguage) {
            for (let bible of biblesByLanguage[langKey].bibles) {
                if (bible.abbr === bibleAbbr) {
                    if (!selectedLanguages.includes(langKey)) {
                        selectedLanguages.push(langKey);
                    }
                }
            }
        }
    });
}

function updateBibleButtons() {
    // Update tab-style bible buttons
    document.querySelectorAll('.bible-tab-btn').forEach(btn => {
        btn.classList.remove('active');
        if (selectedBibles.includes(btn.textContent)) {
            btn.classList.add('active');
        }
    });
    
    // Also update old-style buttons if they exist
    document.querySelectorAll('.bible-btn').forEach(btn => {
        btn.classList.remove('active');
        if (selectedBibles.includes(btn.textContent)) {
            btn.classList.add('active');
        }
    });
}

function updateSelectedBiblesDisplay() {
    const container = document.getElementById('selectedBiblesContainer');
    const list = document.getElementById('selectedBiblesList');
    
    if (selectedBibles.length > 0) {
        container.style.display = 'block';
        list.innerHTML = '';
        
        selectedBibles.forEach((bibleAbbr, index) => {
            const tag = document.createElement('span');
            tag.className = 'selected-bible-tag';
            tag.innerHTML = `${bibleAbbr} <i class="bi bi-x" style="cursor: pointer; margin-left: 4px;" onclick="removeBible('${bibleAbbr}')"></i>`;
            list.appendChild(tag);
        });
    } else {
        container.style.display = 'none';
    }
}

function removeBible(bibleAbbr) {
    const index = selectedBibles.indexOf(bibleAbbr);
    if (index > -1) {
        selectedBibles.splice(index, 1);
        updateSelectedLanguages();
        updateSelectedBiblesDisplay();
        updateBibleButtons();
        updateLanguageButtons();
        updateURL();
        loadVerses();
    }
}

function loadBooksForBible(bibleAbbr) {
    fetch(`api.php?action=getBooks&bible=${bibleAbbr}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                booksData = data.books;
                updateBookDropdown();
                updateChapters();
            }
        })
        .catch(error => console.error('Error loading books:', error));
}

function updateBookDropdown() {
    const bookSelect = document.getElementById('bookSelect');
    const currentBook = bookSelect.value;
    bookSelect.innerHTML = '';
    
    booksData.forEach(book => {
        const option = document.createElement('option');
        option.value = book.bookNo;
        option.textContent = book.longName;
        if (book.bookNo == currentBook) {
            option.selected = true;
        }
        bookSelect.appendChild(option);
    });
}

function updateChapters() {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    const selectedBookNo = parseInt(bookSelect.value);
    
    chapterSelect.innerHTML = '';
    
    const book = booksData.find(b => b.bookNo === selectedBookNo);
    if (book) {
        // Use initialSelectedChapter only on first load, otherwise default to chapter 1
        const defaultChapter = window.initialSelectedChapter || 1;
        const shouldUseInitial = window.initialSelectedChapter && !window.hasLoadedOnce;
        
        for (let i = 1; i <= book.chapterCount; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Chapter ${i}`;
            
            // Select the appropriate chapter
            if (shouldUseInitial && i === defaultChapter) {
                option.selected = true;
            } else if (!shouldUseInitial && i === 1) {
                option.selected = true;
            }
            
            chapterSelect.appendChild(option);
        }
        
        // Mark that we've loaded once
        window.hasLoadedOnce = true;
    }
    
    updateURL();
    
    // Load verses after updating chapters
    loadVerses();
}

function loadVerses() {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    const selectedBook = parseInt(bookSelect.value);
    const selectedChapter = parseInt(chapterSelect.value);
    
    if (selectedBibles.length === 0) {
        document.getElementById('versesContainer').innerHTML = 
            '<div class="alert alert-warning">Please select at least one Bible version.</div>';
        return;
    }
    
    // Show loading
    document.getElementById('versesContainer').innerHTML = 
        '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Load verses for all selected bibles
    const promises = selectedBibles.map(bibleAbbr => 
        fetch(`api.php?action=getVerses&bible=${bibleAbbr}&book=${selectedBook}&chapter=${selectedChapter}`)
            .then(response => response.json())
    );
    
    Promise.all(promises)
        .then(results => {
            displayVerses(results);
            updateURL();
            updateMetaTags();
        })
        .catch(error => {
            console.error('Error loading verses:', error);
            document.getElementById('versesContainer').innerHTML = 
                '<div class="alert alert-danger">Error loading verses. Please try again.</div>';
        });
}

function displayVerses(results) {
    const container = document.getElementById('versesContainer');
    container.innerHTML = '';
    
    if (results.length === 0 || !results[0].success) {
        container.innerHTML = '<div class="alert alert-warning">No verses found.</div>';
        return;
    }
    
    const maxVerses = Math.max(...results.map(r => r.success ? r.verses.length : 0));
    
    for (let i = 0; i < maxVerses; i++) {
        const verseContainer = document.createElement('div');
        verseContainer.className = 'verse-container p-3';
        
        let verseNumber = '';
        let versesContent = '';
        
        results.forEach((result, index) => {
            if (result.success && result.verses[i]) {
                const verse = result.verses[i];
                if (!verseNumber) verseNumber = verse.number;
                
                versesContent += `
                    <div class="mb-2">
                        <div class="bible-version">${selectedBibles[index]}</div>
                        <div class="verse-text">${verse.verse}</div>
                    </div>
                `;
            }
        });
        
        verseContainer.innerHTML = `
            <div class="d-flex">
                <div class="verse-number p-2 text-center">${verseNumber}</div>
                <div class="flex-grow-1 px-3">
                    ${versesContent}
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                            onclick="copyVerse(${i})" 
                            title="Copy verse">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(verseContainer);
    }
}

function copyVerse(verseIndex) {
    const verseContainer = document.querySelectorAll('.verse-container')[verseIndex];
    const verseNumber = verseContainer.querySelector('.verse-number').textContent;
    const verseTexts = verseContainer.querySelectorAll('.verse-text');
    const bibleVersions = verseContainer.querySelectorAll('.bible-version');
    
    // Get current book and chapter info
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    const bookName = bookSelect.options[bookSelect.selectedIndex].text;
    const chapterNumber = chapterSelect.value;
    
    // Format: "Genesis 1:1"
    let copyText = `${bookName} ${chapterNumber}:${verseNumber}\n\n`;
    
    verseTexts.forEach((text, index) => {
        const version = bibleVersions[index].textContent;
        copyText += `${version}: ${text.textContent}\n`;
    });
    
    // Add website URL at the bottom
    copyText += '\nhttps://www.wordofgod.in/bibles';
    
    navigator.clipboard.writeText(copyText).then(() => {
        // Show success feedback
        const btn = verseContainer.querySelector('.copy-btn');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i>';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 1000);
    });
}

function updateURL() {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    
    if (bookSelect && chapterSelect) {
        const params = new URLSearchParams();
        if (selectedBibles.length > 0) params.set('bibles', selectedBibles.join(','));
        if (selectedLanguages.length > 0) params.set('langs', selectedLanguages.join(','));
        params.set('book', bookSelect.value);
        params.set('chapter', chapterSelect.value);
        
        const newURL = window.location.pathname + '?' + params.toString();
        window.history.replaceState({}, '', newURL);
    }
}

function updateMetaTags() {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    
    if (bookSelect && chapterSelect) {
        const bookName = bookSelect.options[bookSelect.selectedIndex].text;
        const chapterNum = chapterSelect.value;
        
        document.title = `Online Bibles - ${bookName} Chapter ${chapterNum} | WordOfGod.in`;
        
        const metaDesc = document.querySelector('meta[name="description"]');
        if (metaDesc) {
            metaDesc.content = `Read ${bookName} Chapter ${chapterNum} in multiple Bible versions online. Compare different translations side by side.`;
        }
    }
}

// Zoom functionality
function zoomIn() {
    if (currentFontSize < 24) {
        currentFontSize += 2;
        updateFontSize();
        
        // Aggressive mobile viewport stabilization
        stabilizeMobileControls();
    }
}

function zoomOut() {
    if (currentFontSize > 12) {
        currentFontSize -= 2;
        updateFontSize();
        
        // Aggressive mobile viewport stabilization
        stabilizeMobileControls();
    }
}

function resetZoom() {
    currentFontSize = 16;
    updateFontSize();
    
    // Aggressive mobile viewport stabilization
    stabilizeMobileControls();
}

function updateFontSize() {
    document.documentElement.style.setProperty('--font-size-base', currentFontSize + 'px');
}

// Enhanced mobile device detection
function isMobileDevice() {
    const userAgent = navigator.userAgent.toLowerCase();
    const isMobileUA = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent);
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    const isSmallScreen = window.innerWidth <= 768;
    
    return isMobileUA || (isTouchDevice && isSmallScreen);
}

// Aggressive mobile controls stabilization
function stabilizeMobileControls() {
    if (!isMobileDevice()) return;
    
    const floatingControls = document.querySelector('.floating-controls');
    if (!floatingControls) return;
    
    // Method 1: Force hardware acceleration
    floatingControls.style.transform = 'translate3d(0, 0, 0)';
    floatingControls.style.webkitTransform = 'translate3d(0, 0, 0)';
    
    // Method 2: Force position recalculation
    floatingControls.style.position = 'absolute';
    floatingControls.style.bottom = '0px';
    
    // Method 3: Use requestAnimationFrame for smooth repositioning
    requestAnimationFrame(() => {
        floatingControls.style.position = 'fixed';
        floatingControls.style.bottom = '0px';
        floatingControls.style.left = '0px';
        floatingControls.style.right = '0px';
        
        // Method 4: Force a layout recalculation
        floatingControls.offsetHeight;
        
        // Method 5: Reset transforms after repositioning
        setTimeout(() => {
            floatingControls.style.transform = 'translateZ(0)';
            floatingControls.style.webkitTransform = 'translateZ(0)';
        }, 10);
    });
    
    // Method 6: Prevent scroll-related shifts
    setTimeout(() => {
        window.scrollTo(window.scrollX, window.scrollY);
    }, 50);
}

// Initialize mobile controls stabilization on load
function initializeMobileControls() {
    if (isMobileDevice()) {
        // Add event listeners for orientation changes
        window.addEventListener('orientationchange', () => {
            setTimeout(stabilizeMobileControls, 100);
        });
        
        // Add event listeners for resize
        window.addEventListener('resize', () => {
            setTimeout(stabilizeMobileControls, 50);
        });
        
        // Initial stabilization
        setTimeout(stabilizeMobileControls, 100);
    }
}

// Prevent mobile floating controls from shifting (legacy function for compatibility)
function preventMobileShift() {
    stabilizeMobileControls();
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Initialize global variables from PHP
function initializeGlobalVariables(phpData) {
    selectedBibles = phpData.selectedBibles || [];
    selectedLanguages = phpData.selectedLanguages || [];
    biblesByLanguage = phpData.biblesByLanguage || {};
    booksData = phpData.booksData || [];
    chapterCounts = phpData.chapterCounts || {};
}