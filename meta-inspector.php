<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meta Tags Inspector</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>🔍 Meta Tags Inspector</h2>
        
        <div class="alert alert-info">
            <strong>Instructions:</strong> Click the buttons below to open different pages and see their meta tags in the browser tab titles and inspect their page source.
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>🧪 Test Different Configurations</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php" target="_blank" class="btn btn-primary">
                                Default Configuration
                            </a>
                            <a href="index.php?bibles=TOV2017&book=1&chapter=1" target="_blank" class="btn btn-primary">
                                Single Bible - Genesis 1
                            </a>
                            <a href="index.php?bibles=TOV2017,TCVIN2022&book=19&chapter=23" target="_blank" class="btn btn-primary">
                                Multiple Bibles - Psalms 23
                            </a>
                            <a href="index.php?bibles=TOV2017,TCVIN2022,TCB1973&book=43&chapter=3" target="_blank" class="btn btn-primary">
                                Three Bibles - John 3
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>🔧 Debug Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="debug-meta.php" target="_blank" class="btn btn-warning">
                                Debug Default
                            </a>
                            <a href="debug-meta.php?bibles=TOV2017&book=1&chapter=1" target="_blank" class="btn btn-warning">
                                Debug Single Bible
                            </a>
                            <a href="debug-meta.php?bibles=TOV2017,TCVIN2022&book=19&chapter=23" target="_blank" class="btn btn-warning">
                                Debug Multiple Bibles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>🔍 How to Check Meta Tags</h5>
            </div>
            <div class="card-body">
                <ol>
                    <li><strong>Browser Tab Title:</strong> Look at the browser tab title - it should show the Bible name and book/chapter</li>
                    <li><strong>View Page Source:</strong> Right-click on any page and select "View Page Source" to see the HTML meta tags</li>
                    <li><strong>Developer Tools:</strong> Press F12 and look at the &lt;head&gt; section for meta tags</li>
                    <li><strong>Debug Pages:</strong> Use the debug links above to see exactly what data is being processed</li>
                </ol>
            </div>
        </div>
        
        <div id="current-page-meta" class="card mt-4">
            <div class="card-header">
                <h5>📄 Current Page Meta Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Title:</strong> <span id="page-title"></span></p>
                <p><strong>Description:</strong> <span id="page-description"></span></p>
                <p><strong>Keywords:</strong> <span id="page-keywords"></span></p>
            </div>
        </div>
    </div>

    <script>
        // Display current page meta information
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('page-title').textContent = document.title;
            
            const description = document.querySelector('meta[name="description"]');
            document.getElementById('page-description').textContent = description ? description.content : 'No description meta tag found';
            
            const keywords = document.querySelector('meta[name="keywords"]');
            document.getElementById('page-keywords').textContent = keywords ? keywords.content : 'No keywords meta tag found';
        });
    </script>
</body>
</html>