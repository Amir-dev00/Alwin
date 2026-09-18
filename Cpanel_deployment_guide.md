# ALWIN — put the site on cPanel

Persian guide: `Cpanel_deployment_guide.fa.md`

You do **not** need Terminal, SSH, Composer, or `php artisan` on the server.

Do this on your **Windows PC** first. Then use cPanel: File Manager, MySQL, phpMyAdmin, MultiPHP, Cron Jobs.

The site root is `public_html`. After upload you must see `index.php` there.  
Do **not** point the domain at a `public` folder.

---

## You need

- PHP **8.2** or **8.3**
- One MySQL database and user
- File Manager (or FTP) and phpMyAdmin

---

## 1. On your PC

1. Make sure the `vendor` folder exists (from `composer install`).
2. Copy `.env.production.example` and name the copy `.env`.  
   Do **not** use your local SQLite `.env`.
3. Create an app key on your PC:

   ```text
   php artisan key:generate --show
   ```

   Put the `base64:...` value in `.env` as `APP_KEY=`.
4. If you do not have a SQL file yet:

   ```text
   php artisan deploy:package --sql-only --dir=.
   ```

   This creates `alwin-production.sql`.
5. Turn on **hidden files** in File Explorer so `.htaccess` and `.env` are visible.
6. Zip these into `alwin-cpanel.zip`:

   - `.env`
   - `.htaccess`
   - `index.php`
   - `vendor`
   - `src`
   - `templates`
   - `includes`
   - `api`
   - `config`
   - `bootstrap`
   - `assets`
   - `images`
   - `storage`
   - `robots.txt`, `sitemap.xml`, `sitemap-articles.xml`, `favicon.ico` (if you have them)

Do **not** zip: `.git`, `tests`, `node_modules`, local SQLite files, log files, or your local `.env`.

If `vendor` is too big for File Manager, upload with FileZilla (FTP).

---

## 2. Upload

1. Open **File Manager**.
2. **Settings** → turn on **Show Hidden Files** → Save.
3. Open `public_html`.
4. Delete `index.html` if it exists.
5. Upload the zip → right-click → **Extract**.
6. If you now have `public_html/Alwin/index.php`, move everything **one folder up**.
7. You should see `index.php`, `.htaccess`, `.env`, `vendor`, `src`, `templates`, `assets`, `images`, `storage` in `public_html`.

Make these folders if they are missing:

```text
storage/app/public
storage/app/private
storage/framework/cache/data
storage/framework/sessions
storage/framework/views
storage/logs
bootstrap/cache
```

Set folders to `755`, files to `644`.  
Set `storage` and `bootstrap/cache` to `755` or `775` (PHP must be able to write there).

---

## 3. PHP

1. cPanel → **MultiPHP Manager**.
2. Choose the domain → PHP **8.2** or **8.3** → Apply.
3. Optional — **MultiPHP INI Editor**:

   - `upload_max_filesize` = `64M`
   - `post_max_size` = `64M`
   - `memory_limit` = `256M`

---

## 4. Database

1. cPanel → **MySQL Databases**.
2. Create a database and a user. Give the user **ALL PRIVILEGES**.
3. Copy the **full** names (they look like `cpaneluser_alwin`).
4. Open **phpMyAdmin** → select that database → **Import** → `alwin-production.sql`.

Import this SQL **only into an empty database** (first install).  
**Never** import it into a live site that already has products, admins, or CMS content. The dump drops tables.

`DB_HOST` is usually `127.0.0.1`.

---

## 5. Edit `.env` on the server

File Manager → show hidden files → `.env` → Edit.

Change at least these:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-DOMAIN.com
APP_KEY=base64:PASTE_THE_KEY_FROM_YOUR_PC

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=cpaneluser_alwin
DB_USERNAME=cpaneluser_alwinuser
DB_PASSWORD=your-mysql-password

ADMIN_EMAIL=admin@YOUR-DOMAIN.com
CRON_SECRET=a-long-random-string
```

- Use your real `https://` address. No slash at the end.
- Use MySQL, not SQLite.
- Save.

---

## 6. Cron

cPanel → **Cron Jobs**:

```text
*/5 * * * * curl -fsS "https://YOUR-DOMAIN.com/cron/YOUR_CRON_SECRET" >/dev/null 2>&1
```

Use the same secret as `CRON_SECRET` in `.env`.  
If `curl` is missing, use:

```text
*/5 * * * * wget -q -O /dev/null "https://YOUR-DOMAIN.com/cron/YOUR_CRON_SECRET"
```

---

## 7. Check

| Open this | You should see |
|---|---|
| `https://YOUR-DOMAIN.com/` | Home page |
| `https://YOUR-DOMAIN.com/admin/login` | Admin login |
| `https://YOUR-DOMAIN.com/.env` | **403 / Forbidden**, never the file |

Log in with the admin user from the SQL file.

**If it fails**

- Default cPanel page → delete `index.html`.
- No CSS or images → `assets` and `images` must sit next to `index.php`.
- Error 500 → set `APP_DEBUG=true` for one reload, read the error, then set it back to `false`. Also check PHP 8.2+, MySQL names in `.env`, writable `storage`, and that `.htaccess` was uploaded.

---

## Later updates (existing production)

The live database is the source of truth. Do **not** replace it.

1. Backup the live database first (`php artisan db:backup` on your PC against a copy, or phpMyAdmin Export).
2. Upload new code: `src`, `templates`, `includes`, `api`, `config`, `assets`.  
   Replace `vendor` only if packages changed on your PC.
3. Keep the existing MySQL database. Do **not** re-import `alwin-production.sql`.
4. Run pending migrations only (Admin → System check, or `php artisan migrate --force`). Never `migrate:fresh`, `db:wipe`, or seeders that reset content.
5. Test the website and admin. Confirm existing products, projects, articles, images, and admin login.

**Never replace** `.env`, `storage/app/public`, uploaded images, or the live database.

Then delete the files inside `storage/framework/views` (keep the empty folder).

---

## Checklist

1. Zip includes `vendor`, `.htaccess`, and a production `.env` with `APP_KEY`.
2. Extract so `index.php` is in `public_html`.
3. Hidden files on. `.htaccess` and `.env` are there.
4. PHP 8.2 or 8.3.
5. First install only: import `alwin-production.sql` into an **empty** database. Existing sites: never re-import.
6. `.env` has the real domain and MySQL names.
7. `storage` is writable.
8. Cron URL uses `CRON_SECRET`.
9. Home, admin login, and `/.env` (blocked) all work.
10. Next time, do not overwrite `.env` or uploaded images.
