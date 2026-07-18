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

### 📚 Word Study Tools
Clicking any word (or Strong's number) in a verse opens a modal with three tabs:
- **Concordance**: Every other verse where that word appears
- **Dictionary**: Matching entries from available Bible dictionaries
- **Devotions**: Devotional readings available for that specific verse, grouped into collapsible panels (one per devotion, titled with the devotion's title). Opening a panel automatically closes any other open panel.

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
├── .htaccess              # URL rewriting and security
├── README.md              # This documentation
└── data/
    ├── languages.json     # Language and Bible configuration
    ├── TCB1973/          # Bible version folder
    │   ├── bibles.json   # Book structure and metadata
    │   └── [books]/      # Individual book folders
    │       └── [n].json  # Chapter files with verses
    └── [other bibles]/   # Additional Bible versions
```

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

### External Word Study APIs
The Concordance, Dictionary, and Devotions tabs pull data live from wordofgod.in:
- Concordance: `https://.../bible-concordance/data/{language}/{bible}/words/...`
- Dictionary: `https://wordofgod.in/bibledictionary/api.php?action=getDictionaries&word={word}`
- Devotions: `https://wordofgod.in/bible-devotions/api.php?action=getDevotions&lang={language}&book={num}&chapter={num}&verse={num}`

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