<?php
    include 'counter.php';

// Load bibles.json
$biblesJson = file_get_contents('data/bibles.json');
$biblesData = json_decode($biblesJson, true);

// Find default bible
$defaultBibleIndex = 0;
foreach ($biblesData['bibles'] as $i => $bible) {
    if (isset($bible['isDefault']) && $bible['isDefault'] === true) {
        $defaultBibleIndex = $i;
        break;
    }
}
$defaultBible = $biblesData['bibles'][$defaultBibleIndex];

// Get first book and chapter
$firstBook = $defaultBible['books'][0];
$firstChapter = 1;

// Build path for chapter data
$chapterPath = "data/{$defaultBible['info']['abbr']}/{$firstBook['bookNo']}-{$firstBook['longName']}/{$firstChapter}.json";
$chapterData = [];
if (file_exists($chapterPath)) {
    $chapterJson = file_get_contents($chapterPath);
    $chapterData = json_decode($chapterJson, true);
}

?>
<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="utf-8">
    <title id="dynamic-title">Online Bible</title>
    <meta name="description" id="dynamic-desc" content="Online Bible">
    <style>
        :root {
            --primary-blue: #1565c0;
            --primary-blue-dark: #0d47a1;
            --primary-blue-light: #e3f0fb;
            --accent: #42a5f5;
            --background: #f4f8fb;
            --surface: #fff;
            --border: #cfd8dc;
            --text: #1a237e;
            --text-light: #fff;
            --shadow: 0 2px 8px rgba(21,101,192,0.07);
        }
        body {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--background);
            color: var(--text);
        }
        header, footer {
            background: var(--primary-blue);
            color: var(--text-light);
            padding: 0.4em 0;
            text-align: center;
            letter-spacing: 0.03em;
            font-size: 1.08em;
            box-shadow: var(--shadow);
            min-height: 0;
        }
        .main-menu {
            display: flex;
            gap: 2em;
            justify-content: center;
            align-items: center;
            margin-bottom: 0.2em;
        }
        .menu-link {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            font-size: 1em;
            padding: 0.2em 0.7em;
            border-radius: 4px;
            transition: background 0.18s, color 0.18s;
        }
        .menu-link:hover, .menu-link:focus {
            background: var(--accent);
            color: #fff;
        }
        main {
            padding: 2em 1em 1.5em 1em;
            margin: auto;
            width: 100%;
            max-width: 1200px;
            box-sizing: border-box;
        }
        .dropdown-row {
            display: flex;
            gap: 1em;
            margin-bottom: 1.5em;
            align-items: flex-end;
        }
        .dropdown-group {
            flex: 1 1 0;
            min-width: 0;
        }
        label {
            font-weight: 600;
            margin-bottom: 0.3em;
            display: block;
            color: var(--primary-blue-dark);
            letter-spacing: 0.01em;
        }
        select {
            width: 100%;
            padding: 0.7em 2.5em 0.7em 1em;
            margin-bottom: 0.2em;
            font-size: 1.07em;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            background: var(--surface) url('data:image/svg+xml;utf8,<svg fill="gray" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 0.8em center/1.2em 1.2em;
            appearance: none;
            transition: border 0.2s, box-shadow 0.2s;
            box-shadow: var(--shadow);
            color: var(--text);
        }
        select:focus {
            border: 1.5px solid var(--primary-blue);
            outline: none;
            background-color: var(--primary-blue-light);
            box-shadow: 0 0 0 2px #90caf9;
        }
        .chapter-nav-btn {
            background: var(--primary-blue);
            color: var(--text-light);
            border: none;
            border-radius: 5px;
            padding: 0.7em 1.2em;
            font-size: 1em;
            cursor: pointer;
            margin: 0 0.5em;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: var(--shadow);
        }
        .chapter-nav-btn:hover:not(:disabled) {
            background: var(--accent);
        }
        .chapter-nav-btn:disabled,
        .chapter-nav-btn[disabled] {
            background: var(--border);
            color: #b0bec5;
            cursor: not-allowed;
        }
        .chapter-nav-bottom {
            display: flex;
            justify-content: space-between;
            margin-top: 2em;
        }
        .verse {
            background: var(--surface);
            margin-bottom: 0.7em;
            padding: 1em 1.2em;
            border-radius: 7px;
            display: flex;
            align-items: center;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-blue-light);
            transition: border-color 0.2s;
        }
        .verse-number {
            font-weight: bold;
            margin-right: 0.7em;
            color: var(--primary-blue-dark);
            font-size: 1.1em;
        }
        .verse-text {
            flex: 1;
            font-size: 1.08em;
            color: var(--text);
            line-height: 1.75;
        }
        .copy-btn {
            margin-left: 1em;
            background: var(--accent);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2em;
            color: var(--text-light);
            width: 2.2em;
            height: 2.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 1px 4px rgba(21,101,192,0.08);
        }
        .copy-btn:hover {
            background: var(--primary-blue-dark);
            color: #fff;
        }
        .copy-btn:active {
            background: var(--primary-blue);
        }
        @media (max-width: 900px) {
            main { max-width: 98vw; }
        }
        @media (max-width: 700px) {
            .dropdown-row { flex-direction: column; gap: 0; }
            .chapter-nav-btn { width: 100%; margin: 0.5em 0; }
            .chapter-nav-bottom { flex-direction: column; gap: 0.5em; }
        }
        @media (max-width: 600px) {
            main { padding: 0.5em; max-width: 100vw; }
            select { font-size: 1em; }
            .verse { font-size: 1em; }
        }
        a {
            color: var(--text-light);
            text-decoration: underline;
        }
 
    </style>
</head>
<body>
<header>
    <h1>Online Bible</h1>
    <nav class="main-menu">
        <a href="https://wordofgod.in/bibles" class="menu-link">Home</a>
        <a href="https://wordofgod.in/bible-wallpapers" target="_blank" rel="noopener" class="menu-link">Bible Wallpapers</a>
        <a href="https://wordofgod.in/bibledictionary/" target="_blank" rel="noopener" class="menu-link">Bible Dictionaries</a>
        <a href="sitemap.xml" target="_blank" rel="noopener" class="menu-link">Sitemap</a>
    </nav>
</header>
<main>
    <div class="dropdown-row" style="align-items: flex-end;">
        <button id="prev-chapter-btn" class="chapter-nav-btn" title="Previous Chapter" disabled>&larr; Previous</button>
        <div class="dropdown-group">
            <label for="bible-select">Bible:</label>
            <select id="bible-select">
                <?php foreach ($biblesData['bibles'] as $i => $bible): ?>
                    <option value="<?= $i ?>" <?= $i == $defaultBibleIndex ? 'selected' : '' ?>  title="<?= $bible['info']['shortName'] ?>">
                        <?= htmlspecialchars($bible['info']['shortName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="dropdown-group">
            <label for="book-select">Book:</label>
            <select id="book-select">
                <?php foreach ($defaultBible['books'] as $j => $book): ?>
                    <option value="<?= $j ?>" <?= $j == 0 ? 'selected' : '' ?>>
                        <?= htmlspecialchars($book['longName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="dropdown-group">
            <label for="chapter-select">Chapter:</label>
            <select id="chapter-select">
                <?php for ($c = 1; $c <= $firstBook['chapterCount']; $c++): ?>
                    <option value="<?= $c ?>" <?= $c == 1 ? 'selected' : '' ?>><?= $c ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button id="next-chapter-btn" class="chapter-nav-btn" title="Next Chapter">&rarr; Next</button>
    </div>
    <div id="verses-list">
        <?php if (!empty($chapterData['verses'])): ?>
            <?php foreach ($chapterData['verses'] as $verse): ?>
                <div class="verse" data-verse-number="<?= $verse['number'] ?>">
                    <span class="verse-number"><?= $verse['number'] ?></span>
                    <span class="verse-text"><?= htmlspecialchars($verse['verse']) ?></span>
                    <button class="copy-btn" title="Copy">&#128203;</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="chapter-nav-bottom" style="display: flex; justify-content: space-between; margin-top: 1.5em;">
        <button id="prev-chapter-btn-bottom" class="chapter-nav-btn" title="Previous Chapter" disabled>&larr; Previous</button>
        <button id="next-chapter-btn-bottom" class="chapter-nav-btn" title="Next Chapter">&rarr; Next</button>
    </div>
</main>
<footer>
    <p>&nbsp; &nbsp; No Copyright, Freely Copy and Distribute (as per Matthew 10:8), <a target="_blank" href="https://www.wordofgod.in/">www.WordOfGod.in</a> 
		| <a href="sitemap.php" target="_blank">Sitemap</a> 
		| Visitors: <?= $visitors2 ?></a>
</footer>
<script>
const bibles = <?php echo json_encode($biblesData['bibles']); ?>;
const bibleSelect = document.getElementById('bible-select');
const bookSelect = document.getElementById('book-select');
const chapterSelect = document.getElementById('chapter-select');
const versesList = document.getElementById('verses-list');
const prevBtn = document.getElementById('prev-chapter-btn');
const nextBtn = document.getElementById('next-chapter-btn');
const prevBtnBottom = document.getElementById('prev-chapter-btn-bottom');
const nextBtnBottom = document.getElementById('next-chapter-btn-bottom');

// --- URL helpers ---
function updateUrl() {
    const bibleIdx = bibleSelect.value;
    const bookIdx = bookSelect.value;
    const chapterNum = chapterSelect.value;
    const bible = bibles[bibleIdx];
    const book = bible.books[bookIdx];
    const params = new URLSearchParams({
        bible: bible.info.shortName,
        book: book.longName,
        chapter: chapterNum
    });
    history.replaceState(null, '', '?' + params.toString());
}
function getInitialSelection() {
    const params = new URLSearchParams(window.location.search);
    const bibleShortName = params.get('bible');
    const bookLongName = params.get('book');
    const chapterNum = parseInt(params.get('chapter'), 10);

    let bibleIdx = 0, bookIdx = 0, chapter = 1;

    // Bible
    if (bibleShortName) {
        const idx = bibles.findIndex(b => b.info.shortName === bibleShortName);
        if (idx !== -1) bibleIdx = idx;
    } else {
        // No URL param → look for default
        const idx = bibles.findIndex(b => b.isDefault === true);
        if (idx !== -1) bibleIdx = idx;
    }

    // Book
    const books = bibles[bibleIdx].books;
    if (bookLongName) {
        const idx = books.findIndex(bk => bk.longName === bookLongName);
        if (idx !== -1) bookIdx = idx;
    }
    // Chapter
    if (chapterNum && books[bookIdx] && chapterNum >= 1 && chapterNum <= books[bookIdx].chapterCount) {
        chapter = chapterNum;
    }
    return { bibleIdx, bookIdx, chapter };
}

// --- Dropdown population ---
function updateBooks(callback) {
    const bibleIdx = bibleSelect.value;
    const books = bibles[bibleIdx].books;
    bookSelect.innerHTML = '';
    books.forEach((book, i) => {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = book.longName;
        bookSelect.appendChild(opt);
    });
    updateChapters(callback);
}
function updateChapters(callback) {
    const bibleIdx = bibleSelect.value;
    const bookIdx = bookSelect.value;
    const book = bibles[bibleIdx].books[bookIdx];
    chapterSelect.innerHTML = '';
    for (let i = 1; i <= book.chapterCount; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = i;
        chapterSelect.appendChild(opt);
    }
    if (callback) callback();
    else {
        loadChapter();
        updateChapterNavButtons();
    }
}

// --- Load verses ---
function loadChapter() {
    const bibleIdx = bibleSelect.value;
    const bookIdx = bookSelect.value;
    const chapterNum = chapterSelect.value;
    const bible = bibles[bibleIdx];
    const book = bible.books[bookIdx];
    const abbr = bible.info.abbr;
    const bookDir = `${book.bookNo}-${book.longName}`;
    const path = `data/${abbr}/${bookDir}/${chapterNum}.json`;

    fetch(path)
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(data => {
            versesList.innerHTML = '';
            (data.verses || []).forEach(verse => {
                const div = document.createElement('div');
                div.className = 'verse';
                div.setAttribute('data-verse-number', verse.number);
                div.innerHTML = `
                    <span class="verse-number">${verse.number}</span>
                    <span class="verse-text">${verse.verse}</span>
                    <button class="copy-btn" title="Copy">&#128203;</button>
                `;
                versesList.appendChild(div);
            });
            updateUrl();
            updatePageTitleAndDescription();
        })
        .catch(() => {
            versesList.innerHTML = '<div style="color:red;">Chapter not found.</div>';
            updateUrl();
            updatePageTitleAndDescription();
        });
}

// --- Navigation buttons ---
function updateChapterNavButtons() {
    const bibleIdx = parseInt(bibleSelect.value);
    const bookIdx = parseInt(bookSelect.value);
    const chapterNum = parseInt(chapterSelect.value);
    const books = bibles[bibleIdx].books;
    const chapterCount = books[bookIdx].chapterCount;
    // Previous: disable if first chapter of first book
    const prevDisabled = (bookIdx === 0 && chapterNum === 1);
    prevBtn.disabled = prevDisabled;
    prevBtnBottom.disabled = prevDisabled;
    // Next: disable if last chapter of last book
    const nextDisabled = (bookIdx === books.length - 1 && chapterNum === chapterCount);
    nextBtn.disabled = nextDisabled;
    nextBtnBottom.disabled = nextDisabled;
}
function gotoPrevChapter() {
    let bibleIdx = parseInt(bibleSelect.value);
    let bookIdx = parseInt(bookSelect.value);
    let chapterNum = parseInt(chapterSelect.value);

    if (chapterNum > 1) {
        chapterNum--;
        chapterSelect.value = chapterNum;
        loadChapter();
        updateChapterNavButtons();
    } else if (bookIdx > 0) {
        bookIdx--;
        bookSelect.value = bookIdx;
        updateChapters(() => {
            const lastChapter = bibles[bibleIdx].books[bookIdx].chapterCount;
            chapterSelect.value = lastChapter;
            loadChapter();
            updateChapterNavButtons();
        });
    }
}
function gotoNextChapter() {
    let bibleIdx = parseInt(bibleSelect.value);
    let bookIdx = parseInt(bookSelect.value);
    let chapterNum = parseInt(chapterSelect.value);
    const books = bibles[bibleIdx].books;
    const chapterCount = books[bookIdx].chapterCount;

    if (chapterNum < chapterCount) {
        chapterNum++;
        chapterSelect.value = chapterNum;
        loadChapter();
        updateChapterNavButtons();
    } else if (bookIdx < books.length - 1) {
        bookIdx++;
        bookSelect.value = bookIdx;
        updateChapters(() => {
            chapterSelect.value = 1;
            loadChapter();
            updateChapterNavButtons();
        });
    }
}

// --- Page title and description ---
function updatePageTitleAndDescription() {
    const bibleIdx = bibleSelect.value;
    const bookIdx = bookSelect.value;
    const chapterNum = chapterSelect.value;
    const bible = bibles[bibleIdx];
    const book = bible.books[bookIdx];
    const titleStr = `${book.longName} - ${chapterNum} - ${bible.info.abbr} - ${bible.info.shortName} - ${bible.info.commonName} - ${bible.info.longName}`;
    document.title = titleStr;
    const desc = document.getElementById('dynamic-desc');
    if (desc) desc.setAttribute('content', titleStr);
}

// --- Event listeners ---
bibleSelect.addEventListener('change', () => {
    updateBooks(() => {
        bookSelect.value = 0;
        chapterSelect.value = 1;
        loadChapter();
        updateChapterNavButtons();
    });
});
bookSelect.addEventListener('change', () => {
    updateChapters(() => {
        chapterSelect.value = 1;
        loadChapter();
        updateChapterNavButtons();
    });
});
chapterSelect.addEventListener('change', () => {
    loadChapter();
    updateChapterNavButtons();
});
prevBtn.addEventListener('click', gotoPrevChapter);
nextBtn.addEventListener('click', gotoNextChapter);
prevBtnBottom.addEventListener('click', gotoPrevChapter);
nextBtnBottom.addEventListener('click', gotoNextChapter);

// --- Copy verse ---
versesList.addEventListener('click', function(e) {
    if (e.target.classList.contains('copy-btn')) {
        const verseDiv = e.target.closest('.verse');
        const bibleIdx = bibleSelect.value;
        const bookIdx = bookSelect.value;
        const chapterNum = chapterSelect.value;
        const bible = bibles[bibleIdx];
        const book = bible.books[bookIdx];
        const verseNum = verseDiv.getAttribute('data-verse-number');
        const verseText = verseDiv.querySelector('.verse-text').textContent;
        const copyText = `${book.longName} ${chapterNum}:${verseNum} ${verseText}`;
        navigator.clipboard.writeText(copyText);
        e.target.textContent = '✓';
        setTimeout(() => e.target.innerHTML = '&#128203;', 1000);
    }
});

// --- Initial load: only ONE DOMContentLoaded! ---
document.addEventListener('DOMContentLoaded', () => {
    const { bibleIdx, bookIdx, chapter } = getInitialSelection();
    bibleSelect.value = bibleIdx;
    updateBooks(() => {
        bookSelect.value = bookIdx;
        updateChapters(() => {
            chapterSelect.value = chapter;
            loadChapter();
            updateChapterNavButtons();
        });
    });
});
</script>
</body>
</html>