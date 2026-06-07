# MyFitness – Deployment Guide
# Domain: wlati-mn.com

## Architektur
| URL | Inhalt |
|-----|--------|
| `https://myfitness.wlati-mn.com` | React Frontend |
| `https://api.wlati-mn.com` | Laravel Backend (API) |
| `https://admin.wlati-mn.com` | Redirect → myfitness.wlati-mn.com/admin |

---

## Schritt 1 – Subdomains in cPanel erstellen

Gehe in cPanel → **Domains** → **Subdomains** und erstelle:

| Subdomain | Document Root |
|-----------|--------------|
| `myfitness.wlati-mn.com` | `/public_html/myfitness` |
| `api.wlati-mn.com` | `/public_html/api/public` |
| `admin.wlati-mn.com` | `/public_html/admin-redirect` |

---

## Schritt 2 – Backend (Laravel) hochladen

### 2a. Backend-Ordner vorbereiten (lokal)
```powershell
cd D:\Personal\my-apps\fitness-backend

# Dependencies installieren (ohne Dev-Pakete)
composer install --no-dev --optimize-autoloader

# .env.production aktivieren
Copy-Item .env.production .env

# App-Key neu generieren (WICHTIG nach Kopie!)
php artisan key:generate

# Config/Route cache erstellen
php artisan config:cache
php artisan route:cache
```

### 2b. Folgende Dateien/Ordner via FTP hochladen nach `/public_html/api/`:
```
app/
bootstrap/
config/
database/
public/          ← nur dieser Ordner ist Document Root
resources/
routes/
storage/
vendor/
.env             ← die .env.production (umbenannt)
artisan
composer.json
```
**NICHT hochladen:** `.git/`, `node_modules/`, `tests/`

### 2c. Berechtigungen setzen

**Option A – via SSH/Terminal in cPanel:**
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

**Option B – PHP-Skript (falls kein SSH):**  
Lade `fixperms.php` nach `/public_html/api/public/` hoch (Document Root!) und rufe sie einmal im Browser auf:
```php
<?php
function chmodR($path, $mode) {
    if (!is_dir($path)) return;
    $dir = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
    foreach (new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST) as $file) {
        chmod($file->getPathname(), $mode);
    }
    chmod($path, $mode);
}
chmodR(__DIR__.'/../storage', 0775);
chmodR(__DIR__.'/../bootstrap/cache', 0775);
echo '<b>Done!</b> Permissions gesetzt!';
echo '<br><b style="color:red">WICHTIG: Diese Datei jetzt wieder löschen!</b>';
```
URL: `https://api.wlati-mn.com/fixperms.php`  
Danach die `fixperms.php` wieder löschen!

### 2d. Datenbank-Migration (ohne SSH)

Lade `migrate_run.php` nach `/public_html/api/public/` hoch (Document Root!) und rufe sie im Browser auf:

```php
<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
echo '<pre>';
$kernel->call('migrate', ['--force' => true]);
echo '</pre>';
echo '<b>Migration abgeschlossen!</b><br>';
echo '<b style="color:red">WICHTIG: Diese Datei jetzt wieder löschen!</b>';
```

URL: `https://api.wlati-mn.com/migrate_run.php`  
Danach `migrate_run.php` sofort wieder löschen!

---
  
## Schritt 3 – Frontend (React) builden und hochladen

### 3a. Frontend builden (lokal)
```powershell
cd D:\Personal\my-apps\fitness\fitness-app\frontend

# Produktions-Build erstellen (nutzt .env.production automatisch)
npm run build
```

### 3b. Den Inhalt von `build/` hochladen nach `/public_html/myfitness/`
Alle Dateien aus dem `build/`-Ordner (inkl. `.htaccess`):
```
build/
  static/
  index.html
  .htaccess       ← wichtig für React Router!
  ...
```

---

## Schritt 4 – Admin-Subdomain einrichten

Lade die Datei `admin-redirect/.htaccess` hoch nach `/public_html/admin-redirect/`:

```
RewriteEngine On
RewriteRule ^(.*)$ https://myfitness.wlati-mn.com/admin [R=301,L]
```

---

## Schritt 5 – SSL-Zertifikat (HTTPS)

In cPanel → **SSL/TLS** → **Let's Encrypt**:
- Zertifikat für `myfitness.wlati-mn.com` ausstellen
- Zertifikat für `api.wlati-mn.com` ausstellen  
- Zertifikat für `admin.wlati-mn.com` ausstellen

---

## Schritt 6 – E-Mail für Passwort-Reset konfigurieren

In `/public_html/api/.env` die MAIL-Werte mit deinen E-Mail-Zugangsdaten von wlati-mn.com füllen:
```
MAIL_HOST=mail.wlati-mn.com
MAIL_USERNAME=noreply@wlati-mn.com
MAIL_PASSWORD=DEIN_PASSWORT
```

---

## Produktions-Checkliste

- [ ] Subdomains erstellt
- [ ] Backend hochgeladen + `.env` konfiguriert
- [ ] `APP_DEBUG=false` in `.env` gesetzt
- [ ] Frontend gebaut und hochgeladen
- [ ] `.htaccess` in myfitness-Ordner vorhanden
- [ ] Admin-Redirect hochgeladen
- [ ] SSL-Zertifikate aktiv (HTTPS)
- [ ] E-Mail für Passwort-Reset konfiguriert
- [ ] Ersten Admin-User per SQL oder Tinker setzen:
  ```sql
  UPDATE users SET is_admin=1 WHERE email='deine@email.de' LIMIT 1;
  ```

---

## Lokale Entwicklung

```powershell
# Backend starten
cd D:\Personal\my-apps\fitness-backend
php artisan serve   # → http://localhost:8000

# Frontend starten
cd D:\Personal\my-apps\fitness\fitness-app\frontend
npm start           # → http://localhost:3001
```

## Prerequisites

- cPanel account on Hetzner server
- FileZilla or similar FTP client installed
- PostgreSQL/MySQL database credentials
- Domain with SSL certificate (already have: `myfitness.deinedomain.com`)
- PHP 8.4 installed (confirmed)
- Composer installed (confirmed - version 2.5.5)

## Database Credentials

Siehe `.env` auf dem Server. Credentials nicht in Git einchecken!

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
APP_URL=https://api.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CORS_ALLOWED_ORIGINS=https://myfitness.yourdomain.com
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
