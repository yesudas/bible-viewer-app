# Online Bibles - Deployment Guide

## Quick Deployment Checklist

### ✅ Files Created
- ✅ `index.php` - Main application with full functionality
- ✅ `api.php` - RESTful API for data handling  
- ✅ `.htaccess` - URL rewriting and security
- ✅ `README.md` - Complete documentation

### 🚀 Deployment Steps

#### For Shared Hosting (cPanel/DirectAdmin)
1. **Upload Files**: Upload all files to your `public_html` or web root directory
2. **Verify Permissions**: Ensure `data/` folder has read permissions (755)
3. **Test URL Rewriting**: Visit your domain to confirm .htaccess is working
4. **Check PHP Version**: Ensure PHP 7.4+ is enabled in hosting panel

#### For VPS/Dedicated Server
1. **Apache Setup**:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **Virtual Host Configuration**:
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/html/bible-viewer-app
       AllowOverride All
   </VirtualHost>
   ```

3. **File Permissions**:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/bible-viewer-app
   sudo chmod -R 755 /var/www/html/bible-viewer-app
   ```

#### For Nginx
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/bible-viewer-app;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ ^/api/(.*)$ {
        try_files $uri /api.php?action=$1&$query_string;
    }
}
```

### 🔒 Security Considerations
- ✅ XSS protection headers enabled
- ✅ Input validation implemented
- ✅ File access restrictions in place
- ✅ CORS handling configured
- ⚠️ Consider enabling HTTPS for production

### 📊 Performance Optimization
- ✅ Gzip compression enabled
- ✅ Browser caching configured
- ✅ Lazy loading implemented
- ✅ Optimized CSS/JS loading

### 🧪 Testing Checklist
- [ ] Homepage loads correctly
- [ ] Language selection works
- [ ] Bible version selection functions
- [ ] Book/chapter dropdowns populate
- [ ] Verses display properly
- [ ] Copy functionality works
- [ ] Zoom controls function
- [ ] Mobile responsiveness verified
- [ ] SEO URLs work correctly

### 🐛 Common Issues & Solutions

#### "404 Not Found" on URLs
**Problem**: Clean URLs not working
**Solution**: 
- Verify mod_rewrite is enabled
- Check .htaccess file permissions
- Ensure AllowOverride is set correctly

#### "Data not loading"
**Problem**: API requests failing
**Solution**:
- Check file permissions on data/ folder
- Verify JSON syntax in language files
- Enable PHP error reporting for debugging

#### "Mobile display issues"
**Problem**: Layout not responsive
**Solution**:
- Verify viewport meta tag
- Check Bootstrap CSS loading
- Test on actual mobile devices

### 📱 Mobile Testing
Test on these browsers/devices:
- iOS Safari (iPhone/iPad)
- Chrome Mobile (Android)
- Samsung Internet
- Firefox Mobile

### 🌐 Browser Compatibility
- ✅ Modern browsers (Chrome 70+, Firefox 65+, Safari 12+)
- ✅ Mobile browsers (iOS Safari 12+, Chrome Mobile 70+)
- ❌ Internet Explorer (not supported)

### 📈 Analytics & Monitoring
Consider adding:
- Google Analytics for usage tracking
- Error monitoring (Sentry, etc.)
- Performance monitoring
- User feedback system

### 🔧 Maintenance
- Regular backup of data files
- Monitor server logs for errors
- Update Bible versions as needed
- Security updates for server software

---

## Support & Resources
- **Documentation**: See README.md for detailed usage
- **Community**: WordOfGod.in
- **Issues**: Report problems through repository

**Happy Deployment! 🚀**