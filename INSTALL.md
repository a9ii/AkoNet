# AkoNet Web Monitor - Installation Guide

## Requirements

- PHP 7.4+ (with `pdo_mysql`, `shell_exec` enabled)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- cPanel Shared Hosting (or any LAMP stack)

---

## Step 1: Upload Files

1. Upload the entire `AkoNet` folder contents to your cPanel's `public_html` directory (or a subdirectory like `public_html/monitor/`)
2. Ensure the folder structure is preserved

---

## Step 2: Create the Database

1. Login to **cPanel** → **MySQL Databases**
2. Create a new database: `akonet_monitor`
3. Create a new user with a strong password
4. Add the user to the database with **ALL PRIVILEGES**
5. Go to **phpMyAdmin** → Select the database → **Import** tab
6. Upload and import `database.sql`

---

## Step 3: Configure the Application

Edit `config.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'YOUR_CPANEL_PREFIX_akonet_monitor');
define('DB_USER', 'YOUR_CPANEL_PREFIX_username');
define('DB_PASS', 'your_password');
```

> **Note:** cPanel prepends your account prefix to database names and users (e.g., `cpuser_akonet_monitor`).

Set your timezone:
```php
define('TIMEZONE', 'Asia/Baghdad');
```

---

## Step 4: Set Up the Cron Job

1. Go to **cPanel** → **Cron Jobs**
2. Add a new cron job:
   - **Schedule:** Every 5 minutes (`*/5 * * * *`)
   - **Command:** `php /home/YOUR_USER/public_html/cron/monitor.php`
3. Replace `YOUR_USER` with your actual cPanel username
4. Replace the path if you installed in a subdirectory

---

## Step 5: Admin Access

1. Navigate to `https://yourdomain.com/admin/login.php`
2. **Default credentials:**
   - Username: `admin`
   - Password: `admin123`
3. ⚠️ **Change the password immediately** after first login

### To Change Admin Password:

Run this SQL in phpMyAdmin:
```sql
UPDATE admins SET password = '$2y$10$YOUR_NEW_HASH' WHERE username = 'admin';
```

Generate a new hash with PHP:
```php
echo password_hash('your_new_password', PASSWORD_BCRYPT);
```

---

## Step 6: Verify Installation

1. Open `https://yourdomain.com/` — you should see the dashboard
2. Check that the summary cards show correct counts
3. Click a provider to see the detail page with charts
4. Wait for the cron to run (or trigger it manually via CLI)
5. Verify data appears in the monitoring logs

---

## Troubleshooting

| Issue | Solution |
|---|---|
| Blank page | Check PHP error logs in cPanel → Error Log |
| Database error | Verify `config.php` credentials |
| No ping data | Ensure `shell_exec` is not disabled in `php.ini` |
| .htaccess errors | Ensure `mod_rewrite` is enabled |
| Charts empty | Check browser console for JavaScript errors |

---

## Security Checklist

- [ ] Change default admin password
- [ ] Verify `config.php` is not accessible via browser
- [ ] Set `display_errors = 0` in production
- [ ] Use HTTPS (install SSL via cPanel → SSL/TLS)
- [ ] Set proper file permissions (644 for files, 755 for directories)
