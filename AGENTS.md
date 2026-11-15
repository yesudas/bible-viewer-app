# AGENTS.md

Guidance for AI coding agents working in this repository. For user-facing docs see [README.md](README.md); for hosting see [DEPLOYMENT.md](DEPLOYMENT.md).

## What this is

**Online Bibles** — a multilingual Bible reader/comparer served at `https://www.wordofgod.in/bibles/`. Plain **PHP 7.4+ + vanilla JS + Bootstrap 5 / Bootstrap Icons (CDN)**. No build step, no package manager, no framework, no test runner. It is installable as a PWA. Data is static JSON on disk (no database).

## Running it

```bash
php -S localhost:8000      # from the repo root; open http://localhost:8000/
php -l index.php           # syntax check any PHP file you touched
```

- There is no automated test suite. The `test-*.php`, `debug-*.php`, `direct-test.php`, `raw-test.php`, `meta-inspector.php` files in `test/` (moved out of the root) are old ad-hoc manual pages from Oct 2025; they are not run by anything. `test/.htaccess` limits them to local requests (`Require local`), so they are blocked on the production server; pages that read app files `chdir()` to the app root first, and their links to `index.php` / `api.php` use `../`. Don't treat them as a test harness and don't extend them.
- Verify UI changes in a browser (also check the mobile width and a dark theme). `php -l` only proves syntax.
- External features (concordance, dictionary, devotions, commentary, cross references, quiz) call wordofgod.in; from `localhost` some of them show "no data" because of CORS. That is expected, not a bug.

## Layout

| Path | Role |
|---|---|
| `index.php` | The whole page: server-side reads URL params, loads `data/languages.json` + the first Bible's `bibles.json`, builds SEO meta/canonical/JSON-LD, renders navbar, selectors, verse container, word-study modal, footer. `$version` is defined at the top but not currently used for anything. |
| `api.php` | JSON API, `?action=` switch: `getBooks`, `getVerses`, `getVersesByRefs` (POST). |
| `js/app.js` | ~2400 lines, all client logic (state, selectors, verse render, word-study modal tabs, themes, URL sync). |
| `js/script.js` | PWA install buttons/banner + service-worker registration. |
| `css/styles.css` | All styling, including the 7 themes (CSS variables). |
| `shared-menus.php` | `viewerSharedMenus()` / `viewerMenuLink()` — header and footer links, read from `../shared/menus.json` with a built-in fallback. |
| `counter.php` | Calls `viewerTrackPageLoad()` (`includes/tracking.php`) and sets `$visitors2` (all-time page loads, shown in the footer — do not add anything else to the footer). Included at the top of `index.php`, before output (it may set the `bv_seen` cookie). |
| `track.php` | POST-only, same-origin, bot-ignoring endpoint for the anonymous events `js/app.js` sends (`chapter`, `bibles`, `feature`); always 204. Validates book/chapter ranges, Bible abbreviations against `languages.json`, and feature/theme names against whitelists. |
| `a.php` | Admin screen (views `tracking`, `users`, `sitemap`; add new admin features as new entries in `$views`). Access = signed in via `../auth` **and** email in `viewerAdmins()` (built-in default admin + `config.php` `admins`). Others get 403. |
| `includes/` | `app-config.php` (config, test mode, admins), `google-auth-client.php` (bridge to `../auth/session.php`, CSRF token), `counters.php` (locked JSON counter read/write), `tracking.php` (UA parsing, page-load + event recording), `books.php` (book names). |
| `counters/` | Runtime tracking data, `{name}.txt` as `{"total":N}` or `{"items":{key:N}}`. Gitignored except `.htaccess` (denies web access). Per-day maps are capped at 800 keys. |
| `users/` | One JSON per Google account that opened/tried a.php (`includes/admin-users.php`): name, first/last seen, visits, denied, `blocked`. Gitignored except `.htaccess`. `viewerIsAdmin()` = listed in config **and** not blocked; the default admin is never blockable. Adding admins stays config-only by design. |
| `config.php` | Local/server config, gitignored (`config.example.php` is the template): `admins`, `test_mode`, `test_user`. `test_mode` only works on a loopback connection to `localhost`. |
| `bot.php` | Honeypot that logs to `bot.log`. |
| `sitemap.php` | Generator for `sitemap.xml` + `sitemap-*.xml`: CLI (`php sitemap.php`) or from the web, where it requires a POST from an admin session carrying the CSRF token (the form on a.php → Sitemap). |
| `sw.js` | Deliberately a no-op service worker (no caching). |
| `.htaccess` | www+https redirect, AI-crawler detection/rate limiting, MIME types, caching, security headers. Apache only. |
| `data/` | All Bible text (see below). Very large. |
| `concordance-test/` | Sample JSON fixtures for concordance output. |

## Data model

`data/languages.json` → `biblesByLanguage[<language folder name>]` with `languageCode`, `languageName`, `bibles[]` (`abbr`, `isDefault`, `commonName`, optional `hide`). Language keys are the **folder names** on disk, including non-Latin ones (`தமிழ்`, `हिन्दी`, `മലയാളം`…).

```
data/<Language>/<ABBR>/bibles.json                     # bibles[0].books[]: bookNo, longName, chapterCount…
data/<Language>/<ABBR>/<bookNo>-<longName>/<chapter>.json   # {bibleAbbr, bookName, chapterNumber, verses:[{number, verse}]}
```

- Verse text can embed Strong's tags like `<WH7225>` / `<WG…>`; `processStrongsNumbers` / `makeWordsClickable` in `app.js` turn words and tags into clickable word-study links.
- `isDefault: true` and not `hide` ⇒ preselected when no `bibles=` param is given. `hide: true` Bibles exist on disk but are not offered.
- Adding a Bible = add the folder + `bibles.json` + chapter files, then register it in `languages.json` (and regenerate sitemaps). Adding a language also needs a new key there.
- `api.php` builds file paths from `bibleAbbr`/`bookNo` via `findBibleLanguage()` against `languages.json`; keep user input validated through that lookup rather than concatenating raw params into paths.

## API details (`api.php`)

- All responses are `{success: bool, …}` JSON; errors are `{success:false, error}` with HTTP 200 (the client checks `data.success`, not the status code). Unknown action returns `Invalid action`.
- `getBooks&bible=ABBR` → `{books}` (from `bibles.json`). `getVerses&bible&book&chapter` → `{verses:[{number, verse}], bibleAbbr, bookName, chapterNumber}`; verse HTML/Strong's tags are returned raw. `getVersesByRefs` (POST JSON `{bible, refs:[{book, chapter, verseStart, verseEnd}]}`) returns plain text per ref: Strong's tags and HTML are **stripped**, multi-verse ranges are prefixed with verse numbers, and chapter files are cached per request.
- Unrecognised/missing Bible, book or chapter files return `success:false` entries rather than errors; `getVersesByRefs` returns `text:null` per ref.
- Safety relies on `findBibleLanguage()` whitelisting `bible` against `languages.json` (so arbitrary `bible` values can't build paths). `bookNo`/`chapterNo` are `intval()`'d. The helpers `sanitizeInput()`, `validateBibleAbbr()` and `validateNumber()` exist at the bottom but are **not currently called**; keep that in mind before assuming they run.
- The Bible folder name is `data/{Language}/{ABBR}/{bookNo}-{longName}/{chapter}.json`, where `longName` is taken from that Bible's own `bibles.json`, so folder names differ per Bible and language.

## URL contract

`index.php` reads `langs`, `bibles` (comma-separated), `book`, `chapter`, `verse`. The JS keeps the address bar in sync (`updateURL()`), and the server emits matching canonical/meta tags. Changing parameter names or semantics must be done on both sides and kept compatible with existing indexed URLs and wordofgod.in deep links (commentary links point at `…/bibles/?book=&chapter=&verse=`).

## PHP → JS hand-off

`index.php` computes the initial state server-side and passes it to the client in an inline script on `DOMContentLoaded`: `window.initialSelectedChapter` / `initialSelectedVerse`, then `initializeGlobalVariables({selectedBibles, selectedLanguages, biblesByLanguage, booksData, chapterCounts})`, then `initTheme()`. The order matters (`initializeGlobalVariables` synchronously calls `updateChapters()`, which reads those globals). When adding initial state, extend this call rather than re-fetching from the API. Everything is `json_encode`d, so the whole `biblesByLanguage` map ships in every page.

## Frontend internals (`js/app.js`, read in full)

### Style and state
- Plain globals and top-level functions, no modules, no framework. Match that; don't introduce a bundler. Handlers are wired two ways: inline `onclick`/`onchange` attributes for controls (`selectLanguage()`, `onBookSelectChange(this)`, `copyVerse(i)`, `openVerseStudy(...)`, `removeBible('ABBR')`) and **one delegated `click` listener** on `document` (registered in the first `DOMContentLoaded` block) for `.clickable-word`, `.crossref-more-link` / `.crossref-inline-ref`, `.crossref-ref-link` and `.commentary-toc-link`. New dynamic elements should use the delegated listener, not new inline handlers.
- Core state: `selectedBibles` (ordered; the **first one drives** the book/chapter dropdowns and `booksData`), `selectedLanguages`, `currentLanguage`, `biblesByLanguage`, `booksData`, `chapterCounts`, `currentFontSize` (zoom via `--font-size-base`, 12–40px). Modal state: `currentModalWord/Book/Chapter/Verse/Language`.
- One-shot window flags: `window.initialSelectedChapter` (used only on first `updateChapters()` call, guarded by `window.hasLoadedOnce`) and `window.initialSelectedVerse` (consumed by `scrollToInitialVerseIfNeeded()`, which scrolls, adds `.verse-highlight` for 3s and sets `currentVerseNumber`). `currentVerseNumber` is what `updateURL()` writes back as `verse=`.

### Selection and navigation flow
1. `initializeGlobalVariables()` → `loadBiblesForLanguage()` → `updateBookDropdown()` → `updateChapters()` → `loadVerses()`.
2. `loadVerses()` fetches `api.php?action=getVerses` once **per selected Bible in parallel** (`Promise.all`), then `displayVerses()` → `updateURL()` (uses `history.replaceState`, never `pushState`, so Back doesn't step through chapters) → `updateMetaTags()` (client-side mirror of the PHP title/description/keywords; keep both in sync) → nav-button state → `loadInlineCrossReferences()`.
3. `displayVerses()` zips the Bibles by array index (verse `i` of each Bible), so verse numbering is assumed to line up across translations; the verse number shown is the first Bible's that has verse `i`. `copyVerse(i)` finds the verse by DOM index.
4. Book/chapter selects exist twice (top `#bookSelect`/`#chapterSelect`, bottom `#bookSelectBottom`/`#chapterSelectBottom`, classes `.book-select`/`.chapter-select`). **Always** read/write through `getBookSelects()`, `getChapterSelects()`, `setBookValue()`, `setChapterValue()`, `getPrimary…()`. Prev/next buttons are `.prev-chapter-btn`, `.next-chapter-btn`, `.prev-book-btn`, `.next-book-btn` (also duplicated top/bottom).
5. `previousChapter()` / `nextChapter()` cross book boundaries using a `setTimeout(…, 100)` after `updateChapters()` — a known hack; don't copy it, and be careful when touching that path.
6. `navigateToCrossReference()` can navigate to a book the primary Bible lacks: it falls back to the static `CROSSREF_BOOKS` table (66 books, `shortName`/`longName`/`chapterCount`) and injects a temporary `<option>`. `updateBookNavigationButtons()` disables prev/next book when the current book isn't in `booksData`.
7. Legacy hooks for `.language-tab`, `.language-btn`, `.bible-tab-btn`, `.bible-btn` remain in `updateLanguageButtons()` / `updateBibleButtons()` but no current markup uses them.

### Word study modal (`#concordanceModal`)
- One Bootstrap modal reused by everything. Tab buttons: `#concordance-tab`, `#dictionary-tab`, `#devotions-tab`, `#commentary-tab`, `#crossreferences-tab`, `#quiz-tab`; panes `#concordanceContent`, `#dictionaryContent`, `#devotionsContent`, `#commentarySections` (+ `#commentarySourceSelect`), `#crossrefPanels` (+ `#crossrefSourceSelect`), `#quizContent`. Title is `#concordanceModalLabel`.
- **Two modes**, toggled by the `mode-crossref` class on `#concordanceTabs`: word click removes it (all 6 tabs visible, opens on Concordance); cross-reference/quiz/study entry adds it (CSS hides Concordance and Dictionary and moves Cross References first via `order:-1`). Per the code, Cross References is visible in word mode too (README text says otherwise — trust the code/CSS).
- Entry points: word/Strong's click → `openConcordance()` → `showConcordanceModal()`; inline citation, "click for more", or the per-verse study button → `openCrossReferences()`; per-verse quiz button → `openVerseQuiz()` (sets title "Quiz: Book N", shows `#quiz-tab`, then `loadQuizData()`).
- Every entry point must set `currentModalBook/Chapter/Verse/Language` and reset `loadedDevotionsKey` / `loadedQuizKey` (the delegated click handler, `openVerseStudy()` and `openVerseQuiz()` each do this; a new entry point must too).
- Tabs lazy-load on `shown.bs.tab` listeners (bottom of the first `DOMContentLoaded` block). Each loader has a cache key and a **stale-response guard** — compare the key/word/`requestId` again inside `.then()` before touching the DOM (`loadedDevotionsKey`, `loadedCommentaryKey`, `loadedCrossReferencesKey`, `currentModalWord`, `crossRefInlineRequestId`). Follow this pattern for any new async tab content.
- Cross-reference sections build per-selected-Bible collapse panels and fetch their verse text lazily on `shown.bs.collapse` via `POST api.php?action=getVersesByRefs`.
- Sources are config constants near the top: `CROSS_REFERENCE_SOURCES` + `DEFAULT_CROSS_REFERENCE_SOURCE` ('OB'), `COMMENTARY_SOURCES` + `DEFAULT_COMMENTARY_SOURCE` ('GNTBC'), `DICTIONARY_LABELS` (slug → display name; unknown slugs get an auto-formatted label). `INLINE_CROSSREF_LIMIT` (8) caps citations shown under each verse.

### Data sources and origins
- `WORDOFGOD_ORIGIN` = the page's own origin when the host is `wordofgod.in`/`*.wordofgod.in` (those APIs send no CORS headers), otherwise **`https://www.wordofgod.in`** (not the bare domain, despite what the README says). Use it for dictionary, devotions, commentary and cross-reference fetches.
- Concordance is different: it uses a **relative path `../bible-concordance/data/{lang}/{bible}/words/…`**, i.e. it only works when this app is served from `…/bibles/` next to a `bible-concordance/` sibling folder (as on production). Strong's files are `…/words/Strongs/Strongs-H1234.json`; normal words are `…/words/{firstGrapheme}/{firstGrapheme}-{word}.json`, where the first letter comes from `getFirstLetter()` (uses `Intl.Segmenter` for Tamil/Indic grapheme clusters).
- The Quiz iframe URL hardcodes `https://wordofgod.in/bible-quiz/embed-quiz.php` (zero-padded `books=NN&chapters=NN-NNN`, `lang` = the first Bible's language folder name).
- `getBibleLanguage()` defaults to Tamil (`தமிழ்`) when a Bible isn't found — the site's primary language.
- Concordance word highlighting (`highlightWordInText` / `shouldHighlightWord` / `getTamilWordRoot`) is heuristic: substring match, plus suffix-stripping for Tamil. Changes affect all languages.

### Verse text rendering
- `makeWordsClickable()` first converts `<WH123>`/`<WG123>` tags to `.strongs-number.clickable-word` spans (`data-strongs="H123"`), then wraps every word of ≥2 chars in `.clickable-word` spans carrying `data-word/bible/book/chapter/verse`. Existing `<span class="emphasis|notes|strongs-number">` markup in verse JSON is preserved (emphasis/notes inner text still gets clickable words). Verse text is trusted data and is inserted as HTML, not escaped.

### Escaping rules
- `escapeHtml()` for anything from APIs that should be text (titles, labels, author, devotion text, bible abbreviations).
- Raw HTML is intentionally inserted for: dictionary `paragraph`, commentary `section.text` and `meta.html_style`, devotion `embed` fields (`DEVOTION_HTML_FIELDS`), and verse text. These come from wordofgod.in / our own data; don't extend that list to new sources.
- Commentary CSS from the API is scoped under `#commentarySections` by `scopeCommentaryStyle()` (also substitutes `%COLOR_GREEN%`/`%COLOR_BLUE%`); links to `…/bibles/` inside commentary get the reader's `bibles`/`langs` appended by `enrichCommentaryVerseLinks()`.

### Themes
- `light, light-gray, gray, sepia, dark, warm-dark, true-black` plus `auto` (`auto` = dark if the OS prefers dark, else dark between 18:00–06:00, rechecked every 60 s and on OS change). Stored in `localStorage` `bibleViewerTheme`; first visit follows `prefers-color-scheme`.
- `applyTheme()` sets `data-theme` and `data-bs-theme` on `<html>`, updates the theme label/dropdown highlight and `<meta name="theme-color">`. A new theme needs: an entry in `VISUAL_THEMES` (+ `DARK_THEMES` if dark), `THEME_LABELS`, `THEME_META_COLORS`, a `[data-theme="…"]` variable block in `css/styles.css`, a `.theme-swatch-…` rule, and a dropdown item in `index.php`.

### Keyboard shortcuts (`initializeKeyboardShortcuts()`)
`←`/`,` previous chapter, `→`/`.` next chapter, `Shift+←`/`Shift+→` previous/next book; ignored while focus is in input/textarea/select. Known quirk: the plain-arrow branch has no `!shiftKey` guard, so Shift+Arrow fires the chapter handler **and** the book handler. Fix it if you touch this code.

## CSS (`css/styles.css`, ~1000 lines)

- Built on Bootstrap 5 utilities; this file overrides/extends them. **All colors come from CSS variables** defined per theme at the top (`:root, [data-theme="light"]` holds the full set: `--bg-body`, `--bg-surface`, `--bg-muted`, `--bg-hover`, `--text-primary/-muted/-faint`, `--border-color`, `--accent`, `--verse-highlight-*`, `--tag-*`, `--strongs-color`, `--word-highlight-color`, `--navbar-bg`, `--footer-bg`, `--commentary-*`, …). Other themes override only what differs. Define a new variable in the light/`:root` block first (every theme falls back to it), then override it in each theme where the value must differ, especially the dark ones.
- `--font-size-base` is the zoom variable set by the JS zoom controls.
- Component sections, in order: theme selector/swatches, Strong's/emphasis/highlight, PWA install banner, nav tabs, Bible selection card + selected tags, scroll container, verse container/number/version label, `.floating-controls` (fixed bottom-right zoom/top buttons), `.study-btn`/`.verse-actions`, `.clickable-word`, then per-tab styles (concordance, dictionary, devotion, cross-reference, commentary). Mobile tweaks are in `@media (max-width: 768px)` blocks.
- `#concordanceTabs.mode-crossref …` rules implement the modal's two tab modes (see above).
- Because the file is inlined into the page (see below), keep it self-contained and free of `@import`/relative `url()` that would break when served from `index.php`.

## Header menu behavior

`index.php` renders the navbar from `viewerSharedMenus()`. On desktop (≥ lg) only the first **7** links show inline; the rest go into a **More** dropdown. In the collapsed (mobile/tablet) navbar every link is shown flat. This is done with duplicate `<li>`s toggled by `d-lg-none` / `d-none d-lg-block`, so a change to link markup must be made in both places. The footer always lists all links.

## Shared menu (`shared-menus.php`)

Reads `../shared/menus.json` (sibling folder on the production server, **not in this repo**). Validates each item (http(s) or single-slash URLs, `bi-` icon classes), skips `enabled:false` and the app's own link (last path segment `bibles`), and falls back to a built-in list if the file is missing/invalid. Preserve the fallback and the escaping.

## PWA and analytics

- `manifest.json` (standalone, start URL `./?f=app`, icons in `images/`) + the no-op `sw.js`. Install prompt UI is the `.install-app-btn` buttons (header, footer, banner) driven by `js/script.js`. The service worker intentionally doesn't cache; don't add offline caching without discussing it, since data and wordofgod.in calls must stay fresh.
- Google Analytics (gtag) is loaded in `index.php`; our own anonymous counters (`counter.php`, `track.php`, `counters/`) are separate. Don't add other trackers or send user data to new third parties. Keep the counters anonymous: no IPs, no user ids, only aggregate counts and whitelisted keys.
- New metric = call `viewerIncrementMap()` / `viewerIncrementCounter()` from `includes/tracking.php`, validate/whitelist any client-supplied key, and add a table for it in the `tracking` view of `a.php`. Client side, use `trackFeature(name)` and add the name to `viewerFeatureNames()`. Per-item counts (a word, a dictionary/devotion/commentary/cross-reference source) use `trackItem(kind, key)` and `viewerItemKinds()`; their keys come from the client, so they are sanitised and the maps are size-capped.

## Server config worth knowing (`.htaccess`)

- Forces `https://www.wordofgod.in` for wordofgod.in hosts; sends `X-Frame-Options: DENY` (the app itself cannot be iframed; the Quiz iframe is the app embedding *wordofgod.in*, which is a different direction); blocks dotfiles; adds `X-Robots-Tag: noai, noimageai`.
- Any new file you want web-served must not start with a dot, and anything sensitive must not live under the web root.

## Things to be careful about

- **Do not commit or hand-edit generated/runtime files**: `counters/*` (the page-load total is `counters/counter.txt`; a root `counter.txt` is legacy and only imported if the former is missing), `config.php`, `bot.log`, `error_log`, `.sync.ffs_db`, `sitemap-*.xml` (all in `.gitignore`, but `.sync.ffs_db` and `error_log` are still tracked, so they show up as modified/deleted in `git status` — leave them out of your changes).
- **`data/` is huge** (hundreds of thousands of files). Never `grep -r`/`find` across it without narrowing to one Bible, and never list it into a tool result. Exclude it from searches (`--glob '!data/**'`).
- `index.php` **inlines** `css/styles.css` and `js/app.js` into the HTML (`file_get_contents` inside `<style>`/`<script>`), falling back to `<link>`/`<script src>` only if the file is missing. So edits ship with the next page load and there is no asset cache-busting to manage; `js/script.js` is the only separately loaded script. Don't put `</script>` or `</style>` sequences inside those files.
- `.htaccess` is Apache-specific; the PHP built-in server ignores it (no rewrite, no headers).
- `.htaccess` deliberately slows AI crawlers and `bot.php` is a honeypot linked invisibly — leave both alone unless asked.
- Production is Apache + PHP on shared hosting; keep PHP 7.4-compatible syntax (no `match`, no enums, no typed-property-only features, no `str_contains`).
- The site serves many scripts, including right-to-left ones (Urdu, Hebrew data exist), but the page sets no `dir` handling today; test new layout with long Indic/Tamil words and don't hard-code left/right where logical properties work.

## Workflow notes

- No `CLAUDE.md`; this file is the agent guide. `.claude/` and `.vscode/` are gitignored local settings.
- Commit history is informal one-liners ("added Quiz feature", "updated"). Don't commit or push unless asked.
- Syntax checks used so far: `php -l <file>` and `node --check js/app.js`.
- Existing user-facing strings are mixed English/Tamil/Indic; new UI text should be English unless the surrounding feature is language-specific.

## Documentation upkeep

When behavior changes, update the matching section of `README.md` in the same change (feature list, project-structure tree, API/URL docs, "Shared menu" section). Keep this file in sync if you add a top-level file, change the data layout, or alter the conventions above.
