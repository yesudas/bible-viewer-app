<!DOCTYPE html>
<html>
<head>
    <title>Enhanced Copy Functionality Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .example-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #0d6efd; }
        .copy-example { font-family: monospace; white-space: pre-line; background: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>✅ Enhanced Copy Functionality</h1>
    
    <div class="test-section success">
        <h3>🎯 New Copy Format:</h3>
        <p>When users click the copy button on any verse, the copied text now includes:</p>
        <ul>
            <li>✅ <strong>Book Chapter:Verse format</strong> (e.g., "Genesis 1:1")</li>
            <li>✅ <strong>Bible version and verse text</strong></li>
            <li>✅ <strong>Website URL</strong> at the bottom</li>
        </ul>
    </div>
    
    <div class="test-section info">
        <h3>📋 Copy Format Example:</h3>
        <div class="example-box">
            <h4>When copying Genesis 1:1 from TOV2017:</h4>
            <div class="copy-example">Genesis 1:1

TOV2017: ஆதியிலே தேவன் வானத்தையும் பூமியையும் சிருஷ்டித்தார்.

https://www.wordofgod.in/bibles</div>
        </div>
        
        <div class="example-box">
            <h4>When copying Psalm 23:1 with multiple Bibles:</h4>
            <div class="copy-example">Psalms 23:1

TOV2017: கர்த்தர் என் மேய்ப்பர்; எனக்கு குறைவில்லை.
TCB1973: The LORD is my shepherd; I shall not want.

https://www.wordofgod.in/bibles</div>
        </div>
    </div>
    
    <div class="test-section warning">
        <h3>🧪 Test the Copy Functionality:</h3>
        <ol>
            <li>Open the <a href="index.php" target="_blank">main Bible app</a></li>
            <li>Navigate to any chapter with verses</li>
            <li>Click the copy button (📋) next to any verse</li>
            <li>Paste the content somewhere to verify the format</li>
            <li>Try with different books and chapters</li>
            <li>Try with multiple Bible versions selected</li>
        </ol>
        
        <p><strong>Expected behavior:</strong></p>
        <ul>
            <li>Copy button shows green checkmark briefly</li>
            <li>Clipboard contains properly formatted text</li>
            <li>Format matches the examples above</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h3>🔧 Technical Implementation:</h3>
        <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
function copyVerse(verseIndex) {
    // Get verse content and UI elements
    const verseContainer = document.querySelectorAll('.verse-container')[verseIndex];
    const verseNumber = verseContainer.querySelector('.verse-number').textContent;
    const bookSelect = document.getElementById('bookSelect');
    const chapterSelect = document.getElementById('chapterSelect');
    const bookName = bookSelect.options[bookSelect.selectedIndex].text;
    const chapterNumber = chapterSelect.value;
    
    // ✅ NEW: Format as "Genesis 1:1"
    let copyText = `${bookName} ${chapterNumber}:${verseNumber}\n\n`;
    
    // Add Bible versions and verse text
    verseTexts.forEach((text, index) => {
        const version = bibleVersions[index].textContent;
        copyText += `${version}: ${text.textContent}\n`;
    });
    
    // ✅ NEW: Add website URL
    copyText += '\nhttps://www.wordofgod.in/bibles';
    
    // Copy to clipboard with success feedback
    navigator.clipboard.writeText(copyText);
}
        </pre>
    </div>
    
    <div class="test-section">
        <h3>📚 Quick Test Links:</h3>
        <ul>
            <li><a href="index.php?bibles=TOV2017&langs=தமிழ்&book=1&chapter=1">Genesis 1 (Tamil)</a></li>
            <li><a href="index.php?bibles=TCB1973&langs=English&book=19&chapter=23">Psalm 23 (English)</a></li>
            <li><a href="index.php?bibles=TOV2017,TCB1973&langs=தமிழ்,English&book=40&chapter=5">Matthew 5 (Multi-Bible)</a></li>
            <li><a href="index.php?bibles=TOV2017&langs=தமிழ்&book=66&chapter=1">Revelation 1 (Tamil)</a></li>
        </ul>
        <p><em>Click copy buttons in these chapters to test the new format</em></p>
    </div>
    
    <div class="test-section success">
        <h3>✅ Benefits of Enhanced Copy:</h3>
        <ul>
            <li><strong>Better Reference:</strong> Clear book, chapter, and verse identification</li>
            <li><strong>Professional Format:</strong> Standard biblical citation format</li>
            <li><strong>Attribution:</strong> Website URL for source attribution</li>
            <li><strong>Sharing Ready:</strong> Perfect for social media, emails, and documents</li>
            <li><strong>Multi-Version:</strong> Shows all selected Bible versions in one copy</li>
        </ul>
    </div>
</body>
</html>