if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('./sw.js')
        .then(() => console.log("Service Worker Registered"))
        .catch(err => console.log("Service Worker Failed:", err));
}

document.addEventListener("DOMContentLoaded", () => {
  
  // PWA functionality - Multiple install buttons support
  const installButtons = document.querySelectorAll('.install-app-btn');
  const installBanner = document.getElementById("pwaInstallBanner");
  const dismissBannerBtn = document.getElementById("dismissBannerBtn");
  const dontShowAgainBtn = document.getElementById("dontShowAgainBtn");
  
  let deferredPrompt;

  // Check if user has dismissed the banner permanently
  const bannerDismissed = localStorage.getItem('pwaBannerDismissed');
  
  // Hide all install buttons initially
  installButtons.forEach(btn => {
    if (btn) btn.style.display = "none";
  });

  // Hide banner initially
  if (installBanner) {
    installBanner.style.display = "none";
  }

  window.addEventListener("beforeinstallprompt", (e) => {
    console.log("beforeinstallprompt event fired");
    e.preventDefault();
    deferredPrompt = e;
    
    try {
        const urlParams = new URLSearchParams(window.location.search);
        // Show install options if not launched from installed app
        if (urlParams.get('f') !== 'app') {
            // Show all install buttons
            installButtons.forEach(btn => {
                if (btn) btn.style.display = "inline-block";
            });
            
            // Show banner if not dismissed permanently
            if (installBanner && !bannerDismissed) {
                installBanner.style.display = "block";
            }
            
            console.log("Install buttons and banner shown");
        }
    } catch (e) {
        console.error("Error managing install UI visibility:", e);
    }
  });

  // Check if app is already installed
  window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
    // Hide all install buttons
    installButtons.forEach(btn => {
      if (btn) btn.style.display = "none";
    });
    // Hide banner
    if (installBanner) {
      installBanner.style.display = "none";
    }
  });

  // Handle click on any install button
  installButtons.forEach(btn => {
    if (btn) {
      btn.addEventListener("click", async () => {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          const choice = await deferredPrompt.userChoice;
          console.log("User choice:", choice.outcome);
          
          if (choice.outcome === 'accepted') {
            // Hide all install buttons and banner
            installButtons.forEach(b => {
              if (b) b.style.display = "none";
            });
            if (installBanner) {
              installBanner.style.display = "none";
            }
          }
          deferredPrompt = null;
        } else {
          console.log("No deferred prompt available");
        }
      });
    }
  });

  // Handle dismiss banner button (temporary)
  if (dismissBannerBtn) {
    dismissBannerBtn.addEventListener("click", () => {
      if (installBanner) {
        installBanner.style.display = "none";
      }
    });
  }

  // Handle "Don't show again" button (permanent)
  if (dontShowAgainBtn) {
    dontShowAgainBtn.addEventListener("click", () => {
      localStorage.setItem('pwaBannerDismissed', 'true');
      if (installBanner) {
        installBanner.style.display = "none";
      }
    });
  }
});

