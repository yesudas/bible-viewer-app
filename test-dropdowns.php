<!DOCTYPE html>
<html>
<head>
    <title>Dropdown Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <h1>Bible App Dropdown Test</h1>
    
    <div class="test-section">
        <h3>Test Instructions:</h3>
        <ol>
            <li>Open the <a href="index.php" target="_blank">main Bible app</a></li>
            <li>Check if the chapter dropdown is populated</li>
            <li>Verify that verses are displayed</li>
            <li>Test different book and chapter selections</li>
        </ol>
    </div>
    
    <div class="test-section success">
        <h3>✅ Expected Behavior:</h3>
        <ul>
            <li>Chapter dropdown should show "Chapter 1, Chapter 2, ..." etc.</li>
            <li>Default Bible (TOV2017) should be selected</li>
            <li><strong>Selected Bibles section should be visible showing "TOV2017"</strong></li>
            <li>Default book should be Genesis (book 1)</li>
            <li>Default chapter should be Chapter 1</li>
            <li>Verses should load automatically</li>
        </ul>
    </div>
    
    <div class="test-section warning">
        <h3>🔍 What to Check:</h3>
        <ul>
            <li>Language buttons are active (should show Tamil selected)</li>
            <li>Bible buttons are active (should show TOV2017 selected)</li>
            <li><strong>"Selected Bibles:" section is visible with TOV2017 badge</strong></li>
            <li>Book dropdown has all books listed</li>
            <li>Chapter dropdown shows chapters for selected book</li>
            <li>Verses container shows actual verse content, not "No verses found"</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h3>Quick Test Links:</h3>
        <ul>
            <li><a href="index.php">Default (should load TOV2017, Genesis 1)</a></li>
            <li><a href="index.php?bibles=TOV2017&langs=தமிழ்&book=19&chapter=23">Psalm 23</a></li>
            <li><a href="index.php?bibles=TCB1973&langs=English&book=40&chapter=5">Matthew 5</a></li>
            <li><a href="debug-defaults.php">Debug Default Values</a></li>
        </ul>
    </div>
    
    <script>
        // Auto-refresh every 5 seconds to see changes
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>