# MyFitness - Complete Deployment Guide

## Prerequisites

- cPanel account on Hetzner server
- FileZilla or similar FTP client installed
- PostgreSQL/MySQL database credentials
- Domain with SSL certificate (already have: `myfitness.deinedomain.com`)
- PHP 8.4 installed (confirmed)
- Composer installed (confirmed - version 2.5.5)

## Database Credentials

```
Host: l9du.your-database.de
Database: wlatie_db0
User: wlatie_0
Password: c4,SPY6ox:zs
Port: 3306
```

## Step 1: Setup Subdomain in cPanel

### Create Addon Domain or Subdomain

1. Login to **cPanel**
2. Navigate to **Addon Domains** or **Subdomains**
3. **Subdomain name:** `myfitness`
4. **Parent domain:** `deinedomain.com`
5. **Document root:** `/public_html/myfitness`
6. Click **"Create"**
7. Wait for DNS propagation (usually instant)

## Step 2: Upload Laravel Backend via FileZilla

### Connect to Server

1. Open **FileZilla**
2. **Host:** `ftp://deinedomain.com` (or your FTP host)
3. **Username:** Your cPanel username
4. **Password:** Your cPanel password
5. **Port:** 21 (or 22 for SFTP)
6. Click **"Quickconnect"**

### Upload Backend Files

1. Navigate to `/public_html/myfitness` folder
2. **Download** all files from this repository's `backend/` folder
3. **Upload** ALL files to `/public_html/myfitness`

Folder structure should be:
```
/public_html/myfitness/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── .env
├── composer.json
├── artisan
└── ...
```

## Step 3: Configure Environment File

### Create/Edit .env File

1. In FileZilla, navigate to `/public_html/myfitness`
2. Right-click → **Edit** → `.env` file
3. Update with YOUR credentials:

```env
APP_NAME=FitnessApp
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://myfitness.deinedomain.com

DB_CONNECTION=mysql
DB_HOST=l9du.your-database.de
DB_PORT=3306
DB_DATABASE=wlatie_db0
DB_USERNAME=wlatie_0
DB_PASSWORD=c4,SPY6ox:zs

JWT_SECRET=your-secret-key-here-32-chars-min
JWT_ALGORITHM=HS256
JWT_TTL=60

CORS_ALLOWED_ORIGINS=https://myfitness.deinedomain.com
```

4. Save file

## Step 4: Run Database Migrations

### Via cPanel Terminal

1. In **cPanel**, click **"Terminal"** (if available)
2. Navigate to your app:
   ```bash
   cd /home/cpanel_user/public_html/myfitness
   ```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

4. (Optional) Seed sample data:
   ```bash
   php artisan seed
   ```

### If Terminal Not Available

Create `migrate.php` file:

```php
<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
echo "<pre>";
$status = $kernel->call('migrate');
echo "</pre>";
echo "Migrations completed with status: " . $status;
?>
```

Upload to `/public_html/myfitness/`  
Visit: `https://myfitness.deinedomain.com/migrate.php`

## Step 5: Build React Frontend

### On Your Local Computer

1. Open terminal/command prompt
2. Navigate to `frontend` folder
3. Install dependencies:
   ```bash
   npm install
   ```

4. Create `.env` file:
   ```env
   REACT_APP_API_URL=https://myfitness.deinedomain.com/api
   ```

5. Build for production:
   ```bash
   npm run build
   ```

6. This creates `/build` folder with static files

## Step 6: Upload React Frontend via FileZilla

1. Locate `/build` folder (created by `npm run build`)
2. Open FileZilla
3. Navigate to `/public_html/myfitness`
4. **Delete** existing index.html (if any)
5. Upload ALL contents of `/build` folder to `/public_html/myfitness/`

Structure:
```
/public_html/myfitness/
├── index.html
├── static/
│   ├── js/
│   ├── css/
│   └── media/
├── .htaccess (create this!)
└── ...
```

## Step 7: Create .htaccess File for React Routing

1. Create new file: `.htaccess`
2. Content:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /myfitness/
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /myfitness/index.html [L]
</IfModule>
```

3. Upload to `/public_html/myfitness/`

## Step 8: Verify Installation

### Test Backend API

1. Visit: `https://myfitness.deinedomain.com/api/auth/login`
2. Should get JSON error (no POST data) — that's OK, API is working

### Test Frontend

1. Visit: `https://myfitness.deinedomain.com`
2. Should see **Login page**
3. Click "Register here"
4. Create test account
5. Should redirect to Dashboard

## Step 9: Configure Nginx (if applicable)

If your cPanel uses Nginx instead of Apache:

```nginx
server {
    listen 443 ssl http2;
    server_name myfitness.deinedomain.com;
    
    root /home/cpanel_user/public_html/myfitness;
    index index.html index.htm;
    
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    
    # API requests
    location /api {
        try_files $uri $uri/ /api/index.php?$query_string;
    }
    
    # PHP requests
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # React SPA routing
    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

## Troubleshooting

### 502 Bad Gateway

- Check if PHP is running
- Verify `.env` database credentials
- Check `/storage/logs/` for errors

### CORS Errors

- Verify `CORS_ALLOWED_ORIGINS` in backend `.env`
- Should be: `https://myfitness.deinedomain.com`
- Clear browser cache

### React Routes Not Working

- Ensure `.htaccess` file is uploaded
- Check Apache mod_rewrite is enabled
- Verify RewriteBase matches subdirectory

### Database Connection Error

- Verify credentials match exactly
- Test connection from cPanel "phpMyAdmin"
- Check if host is accessible

### SSL Certificate Issues

- Use cPanel "AutoSSL" to auto-renew
- Or manually in "SSL/TLS Status"

## Final Checklist

- ✅ Subdomain created (`myfitness.deinedomain.com`)
- ✅ Laravel files uploaded
- ✅ `.env` configured with DB credentials
- ✅ Migrations run successfully
- ✅ React build uploaded
- ✅ `.htaccess` file in place
- ✅ API responds at `/api`
- ✅ Frontend loads at `/`
- ✅ HTTPS working
- ✅ Can register and login

## Going Live

1. Set `APP_DEBUG=false` in `.env`
2. Set `APP_ENV=production`
3. Clear all caches: `php artisan config:clear`
4. Monitor logs in `/storage/logs/`
5. Setup backup strategy

## Support

For issues:
1. Check `/storage/logs/laravel.log`
2. Review browser console errors (F12)
3. Verify all credentials
4. Test API endpoints with Postman

---

**You're done! MyFitness is now live at https://myfitness.deinedomain.com** 🎉
