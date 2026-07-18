// The word-study features (Dictionary, Devotions, Commentary, Cross References) fetch from
// sibling paths on wordofgod.in that don't send CORS headers, so those fetches only work when
// same-origin. The site is reachable at both the bare and www hosts without a forced redirect,
// so hardcoding either one breaks fetches for visitors on the other - derive the origin actually
// in use instead, falling back to the bare host for any other environment (e.g. local testing).
const WORDOFGOD_ORIGIN = /(^|\.)wordofgod\.in$/.test(window.location.hostname)
    ? window.location.origin
    : 'https://wordofgod.in';

// Global variables
let selectedBibles = [];
let selectedLanguages = [];
let currentLanguage = '';
let biblesByLanguage = {};
let booksData = [];
let chapterCounts = {};
let currentFontSize = 16;
let currentModalWord = '';
let loadedDictionaryWord = '';
let currentModalBook = null;
let currentModalChapter = null;
let currentModalVerse = null;
let currentModalLanguage = '';
let loadedDevotionsKey = '';
let currentVerseNumber = null; // verse to highlight/keep in the URL for the current chapter view

// Friendly names for known dictionary slugs returned by the getDictionaries API
const DICTIONARY_LABELS = {
    'tamil-bible-dictionary': 'Tamil Bible Dictionary',
    'சத்திய-வேதாகமப்-பெயர்-அகராதி': 'சத்திய வேதாகமப் பெயர் அகராதி'
};

// Cross reference sources. Change DEFAULT_CROSS_REFERENCE_SOURCE to switch the default.
const CROSS_REFERENCE_SOURCES = [
    { key: 'OB', label: 'OB - Open Bible', count: 344799 },
    { key: 'RST', label: 'RST - Russian Synodal Bible', count: 7434 },
    { key: 'TSK', label: 'TSK - Treasury of Scripture Knowledge', count: 627045 }
];
const DEFAULT_CROSS_REFERENCE_SOURCE = 'OB';
let currentCrossRefSource = DEFAULT_CROSS_REFERENCE_SOURCE;
let loadedCrossReferencesKey = ''; // `${book}|${chapter}|${verse}|${source}`
let currentCrossRefEntries = [];

// Max citations shown inline under a verse before falling back to "view all" in the modal
const INLINE_CROSSREF_LIMIT = 8;
let crossRefInlineRequestId = 0; // guards against a slow chapter fetch overwriting a newer chapter's verses

// Commentary sources. Change DEFAULT_COMMENTARY_SOURCE to switch the default.
const COMMENTARY_SOURCES = [
    { key: 'MHWBC', label: "MHWBC - Matthew Henry's Whole Bible Commentary" },
    { key: 'GNTBC', label: 'GNTBC - Good News தமிழ் வேதாகம விளக்கவுரை' }
];
const DEFAULT_COMMENTARY_SOURCE = 'MHWBC';
let currentCommentarySource = DEFAULT_COMMENTARY_SOURCE;
let loadedCommentaryKey = ''; // `${book}|${chapter}|${source}`
let currentCommentarySections = [];

// Cross references can point anywhere across all 66 books, but `booksData` only reflects
// whichever Bible currently drives the book/chapter dropdown (which may be a partial-canon
// edition, e.g. an Old-Testament-only interlinear). This static table lets us label and
// navigate to any cross-reference target regardless of which Bible currently drives the
// dropdown - the selected Bibles that actually contain the target book will still render
// its text once we get there; the ones that don't will simply be skipped for that chapter.
const CROSSREF_BOOKS = {
    1: { shortName: 'Gen', longName: 'Genesis', chapterCount: 50 },
    2: { shortName: 'Exo', longName: 'Exodus', chapterCount: 40 },
    3: { shortName: 'Lev', longName: 'Leviticus', chapterCount: 27 },
    4: { shortName: 'Num', longName: 'Numbers', chapterCount: 36 },
    5: { shortName: 'Deu', longName: 'Deuteronomy', chapterCount: 34 },
    6: { shortName: 'Jos', longName: 'Joshua', chapterCount: 24 },
    7: { shortName: 'Jdg', longName: 'Judges', chapterCount: 21 },
    8: { shortName: 'Rth', longName: 'Ruth', chapterCount: 4 },
    9: { shortName: '1Sa', longName: '1 Samuel', chapterCount: 31 },
    10: { shortName: '2Sa', longName: '2 Samuel', chapterCount: 24 },
    11: { shortName: '1Ki', longName: '1 Kings', chapterCount: 22 },
    12: { shortName: '2Ki', longName: '2 Kings', chapterCount: 25 },
    13: { shortName: '1Ch', longName: '1 Chronicles', chapterCount: 29 },
    14: { shortName: '2Ch', longName: '2 Chronicles', chapterCount: 36 },
    15: { shortName: 'Ezr', longName: 'Ezra', chapterCount: 10 },
    16: { shortName: 'Neh', longName: 'Nehemiah', chapterCount: 13 },
    17: { shortName: 'Est', longName: 'Esther', chapterCount: 10 },
    18: { shortName: 'Job', longName: 'Job', chapterCount: 42 },
    19: { shortName: 'Psa', longName: 'Psalms', chapterCount: 150 },
    20: { shortName: 'Pro', longName: 'Proverbs', chapterCount: 31 },
    21: { shortName: 'Ecc', longName: 'Ecclesiastes', chapterCount: 12 },
    22: { shortName: 'Son', longName: 'Song of Songs', chapterCount: 8 },
    23: { shortName: 'Isa', longName: 'Isaiah', chapterCount: 66 },
    24: { shortName: 'Jer', longName: 'Jeremiah', chapterCount: 52 },
    25: { shortName: 'Lam', longName: 'Lamentations', chapterCount: 5 },
    26: { shortName: 'Eze', longName: 'Ezekiel', chapterCount: 48 },
    27: { shortName: 'Dan', longName: 'Daniel', chapterCount: 12 },
    28: { shortName: 'Hos', longName: 'Hosea', chapterCount: 14 },
    29: { shortName: 'Joe', longName: 'Joel', chapterCount: 3 },
    30: { shortName: 'Amo', longName: 'Amos', chapterCount: 9 },
    31: { shortName: 'Oba', longName: 'Obadiah', chapterCount: 1 },
    32: { shortName: 'Jon', longName: 'Jonah', chapterCount: 4 },
    33: { shortName: 'Mic', longName: 'Micah', chapterCount: 7 },
    34: { shortName: 'Nah', longName: 'Nahum', chapterCount: 3 },
    35: { shortName: 'Hab', longName: 'Habakkuk', chapterCount: 3 },
    36: { shortName: 'Zep', longName: 'Zephaniah', chapterCount: 3 },
    37: { shortName: 'Hag', longName: 'Haggai', chapterCount: 2 },
    38: { shortName: 'Zec', longName: 'Zechariah', chapterCount: 14 },
    39: { shortName: 'Mal', longName: 'Malachi', chapterCount: 4 },
    40: { shortName: 'Mat', longName: 'Matthew', chapterCount: 28 },
    41: { shortName: 'Mar', longName: 'Mark', chapterCount: 16 },
    42: { shortName: 'Luk', longName: 'Luke', chapterCount: 24 },
    43: { shortName: 'Joh', longName: 'John', chapterCount: 21 },
    44: { shortName: 'Act', longName: 'Acts of the Apostles', chapterCount: 28 },
    45: { shortName: 'Rom', longName: 'Romans', chapterCount: 16 },
    46: { shortName: '1Co', longName: '1 Corinthians', chapterCount: 16 },
    47: { shortName: '2Co', longName: '2 Corinthians', chapterCount: 13 },
    48: { shortName: 'Gal', longName: 'Galatians', chapterCount: 6 },
    49: { shortName: 'Eph', longName: 'Ephesians', chapterCount: 6 },
    50: { shortName: 'Php', longName: 'Philippians', chapterCount: 4 },
    51: { shortName: 'Col', longName: 'Colossians', chapterCount: 4 },
    52: { shortName: '1Th', longName: '1 Thessalonians', chapterCount: 5 },
    53: { shortName: '2Th', longName: '2 Thessalonians', chapterCount: 3 },
    54: { shortName: '1Ti', longName: '1 Timothy', chapterCount: 6 },
    55: { shortName: '2Ti', longName: '2 Timothy', chapterCount: 4 },
    56: { shortName: 'Tit', longName: 'Titus', chapterCount: 3 },
    57: { shortName: 'Phm', longName: 'Philemon', chapterCount: 1 },
    58: { shortName: 'Heb', longName: 'Hebrews', chapterCount: 13 },
    59: { shortName: 'Jas', longName: 'James', chapterCount: 5 },
    60: { shortName: '1Pe', longName: '1 Peter', chapterCount: 5 },
    61: { shortName: '2Pe', longName: '2 Peter', chapterCount: 3 },
    62: { shortName: '1Jn', longName: '1 John', chapterCount: 5 },
    63: { shortName: '2Jn', longName: '2 John', chapterCount: 1 },
    64: { shortName: '3Jn', longName: '3 John', chapterCount: 1 },
    65: { shortName: 'Jud', longName: 'Jude', chapterCount: 1 },
    66: { shortName: 'Rev', longName: 'Revelation', chapterCount: 22 }
};

// Initialize the app
document.addEventListener('DOMContentLoaded', function() {
    // Don't call these functions here - they will be called after PHP data is loaded
    // initializeSelections();
    // updateChapters();
    // loadVerses();
    
    // Add event delegation for clickable words
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('clickable-word')) {
            const word = event.target.getAttribute('data-word');
            const strongs = event.target.getAttribute('data-strongs');
            const bible = event.target.getAttribute('data-bible');

            // Track the book/chapter/verse/language of the clicked word so the Devotions
            // tab can look up devotions for this verse regardless of which word was clicked
            currentModalBook = event.target.getAttribute('data-book');
            currentModalChapter = event.target.getAttribute('data-chapter');
            currentModalVerse = event.target.getAttribute('data-verse');
            currentModalLanguage = getBibleLanguage(bible);
            loadedDevotionsKey = '';

            // Word clicks always use the full 3-tab (Concordance/Dictionary/Devotions) mode
            const tabsEl = document.getElementById('concordanceTabs');
            if (tabsEl) tabsEl.classList.remove('mode-crossref');

            // Use Strong's number if available, otherwise use the word
            const searchTerm = strongs || word;
            openConcordance(searchTerm, bible);
            return;
        }

        // Both the individual inline citations (e.g. "Joh 1:1-3") and the trailing
        // "click for more" link open the Cross References modal for the verse being read
        const crossrefOpener = event.target.closest('.crossref-more-link, .crossref-inline-ref');
        if (crossrefOpener) {
            event.preventDefault();
            const book = crossrefOpener.getAttribute('data-book');
            const chapter = crossrefOpener.getAttribute('data-chapter');
            const verse = crossrefOpener.getAttribute('data-verse');

            currentModalBook = book;
            currentModalChapter = chapter;
            currentModalVerse = verse;
            currentModalLanguage = getBibleLanguage(selectedBibles[0]);
            loadedDevotionsKey = '';

            openCrossReferences(book, chapter, verse);
            return;
        }

        const crossrefRefLink = event.target.closest('.crossref-ref-link');
        if (crossrefRefLink) {
            event.preventDefault();
            const book = crossrefRefLink.getAttribute('data-book');
            const chapter = crossrefRefLink.getAttribute('data-chapter');
            const verse = crossrefRefLink.getAttribute('data-verse');
            navigateToCrossReference(book, chapter, verse);
            return;
        }

        // Commentary table-of-contents entry: jump straight to that section instead of
        // making the user scroll past every preceding section to reach it
        const commentaryTocLink = event.target.closest('.commentary-toc-link');
        if (commentaryTocLink) {
            event.preventDefault();
            const targetEl = document.getElementById(commentaryTocLink.getAttribute('data-target'));
            if (targetEl) targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
    });

    // Load dictionary content when the Dictionary tab is shown
    const dictionaryTabEl = document.getElementById('dictionary-tab');
    if (dictionaryTabEl) {
        dictionaryTabEl.addEventListener('shown.bs.tab', function() {
            if (currentModalWord) {
                loadDictionaryData(currentModalWord);
            }
        });
    }

    // Load devotions content when the Devotions tab is shown
    const devotionsTabEl = document.getElementById('devotions-tab');
    if (devotionsTabEl) {
        devotionsTabEl.addEventListener('shown.bs.tab', function() {
            if (currentModalBook && currentModalChapter && currentModalVerse) {
                loadDevotionsData(currentModalBook, currentModalChapter, currentModalVerse, currentModalLanguage);
            }
        });
    }

    // Load cross references when the Cross References tab is shown. openCrossReferences()
    // already loads data when entered via an inline citation click, but a word click leaves
    // this tab unloaded until the user switches to it manually - this listener covers that path.
    const crossreferencesTabEl = document.getElementById('crossreferences-tab');
    if (crossreferencesTabEl) {
        crossreferencesTabEl.addEventListener('shown.bs.tab', function() {
            if (currentModalBook && currentModalChapter && currentModalVerse) {
                loadCrossReferencesData(currentModalBook, currentModalChapter, currentModalVerse);
            }
        });
    }

    // Load commentary content when the Commentary tab is shown. Word clicks and cross-reference
    // clicks both set currentModalBook/currentModalChapter, so this tab works from either entry point.
    const commentaryTabEl = document.getElementById('commentary-tab');
    if (commentaryTabEl) {
        commentaryTabEl.addEventListener('shown.bs.tab', function() {
            if (currentModalBook && currentModalChapter) {
                loadCommentaryData(currentModalBook, currentModalChapter);
            }
        });
    }
});

function initializeSelections() {
    // Set default bible if none selected
    if (selectedBibles.length === 0) {
        for (let langKey in biblesByLanguage) {
            for (let bible of biblesByLanguage[langKey].bibles) {
                if (bible.isDefault && !bible.hide) {
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
            // Skip hidden bibles
            if (bible.hide) return;

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
    
    // If this is the first bible, update book/chapter dropdowns
    if (selectedBibles.length > 0) {
        loadBooksForBible(selectedBibles[0]);
    } else {
        // If no bibles selected, update URL without book/chapter
        const params = new URLSearchParams();
        if (selectedLanguages.length > 0) params.set('langs', selectedLanguages.join(','));
        const newURL = window.location.pathname + '?' + params.toString();
        window.history.replaceState({}, '', newURL);
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
    fetch(`api.php?action=getBooks&bible=${encodeURIComponent(bibleAbbr)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                booksData = data.books;
                updateBookDropdown();
                updateChapters();
            }
        })
        .catch(error => {
            // Error loading books
        });
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
    
    // Update chapter navigation buttons
    updateChapterNavigationButtons();
    
    // Update book navigation buttons
    updateBookNavigationButtons();
    
    // Load verses after updating chapters
    loadVerses();
}

// Chapter Navigation Functions
function previousChapter() {
    const chapterSelect = document.getElementById('chapterSelect');
    const currentChapter = parseInt(chapterSelect.value);
    
    if (currentChapter > 1) {
        chapterSelect.value = currentChapter - 1;
        loadVerses();
        updateChapterNavigationButtons();
    } else {
        // Go to previous book, last chapter
        const bookSelect = document.getElementById('bookSelect');
        const currentBookNo = parseInt(bookSelect.value);
        const currentBookIndex = booksData.findIndex(b => b.bookNo === currentBookNo);
        
        if (currentBookIndex > 0) {
            const previousBook = booksData[currentBookIndex - 1];
            bookSelect.value = previousBook.bookNo;
            updateChapters(); // This will load the last chapter of the previous book
            
            // Set to last chapter of previous book
            setTimeout(() => {
                const chapterSelect = document.getElementById('chapterSelect');
                const lastChapterOption = chapterSelect.options[chapterSelect.options.length - 1];
                if (lastChapterOption) {
                    chapterSelect.value = lastChapterOption.value;
                    loadVerses();
                    updateChapterNavigationButtons();
                }
            }, 100);
        }
    }
}

function nextChapter() {
    const chapterSelect = document.getElementById('chapterSelect');
    const bookSelect = document.getElementById('bookSelect');
    const currentChapter = parseInt(chapterSelect.value);
    const currentBookNo = parseInt(bookSelect.value);
    
    // Find current book
    const currentBook = booksData.find(b => b.bookNo === currentBookNo);
    
    if (currentBook && currentChapter < currentBook.chapterCount) {
        chapterSelect.value = currentChapter + 1;
        loadVerses();
        updateChapterNavigationButtons();
    } else {
        // Go to next book, first chapter
        const currentBookIndex = booksData.findIndex(b => b.bookNo === currentBookNo);
        
        if (currentBookIndex < booksData.length - 1) {
            const nextBook = booksData[currentBookIndex + 1];
            bookSelect.value = nextBook.bookNo;
            updateChapters(); // This will load chapter 1 of the next book
            
            // Set to first chapter of next book
            setTimeout(() => {
                const chapterSelect = document.getElementById('chapterSelect');
                chapterSelect.value = 1;
                loadVerses();
                updateChapterNavigationButtons();
            }, 100);
        }
    }
}

function updateChapterNavigationButtons() {
    const chapterSelect = document.getElementById('chapterSelect');
    const bookSelect = document.getElementById('bookSelect');
    const prevBtn = document.getElementById('prevChapterBtn');
    const nextBtn = document.getElementById('nextChapterBtn');
    
    if (!chapterSelect || !bookSelect || !prevBtn || !nextBtn) return;
    
    const currentChapter = parseInt(chapterSelect.value);
    const currentBookNo = parseInt(bookSelect.value);
    const currentBook = booksData.find(b => b.bookNo === currentBookNo);
    const currentBookIndex = booksData.findIndex(b => b.bookNo === currentBookNo);
    
    // Enable/disable previous button
    const isFirstChapterOfFirstBook = currentBookIndex === 0 && currentChapter === 1;
    prevBtn.disabled = isFirstChapterOfFirstBook;
    
    // Enable/disable next button
    const isLastChapterOfLastBook = currentBookIndex === (booksData.length - 1) && 
                                   currentBook && currentChapter === currentBook.chapterCount;
    nextBtn.disabled = isLastChapterOfLastBook;
}

// Book Navigation Functions
function previousBook() {
    const bookSelect = document.getElementById('bookSelect');
    const currentBookNo = parseInt(bookSelect.value);
    const currentBookIndex = booksData.findIndex(b => b.bookNo === currentBookNo);
    
    if (currentBookIndex > 0) {
        const previousBook = booksData[currentBookIndex - 1];
        bookSelect.value = previousBook.bookNo;
        updateChapters(); // This will automatically load chapter 1 and update navigation buttons
    }
}

function nextBook() {
    const bookSelect = document.getElementById('bookSelect');
    const currentBookNo = parseInt(bookSelect.value);
    const currentBookIndex = booksData.findIndex(b => b.bookNo === currentBookNo);
    
    if (currentBookIndex < booksData.length - 1) {
        const nextBook = booksData[currentBookIndex + 1];
        bookSelect.value = nextBook.bookNo;
        updateChapters(); // This will automatically load chapter 1 and update navigation buttons
    }
}

function updateBookNavigationButtons() {
    const bookSelect = document.getElementById('bookSelect');
    const prevBtn = document.getElementById('prevBookBtn');
    const nextBtn = document.getElementById('nextBookBtn');
    
    if (!bookSelect || !prevBtn || !nextBtn) return;
    
    const currentBookNo = parseInt(bookSelect.value);
    const currentBookIndex = booksData.findIndex(b => b.bookNo === currentBookNo);

    if (currentBookIndex === -1) {
        // Current book isn't in this Bible's list (e.g. we navigated here via a cross
        // reference into a book the primary Bible doesn't have) - prev/next book are undefined
        prevBtn.disabled = true;
        nextBtn.disabled = true;
        return;
    }

    // Enable/disable previous button
    prevBtn.disabled = currentBookIndex === 0;

    // Enable/disable next button
    nextBtn.disabled = currentBookIndex === (booksData.length - 1);
}

function loadVerses() {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    const selectedBook = parseInt(bookSelect.value);
    const selectedChapter = parseInt(chapterSelect.value);

    // Clear any previously highlighted verse unless a fresh deep-link verse is queued for this load
    if (!window.initialSelectedVerse) {
        currentVerseNumber = null;
    }

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
        fetch(`api.php?action=getVerses&bible=${encodeURIComponent(bibleAbbr)}&book=${selectedBook}&chapter=${selectedChapter}`)
            .then(response => response.json())
    );
    
    Promise.all(promises)
        .then(results => {
            displayVerses(results, selectedBook, selectedChapter);
            updateURL();
            updateMetaTags();
            updateChapterNavigationButtons(); // Update navigation buttons after loading verses
            updateBookNavigationButtons(); // Update book navigation buttons after loading verses
            loadInlineCrossReferences(selectedBook, selectedChapter);
        })
        .catch(error => {
            document.getElementById('versesContainer').innerHTML =
                '<div class="alert alert-danger">Error loading verses. Please try again.</div>';
        });
}

function displayVerses(results, selectedBook, selectedChapter) {
    const container = document.getElementById('versesContainer');
    container.innerHTML = '';

    if (results.length === 0 || !results.some(r => r.success)) {
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

                // Make words clickable by wrapping each word in a span
                const clickableText = makeWordsClickable(verse.verse, selectedBibles[index], selectedBook, selectedChapter, verse.number);
                
                versesContent += `
                    <div class="mb-2">
                        <div class="bible-version">${selectedBibles[index]}</div>
                        <div class="verse-text">${clickableText}</div>
                    </div>
                `;
            }
        });
        
        verseContainer.dataset.verseNumber = verseNumber;

        const crossrefLinkRow = `
            <div class="crossref-inline-row mt-1">
                <span class="crossref-inline-list" id="crossref-inline-${verseNumber}">${renderCrossRefMoreLink(selectedBook, selectedChapter, verseNumber)}</span>
            </div>
        `;

        verseContainer.innerHTML = `
            <div class="d-flex">
                <div class="verse-number p-2 text-center">${verseNumber}</div>
                <div class="flex-grow-1 px-3">
                    ${versesContent}
                    ${crossrefLinkRow}
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary btn-sm copy-btn"
                            onclick="copyVerse(${i})"
                            title="Copy verse">
                        <i class="bi bi-clipboard-check"></i>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(verseContainer);
    }

    scrollToInitialVerseIfNeeded();
}

function scrollToInitialVerseIfNeeded() {
    if (!window.initialSelectedVerse) return;

    const targetVerse = String(window.initialSelectedVerse);
    window.initialSelectedVerse = null; // consume once so the scroll/highlight doesn't refire

    const verseEl = document.querySelector(`.verse-container[data-verse-number="${targetVerse}"]`);
    if (!verseEl) return;

    currentVerseNumber = targetVerse;

    verseEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    verseEl.classList.add('verse-highlight');
    setTimeout(() => verseEl.classList.remove('verse-highlight'), 3000);
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
        btn.classList.remove('btn-outline-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 1000);
    });
}

function updateURL() {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    
    if (bookSelect && chapterSelect && bookSelect.value && chapterSelect.value) {
        const params = new URLSearchParams();
        if (selectedBibles.length > 0) params.set('bibles', selectedBibles.join(','));
        if (selectedLanguages.length > 0) params.set('langs', selectedLanguages.join(','));
        
        // Only set book and chapter if they have valid values
        const bookValue = bookSelect.value;
        const chapterValue = chapterSelect.value;
        
        if (bookValue && chapterValue) {
            params.set('book', bookValue);
            params.set('chapter', chapterValue);
            if (currentVerseNumber) params.set('verse', currentVerseNumber);

            const newURL = window.location.pathname + '?' + params.toString();
            window.history.replaceState({}, '', newURL);
        }
    }
}

function updateMetaTags() {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    
    if (bookSelect && chapterSelect) {
        const bookName = bookSelect.options[bookSelect.selectedIndex].text;
        const chapterNum = chapterSelect.value;
        
        // Get selected Bible information for meta tags
        const selectedBibleInfo = [];
        selectedBibles.forEach(bibleAbbr => {
            for (let langKey in biblesByLanguage) {
                const langData = biblesByLanguage[langKey];
                const bible = langData.bibles.find(b => b.abbr === bibleAbbr);
                if (bible) {
                    selectedBibleInfo.push({
                        abbr: bible.abbr,
                        commonName: bible.commonName,
                        language: langKey
                    });
                    break;
                }
            }
        });
        
        // Build meta tag content with Bible information
        const bibleNames = selectedBibleInfo.map(info => info.commonName);
        const bibleAbbreviations = selectedBibleInfo.map(info => info.abbr);
        const languages = [...new Set(selectedBibleInfo.map(info => info.language))];
        
        // Create formatted strings for meta tags
        const bibleNamesStr = bibleNames.length > 0 ? bibleNames.slice(0, 3).join(', ') : 'Bible';
        const bibleAbbrStr = bibleAbbreviations.length > 0 ? '(' + bibleAbbreviations.slice(0, 3).join(', ') + ')' : '';
        const languagesStr = languages.join(', ');
        
        // Add "and more" if there are more than 3 Bibles selected
        const finalBibleNamesStr = bibleNames.length > 3 ? 
            bibleNamesStr + ' and ' + (bibleNames.length - 3) + ' more versions' : 
            bibleNamesStr;
        const finalBibleAbbrStr = bibleAbbreviations.length > 3 ? 
            '(' + bibleAbbreviations.slice(0, 3).join(', ') + ' +' + (bibleAbbreviations.length - 3) + ')' : 
            bibleAbbrStr;
        
        // Update title and meta tags with Bible information
        document.title = `Online Bibles - ${bookName} Chapter ${chapterNum} | ${finalBibleNamesStr} ${finalBibleAbbrStr} | WordOfGod.in`;
        
        const metaDesc = document.querySelector('meta[name="description"]');
        if (metaDesc) {
            metaDesc.content = `Read ${bookName} Chapter ${chapterNum} in ${finalBibleNamesStr} ${finalBibleAbbrStr}. Compare different Bible translations side by side online.`;
        }
        
        const metaKeywords = document.querySelector('meta[name="keywords"]');
        if (metaKeywords) {
            let keywords = `bible, online bible, ${bookName}, scripture, biblical text, ${finalBibleNamesStr}, ${bibleAbbreviations.join(', ')}`;
            if (languagesStr) {
                keywords += `, ${languagesStr} bible`;
            }
            metaKeywords.content = keywords;
        }
    }
}

// Zoom functionality
function zoomIn() {
    if (currentFontSize < 40) {
        currentFontSize += 2;
        updateFontSize();
    }
}


function zoomOut() {
    if (currentFontSize > 12) {
        currentFontSize -= 2;
        updateFontSize();
    }
}

function resetZoom() {
    currentFontSize = 16;
    updateFontSize();
}

function updateFontSize() {
    document.documentElement.style.setProperty('--font-size-base', currentFontSize + 'px');
}



function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Word Concordance Functions
function makeWordsClickable(text, bibleAbbr, book, chapter, verse) {
    // First, process Strong's numbers
    text = processStrongsNumbers(text, bibleAbbr, book, chapter, verse);
    
    // Split text while preserving HTML tags from Strong's numbers
    // Use a more sophisticated approach that doesn't break HTML tags
    let result = '';
    let currentPos = 0;
    
    // Find all HTML spans (Strong's numbers) and preserve them
    const htmlSpanRegex = /<span[^>]*class="[^"]*(?:strongs-number|emphasis|notes)[^"]*"[^>]*>.*?<\/span>/g;
    let match;
    
    while ((match = htmlSpanRegex.exec(text)) !== null) {
        // Process text before this HTML span
        const beforeSpan = text.substring(currentPos, match.index);
        result += makeTextWordsClickable(beforeSpan, bibleAbbr, book, chapter, verse);

        // For strongs-number spans, preserve as-is
        // For emphasis/notes spans, process inner text to make words clickable
        if (/class="[^"]*strongs-number[^"]*"/.test(match[0])) {
            result += match[0];
        } else {
            const spanParts = match[0].match(/^(<span[^>]*>)([\s\S]*?)(<\/span>)$/);
            if (spanParts) {
                result += spanParts[1] + makeTextWordsClickable(spanParts[2], bibleAbbr, book, chapter, verse) + spanParts[3];
            } else {
                result += match[0];
            }
        }

        currentPos = match.index + match[0].length;
    }

    // Process remaining text after last HTML span
    if (currentPos < text.length) {
        const remainingText = text.substring(currentPos);
        result += makeTextWordsClickable(remainingText, bibleAbbr, book, chapter, verse);
    }

    return result;
}

function makeTextWordsClickable(text, bibleAbbr, book, chapter, verse) {
    // Process plain text (no HTML tags) to make words clickable
    const words = text.split(/(\s+|[.,;:!?'"()[\]{}\-–—])/);

    return words.map(word => {
        // Skip whitespace and punctuation
        if (!word || /^\s*$/.test(word) || /^[.,;:!?'"()[\]{}\-–—]+$/.test(word)) {
            return word;
        }

        // Clean up the word
        const cleanWord = word.trim().replace(/^[.,;:!?'"()[\]{}]+|[.,;:!?'"()[\]{}]+$/g, '');

        // Make words with letters clickable (minimum 2 characters)
        if (cleanWord && cleanWord.length >= 2) {
            // Use data attributes instead of onclick for better reliability
            return `<span class="clickable-word" data-word="${cleanWord}" data-bible="${bibleAbbr}" data-book="${book}" data-chapter="${chapter}" data-verse="${verse}" style="cursor: pointer;" title="Click for concordance/dictionary">${cleanWord}</span>`;
        }

        return word;
    }).join('');
}

function processStrongsNumbers(text, bibleAbbr, book, chapter, verse) {
    // Process Hebrew Strong's numbers <WH####>
    text = text.replace(/<WH(\d+)>/g, (match, number) => {
        return `<span class="strongs-number clickable-word" data-strongs="H${number}" data-bible="${bibleAbbr}" data-book="${book}" data-chapter="${chapter}" data-verse="${verse}" style="color: blueviolet; cursor: pointer; font-size: 90%;" title="Strong's H${number} - Click for concordance/dictionary">H${number}</span>`;
    });

    // Process Greek Strong's numbers <WG####>
    text = text.replace(/<WG(\d+)>/g, (match, number) => {
        return `<span class="strongs-number clickable-word" data-strongs="G${number}" data-bible="${bibleAbbr}" data-book="${book}" data-chapter="${chapter}" data-verse="${verse}" style="color: blueviolet; cursor: pointer; font-size: 90%;" title="Strong's G${number} - Click for concordance/dictionary">G${number}</span>`;
    });

    return text;
}

function getFirstLetter(word) {
    if (!word || word.length === 0) return '';
    
    // Clean the word - remove punctuation and numbers
    const cleanWord = word.replace(/[^\w\u0080-\u0fff\u1000-\u1fff\u2000-\u2fff\u3000-\u3fff\u4000-\u4fff\u5000-\u5fff\u6000-\u6fff\u7000-\u7fff\u8000-\u8fff\u9000-\u9fff\ua000-\uafff\ub000-\ubfff\uc000-\ucfff\ud000-\udfff\ue000-\uefff\uf000-\uffff]/g, '');
    
    if (!cleanWord) return word.charAt(0).toLowerCase();
    
    // Use modern Intl.Segmenter for proper grapheme cluster detection
    // This handles Tamil syllables, emojis, and complex scripts correctly
    if ('Intl' in window && 'Segmenter' in Intl) {
        try {
            const segmenter = new Intl.Segmenter(undefined, { granularity: "grapheme" });
            const segments = Array.from(segmenter.segment(cleanWord));
            
            if (segments.length > 0) {
                const firstSegment = segments[0].segment;
                
                // For Tamil words, return just the first grapheme cluster
                // Don't try to combine segments as Intl.Segmenter already handles complete syllables
                if (/[\u0b80-\u0bff]/.test(firstSegment)) {
                    return firstSegment;
                }
                
                return firstSegment;
            }
        } catch (error) {
            // Intl.Segmenter not supported, falling back to simple approach
        }
    }
    
    // Fallback for older browsers - use simple character detection
    // For Tamil words - try to detect syllables manually
    if (/[\u0b80-\u0bff]/.test(cleanWord)) {
        // Check for consonant + vowel combinations (Tamil syllables)
        const tamilSyllablePattern = /^([\u0b95-\u0bb9][\u0bbe-\u0bcc]?)/;
        const syllableMatch = cleanWord.match(tamilSyllablePattern);
        if (syllableMatch) {
            return syllableMatch[1];
        }
        
        return cleanWord.charAt(0);
    }
    
    // For English and other languages, return lowercase first letter
    return cleanWord.charAt(0).toLowerCase();
}

function getBibleLanguage(bibleAbbr) {
    // Find the language for this bible
    for (let langKey in biblesByLanguage) {
        for (let bible of biblesByLanguage[langKey].bibles) {
            if (bible.abbr === bibleAbbr) {
                return langKey;
            }
        }
    }
    return 'தமிழ்'; // Default to Tamil
}

function openConcordance(word, bibleAbbr) {
    
    // Check if this is a Strong's number
    if (word.match(/^[HG]\d+$/)) {
        // This is a Strong's number
        const strongsType = word.charAt(0); // H or G
        const strongsNumber = word.substring(1); // the number part
        const language = getBibleLanguage(bibleAbbr);
        
        // For Strong's numbers, use the correct concordance URL structure with H/G prefix
        const concordanceUrl = `../bible-concordance/data/${language}/${bibleAbbr}/words/Strongs/Strongs-${strongsType}${strongsNumber}.json`;
        
        // Show the modal and load data (display label differs from the raw word used for dictionary lookups)
        showConcordanceModal(`Strong's ${word}`, concordanceUrl, word, bibleAbbr);
        return;
    }
    
    // Regular word concordance logic
    const firstLetter = getFirstLetter(word);
    const language = getBibleLanguage(bibleAbbr);
    
    // Detect language type for better logging
    let languageType = 'Unknown';
    if (/[\u0b80-\u0bff]/.test(word)) languageType = 'Tamil';
    else if (/^[a-zA-Z]+$/.test(word)) languageType = 'English/Latin';
    else if (/[\u0900-\u097f]/.test(word)) languageType = 'Hindi/Devanagari';
    else if (/[\u0600-\u06ff]/.test(word)) languageType = 'Arabic';
    else if (/[\u4e00-\u9fff]/.test(word)) languageType = 'Chinese';
    else if (/[\u3040-\u309f\u30a0-\u30ff]/.test(word)) languageType = 'Japanese';
    else if (/[\uac00-\ud7af]/.test(word)) languageType = 'Korean';
    
    // Construct the concordance URL
    const concordanceUrl = `../bible-concordance/data/${language}/${bibleAbbr}/words/${firstLetter}/${firstLetter}-${word}.json`;
    
    // Show the modal and load data
    showConcordanceModal(word, concordanceUrl, null, bibleAbbr);
}

function showConcordanceModal(word, url, dictionaryWord, bibleAbbr) {
    // Set the modal title
    document.getElementById('concordanceModalLabel').textContent = `Concordance: ${word}`;

    // Track the word currently shown in the modal (used for the Dictionary tab)
    // Any in-flight dictionary fetches for a previous word are invalidated by this reassignment
    // For Strong's numbers, the display label ("Strong's H7225") differs from the raw
    // lookup word ("H7225") the dictionary API expects
    currentModalWord = dictionaryWord || word;
    loadedDictionaryWord = '';

    // Always reopen on the Concordance tab so Dictionary data only loads on an explicit click,
    // and stale content from a previous word is never left showing on the Dictionary pane
    const concordanceTabEl = document.getElementById('concordance-tab');
    if (concordanceTabEl && window.bootstrap && bootstrap.Tab) {
        bootstrap.Tab.getOrCreateInstance(concordanceTabEl).show();
    }
    document.getElementById('dictionaryContent').innerHTML = '<div class="text-muted">Loading dictionary entries will start when you open the Dictionary tab.</div>';

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('concordanceModal'));
    modal.show();

    // Load concordance data
    loadConcordanceData(url, word, bibleAbbr);
}

function loadConcordanceData(url, word, bibleAbbr) {
    const concordanceContent = document.getElementById('concordanceContent');
    const dictionaryContent = document.getElementById('dictionaryContent');

    // Show loading in concordance tab
    concordanceContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    dictionaryContent.innerHTML = '<div class="text-muted">Loading dictionary entries will start when you open the Dictionary tab.</div>';

    // Fetch concordance data
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            displayConcordanceResults(data, word);
        })
        .catch(error => {
            concordanceContent.innerHTML = `<div class="alert alert-warning">No concordance data found for "${word}" in ${bibleAbbr}.</div>`;
        });
}

function displayConcordanceResults(data, word) {
    const concordanceContent = document.getElementById('concordanceContent');
    
    if (!data || !data.verses || data.verses.length === 0) {
        concordanceContent.innerHTML = `<div class="alert alert-info">No verses found for "${word}".</div>`;
        return;
    }
    
    // Single card containing all verses
    let html = '<div class="concordance-results"><div class="concordance-item p-3 border rounded"><div class="verse-list">';
    
    data.verses.forEach((verseData, index) => {
        // Highlight the word in the verse text
        const highlightedVerse = highlightWordInText(verseData.verse, word);
        
        // Format: number. verse - reference (all in one line)
        html += `
            <div class="verse-item mb-2">
                <span class="text-primary fw-bold">${index + 1}.</span>
                <span class="verse-text">${highlightedVerse}</span>
                <span class="verse-separator"> - </span>
                <span class="text-primary fw-semibold">${verseData.reference}</span>
            </div>
        `;
    });
    
    html += '</div></div></div>';
    concordanceContent.innerHTML = html;
}

function loadDictionaryData(word) {
    const dictionaryContent = document.getElementById('dictionaryContent');

    // Avoid re-fetching if we've already loaded this word's dictionary data
    if (loadedDictionaryWord === word) {
        return;
    }
    loadedDictionaryWord = word;

    dictionaryContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    const listUrl = `${WORDOFGOD_ORIGIN}/bibledictionary/api.php?action=getDictionaries&word=${encodeURIComponent(word)}`;

    fetch(listUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            // Ignore late responses for a word the user has already navigated away from
            if (currentModalWord !== word) return;

            const dictionaries = (data && data.dictionaries) || [];
            if (dictionaries.length === 0) {
                dictionaryContent.innerHTML = `<div class="alert alert-info">No dictionary entries found for "${word}".</div>`;
                return;
            }
            renderDictionarySections(dictionaries, word);
        })
        .catch(error => {
            if (currentModalWord !== word) return;
            dictionaryContent.innerHTML = `<div class="alert alert-warning">No dictionary data found for "${word}".</div>`;
        });
}

function formatDictionaryLabel(dictionarySlug) {
    if (DICTIONARY_LABELS[dictionarySlug]) {
        return DICTIONARY_LABELS[dictionarySlug];
    }
    // Fallback: turn hyphenated slug into a readable label
    return dictionarySlug.split('-').map(part => {
        return /^[a-zA-Z]/.test(part) ? part.charAt(0).toUpperCase() + part.slice(1) : part;
    }).join(' ');
}

function renderDictionarySections(dictionaries, word) {
    if (currentModalWord !== word) return;

    const dictionaryContent = document.getElementById('dictionaryContent');

    let html = '<div class="dictionary-results" id="dictionaryAccordion">';
    dictionaries.forEach((entry, index) => {
        const sectionId = `dictionary-section-${index}`;
        const collapseId = `dictionary-collapse-${index}`;
        html += `
            <div class="dictionary-section mb-2 border rounded">
                <h6 class="mb-0">
                    <button class="btn dictionary-section-toggle w-100 text-start d-flex justify-content-between align-items-center collapsed"
                            type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                            aria-expanded="false" aria-controls="${collapseId}">
                        <span class="text-primary fw-bold">${formatDictionaryLabel(entry.dictionary)}</span>
                        <i class="bi bi-chevron-down dictionary-toggle-icon"></i>
                    </button>
                </h6>
                <div id="${collapseId}" class="collapse" data-bs-parent="#dictionaryAccordion">
                    <div id="${sectionId}" class="dictionary-section-body p-2">
                        <div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    dictionaryContent.innerHTML = html;

    dictionaries.forEach((entry, index) => {
        const sectionId = `dictionary-section-${index}`;
        const entryUrl = `${WORDOFGOD_ORIGIN}/bibledictionary/${encodeURIComponent(entry.dictionary)}/data/${encodeURIComponent(entry.slug)}.json`;

        fetch(entryUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(entryData => {
                // Section IDs are reused per word, so confirm this response still belongs
                // to the word currently shown in the modal before touching the DOM
                if (currentModalWord !== word) return;
                displayDictionaryEntry(sectionId, entryData, word);
            })
            .catch(error => {
                if (currentModalWord !== word) return;
                const sectionEl = document.getElementById(sectionId);
                if (sectionEl) {
                    sectionEl.innerHTML = `<div class="alert alert-warning mb-0">No entry found for "${word}".</div>`;
                }
            });
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function displayDictionaryEntry(sectionId, entryData, word) {
    const sectionEl = document.getElementById(sectionId);
    if (!sectionEl) return;

    const sections = (entryData && entryData.sections) || [];
    if (sections.length === 0) {
        sectionEl.innerHTML = `<div class="alert alert-info mb-0">No entry found for "${word}".</div>`;
        return;
    }

    let html = '';
    if (entryData && entryData.word) {
        html += `<div class="dictionary-entry-word mb-2">${escapeHtml(entryData.word)}</div>`;
    }
    sections.forEach(section => {
        html += `<div class="dictionary-paragraph mb-2">${section.paragraph}</div>`;
    });
    sectionEl.innerHTML = html;
}

function loadDevotionsData(book, chapter, verse, language) {
    const devotionsContent = document.getElementById('devotionsContent');
    const key = `${language}|${book}|${chapter}|${verse}`;

    // Avoid re-fetching if we've already loaded devotions for this verse
    if (loadedDevotionsKey === key) {
        return;
    }
    loadedDevotionsKey = key;

    devotionsContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    const listUrl = `${WORDOFGOD_ORIGIN}/bible-devotions/api.php?action=getDevotions&lang=${encodeURIComponent(language)}&book=${encodeURIComponent(book)}&chapter=${encodeURIComponent(chapter)}&verse=${encodeURIComponent(verse)}`;

    fetch(listUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            // Ignore late responses for a verse the user has already navigated away from
            if (loadedDevotionsKey !== key) return;

            const devotions = Array.isArray(data) ? data : [];
            if (devotions.length === 0) {
                devotionsContent.innerHTML = '<div class="alert alert-info">No devotions found for this verse.</div>';
                return;
            }
            renderDevotionSections(devotions, language, key);
        })
        .catch(error => {
            if (loadedDevotionsKey !== key) return;
            devotionsContent.innerHTML = '<div class="alert alert-warning">No devotions found for this verse.</div>';
        });
}

function formatDevotionBrandLabel(brandSlug) {
    return brandSlug.split('-').map(part => {
        return /^[a-zA-Z]/.test(part) ? part.charAt(0).toUpperCase() + part.slice(1) : part;
    }).join(' ');
}

function renderDevotionSections(devotions, language, key) {
    if (loadedDevotionsKey !== key) return;

    const devotionsContent = document.getElementById('devotionsContent');

    let html = '<div class="devotion-results" id="devotionsAccordion">';
    devotions.forEach((entry, index) => {
        const sectionId = `devotion-section-${index}`;
        const collapseId = `devotion-collapse-${index}`;
        html += `
            <div class="devotion-section mb-2 border rounded">
                <h6 class="mb-0">
                    <button class="btn devotion-section-toggle w-100 text-start d-flex justify-content-between align-items-center collapsed"
                            type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                            aria-expanded="false" aria-controls="${collapseId}">
                        <span>
                            <span class="d-block devotion-brand-label">${escapeHtml(formatDevotionBrandLabel(entry.brand))}</span>
                            <span class="text-primary fw-bold">${escapeHtml(entry.title)}</span>
                        </span>
                        <i class="bi bi-chevron-down devotion-toggle-icon"></i>
                    </button>
                </h6>
                <div id="${collapseId}" class="collapse" data-bs-parent="#devotionsAccordion">
                    <div id="${sectionId}" class="devotion-section-body p-2">
                        <div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    devotionsContent.innerHTML = html;

    devotions.forEach((entry, index) => {
        const sectionId = `devotion-section-${index}`;
        const entryUrl = `${WORDOFGOD_ORIGIN}/bible-devotions/${encodeURIComponent(entry.brand)}/meditations/${encodeURIComponent(language)}/${encodeURIComponent(entry.filename)}`;

        fetch(entryUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(entryData => {
                // Section IDs are reused per verse, so confirm this response still belongs
                // to the verse currently shown in the modal before touching the DOM
                if (loadedDevotionsKey !== key) return;
                displayDevotionEntry(sectionId, entryData);
            })
            .catch(error => {
                if (loadedDevotionsKey !== key) return;
                const sectionEl = document.getElementById(sectionId);
                if (sectionEl) {
                    sectionEl.innerHTML = '<div class="alert alert-warning mb-0">Could not load this devotion.</div>';
                }
            });
    });
}

// Fields rendered as raw trusted HTML from our own devotions API (e.g. audio/video embeds)
const DEVOTION_HTML_FIELDS = new Set(['embed']);
const DEVOTION_SKIP_FIELDS = new Set(['uniqueid', 'title', 'date']);

function displayDevotionEntry(sectionId, entryData) {
    const sectionEl = document.getElementById(sectionId);
    if (!sectionEl) return;

    if (!entryData) {
        sectionEl.innerHTML = '<div class="alert alert-info mb-0">Could not load this devotion.</div>';
        return;
    }

    let html = '';

    Object.keys(entryData).forEach(fieldKey => {
        if (DEVOTION_SKIP_FIELDS.has(fieldKey)) return;

        const field = entryData[fieldKey];
        if (!field || typeof field !== 'object') return;

        const label = field.label ? escapeHtml(field.label) : formatDevotionBrandLabel(fieldKey);

        if (fieldKey === 'audio_mp3' && field.url) {
            html += `
                <div class="devotion-field">
                    <div class="devotion-field-label">${label}</div>
                    <audio controls class="w-100" src="${escapeHtml(field.url)}"></audio>
                </div>
            `;
            return;
        }

        if (fieldKey === 'author') {
            const authorName = field.author ? escapeHtml(field.author) : '';
            const website = field.website ? `<a href="${escapeHtml(field.website)}" target="_blank" rel="noopener">${escapeHtml(field.website)}</a>` : '';
            html += `
                <div class="devotion-field">
                    <div class="devotion-field-label">${label}</div>
                    <div class="devotion-field-text">${authorName}${website ? ' - ' + website : ''}</div>
                </div>
            `;
            return;
        }

        if (DEVOTION_HTML_FIELDS.has(fieldKey) && field.text) {
            html += `
                <div class="devotion-field">
                    <div class="devotion-field-label">${label}</div>
                    <div class="devotion-field-text">${field.text}</div>
                </div>
            `;
            return;
        }

        if (field.text) {
            html += `
                <div class="devotion-field">
                    <div class="devotion-field-label">${label}</div>
                    <div class="devotion-field-text">${escapeHtml(field.text)}</div>
                </div>
            `;
        }
    });

    sectionEl.innerHTML = html || '<div class="alert alert-info mb-0">Could not load this devotion.</div>';
}

function populateCommentarySourceSelect() {
    const select = document.getElementById('commentarySourceSelect');
    if (!select || select.dataset.populated === 'true') return;

    select.innerHTML = COMMENTARY_SOURCES.map(source =>
        `<option value="${source.key}">${escapeHtml(source.label)}</option>`
    ).join('');
    select.dataset.populated = 'true';

    select.addEventListener('change', function() {
        currentCommentarySource = select.value;
        loadCommentaryData(currentModalBook, currentModalChapter);
    });
}

function loadCommentaryData(book, chapter) {
    populateCommentarySourceSelect();
    const select = document.getElementById('commentarySourceSelect');
    if (select) select.value = currentCommentarySource;

    const key = `${book}|${chapter}|${currentCommentarySource}`;
    if (loadedCommentaryKey === key) {
        renderCommentarySections(key);
        return;
    }

    const sectionsEl = document.getElementById('commentarySections');
    sectionsEl.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    const paddedBook = pad(book, 2);
    const paddedChapter = pad(chapter, 3);
    const commentaryUrl = `${WORDOFGOD_ORIGIN}/bible-commentaries/data/${currentCommentarySource}/${paddedBook}/${paddedChapter}.json`;

    fetch(commentaryUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (loadedCommentaryKey === key) return; // already resolved by a newer/duplicate call
            currentCommentarySections = {
                meta: data.commentary || null,
                sections: Array.isArray(data.sections) ? data.sections : []
            };
            loadedCommentaryKey = key;
            renderCommentarySections(key);
        })
        .catch(error => {
            if (loadedCommentaryKey === key) return;
            currentCommentarySections = { meta: null, sections: [] };
            loadedCommentaryKey = key;
            renderCommentarySections(key);
        });
}

// Commentary sources ship their own scoped CSS (colors, table styling) with %COLOR_X% placeholder
// tokens. Substitute the tokens with the app's palette and scope every rule under the content
// container so it can't leak out and affect the rest of the page.
const COMMENTARY_STYLE_COLORS = {
    '%COLOR_GREEN%': '#198754',
    '%COLOR_BLUE%': '#0d6efd'
};

function scopeCommentaryStyle(css, scopeSelector) {
    let scoped = css;
    Object.keys(COMMENTARY_STYLE_COLORS).forEach(token => {
        scoped = scoped.split(token).join(COMMENTARY_STYLE_COLORS[token]);
    });

    return scoped.replace(/([^{}]+)\{([^{}]*)\}/g, function(match, selectors, body) {
        const scopedSelectors = selectors.split(',')
            .map(s => `${scopeSelector} ${s.trim()}`)
            .join(', ');
        return `${scopedSelectors} { ${body} }`;
    });
}

// Sections without a real verse range (verse_from/verse_to null or 0) aren't necessarily all
// the chapter introduction - some sources use it for several untitled sections in one chapter.
// Only the first one is labelled "Introduction"; the rest are numbered "Section 2", "Section 3", ...
// so the table of contents doesn't show a wall of identical "Introduction" entries.
function commentarySectionLabels(sections) {
    let untitledCount = 0;
    return sections.map(section => {
        const from = section.verse_from;
        const to = section.verse_to;
        if (!from && !to) {
            untitledCount++;
            return untitledCount === 1 ? 'Introduction' : `Section ${untitledCount}`;
        }
        if (from === to) return `Verse ${from}`;
        return `Verses ${from}-${to}`;
    });
}

function renderCommentarySections(key) {
    if (loadedCommentaryKey !== key) return;

    const sectionsEl = document.getElementById('commentarySections');
    const { meta, sections } = currentCommentarySections;

    if (sections.length === 0) {
        sectionsEl.innerHTML = '<div class="alert alert-info">No commentary found for this chapter.</div>';
        return;
    }

    let styleTag = '';
    if (meta && meta.html_style) {
        styleTag = `<style>${scopeCommentaryStyle(meta.html_style, '#commentarySections')}</style>`;
    }

    const labels = commentarySectionLabels(sections);

    let tocHtml = '<div class="commentary-toc mb-2"><div class="commentary-toc-label">Contents</div><div class="commentary-toc-list">';
    sections.forEach((_, index) => {
        const sectionId = `commentary-section-${index}`;
        tocHtml += `<a href="#" class="commentary-toc-link" data-target="${sectionId}">${escapeHtml(labels[index])}</a>`;
    });
    tocHtml += '</div></div>';

    let html = styleTag + tocHtml + '<div class="commentary-results">';
    sections.forEach((section, index) => {
        const sectionId = `commentary-section-${index}`;
        html += `
            <div class="commentary-section mb-3" id="${sectionId}">
                <div class="commentary-section-header">${escapeHtml(labels[index])}</div>
                <div class="commentary-section-body">${section.text || ''}</div>
            </div>
        `;
    });
    html += '</div>';

    sectionsEl.innerHTML = html;
}

function pad(num, len) {
    return String(num).padStart(len, '0');
}

function renderCrossRefMoreLink(book, chapter, verse) {
    return `<a href="#" class="crossref-more-link" data-book="${book}" data-chapter="${chapter}" data-verse="${verse}" title="View all cross references">click for more</a>`;
}

function crossRefTargetLabel(entry) {
    const bookInfo = CROSSREF_BOOKS[entry.to_book_no];
    const shortName = bookInfo ? bookInfo.shortName : `Book${entry.to_book_no}`;
    const rangeLabel = entry.to_verse_end > entry.to_verse_start
        ? `${entry.to_verse_start}-${entry.to_verse_end}`
        : `${entry.to_verse_start}`;
    return `${escapeHtml(shortName)} ${entry.to_chapter_no}:${rangeLabel}`;
}

function loadInlineCrossReferences(book, chapter) {
    const requestId = ++crossRefInlineRequestId;

    const paddedBook = pad(book, 2);
    const paddedChapter = pad(chapter, 3);
    const listUrl = `${WORDOFGOD_ORIGIN}/bible-cross-references/data/${DEFAULT_CROSS_REFERENCE_SOURCE}/${paddedBook}/${paddedChapter}.json`;

    fetch(listUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (requestId !== crossRefInlineRequestId) return; // a newer chapter has since loaded

            const entries = Array.isArray(data) ? data : [];
            const byVerse = {};
            entries.forEach(entry => {
                if (entry.book_no !== Number(book) || entry.chapter_no !== Number(chapter)) return;
                const rangeEnd = entry.verse_no_end || entry.verse_no;
                for (let vn = entry.verse_no; vn <= rangeEnd; vn++) {
                    if (!byVerse[vn]) byVerse[vn] = [];
                    byVerse[vn].push(entry);
                }
            });

            Object.keys(byVerse).forEach(verseNumber => {
                const sectionEl = document.getElementById(`crossref-inline-${verseNumber}`);
                if (!sectionEl) return;

                const verseEntries = byVerse[verseNumber].sort((a, b) => (b.votes || 0) - (a.votes || 0));
                const shown = verseEntries.slice(0, INLINE_CROSSREF_LIMIT);

                // Clicking any citation opens the modal for this verse (the source verse,
                // not the target) - same destination as the "click for more" link
                const citationsHtml = shown.map(entry =>
                    `<a href="#" class="crossref-inline-ref" data-book="${book}" data-chapter="${chapter}" data-verse="${verseNumber}">${crossRefTargetLabel(entry)}</a>`
                ).join('; ');

                const moreLink = renderCrossRefMoreLink(book, chapter, verseNumber);
                sectionEl.innerHTML = citationsHtml ? `${citationsHtml} ${moreLink}` : moreLink;
            });
        })
        .catch(error => {
            // Leave the existing "more" icon in place; inline citations are a nice-to-have
        });
}

function openCrossReferences(book, chapter, verse) {
    const tabsEl = document.getElementById('concordanceTabs');
    if (tabsEl) tabsEl.classList.add('mode-crossref');

    const bookInfo = booksData.find(b => b.bookNo === Number(book));
    const bookName = bookInfo ? bookInfo.longName : `Book ${book}`;
    document.getElementById('concordanceModalLabel').textContent = `Cross References: ${bookName} ${chapter}:${verse}`;

    const crossrefTabEl = document.getElementById('crossreferences-tab');
    bootstrap.Tab.getOrCreateInstance(crossrefTabEl).show();

    const modalEl = document.getElementById('concordanceModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    loadCrossReferencesData(book, chapter, verse);
}

function populateCrossRefSourceSelect() {
    const select = document.getElementById('crossrefSourceSelect');
    if (!select || select.dataset.populated === 'true') return;

    select.innerHTML = CROSS_REFERENCE_SOURCES.map(source =>
        `<option value="${source.key}">${escapeHtml(source.label)} (${source.count.toLocaleString()})</option>`
    ).join('');
    select.dataset.populated = 'true';

    select.addEventListener('change', function() {
        currentCrossRefSource = select.value;
        loadCrossReferencesData(currentModalBook, currentModalChapter, currentModalVerse);
    });
}

function loadCrossReferencesData(book, chapter, verse) {
    populateCrossRefSourceSelect();
    const select = document.getElementById('crossrefSourceSelect');
    if (select) select.value = currentCrossRefSource;

    const key = `${book}|${chapter}|${verse}|${currentCrossRefSource}`;
    if (loadedCrossReferencesKey === key) {
        renderCrossRefPanels(key);
        return;
    }

    const panelsEl = document.getElementById('crossrefPanels');
    panelsEl.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    const paddedBook = pad(book, 2);
    const paddedChapter = pad(chapter, 3);
    const listUrl = `${WORDOFGOD_ORIGIN}/bible-cross-references/data/${currentCrossRefSource}/${paddedBook}/${paddedChapter}.json`;

    fetch(listUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (loadedCrossReferencesKey === key) return; // already resolved by a newer/duplicate call
            const entries = Array.isArray(data) ? data : [];
            const verseNum = Number(verse);
            currentCrossRefEntries = entries.filter(entry => {
                return entry.book_no === Number(book) &&
                    entry.chapter_no === Number(chapter) &&
                    verseNum >= entry.verse_no &&
                    verseNum <= (entry.verse_no_end || entry.verse_no);
            }).sort((a, b) => (b.votes || 0) - (a.votes || 0));
            loadedCrossReferencesKey = key;
            renderCrossRefPanels(key);
        })
        .catch(error => {
            if (loadedCrossReferencesKey === key) return;
            currentCrossRefEntries = [];
            loadedCrossReferencesKey = key;
            renderCrossRefPanels(key);
        });
}

function renderCrossRefPanels(key) {
    if (loadedCrossReferencesKey !== key) return;

    const panelsEl = document.getElementById('crossrefPanels');

    if (currentCrossRefEntries.length === 0) {
        panelsEl.innerHTML = '<div class="alert alert-info">No cross references found for this verse.</div>';
        return;
    }

    let html = '<div class="crossref-results">';
    selectedBibles.forEach((bibleAbbr, index) => {
        const sectionId = `crossref-section-${index}`;
        const collapseId = `crossref-collapse-${index}`;
        html += `
            <div class="crossref-section mb-2 border rounded">
                <h6 class="mb-0">
                    <button class="btn crossref-section-toggle w-100 text-start d-flex justify-content-between align-items-center collapsed"
                            type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                            aria-expanded="false" aria-controls="${collapseId}">
                        <span class="text-primary fw-bold">${escapeHtml(bibleAbbr)} &mdash; ${currentCrossRefEntries.length} reference(s)</span>
                        <i class="bi bi-chevron-down crossref-toggle-icon"></i>
                    </button>
                </h6>
                <div id="${collapseId}" class="collapse">
                    <div id="${sectionId}" class="crossref-section-body p-2" data-bible="${escapeHtml(bibleAbbr)}" data-loaded="false">
                        <div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    panelsEl.innerHTML = html;

    selectedBibles.forEach((bibleAbbr, index) => {
        const collapseEl = document.getElementById(`crossref-collapse-${index}`);
        const sectionId = `crossref-section-${index}`;
        collapseEl.addEventListener('shown.bs.collapse', function() {
            if (loadedCrossReferencesKey !== key) return;
            const sectionEl = document.getElementById(sectionId);
            if (!sectionEl || sectionEl.dataset.loaded === 'true') return;
            loadCrossRefVersesForBible(bibleAbbr, sectionId, key);
        });
    });
}

function loadCrossRefVersesForBible(bibleAbbr, sectionId, key) {
    const entries = currentCrossRefEntries;
    const refs = entries.map(entry => ({
        book: entry.to_book_no,
        chapter: entry.to_chapter_no,
        verseStart: entry.to_verse_start,
        verseEnd: entry.to_verse_end
    }));

    fetch('api.php?action=getVersesByRefs', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bible: bibleAbbr, refs: refs })
    })
        .then(response => response.json())
        .then(data => {
            if (loadedCrossReferencesKey !== key) return;
            const sectionEl = document.getElementById(sectionId);
            if (!sectionEl) return;

            if (!data.success || !Array.isArray(data.verses)) {
                sectionEl.innerHTML = '<div class="alert alert-warning mb-0">Could not load cross-referenced verses.</div>';
                return;
            }

            sectionEl.dataset.loaded = 'true';
            let html = '';
            data.verses.forEach((resolved, i) => {
                const entry = entries[i];
                if (!resolved || !resolved.text) return;

                const rangeLabel = resolved.verseEnd > resolved.verseStart
                    ? `${resolved.verseStart}-${resolved.verseEnd}`
                    : `${resolved.verseStart}`;

                html += `
                    <div class="crossref-item mb-2 pb-2 border-bottom">
                        <a href="#" class="crossref-ref-link fw-bold text-primary"
                           data-book="${entry.to_book_no}" data-chapter="${entry.to_chapter_no}" data-verse="${entry.to_verse_start}">
                            ${escapeHtml(resolved.bookName || '')} ${resolved.chapter}:${rangeLabel}
                        </a>
                        <div class="crossref-verse-text">${escapeHtml(resolved.text)}</div>
                    </div>
                `;
            });

            sectionEl.innerHTML = html || '<div class="alert alert-info mb-0">No verse text found.</div>';
        })
        .catch(error => {
            if (loadedCrossReferencesKey !== key) return;
            const sectionEl = document.getElementById(sectionId);
            if (sectionEl) {
                sectionEl.innerHTML = '<div class="alert alert-warning mb-0">Could not load cross-referenced verses.</div>';
            }
        });
}

function navigateToCrossReference(bookNo, chapterNo, verseNo) {
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');

    // `booksData` only reflects whichever single Bible currently drives the book/chapter
    // dropdown, which may be a partial-canon edition (e.g. Old-Testament-only). A reference
    // can still be valid even if that particular Bible doesn't have it - one of the other
    // currently selected Bibles might (that's often exactly the panel the user clicked from).
    // Fall back to the canonical book table and inject a temporary <option> so navigation
    // still works; loadVerses() will simply skip any selected Bible that lacks this book.
    let chapterCount = booksData.find(b => b.bookNo === Number(bookNo))?.chapterCount;
    if (!chapterCount) {
        const fallbackBook = CROSSREF_BOOKS[bookNo];
        if (!fallbackBook) return; // unknown book number, nothing we can do
        chapterCount = fallbackBook.chapterCount;
        if (!bookSelect.querySelector(`option[value="${bookNo}"]`)) {
            const option = document.createElement('option');
            option.value = bookNo;
            option.textContent = fallbackBook.longName;
            bookSelect.appendChild(option);
        }
    }

    window.initialSelectedVerse = Number(verseNo);
    bookSelect.value = bookNo;

    chapterSelect.innerHTML = '';
    for (let i = 1; i <= chapterCount; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `Chapter ${i}`;
        if (i === Number(chapterNo)) option.selected = true;
        chapterSelect.appendChild(option);
    }

    updateURL();
    updateChapterNavigationButtons();
    updateBookNavigationButtons();
    loadVerses();

    const modalEl = document.getElementById('concordanceModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
}

function highlightWordInText(text, searchWord) {
    // Don't highlight the exact search word, instead find and highlight the actual word occurrences in the text
    // This is crucial because different verses may have different forms of the same word
    
    // For universal language support, we need to find words that are similar/related to the search word
    // This is more complex for different languages, so let's use a flexible approach
    
    // Clean the search word for comparison
    const cleanSearchWord = searchWord.replace(/[^\w\u0080-\u0fff\u1000-\u1fff\u2000-\u2fff\u3000-\u3fff\u4000-\u4fff\u5000-\u5fff\u6000-\u6fff\u7000-\u7fff\u8000-\u8fff\u9000-\u9fff\ua000-\uafff\ub000-\ubfff\uc000-\ucfff\ud000-\udfff\ue000-\uefff\uf000-\uffff]/g, '');
    
    // Split text into words and check each one
    const words = text.split(/(\s+|[.,;:!?'"()[\]{}\-–—])/);
    
    return words.map(word => {
        if (!word || /^\s*$/.test(word) || /^[.,;:!?'"()[\]{}\-–—]+$/.test(word)) {
            return word;
        }
        
        const cleanWord = word.trim().replace(/^[.,;:!?'"()[\]{}]+|[.,;:!?'"()[\]{}]+$/g, '');
        
        // Check if this word matches our search term
        if (cleanWord && shouldHighlightWord(cleanWord, cleanSearchWord)) {
            return word.replace(cleanWord, `<span style="color: deeppink; font-weight: 600;">${cleanWord}</span>`);
        }
        
        return word;
    }).join('');
}

function shouldHighlightWord(wordInText, searchWord) {
    // Exact match (case insensitive)
    if (wordInText.toLowerCase() === searchWord.toLowerCase()) {
        return true;
    }
    
    // For Tamil and other complex scripts, check if they're substantially similar
    // This handles different forms of the same word
    if (/[\u0b80-\u0bff]/.test(wordInText) && /[\u0b80-\u0bff]/.test(searchWord)) {
        // Tamil word matching - check if they share the same root/base
        const wordRoot = getTamilWordRoot(wordInText);
        const searchRoot = getTamilWordRoot(searchWord);
        if (wordRoot === searchRoot && wordRoot.length >= 2) {
            return true;
        }
        
        // Also check if one word contains the other (for partial matches)
        if (wordInText.includes(searchWord) || searchWord.includes(wordInText)) {
            return true;
        }
    }
    
    // For English and other languages with simpler morphology
    else {
        // Check for exact match or if one contains the other (for different word forms)
        const lowerWord = wordInText.toLowerCase();
        const lowerSearch = searchWord.toLowerCase();
        
        if (lowerWord.includes(lowerSearch) || lowerSearch.includes(lowerWord)) {
            return true;
        }
    }
    
    return false;
}

function getTamilWordRoot(word) {
    // Simple Tamil root extraction - remove common suffixes
    const cleanWord = word.replace(/[^\u0b80-\u0bff]/g, '');
    
    // Remove common Tamil suffixes to get the root
    const commonSuffixes = ['ன்', 'ம்', 'து', 'கள்', 'கல்', 'ல்', 'ர்', 'ய்'];
    
    for (const suffix of commonSuffixes) {
        if (cleanWord.endsWith(suffix) && cleanWord.length > suffix.length + 1) {
            return cleanWord.slice(0, -suffix.length);
        }
    }
    
    // If no suffix found, return the clean word
    return cleanWord;
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Initialize global variables from PHP
function initializeGlobalVariables(phpData) {
    selectedBibles = phpData.selectedBibles || [];
    selectedLanguages = phpData.selectedLanguages || [];
    biblesByLanguage = phpData.biblesByLanguage || {};
    booksData = phpData.booksData || [];
    chapterCounts = phpData.chapterCounts || {};
    
    // Initialize the interface after data is loaded
    if (selectedBibles.length > 0) {
        updateSelectedBiblesDisplay();
        updateLanguageButtons();
        updateBibleButtons();
        
        // If we have books data, update the dropdowns
        if (booksData.length > 0) {
            updateBookDropdown();
            updateChapters();
        }
    }
    
    // Initialize keyboard shortcuts
    initializeKeyboardShortcuts();
}

// Keyboard shortcuts for chapter navigation
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(event) {
        // Only trigger if not typing in an input field
        if (event.target.tagName.toLowerCase() === 'input' || 
            event.target.tagName.toLowerCase() === 'textarea' ||
            event.target.tagName.toLowerCase() === 'select') {
            return;
        }
        
        // Left arrow or comma for previous chapter
        if (event.key === 'ArrowLeft' || event.key === ',') {
            event.preventDefault();
            const prevBtn = document.getElementById('prevChapterBtn');
            if (prevBtn && !prevBtn.disabled) {
                previousChapter();
            }
        }
        
        // Right arrow or period for next chapter
        if (event.key === 'ArrowRight' || event.key === '.') {
            event.preventDefault();
            const nextBtn = document.getElementById('nextChapterBtn');
            if (nextBtn && !nextBtn.disabled) {
                nextChapter();
            }
        }
        
        // Shift + Left arrow for previous book
        if (event.key === 'ArrowLeft' && event.shiftKey) {
            event.preventDefault();
            const prevBookBtn = document.getElementById('prevBookBtn');
            if (prevBookBtn && !prevBookBtn.disabled) {
                previousBook();
            }
        }
        
        // Shift + Right arrow for next book
        if (event.key === 'ArrowRight' && event.shiftKey) {
            event.preventDefault();
            const nextBookBtn = document.getElementById('nextBookBtn');
            if (nextBookBtn && !nextBookBtn.disabled) {
                nextBook();
            }
        }
    });
}