<?php
// Simple test script to check MIME types and file accessibility
header('Content-Type: text/html; charset=utf-8');

echo "<h2>File Accessibility Test</h2>";

$files = [
    'css/styles.css' => 'text/css',
    'js/app.js' => 'application/javascript',
    'data/languages.json' => 'application/json'
];

foreach ($files as $file => $expectedMime) {
    echo "<h3>Testing: {$file}</h3>";
    
    if (file_exists($file)) {
        echo "✅ File exists<br>";
        echo "📁 File size: " . filesize($file) . " bytes<br>";
        echo "🔒 File permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "<br>";
        
        // Test if file is readable
        if (is_readable($file)) {
            echo "✅ File is readable<br>";
            
            // Try to get first few characters
            $content = file_get_contents($file, false, null, 0, 100);
            echo "📄 First 100 chars: " . htmlspecialchars(substr($content, 0, 100)) . "...<br>";
        } else {
            echo "❌ File is not readable<br>";
        }
        
        // Test HTTP access
        $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $file;
        
        echo "🌐 Testing HTTP access: <a href='{$url}' target='_blank'>{$url}</a><br>";
        
        // Use get_headers to check if accessible via HTTP
        $headers = @get_headers($url, 1);
        if ($headers && strpos($headers[0], '200') !== false) {
            echo "✅ HTTP access successful<br>";
            if (isset($headers['Content-Type'])) {
                echo "📋 Content-Type: " . $headers['Content-Type'] . "<br>";
                if (is_array($headers['Content-Type'])) {
                    echo "📋 Content-Type (array): " . implode(', ', $headers['Content-Type']) . "<br>";
                }
            }
        } else {
            echo "❌ HTTP access failed<br>";
            echo "📋 Headers: " . print_r($headers, true) . "<br>";
        }
        
    } else {
        echo "❌ File does not exist<br>";
    }
    
    echo "<hr>";
}

echo "<h3>Server Information</h3>";
echo "🖥️ Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "🐘 PHP Version: " . phpversion() . "<br>";
echo "📁 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "📂 Current Directory: " . __DIR__ . "<br>";
echo "🌐 Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

echo "<h3>Apache Modules (if available)</h3>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_mime', $modules)) {
        echo "✅ mod_mime is loaded<br>";
    } else {
        echo "❌ mod_mime is not loaded<br>";
    }
    if (in_array('mod_rewrite', $modules)) {
        echo "✅ mod_rewrite is loaded<br>";
    } else {
        echo "❌ mod_rewrite is not loaded<br>";
    }
} else {
    echo "ℹ️ Apache module information not available<br>";
}
?>