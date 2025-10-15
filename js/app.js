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
    
    // Add event delegation for clickable words
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('clickable-word')) {
            const word = event.target.getAttribute('data-word');
            const bible = event.target.getAttribute('data-bible');
            openConcordance(word, bible);
        }
    });
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
            displayVerses(results);
            updateURL();
            updateMetaTags();
            updateChapterNavigationButtons(); // Update navigation buttons after loading verses
            updateBookNavigationButtons(); // Update book navigation buttons after loading verses
        })
        .catch(error => {
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
                
                // Make words clickable by wrapping each word in a span
                const clickableText = makeWordsClickable(verse.verse, selectedBibles[index]);
                
                versesContent += `
                    <div class="mb-2">
                        <div class="bible-version">${selectedBibles[index]}</div>
                        <div class="verse-text">${clickableText}</div>
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
    if (currentFontSize < 24) {
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
function makeWordsClickable(text, bibleAbbr) {
    // Use event delegation approach instead of onclick attributes
    // Split by spaces and punctuation, then make each meaningful word clickable
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
            return `<span class="clickable-word" data-word="${cleanWord}" data-bible="${bibleAbbr}" style="cursor: pointer;" title="Click for concordance">${cleanWord}</span>`;
        }
        
        return word;
    }).join('');
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
    showConcordanceModal(word, concordanceUrl);
}

function showConcordanceModal(word, url) {
    // Set the modal title
    document.getElementById('concordanceModalLabel').textContent = `Concordance: ${word}`;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('concordanceModal'));
    modal.show();
    
    // Load concordance data
    loadConcordanceData(url, word);
}

function loadConcordanceData(url, word) {
    const concordanceContent = document.getElementById('concordanceContent');
    const dictionaryContent = document.getElementById('dictionaryContent');
    
    // Show loading in concordance tab
    concordanceContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    dictionaryContent.innerHTML = '<div class="alert alert-info">Dictionary content will be available soon.</div>';
    
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
            // Try local test files for demonstration
            let testFile = null;
            if (word === 'அகங்கரித்துப்' || word.includes('அகங்கரித்து')) {
                testFile = 'concordance-test/test-word.json';
            } else if (word === 'தேवன்' || word === 'தேவன்') {
                testFile = 'concordance-test/test-thevan.json';
            } else if (word.toLowerCase() === 'god' || word.toLowerCase() === 'lord') {
                testFile = 'concordance-test/test-english-word.json';
            }
            
            if (testFile) {
                fetch(testFile)
                    .then(response => response.json())
                    .then(data => {
                        displayConcordanceResults(data, word);
                        // Show info that this is test data
                        const infoAlert = document.createElement('div');
                        infoAlert.className = 'alert alert-info mt-2';
                        infoAlert.innerHTML = '<small><i class="bi bi-info-circle"></i> This is demo data. Full concordance data will be available from the external API.</small>';
                        document.getElementById('concordanceContent').insertBefore(infoAlert, document.getElementById('concordanceContent').firstChild);
                    })
                    .catch(testError => {
                        concordanceContent.innerHTML = `<div class="alert alert-warning">No concordance data found for "${word}". <br><small>Attempted URL: ${url}</small></div>`;
                    });
            } else {
                concordanceContent.innerHTML = `<div class="alert alert-warning">No concordance data found for "${word}". <br><small>Attempted URL: ${url}</small></div>`;
            }
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