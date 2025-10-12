<?php
// Test file to demonstrate the MIME type fallback functionality

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>MIME Type Fallback Test</title>\n";

// Test CSS fallback
$cssPath = 'css/styles.css';
$cssUrl = 'css/styles.css';

echo "<h3>CSS File Test:</h3>\n";
echo "<p>CSS file exists: " . (file_exists($cssPath) ? 'Yes' : 'No') . "</p>\n";

if (file_exists($cssPath)) {
    $testUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $cssUrl;
    echo "<p>Test URL: $testUrl</p>\n";
    
    $headers = @get_headers($testUrl, 1);
    
    if ($headers) {
        echo "<p>Headers response: " . $headers[0] . "</p>\n";
        $contentType = isset($headers['Content-Type']) ? 
            (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : 'Not set';
        echo "<p>Content-Type: $contentType</p>\n";
        
        $cssAccessible = strpos($contentType, 'css') !== false || strpos($contentType, 'text/css') !== false;
        echo "<p>CSS accessible: " . ($cssAccessible ? 'Yes' : 'No') . "</p>\n";
        
        if (!$cssAccessible) {
            echo "<p><strong>CSS file will be embedded directly due to MIME type issue</strong></p>\n";
        }
    } else {
        echo "<p>Could not get headers for CSS file</p>\n";
    }
}

// Test JS fallback
$jsPath = 'js/app.js';
$jsUrl = 'js/app.js';

echo "<h3>JavaScript File Test:</h3>\n";
echo "<p>JS file exists: " . (file_exists($jsPath) ? 'Yes' : 'No') . "</p>\n";

if (file_exists($jsPath)) {
    $testUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $jsUrl;
    echo "<p>Test URL: $testUrl</p>\n";
    
    $headers = @get_headers($testUrl, 1);
    
    if ($headers) {
        echo "<p>Headers response: " . $headers[0] . "</p>\n";
        $contentType = isset($headers['Content-Type']) ? 
            (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : 'Not set';
        echo "<p>Content-Type: $contentType</p>\n";
        
        $jsAccessible = strpos($contentType, 'javascript') !== false || strpos($contentType, 'application/javascript') !== false;
        echo "<p>JS accessible: " . ($jsAccessible ? 'Yes' : 'No') . "</p>\n";
        
        if (!$jsAccessible) {
            echo "<p><strong>JavaScript file will be embedded directly due to MIME type issue</strong></p>\n";
        }
    } else {
        echo "<p>Could not get headers for JS file</p>\n";
    }
}

echo "</head>\n<body>\n";
echo "<h1>MIME Type Fallback Test Complete</h1>\n";
echo "<p><strong>URL Structure:</strong> Now using query parameters like ?bibles=TOV2017,TCB1973&langs=English&book=1&chapter=1</p>\n";
echo "<p><a href='index.php?bibles=TOV2017,TCB1973&langs=English&book=1&chapter=1'>Test Bible App with Query Parameters</a></p>\n";
echo "<p><a href='test-query-params.php'>Test Query Parameter URLs</a></p>\n";
echo "<p><a href='index.php'>Back to Bible App (Default)</a></p>\n";
echo "</body>\n</html>";
?>