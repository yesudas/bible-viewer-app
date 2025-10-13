<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title>Mobile Floating Controls Test</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        <?php echo file_get_contents('css/styles.css'); ?>
        
        /* Additional test styles */
        .test-content {
            min-height: 150vh;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        }
        
        .test-section {
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .device-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="test-content">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">📱 Mobile Floating Controls Test</h2>
                    
                    <div class="test-section">
                        <h4>🎯 Testing Objectives</h4>
                        <ul>
                            <li><strong>Desktop:</strong> Floating controls stay in bottom-right corner</li>
                            <li><strong>Mobile:</strong> Controls become bottom toolbar with no shifting on zoom</li>
                            <li><strong>Zoom Test:</strong> Click zoom buttons and verify controls don't move down</li>
                        </ul>
                    </div>
                    
                    <div class="test-section">
                        <h4>📊 Device Information</h4>
                        <div class="device-info" id="deviceInfo">
                            Loading device information...
                        </div>
                    </div>
                    
                    <div class="test-section">
                        <h4>🔍 Zoom Test Content</h4>
                        <p style="font-size: var(--font-size-base);">
                            This text will change size when you click the zoom buttons. 
                            On mobile devices, the floating controls should NOT shift down 
                            when zooming in or out.
                        </p>
                        
                        <div class="alert alert-info">
                            <strong>Mobile Test Instructions:</strong>
                            <ol>
                                <li>Open this page on a real mobile device</li>
                                <li>Tap the zoom in (+) button multiple times</li>
                                <li>Verify the floating controls stay at the bottom</li>
                                <li>Tap zoom out (-) to test the reverse</li>
                            </ol>
                        </div>
                        
                        <div class="alert alert-warning">
                            <strong>Note:</strong> The shifting issue only occurs on real mobile devices, 
                            not in desktop browser developer tools mobile simulation.
                        </div>
                    </div>
                    
                    <div class="test-section">
                        <h4>📱 Mobile vs Desktop Behavior</h4>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">🖥️ Desktop (>768px)</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li>✅ Round floating buttons</li>
                                            <li>✅ Bottom-right positioning</li>
                                            <li>✅ Standard box shadow</li>
                                            <li>✅ Fixed positioning</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">📱 Mobile (≤768px)</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li>✅ Compact bottom toolbar</li>
                                            <li>✅ Smaller buttons (40x40px)</li>
                                            <li>✅ Reduced padding (8px)</li>
                                            <li>✅ Less space usage</li>
                                            <li>✅ Sticky positioning with fallback</li>
                                            <li>✅ No shifting on zoom</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sample content to test scrolling -->
                    <div class="test-section">
                        <h4>📜 Sample Bible Content</h4>
                        <div style="font-size: var(--font-size-base);">
                            <p><strong>Genesis 1:1-5</strong></p>
                            <p>1. In the beginning God created the heaven and the earth.</p>
                            <p>2. And the earth was without form, and void; and darkness was upon the face of the deep. And the Spirit of God moved upon the face of the waters.</p>
                            <p>3. And God said, Let there be light: and there was light.</p>
                            <p>4. And God saw the light, that it was good: and God divided the light from the darkness.</p>
                            <p>5. And God called the light Day, and the darkness he called Night. And the evening and the morning were the first day.</p>
                        </div>
                    </div>
                    
                    <div class="test-section">
                        <h4>🔄 Current Font Size</h4>
                        <p>Font Size: <span id="currentFontSize">16px</span></p>
                        <div class="progress mb-3">
                            <div class="progress-bar" id="fontSizeProgress" style="width: 33%"></div>
                        </div>
                    </div>
                    
                    <div class="test-section">
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
    </div>

    <!-- Floating Controls -->
    <div class="floating-controls">
        <button class="btn btn-primary" onclick="zoomIn()" title="Zoom In">
            <i class="bi bi-zoom-in"></i>
        </button>
        <button class="btn btn-primary" onclick="zoomOut()" title="Zoom Out">
            <i class="bi bi-zoom-out"></i>
        </button>
        <button class="btn btn-secondary" onclick="resetZoom()" title="Reset Zoom">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button class="btn btn-success" onclick="scrollToTop()" title="Go to Top">
            <i class="bi bi-arrow-up"></i>
        </button>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Simplified zoom functionality for testing
        let currentFontSize = 16;
        
        function zoomIn() {
            if (currentFontSize < 24) {
                currentFontSize += 2;
                updateFontSize();
                
                // Aggressive mobile viewport stabilization
                stabilizeMobileControls();
            }
        }

        function zoomOut() {
            if (currentFontSize > 12) {
                currentFontSize -= 2;
                updateFontSize();
                
                // Aggressive mobile viewport stabilization
                stabilizeMobileControls();
            }
        }

        function resetZoom() {
            currentFontSize = 16;
            updateFontSize();
            
            // Aggressive mobile viewport stabilization
            stabilizeMobileControls();
        }

        function updateFontSize() {
            document.documentElement.style.setProperty('--font-size-base', currentFontSize + 'px');
            
            // Update display
            document.getElementById('currentFontSize').textContent = currentFontSize + 'px';
            
            // Update progress bar
            const progress = ((currentFontSize - 12) / (24 - 12)) * 100;
            document.getElementById('fontSizeProgress').style.width = progress + '%';
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Enhanced mobile device detection
        function isMobileDevice() {
            const userAgent = navigator.userAgent.toLowerCase();
            const isMobileUA = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent);
            const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
            const isSmallScreen = window.innerWidth <= 768;
            
            return isMobileUA || (isTouchDevice && isSmallScreen);
        }

        // Aggressive mobile controls stabilization
        function stabilizeMobileControls() {
            if (!isMobileDevice()) return;
            
            const floatingControls = document.querySelector('.floating-controls');
            if (!floatingControls) return;
            
            console.log('🔧 Stabilizing mobile controls...');
            
            // Method 1: Force hardware acceleration
            floatingControls.style.transform = 'translate3d(0, 0, 0)';
            floatingControls.style.webkitTransform = 'translate3d(0, 0, 0)';
            
            // Method 2: Force position recalculation
            floatingControls.style.position = 'absolute';
            floatingControls.style.bottom = '0px';
            
            // Method 3: Use requestAnimationFrame for smooth repositioning
            requestAnimationFrame(() => {
                floatingControls.style.position = 'fixed';
                floatingControls.style.bottom = '0px';
                floatingControls.style.left = '0px';
                floatingControls.style.right = '0px';
                
                // Method 4: Force a layout recalculation
                floatingControls.offsetHeight;
                
                // Method 5: Reset transforms after repositioning
                setTimeout(() => {
                    floatingControls.style.transform = 'translateZ(0)';
                    floatingControls.style.webkitTransform = 'translateZ(0)';
                }, 10);
            });
            
            // Method 6: Prevent scroll-related shifts
            setTimeout(() => {
                window.scrollTo(window.scrollX, window.scrollY);
            }, 50);
        }

        // Initialize mobile controls stabilization
        function initializeMobileControls() {
            if (isMobileDevice()) {
                console.log('📱 Initializing mobile controls...');
                
                // Add event listeners for orientation changes
                window.addEventListener('orientationchange', () => {
                    setTimeout(stabilizeMobileControls, 100);
                });
                
                // Add event listeners for resize
                window.addEventListener('resize', () => {
                    setTimeout(stabilizeMobileControls, 50);
                });
                
                // Initial stabilization
                setTimeout(stabilizeMobileControls, 100);
            }
        }
        
        // Display device information
        function updateDeviceInfo() {
            const info = {
                'User Agent': navigator.userAgent,
                'Screen Size': `${screen.width} x ${screen.height}`,
                'Window Size': `${window.innerWidth} x ${window.innerHeight}`,
                'Device Pixel Ratio': window.devicePixelRatio,
                'Touch Support': 'ontouchstart' in window ? 'Yes' : 'No',
                'Mobile Detected': isMobileDevice() ? 'Yes' : 'No',
                'Viewport Width': document.documentElement.clientWidth + 'px'
            };
            
            let infoHTML = '';
            for (const [key, value] of Object.entries(info)) {
                infoHTML += `<strong>${key}:</strong> ${value}<br>`;
            }
            
            document.getElementById('deviceInfo').innerHTML = infoHTML;
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateDeviceInfo();
            updateFontSize(); // Initialize progress bar
            initializeMobileControls(); // Initialize mobile controls
            
            // Update device info on resize
            window.addEventListener('resize', updateDeviceInfo);
        });
    </script>
</body>
</html>