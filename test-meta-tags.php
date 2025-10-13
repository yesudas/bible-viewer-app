<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Meta Tags Test - Online Bibles</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">🏷️ Enhanced Meta Tags with Bible Information</h2>
                
                <!-- Feature Overview -->
                <div class="alert alert-info">
                    <h4>✨ What's New:</h4>
                    <p>The page title, description, and keywords now dynamically include information about the selected Bible versions, making the content more discoverable and SEO-friendly.</p>
                </div>
                
                <!-- Test Links -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">🧪 Test Different Bible Combinations</h5>
                    </div>
                    <div class="card-body">
                        <p>Click the links below to see how the page title and meta tags change based on selected Bibles:</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>📖 Single Bible Tests:</h6>
                                <ul class="list-group mb-3">
                                    <li class="list-group-item">
                                        <a href="?bibles=TOV2017&book=1&chapter=1" target="_blank">
                                            Tamil One Version 2017 (TOV2017) - Genesis 1
                                        </a>
                                        <br><small class="text-muted">Default Tamil Bible</small>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="?bibles=TCVIN2022&book=19&chapter=23" target="_blank">
                                            Tamil Common Version 2022 (TCVIN2022) - Psalms 23
                                        </a>
                                        <br><small class="text-muted">Tamil with Introduction</small>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="?bibles=TCB1973&book=40&chapter=5" target="_blank">
                                            Contemporary English Version (TCB1973) - Matthew 5
                                        </a>
                                        <br><small class="text-muted">English Version</small>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>📚 Multiple Bible Tests:</h6>
                                <ul class="list-group mb-3">
                                    <li class="list-group-item">
                                        <a href="?bibles=TOV2017,TCVIN2022&book=23&chapter=53" target="_blank">
                                            Two Tamil Versions - Isaiah 53
                                        </a>
                                        <br><small class="text-muted">Compare Tamil translations</small>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="?bibles=TOV2017,TCB1973,TCVIN2022&book=43&chapter=3" target="_blank">
                                            Three Versions - John 3
                                        </a>
                                        <br><small class="text-muted">Tamil + English comparison</small>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="?bibles=TOV2017,TCVIN2022,TCB1973,TCL1995,TOV1986&book=66&chapter=21" target="_blank">
                                            Five Versions - Revelation 21
                                        </a>
                                        <br><small class="text-muted">Test "and more" functionality</small>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Meta Tag Examples -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">📋 Meta Tag Examples</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>🏷️ Single Bible Example:</h6>
                                <div class="bg-light p-3 rounded">
                                    <strong>Title:</strong><br>
                                    <code>Online Bibles - Genesis Chapter 1 | Tamil One Version 2017 (TOV2017) | WordOfGod.in</code><br><br>
                                    
                                    <strong>Description:</strong><br>
                                    <code>Read Genesis Chapter 1 in Tamil One Version 2017 (TOV2017). Compare different Bible translations side by side online.</code><br><br>
                                    
                                    <strong>Keywords:</strong><br>
                                    <code>bible, online bible, Genesis, scripture, biblical text, Tamil One Version 2017, TOV2017, தமிழ் bible</code>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>📚 Multiple Bibles Example:</h6>
                                <div class="bg-light p-3 rounded">
                                    <strong>Title:</strong><br>
                                    <code>Online Bibles - John Chapter 3 | Tamil One Version 2017, Contemporary English Version, Tamil Common Version with Introduction 2022 (TOV2017, TCB1973, TCVIN2022) | WordOfGod.in</code><br><br>
                                    
                                    <strong>Description:</strong><br>
                                    <code>Read John Chapter 3 in Tamil One Version 2017, Contemporary English Version, Tamil Common Version with Introduction 2022 (TOV2017, TCB1973, TCVIN2022). Compare different Bible translations side by side online.</code><br><br>
                                    
                                    <strong>Keywords:</strong><br>
                                    <code>bible, online bible, John, scripture, biblical text, Tamil One Version 2017, Contemporary English Version, Tamil Common Version with Introduction 2022, TOV2017, TCB1973, TCVIN2022, தமிழ், English bible</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Technical Implementation -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">⚙️ Technical Implementation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>📊 Data Sources:</h6>
                                <ul>
                                    <li><strong>Bible Information:</strong> <code>data/languages.json</code></li>
                                    <li><strong>Common Names:</strong> <code>bible.commonName</code></li>
                                    <li><strong>Abbreviations:</strong> <code>bible.abbr</code></li>
                                    <li><strong>Languages:</strong> Language keys from <code>biblesByLanguage</code></li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>🧠 Smart Logic:</h6>
                                <ul>
                                    <li><strong>Multiple Bibles:</strong> Lists up to 3, then shows "and X more"</li>
                                    <li><strong>Language Detection:</strong> Extracts unique languages from selected Bibles</li>
                                    <li><strong>SEO Optimization:</strong> Includes relevant keywords and Bible names</li>
                                    <li><strong>Fallback Handling:</strong> Graceful degradation if Bible data missing</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SEO Benefits -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">🚀 SEO Benefits</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>🔍 Search Discoverability:</h6>
                                <ul>
                                    <li>Bible version names in title</li>
                                    <li>Specific translation keywords</li>
                                    <li>Language-specific searches</li>
                                    <li>Book and chapter targeting</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-4">
                                <h6>📱 Social Media Sharing:</h6>
                                <ul>
                                    <li>Descriptive page titles</li>
                                    <li>Clear content descriptions</li>
                                    <li>Bible version identification</li>
                                    <li>Professional presentation</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-4">
                                <h6>📈 User Experience:</h6>
                                <ul>
                                    <li>Clear browser tab titles</li>
                                    <li>Bookmarked page context</li>
                                    <li>Search result clarity</li>
                                    <li>Content expectations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary me-2">
                        <i class="bi bi-arrow-left me-2"></i>Back to Main App
                    </a>
                    <a href="test-complete.php" class="btn btn-secondary">
                        <i class="bi bi-list-check me-2"></i>All Tests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add current page meta information display
        document.addEventListener('DOMContentLoaded', function() {
            // Display current page meta tags
            const currentTitle = document.title;
            const currentDescription = document.querySelector('meta[name="description"]')?.content || 'N/A';
            const currentKeywords = document.querySelector('meta[name="keywords"]')?.content || 'N/A';
            
            // Create info box for current page
            const infoBox = document.createElement('div');
            infoBox.className = 'alert alert-success mt-4';
            infoBox.innerHTML = `
                <h6>📄 Current Page Meta Tags:</h6>
                <p><strong>Title:</strong> <code>${currentTitle}</code></p>
                <p><strong>Description:</strong> <code>${currentDescription}</code></p>
                <p><strong>Keywords:</strong> <code>${currentKeywords}</code></p>
            `;
            
            // Insert after the first card
            const firstCard = document.querySelector('.card');
            firstCard.parentNode.insertBefore(infoBox, firstCard.nextSibling);
        });
    </script>
</body>
</html>