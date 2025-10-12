<!DOCTYPE html>
<html>
<head>
    <title>Book Selection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
    </style>
</head>
<body>
    <h1>Book Selection Verses Refresh Test</h1>
    
    <div class="test-section success">
        <h3>✅ Fixed Issue:</h3>
        <p><strong>Problem:</strong> When selecting a book from dropdown, chapters updated but verses didn't refresh</p>
        <p><strong>Solution:</strong> Added <code>loadVerses()</code> call to <code>updateChapters()</code> function</p>
    </div>
    
    <div class="test-section info">
        <h3>🧪 Test Steps:</h3>
        <ol>
            <li>Open the <a href="index.php" target="_blank">main Bible app</a></li>
            <li>Note the current book and verses displayed</li>
            <li>Change the book selection from the dropdown</li>
            <li><strong>Expected:</strong> Chapter dropdown updates AND verses automatically refresh</li>
            <li>Change to different books (Genesis → Psalms → Matthew, etc.)</li>
            <li><strong>Verify:</strong> Each book change loads the appropriate verses</li>
        </ol>
    </div>
    
    <div class="test-section warning">
        <h3>⚠️ What to Verify:</h3>
        <ul>
            <li><strong>Immediate Response:</strong> Verses change instantly when book is selected</li>
            <li><strong>Chapter Reset:</strong> When changing books, chapter resets to Chapter 1</li>
            <li><strong>Correct Content:</strong> Verses match the selected book</li>
            <li><strong>URL Update:</strong> Browser URL updates to reflect new book</li>
            <li><strong>Loading State:</strong> Brief loading spinner before new verses appear</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h3>🔧 Technical Fix Applied:</h3>
        <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
function updateChapters() {
    // ... existing code to update chapter dropdown ...
    
    updateURL();
    
    // ✅ NEW: Load verses after updating chapters
    loadVerses();
}
        </pre>
        <p><strong>Result:</strong> Book selection now properly refreshes both chapters AND verses</p>
    </div>
    
    <div class="test-section">
        <h3>📚 Quick Test Books:</h3>
        <ul>
            <li><a href="index.php?bibles=TOV2017&langs=தமிழ்&book=1&chapter=1">Genesis (Creation)</a></li>
            <li><a href="index.php?bibles=TOV2017&langs=தமிழ்&book=19&chapter=1">Psalms (David's Prayers)</a></li>
            <li><a href="index.php?bibles=TOV2017&langs=தமிழ்&book=40&chapter=1">Matthew (Gospel)</a></li>
            <li><a href="index.php?bibles=TOV2017&langs=தமிழ்&book=66&chapter=1">Revelation (Prophecy)</a></li>
        </ul>
        <p><em>Use these links, then try changing books via dropdown to test the fix</em></p>
    </div>
    
    <div class="test-section">
        <h3>🔗 Related Tests:</h3>
        <ul>
            <li><a href="test-dropdowns.php">General Dropdown Test</a></li>
            <li><a href="test-floating-controls.php">Floating Controls Test</a></li>
            <li><a href="debug-defaults.php">Debug Default Values</a></li>
        </ul>
    </div>
</body>
</html>