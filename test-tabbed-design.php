<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabbed Design Test - Online Bibles</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        <?php echo file_get_contents('css/styles.css'); ?>
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">New Tabbed Design for Bible Selection</h2>
                
                <!-- Original Design (for comparison) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">❌ Old Design (Space Wasting)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Languages:</h6>
                            <div class="scroll-container">
                                <button class="btn language-btn active">English</button>
                                <button class="btn language-btn">Tamil</button>
                                <button class="btn language-btn">Hindi</button>
                                <button class="btn language-btn">Malayalam</button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Bibles:</h6>
                            <div class="scroll-container">
                                <button class="btn bible-btn active">KJV</button>
                                <button class="btn bible-btn">NASB</button>
                                <button class="btn bible-btn">ESV</button>
                                <button class="btn bible-btn">NIV</button>
                            </div>
                        </div>
                        
                        <div>
                            <h6 class="text-muted mb-2">Selected Bibles:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-primary">KJV <i class="bi bi-x-circle"></i></span>
                                <span class="badge bg-primary">ESV <i class="bi bi-x-circle"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- New Tabbed Design -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">✅ New Tabbed Design (Space Efficient)</h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Languages Tabs (First Row) -->
                        <div class="border-bottom">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link language-tab active" type="button">English</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link language-tab" type="button">Tamil</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link language-tab" type="button">Hindi</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link language-tab" type="button">Malayalam</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link language-tab" type="button">Telugu</button>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Bibles Tabs (Second Row) -->
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-muted mb-0">Available Bibles:</h6>
                                <small class="text-muted">Click to select/deselect</small>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="bible-tab-btn active">KJV</button>
                                <button class="bible-tab-btn">NASB</button>
                                <button class="bible-tab-btn active">ESV</button>
                                <button class="bible-tab-btn">NIV</button>
                                <button class="bible-tab-btn">NKJV</button>
                                <button class="bible-tab-btn">RSV</button>
                                <button class="bible-tab-btn">NRSV</button>
                                <button class="bible-tab-btn">NLT</button>
                            </div>
                        </div>
                        
                        <!-- Selected Bibles Display (Compact) -->
                        <div class="border-top p-3">
                            <div class="d-flex flex-column">
                                <h6 class="text-muted mb-2">Selected:</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="selected-bible-tag">KJV <i class="bi bi-x" style="cursor: pointer; margin-left: 4px;"></i></span>
                                    <span class="selected-bible-tag">ESV <i class="bi bi-x" style="cursor: pointer; margin-left: 4px;"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h4>Key Improvements:</h4>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>🎯 Space Efficient:</strong> Uses standard Bootstrap tabs instead of custom scroll containers
                        </li>
                        <li class="list-group-item">
                            <strong>📱 Mobile Friendly:</strong> Tab interface adapts better to small screens
                        </li>
                        <li class="list-group-item">
                            <strong>🎨 Modern Look:</strong> Clean, professional tab design with hover effects
                        </li>
                        <li class="list-group-item">
                            <strong>🔄 Interactive:</strong> Clear visual feedback on hover and selection
                        </li>
                        <li class="list-group-item">
                            <strong>📊 Organized:</strong> Clear hierarchy - Languages → Bibles → Selected
                        </li>
                        <li class="list-group-item">
                            <strong>⚡ Responsive:</strong> Bible buttons wrap nicely on smaller screens
                        </li>
                    </ul>
                </div>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Main App
                    </a>
                    <a href="test-complete.php" class="btn btn-secondary">
                        <i class="bi bi-list-check me-2"></i>View All Tests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add some interactive demo functionality
        document.querySelectorAll('.language-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all language tabs
                document.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
                // Add active to clicked tab
                this.classList.add('active');
                
                // Update bible buttons based on language (demo simulation)
                const bibleContainer = document.querySelector('.bible-tab-btn').parentElement;
                const language = this.textContent;
                
                if (language === 'Tamil') {
                    bibleContainer.innerHTML = `
                        <button class="bible-tab-btn active">TOV2017</button>
                        <button class="bible-tab-btn">TCL1995</button>
                        <button class="bible-tab-btn">TCB1973</button>
                        <button class="bible-tab-btn">TCVIN2022</button>
                    `;
                } else if (language === 'Hindi') {
                    bibleContainer.innerHTML = `
                        <button class="bible-tab-btn active">HIUV</button>
                        <button class="bible-tab-btn">HIWTC</button>
                        <button class="bible-tab-btn">HICL</button>
                    `;
                } else {
                    bibleContainer.innerHTML = `
                        <button class="bible-tab-btn active">KJV</button>
                        <button class="bible-tab-btn">NASB</button>
                        <button class="bible-tab-btn active">ESV</button>
                        <button class="bible-tab-btn">NIV</button>
                        <button class="bible-tab-btn">NKJV</button>
                        <button class="bible-tab-btn">RSV</button>
                        <button class="bible-tab-btn">NRSV</button>
                        <button class="bible-tab-btn">NLT</button>
                    `;
                }
            });
        });
        
        // Add toggle functionality to bible buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('bible-tab-btn')) {
                e.target.classList.toggle('active');
            }
        });
    </script>
</body>
</html>