# Sinath Travels - Full Stack Installation Guide

## Prerequisites

- **XAMPP** (or WAMP/MAMP) - Apache + MySQL + PHP 7.4+
- **Node.js** - v18+ and npm
- **Git** (optional)

---

## Project Structure

```
sinath-travels/
├── client/              # React Frontend
│   ├── src/
│   │   ├── pages/
│   │   ├── components/
│   │   ├── lib/
│   │   │   ├── api.ts       # NEW: API client
│   │   │   ├── data.ts      # UPDATED: Now imports from API
│   │   │   └── i18n.tsx
│   │   └── main.tsx
│   └── public/
├── server/              # NEW: PHP Backend
│   ├── config/
│   │   └── database.php
│   ├── api/
│   │   ├── packages.php
│   │   ├── inquiries.php
│   │   └── services.php
│   ├── uploads/         # Image storage
│   └── .htaccess
├── database.sql         # NEW: MySQL schema
├── .env.example
└── README.md
```

---

## Step 1: Database Setup

### 1.1 Start MySQL Server
- Open XAMPP Control Panel
- Start **Apache** and **MySQL** services

### 1.2 Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose `database.sql` file
4. Click "Go"

**Or** run these commands in SQL console:
```sql
CREATE DATABASE sinath_travels CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sinath_travels;
-- Then paste the contents of database.sql
```

### 1.3 Verify Tables
Check that these tables exist:
- `packages`
- `inquiries`
- `promotions`
- `services`

---

## Step 2: Backend Setup (PHP)

### 2.1 Place Files
Copy the `server/` folder to your XAMPP htdocs:
```
C:\xampp\htdocs\sinath-travels\server\
```

### 2.2 Configure Database Connection
Edit `server/config/database.php`:

```php
private $host = "localhost";
private $db_name = "sinath_travels";
private $username = "root";      // Your MySQL username
private $password = "";          // Your MySQL password (usually empty for local)
```

### 2.3 Create Uploads Directory
```bash
mkdir server/uploads
chmod 755 server/uploads  # On Linux/Mac
```

Or create manually and ensure Apache has write permissions.

### 2.4 Test Backend API
Visit these URLs in your browser:

**Test Packages API:**
```
http://localhost/sinath-travels/server/api/packages.php
```

**Test Services API:**
```
http://localhost/sinath-travels/server/api/services.php
```

You should see JSON responses with data.

---

## Step 3: Frontend Setup (React)

### 3.1 Install Dependencies
```bash
cd client
npm install
```

### 3.2 Configure API URL
Create `.env` file in project root: 

```env
VITE_API_URL=http://localhost/sinath-travels/server/api
```

### 3.3 Run Development Server
```bash
npm run dev:client
```

The app will open at: http://localhost:5000

---

## Step 4: Testing

### 4.1 Test Homepage
- Visit http://localhost:5000
- Packages should load from MySQL database
- Services should display from database

### 4.2 Test Contact Form
1. Go to Contact page
2. Fill out the form
3. Submit
4. Check phpMyAdmin → `inquiries` table for new entry

### 4.3 Test Image Upload (Optional - for admin panel)
```bash
curl -X POST http://localhost/sinath-travels/server/api/packages.php \
  -F "title_en=Test Package" \
  -F "description_en=Test Description" \
  -F "price=500" \
  -F "image=@/path/to/image.jpg"
```

---

## Step 5: Production Build

### 5.1 Build Frontend
```bash
cd client
npm run build
```

This creates `dist/` folder with optimized files.

### 5.2 Deploy
1. Copy `dist/` contents to your web server's public directory
2. Copy `server/` folder to your web server
3. Update `.env` with production API URL
4. Configure Apache VirtualHost (see below)

---

## Apache Configuration (Production)

### Option 1: .htaccess (Shared Hosting)

Place this in your root directory:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # API requests go to PHP backend
  RewriteCond %{REQUEST_URI} ^/server/
  RewriteRule ^ - [L]
  
  # Frontend SPA routing
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

### Option 2: VirtualHost (VPS/Dedicated)

```apache
<VirtualHost *:80>
    ServerName sinathtravels.com
    DocumentRoot /var/www/sinath-travels/dist
    
    <Directory /var/www/sinath-travels/dist>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    Alias /server /var/www/sinath-travels/server
    <Directory /var/www/sinath-travels/server>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## Common Issues & Solutions

### Issue 1: CORS Errors
**Symptom:** Frontend can't connect to backend

**Solution:** 
- Ensure `enableCORS()` is called in all PHP files
- Check Apache `mod_headers` is enabled
- Verify `.htaccess` CORS headers

### Issue 2: 404 on API Calls
**Symptom:** API endpoints return 404

**Solution:**
- Check Apache `mod_rewrite` is enabled
- Verify file paths in `.htaccess`
- Ensure PHP files have correct permissions

### Issue 3: Database Connection Failed
**Symptom:** PHP errors about database connection

**Solution:**
- Verify MySQL is running
- Check credentials in `database.php`
- Ensure database exists
- Check MySQL user permissions

### Issue 4: Images Not Loading
**Symptom:** Package images show broken

**Solution:**
- Check `uploads/` directory exists and is writable
- Verify image paths in database
- Check Apache permissions on uploads directory

### Issue 5: Form Submission Fails
**Symptom:** Contact form doesn't submit

**Solution:**
- Check browser console for errors
- Verify API URL in `.env`
- Check PHP error logs: `/xampp/apache/logs/error.log`

---

## File Permissions (Linux/Mac)

```bash
# Backend
chmod 755 server/api/*.php
chmod 755 server/config/*.php
chmod 777 server/uploads/

# Frontend build
chmod 755 dist/
```

---

## API Endpoints Reference
-----------------------------------------------------------------
| Method | Endpoint                      | Description          |
|--------|-------------------------------|----------------------|
| GET    | `/packages.php`               | Fetch all packages   |
| GET    | `/packages.php?category=tour` | Filter by category   |
| POST   | `/inquiries.php`              | Submit contact form  |
| GET    | `/services.php`               | Fetch services       |
-----------------------------------------------------------------


## Development Workflow

1. **Backend Changes:**
   - Edit PHP files in `server/`
   - Test via browser or Postman
   - Check PHP error logs

2. **Frontend Changes:**
   - Edit React files in `client/src/`
   - Changes hot-reload automatically
   - Check browser console

3. **Database Changes:**
   - Edit tables in phpMyAdmin
   - Update `database.sql` for version control

---

## Next Steps

- Add authentication for admin panel
- Implement email notifications (uncomment in `inquiries.php`)
- Add image optimization for uploads
- Set up automated backups for MySQL
- Configure SSL certificate for production

---

## Support

For issues, check:
1. Browser console (F12)
2. Apache error logs: `xampp/apache/logs/error.log`
3. PHP error logs: `xampp/php/logs/php_error_log`

---

## Technologies Used

**Frontend:**
- React 19
- TypeScript
- Tailwind CSS v4
- shadcn/ui
- React Query
- Wouter (routing)

**Backend:**
- PHP 7.4+
- MySQL 5.7+
- PDO (database)
- Apache

**Development:**
- Vite (build tool)
- XAMPP (local server)