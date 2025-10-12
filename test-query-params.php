<!DOCTYPE html>
<html>
<head>
    <title>Query Parameter URL Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-url { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .current-params { background: #e8f5e9; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Query Parameter URL Test</h1>
    
    <div class="current-params">
        <h3>Current URL Parameters:</h3>
        <?php
        echo "<p><strong>Full URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
        echo "<p><strong>Bibles:</strong> " . (isset($_GET['bibles']) ? $_GET['bibles'] : 'Not set') . "</p>";
        echo "<p><strong>Languages:</strong> " . (isset($_GET['langs']) ? $_GET['langs'] : 'Not set') . "</p>";
        echo "<p><strong>Book:</strong> " . (isset($_GET['book']) ? $_GET['book'] : 'Not set') . "</p>";
        echo "<p><strong>Chapter:</strong> " . (isset($_GET['chapter']) ? $_GET['chapter'] : 'Not set') . "</p>";
        ?>
    </div>
    
    <h3>Test URLs:</h3>
    
    <div class="test-url">
        <strong>Example 1:</strong><br>
        <a href="?bibles=TOV2017,TCB1973&langs=English&book=1&chapter=1">
            ?bibles=TOV2017,TCB1973&langs=English&book=1&chapter=1
        </a>
    </div>
    
    <div class="test-url">
        <strong>Example 2:</strong><br>
        <a href="?bibles=TCL1995&langs=Tamil&book=19&chapter=23">
            ?bibles=TCL1995&langs=Tamil&book=19&chapter=23
        </a>
    </div>
    
    <div class="test-url">
        <strong>Example 3 (Multiple Bibles):</strong><br>
        <a href="?bibles=TOV2017,TCB1973,TCL1995&langs=English,Tamil&book=40&chapter=5">
            ?bibles=TOV2017,TCB1973,TCL1995&langs=English,Tamil&book=40&chapter=5
        </a>
    </div>
    
    <div class="test-url">
        <strong>Main App:</strong><br>
        <a href="index.php">Back to Bible App</a>
    </div>
    
    <h3>API Test:</h3>
    <div class="test-url">
        <a href="api.php?action=getBooks&bible=TCB1973">Test API - Get Books for TCB1973</a>
    </div>
    
    <div class="test-url">
        <a href="api.php?action=getVerses&bible=TCB1973&book=1&chapter=1">Test API - Get Genesis Chapter 1 from TCB1973</a>
    </div>
</body>
</html>