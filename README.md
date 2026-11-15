# Online Bibles - Mobile-Friendly Web Application

A responsive, mobile-first web application for reading and comparing multiple Bible versions online. Built with PHP, Bootstrap 5, and modern web technologies.

## Features

### 📱 Mobile-First Design
- Responsive layout optimized for mobile devices
- Touch-friendly interface with horizontal scrolling
- Bootstrap 5+ with custom mobile optimizations
- Floating zoom controls for accessibility

### 📖 Bible Reading Experience
- **Multi-language Support**: English, Tamil (தமிழ்), Kannada (ಕನ್ನಡ)
- **Multiple Bible Versions**: Compare different translations side by side
- **Dynamic Selection**: Easy language and Bible version selection
- **Bookmarkable URLs**: SEO-friendly URLs for sharing specific passages
- **Verse Deep Linking**: Link directly to a specific verse — the page scrolls to it and briefly highlights it
- **Verse Copy Function**: One-click copying of verses with all versions

### 🔍 Navigation & Search
- Horizontal scrollable language/Bible selection
- Dynamic book and chapter dropdowns
- Intelligent default selection based on availability
- URL-based navigation with history support
- Header menu stays tidy: on desktop only the first 7 links show, the rest sit in a **More** dropdown (mobile shows all links in the collapsed menu)

### 📚 Word Study Tools
Clicking any word (or Strong's number) in a verse opens a modal with six tabs:
- **Concordance**: Every other verse where that word appears
- **Dictionary**: Matching entries from available Bible dictionaries
- **Devotions**: Devotional readings available for that specific verse, grouped into collapsible panels (one per devotion, titled with the devotion's title). Opening a panel automatically closes any other open panel.
- **Commentary**: Verse-by-verse commentary sections for the current chapter, with a dropdown to switch between available commentaries (see below)
- **Cross References**: Hidden in this word-click mode (see below) — surfaced through its own entry point instead.
- **Quiz**: An embedded chapter quiz from wordofgod.in for the clicked word's chapter (see below)

### 🔗 Cross References
Every verse shows a line of short citations underneath it (e.g. `Joh 1:1-3; Heb 11:3; Isa 45:18 … click for more`), sourced from a configurable default cross-reference set. Clicking any citation, or the trailing "click for more" link, opens the same word-study modal in a dedicated 4-tab mode — **Cross References** first, then **Devotions**, **Commentary** and **Quiz**, with Concordance/Dictionary hidden (word clicks continue to use the full 6-tab mode, unaffected).

### ❓ Quiz
The **Quiz** tab embeds the wordofgod.in chapter quiz in an iframe (centered, max 600px wide). Besides the word-click and cross-reference modes, every verse has a quiz button (question-mark icon, below the copy and study buttons) that opens the modal directly on the Quiz tab. The quiz is per chapter, so every verse's button in a chapter loads the same quiz. The iframe is lazy-loaded when the tab is shown and only reloaded when the language or chapter changes.

Inside the Cross References tab:
- A dropdown lets you switch between the available cross-reference sources (OB, RST, TSK)
- One collapsible panel per currently-selected Bible shows that source's referenced verses in that translation, lazy-loaded when you expand the panel
- Clicking a resolved verse inside a panel navigates the reader straight to it and closes the modal

### 📜 Commentary
The **Commentary** tab is always available, in both the word-click and cross-reference-click modes, since both already carry the book/chapter needed to look up commentary for the current chapter.

- A dropdown lets you switch between available commentaries (default: `MHWBC` — Matthew Henry's Whole Bible Commentary; also `GNTBC` — Good News தமிழ் வேதாகம விளக்கவுரை)
- The chapter's commentary is split into sections (e.g. "Introduction", "Verses 1-2", "Verse 31"), each shown under its own colored header
- A **Contents** bar of pill-style links sits above the sections, listing every section for the chapter — clicking one jumps straight to that section instead of scrolling past everything before it
- Commentary text is rendered as trusted HTML from the source (it may include its own inline styling and links back to specific verses on wordofgod.in); each commentary's own CSS is scoped to the tab so it can't affect the rest of the page

### ⚡ Performance & SEO
- Fast loading with optimized data structure
- SEO-friendly meta tags and structured URLs
- Browser caching and compression
- Progressive loading of content

## File Structure

```
bible-viewer-app/
├── index.php              # Main application file
├── api.php                # API endpoint for data requests
├── shared-menus.php       # Loads ../shared/menus.json for the header/footer links
├── counter.php            # Records each page load (included at the top of index.php)
├── track.php              # Receives anonymous usage events from js/app.js
├── a.php                  # Admin screen: Tracking + Sitemap (Google sign-in, admins only)
├── sitemap.php            # Sitemap generator (CLI, or admin-only from a.php)
├── config.example.php     # Copy to config.php: admin emails + test mode
├── includes/              # app-config, google-auth-client, counters, tracking, books
├── counters/              # Tracking data as JSON (*.txt, gitignored; only .htaccess is tracked)
├── .htaccess              # URL rewriting and security
├── README.md              # This documentation
├── AGENTS.md              # Guidance for AI coding agents
└── data/
    ├── languages.json     # Language and Bible configuration
    ├── TCB1973/          # Bible version folder
    │   ├── bibles.json   # Book structure and metadata
    │   └── [books]/      # Individual book folders
    │       └── [n].json  # Chapter files with verses
    └── [other bibles]/   # Additional Bible versions
```

## Admin screen & tracking

`a.php` is a Google-sign-in admin screen with three views:

- **Tracking** – page loads and unique visitors (today / 7 / 30 days / all-time, per day and per month), new vs returning visitors, chapters viewed, top books, translations and languages used, translations shown side by side, feature usage (Copy / Study / Quiz button clicks next to each verse, modal tabs clicked, zoom, themes, app installs), Quiz opens by book, modal-panel detail (words opened in the concordance, dictionaries and devotion brands expanded, commentary and cross-reference source views — the default source counts when the tab opens, others when selected), devices, browsers, operating systems, traffic sources and crawler hits.
- **Users** – every Google account that has opened (or tried to open) the admin screen: searchable list, detail view (first/last seen, visits, denied attempts), **Block / Unblock** and **Delete record**. Access itself is still decided only by `config.php`; blocking turns a listed admin away, and the default admin can't be blocked. Records are JSON files in `users/` (gitignored, web access denied).
- **Sitemap** – a link that regenerates `sitemap.xml` and all `sitemap-*.xml` files, plus a list of the generated files.

Setup: copy `config.example.php` to `config.php`. **Only the built-in default admin (see `viewerAdmins()` in `includes/app-config.php`) and the emails listed under `admins` in `config.php` can open the admin screen**; any other signed-in account gets "no access". Sign-in uses the shared `../auth` service (a sibling folder of this app, as on wordofgod.in).
For local testing set `'test_mode' => true` in `config.php`: on `localhost` only, you are treated as signed in as `test_user`. Never enable it on a public server (it is ignored off localhost anyway).

Tracking is anonymous. Counters are small JSON files in `counters/` (blocked from the web by `counters/.htaccess`); no IP addresses or user ids are stored. A first-party cookie `bv_seen` holds just two dates (first visit | last visit) to tell new from returning visitors and to count a browser once per day. `counter.php` records page loads and `track.php` records events that `js/app.js` sends (`trackEvent` / `trackFeature` / `trackChapterView`). The all-time page-load total lives in `counters/counter.txt` (`{"total":N}`). If you deploy over an old install, the former root `counter.txt` is imported once — only while `counters/counter.txt` doesn't exist — and can then be deleted.

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- Web server (Apache/Nginx) with mod_rewrite enabled
- Modern web browser with JavaScript enabled

### Quick Setup
1. **Upload Files**: Copy all files to your web server directory
2. **Verify Data**: Ensure the `data/` folder structure is intact
3. **Test Access**: Navigate to your domain to test the application
4. **Configure Server**: Ensure .htaccess is working for clean URLs

### Web Server Configuration

#### Apache
```apache
# Ensure mod_rewrite is enabled
LoadModule rewrite_module modules/mod_rewrite.so

# In your virtual host or .htaccess
AllowOverride All
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ ^/api/(.*)$ {
    try_files $uri /api.php?action=$1&$query_string;
}
```

## Usage Guide

### Basic Navigation
1. **Select Language**: Tap on language buttons to filter Bible versions
2. **Choose Bibles**: Select one or more Bible versions to compare
3. **Pick Book & Chapter**: Use dropdowns to navigate to specific passages
4. **Read & Compare**: View verses side by side from multiple versions

### URL Structure
The app reads state from query string parameters on `index.php` and keeps the URL bar in sync as you navigate:
- `book` — numeric book ID (matches `bookNo` in the Bible's `bibles.json`)
- `chapter` — chapter number
- `verse` — *(optional)* verse number to scroll to and highlight
- `bibles` — comma-separated Bible abbreviations to compare
- `langs` — comma-separated languages to show tabs for

### Examples
```
https://yourdomain.com/?book=1&chapter=1
https://yourdomain.com/?bibles=TOV2017,TCB1973&book=1&chapter=1
https://yourdomain.com/?langs=தமிழ்&book=1&chapter=1
https://yourdomain.com/?book=19&chapter=119&verse=6
```

### Advanced Features

#### Multi-Bible Comparison
- Select multiple Bible versions across different languages
- Maintain selection order for consistent display
- Remove individual selections with one click

#### Verse Deep Linking
- Add `&verse={num}` to a book/chapter URL to link straight to a specific verse
- On load, the page scrolls to that verse and briefly highlights it, then fades back to normal after a few seconds
- The `verse` param stays in the URL while you're viewing that verse (e.g. toggling a Bible version), and is dropped once you navigate to a different chapter or book

#### Copy Functionality
- Click the copy icon next to any verse
- Copies all versions of that verse with proper attribution
- Perfect for sharing or study purposes

#### Zoom Controls
- **Plus (+)**: Increase font size
- **Minus (-)**: Decrease font size  
- **Reset**: Return to default size
- **Top Arrow**: Smooth scroll to page top

#### Concordance, Dictionary & Devotions
- Click any word (or Strong's number) in a verse to open the word-study modal
- **Concordance** tab loads immediately, showing other occurrences of the word
- **Dictionary** tab lazy-loads entries only when clicked, so lookups don't fire for words the user never inspects
- **Devotions** tab lazy-loads devotions available for the exact book/chapter/verse of the clicked word (not the word itself), rendered as collapsible, accordion-style panels — only one panel can be expanded at a time

#### Cross References
- A configurable default source (`DEFAULT_CROSS_REFERENCE_SOURCE` in `js/app.js`) is fetched per chapter and rendered as short citations under every verse
- Citations are capped at 8 per verse (`INLINE_CROSSREF_LIMIT`); clicking any citation or the "click for more" link opens the full modal for that verse
- The modal's source dropdown (OB / RST / TSK) lets you switch cross-reference sets; switching reloads the per-Bible panels
- Each translation panel lazy-fetches its resolved verse text via `api.php?action=getVersesByRefs` only when expanded
- Reference targets can fall outside the canon of whichever Bible currently drives the book/chapter dropdown (e.g. an Old-Testament-only edition and a New-Testament reference) — navigation falls back to a static 66-book table (`CROSSREF_BOOKS`) so it still works, and the reader simply omits any selected Bible that doesn't have that book
- The tab lazy-loads the same way when entered via a word click and the user switches to it manually (not just via an inline citation click), so it never stays blank regardless of how the modal was opened

#### Quiz
- `loadQuizData()` in `js/app.js` builds the iframe URL from the current modal book/chapter (zero-padded) and the Bible's language; `openVerseQuiz()` backs the per-verse quiz button

#### Commentary
- A configurable default source (`DEFAULT_COMMENTARY_SOURCE` in `js/app.js`, alongside the full `COMMENTARY_SOURCES` list) is lazy-fetched per chapter the first time the tab is shown
- The dropdown lets you switch between available commentaries; switching reloads the sections for the current chapter
- Each section is rendered with a colored header derived from its verse range (`verse_from`/`verse_to`), followed by the section's HTML body. A section with no real verse range (`verse_from`/`verse_to` null or `0`) is labelled "Introduction" only if it's the first such section in the chapter; any further untitled sections are numbered "Section 2", "Section 3", etc. instead of all repeating "Introduction"
- A Contents bar above the sections links to every section by that same header label; clicking one calls `scrollIntoView` on the matching section so long chapters don't require scrolling past every earlier section to reach it
- The source's own `html_style` (CSS with `%COLOR_X%` placeholder tokens) is substituted with the app's palette and scoped under `#commentarySections` before injection, so it only styles that tab's content
- Links embedded in the commentary text point directly at `https://www.wordofgod.in/bibles/?book=&chapter=&verse=`, which `index.php` already reads as query parameters — no special click handling is needed for them to navigate to the referenced verse
- Before insertion, `enrichCommentaryVerseLinks()` appends the reader's current `bibles`/`langs` selection to any such link (matched by hostname, not just an exact string), so following a commentary link keeps the same Bible/language comparison instead of resetting to the site's defaults

## Technical Details

### Data Structure
The application uses a JSON-based data structure:

```json
{
  "biblesByLanguage": {
    "Language": {
      "languageCode": "code",
      "languageName": "Display Name",
      "bibles": [
        {
          "abbr": "BIBLE_CODE",
          "isDefault": boolean,
          "commonName": "Full Name"
        }
      ]
    }
  }
}
```

### API Endpoints
- `GET /api.php?action=getBooks&bible={abbr}` - Retrieve books for a Bible
- `GET /api.php?action=getVerses&bible={abbr}&book={num}&chapter={num}` - Get verses
- `POST /api.php?action=getVersesByRefs` - Batch-resolve cross-reference targets into verse text. Body: `{"bible": "{abbr}", "refs": [{"book": num, "chapter": num, "verseStart": num, "verseEnd": num}, ...]}`. Reads each distinct book/chapter file only once even if many refs share it, and returns `{success, verses: [{book, chapter, verseStart, verseEnd, bookName, text}, ...]}` in the same order as the request (`text`/`bookName` are `null` for a ref the given Bible doesn't have).

### External Word Study APIs
The Concordance, Dictionary, Devotions, Commentary, Cross References, and Quiz features pull data live from wordofgod.in. Dictionary, Devotions, Commentary, and Cross References use a shared `WORDOFGOD_ORIGIN` constant (`js/app.js`) that resolves to whichever host (`wordofgod.in` or `www.wordofgod.in`) the page itself was loaded from — those sibling paths don't send CORS headers, so the fetch must be same-origin, and the site is reachable at both hosts without a forced redirect. On any other host (e.g. local testing) it falls back to the bare `https://wordofgod.in` domain.
- Concordance: `https://.../bible-concordance/data/{language}/{bible}/words/...`
- Dictionary: `{WORDOFGOD_ORIGIN}/bibledictionary/api.php?action=getDictionaries&word={word}`
- Devotions: `{WORDOFGOD_ORIGIN}/bible-devotions/api.php?action=getDevotions&lang={language}&book={num}&chapter={num}&verse={num}`
- Commentary: `{WORDOFGOD_ORIGIN}/bible-commentaries/data/{source}/{book:2digits}/{chapter:3digits}.json` — `{source}` is `MHWBC` (Matthew Henry's Whole Bible Commentary, default) or `GNTBC` (Good News தமிழ் வேதாகம விளக்கவுரை); book/chapter numbers are zero-padded (e.g. `data/MHWBC/01/001.json` for Genesis 1)
- Cross References: `{WORDOFGOD_ORIGIN}/bible-cross-references/data/{source}/{book:2digits}/{chapter:3digits}.json` — `{source}` is `OB` (Open Bible), `RST` (Russian Synodal Bible), or `TSK` (Treasury of Scripture Knowledge); book/chapter numbers are zero-padded (e.g. `data/OB/01/001.json` for Genesis 1)
- Quiz (iframe): `https://wordofgod.in/bible-quiz/embed-quiz.php?lang={language}&type=chapter&set_size=100&embed=1&books={book:2digits}&chapters={book:2digits}-{chapter:3digits}` (e.g. `books=01&chapters=01-001` for Genesis 1)

These require the app to be served from a domain the wordofgod.in APIs allow via CORS; testing from an arbitrary local origin (e.g. `http://localhost`) may show "no data found" for the per-entry lookups even though the list APIs succeed.

### Security Features
- Input validation and sanitization
- SQL injection prevention
- XSS protection headers
- File access restrictions
- CORS handling

## Browser Support
- **Mobile**: iOS Safari 12+, Chrome Mobile 70+, Samsung Internet 10+
- **Desktop**: Chrome 70+, Firefox 65+, Safari 12+, Edge 79+

## Performance Optimization
- **Lazy Loading**: Verses loaded on demand
- **Caching**: Browser caching for static resources
- **Compression**: Gzip compression enabled
- **Minification**: Optimized CSS and JavaScript

## Customization

### Styling
Modify the CSS variables in the `<style>` section of `index.php`:
```css
:root {
    --font-size-base: 16px;
    --primary-color: #0d6efd;
    --border-radius: 8px;
}
```

### Adding New Bible Versions
1. Create folder in `data/` with Bible abbreviation
2. Add `bibles.json` with book structure
3. Create book folders with chapter JSON files
4. Update `data/languages.json` to include new Bible

### Language Support
Add new languages by updating `data/languages.json`:
```json
"New Language": {
    "languageCode": "code",
    "languageName": "Native Name",
    "bibles": [...]
}
```

## Shared menu (header and footer)

The WordOfGod.in links in the header menu and the footer are not hard-coded. They come from `menus.json`, which is maintained by the site owner and kept in the `shared/` folder that sits beside this app on the server:

```
wordofgod.in/
├── shared/menus.json
├── bibles/   (this app)
└── ...
```

`shared-menus.php` (`viewerSharedMenus()`) reads `../shared/menus.json`, re-validates every item, and returns the list to `index.php`, which uses it twice: for the navbar links and for the `|`-separated links in the footer. In the navbar, desktop (lg and up) shows the first 7 links inline and puts the rest in a **More** dropdown; the collapsed mobile menu lists every link. To change the limit, edit the `array_slice($navItems, 0, 7)` call in `index.php`. The footer always lists all links. It:

- skips items with `"enabled": false` (a missing flag means enabled),
- skips this app's own link (any URL whose last path segment is `bibles`),
- rejects URLs that are not `http(s)://` or a single-slash path, and icons that are not `bi-…` classes,
- escapes every label, URL and icon on output,
- falls back to a built-in copy of the original links if the file is missing or invalid, so the menu never disappears.

This repository does not contain the `shared/` folder. To run the app on your own machine, either create `../shared/menus.json` next to the app (using the sample below) or do nothing and the built-in fallback list is used.

### Keeping `menus.json` inside this app

To ship the menu with the app instead of reading the sibling `shared/` folder, only one file needs to change:

1. Copy `menus.json` into the app root (next to `index.php`).
2. In `shared-menus.php`, change the path in the `$file = …` line from `__DIR__ . '/../shared/menus.json'` to `__DIR__ . '/menus.json'`.

Nothing else reads the file, so the templates, validation and fallback list stay as they are. Keep in mind that the copy is then edited and updated by hand, and no longer follows changes made to the shared menu. If the app's folder is web-readable, the file is public anyway (it only holds the same links shown on the site), but you can still deny direct access with a `.htaccess` rule if you prefer.

### `menus.json` schema (sample)

```json
{
  "version": 1,
  "updated": "2026-11-12T13:49:50+00:00",
  "items": [
    {
      "id": "0512665a7c5e",
      "label": "About Us",
      "url": "https://www.wordofgodteam.com/",
      "icon": "bi-people",
      "newTab": true,
      "enabled": true
    },
    {
      "id": "4e5081ceadbb",
      "label": "Online Bibles",
      "url": "https://wordofgod.in/bibles/",
      "icon": "bi-book-half",
      "newTab": true,
      "enabled": false
    }
  ]
}
```

| Field | Type | Meaning |
|---|---|---|
| `version` | number | Schema version (currently `1`). |
| `updated` | string | ISO-8601 time of the last save (informational). |
| `items` | array | Menu entries; **array order = menu order**. |
| `items[].id` | string | Unique 12-character hex id (used by the admin page only). |
| `items[].label` | string | Text shown, up to 60 characters. |
| `items[].url` | string | `http://`, `https://` or a path starting with a single `/`. |
| `items[].icon` | string | Optional [Bootstrap Icons](https://icons.getbootstrap.com/) class such as `bi-book-half`; `""` for none. |
| `items[].newTab` | boolean | `true` opens the link in a new tab (`target="_blank" rel="noopener"`). |
| `items[].enabled` | boolean | `false` hides the link everywhere; missing means `true`. |

The file is maintained by the site owner; don't edit it by hand from this app. `shared/` must be readable by PHP; `menus.json` is written by the site owner's admin tooling. App-specific entries (Install App) stay in `index.php`.

## Troubleshooting

### Common Issues
1. **404 Errors**: Check if mod_rewrite is enabled and .htaccess is working
2. **Data Not Loading**: Verify file permissions and JSON syntax
3. **Mobile Display Issues**: Ensure viewport meta tag is present
4. **JavaScript Errors**: Check browser console for specific errors

### Debug Mode
Add to the top of `index.php` for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Contributing
1. Fork the repository
2. Create feature branch
3. Test thoroughly on mobile devices
4. Submit pull request with detailed description

## License
This project is released under the MIT License. See individual Bible version files for their specific copyright information.

## Support
- **Website**: [WordOfGod.in](https://wordofgod.in)
- **Resources**: [ChristianPDF.com](https://christianpdf.com)
- **Issues**: Report bugs through the repository issue tracker

---

Built with ❤️ for the global Christian community
Simple Online Bible Viewer App

# Installation
Just upload it to your webserver which runs PHP.
Thats it.