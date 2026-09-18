# ALWIN

یک برنامه PHP برای وب‌سایت و پنل مدیریت آلوین پنجره. صفحه عمومی با Blade رندر می‌شود. پنل مدیریت همان برنامه است، نه یک اپ جدا.

## ساختار پروژه

```
api/                 مسیرهای JSON برای ماشین‌حساب، فرم تماس و کاتالوگ
assets/              CSS، JS، فونت، تصاویر قالب و فایل‌های پنل
bootstrap/           کش کشف پکیج‌های Composer
config/              تنظیمات برنامه
data/                دادهٔ ایستا برای سیدر (مقالات HTML، کاتالوگ)
database/            سیدر، فکتوری، SQLite محلی
images/              تصاویر محصولات، پروژه‌ها و مقالات
includes/            bootstrap، مسیرهای وب، سرور محلی
migrations/          مهاجرت پایگاه‌داده
public/              فایل‌های عمومی کمکی (favicon و نقشه سایت نیز در ریشه هستند)
scripts/             اسکریپت نگهداری و کرون CLI
src/                 کد PHP برنامه
  Controllers/Web    صفحات عمومی
  Controllers/Admin  پنل مدیریت
  Controllers/Api    نقطهٔ ورود JSON
  Models/
  Services/          قیمت‌گذاری و منطق دامنه
  Middleware/
  Support/
storage/             لاگ، کش، نشست، آپلودها
  app/public         فایل‌های آپلود شده (نشانی عمومی /storage/...)
  uploads            میانبر به app/public
templates/           قالب‌های Blade
  layouts/           قالب اصلی سایت
  partials/          هدر، فوتر، سایدبار، ماشین‌حساب
  pages/             خانه، درباره، تماس
  products/
  projects/
  articles/
  admin/
vendor/
.env
.htaccess
artisan
composer.json
index.php
README.md
```

ریشهٔ وب روی cPanel همان پوشهٔ پروژه است (`index.php` + `.htaccess`). پوشه‌های داخلی با `.htaccess` بسته شده‌اند.

## نیازمندی‌ها

- PHP 8.2 یا جدیدتر (افزونه‌ها: pdo، pdo_mysql یا pdo_sqlite، mbstring، openssl، tokenizer، json، ctype، fileinfo، gd با WebP)
- Composer
- MySQL در تولید؛ SQLite برای اجرای محلی کافی است
- Apache با `mod_rewrite` در cPanel

Node.js، Docker، Supervisor یا `php artisan serve` لازم نیست.

## نصب محلی

```bash
composer install
```

اگر `.env` وجود ندارد:

```bash
copy .env.example .env
php artisan key:generate
```

پایگاه SQLite پیش‌فرض `database/database.sqlite` است. اگر فایل خالی است:

```bash
php artisan migrate --seed
```

اگر فایل SQLite فعلی از قبل داده دارد، migrate/seed را دوباره اجرا نکنید.

## اجرای محلی

```bash
php -S localhost:8000 includes/server.php
```

یا:

```bash
composer run serve
```

سپس:

- سایت: http://localhost:8000
- پنل: http://localhost:8000/admin
- API: http://localhost:8000/api/v1

## پیکربندی محیط

اسرار فقط در `.env` هستند.

| کلید | نقش |
|---|---|
| `APP_URL` | نشانی عمومی سایت |
| `APP_KEY` | کلید رمزنگاری Laravel |
| `DB_*` | اتصال پایگاه‌داده |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | کاربر اولیه هنگام seed |
| `CRON_SECRET` | توکن `GET /cron/{token}` |
| `MAIL_*` | ارسال ایمیل |
| `API_URL` | پیش‌فرض `/api/v1` برای ماشین‌حساب و فرم‌ها |

تولید: از `.env.production.example` کپی کنید، `APP_KEY` بسازید، و `DB_*` را پر کنید.

## پایگاه‌داده

مهاجرت‌ها در `migrations/` هستند.

```bash
php artisan migrate
php artisan db:seed
```

داده‌های فعلی (محصولات، پروژه‌ها، مقالات، تنظیمات، قیمت‌گذاری، مدیران، آپلودها) حفظ می‌شوند. این بازآرایی schema را از نو نمی‌سازد.

خروجی SQL برای phpMyAdmin:

```bash
php artisan deploy:package --sql-only
```

## پنل مدیریت

نشانی: `/admin`

ماژول‌ها:

- ورود / خروج / نشست
- داشبورد
- محصولات، دسته‌ها، پروژه‌ها، مقالات
- صفحات، رسانه، تنظیمات، منو، همکاران
- SEO
- پیام‌های تماس
- استودیوی قیمت‌گذاری
- کاربران (فقط super admin)
- گزارش فعالیت و بررسی سیستم

آپلودهای پنل به‌صورت WebP بهینه در `storage/app/public/{collection}/...` ذخیره و با نشانی `/storage/...` سرو می‌شوند. این پوشه را هنگام استقرار بازنویسی نکنید.

## سیستم آپلود تصویر

هر تصویر که از پنل مدیریت بارگذاری شود از `src/Services/ImageService.php` می‌گذرد. کنترلرها تبدیل را خودشان انجام نمی‌دهند.

### جریان

1. اعتبارسنجی MIME واقعی، محتوا، حجم و ابعاد
2. تغییر اندازهٔ متناسب اگر عرض یا ارتفاع از حد مجاز بیشتر باشد (بدون بزرگ‌نمایی)
3. تبدیل به WebP و فشرده‌سازی
4. ذخیرهٔ فقط فایل WebP
5. نوشتن مسیر WebP در پایگاه‌داده
6. ساخت بندانگشتی (به‌جز تنظیمات)
7. حذف تصویر قبلی فقط بعد از موفقیت آپلود جدید

فرمت ورودی: JPG، JPEG، PNG، GIF، BMP، WebP (و در صورت پشتیبانی سرور HEIC/TIFF). خروجی همیشه `.webp` است.

### محل ذخیره

| مجموعه | مسیر |
|---|---|
| محصولات | `storage/app/public/products/` |
| پروژه‌ها | `storage/app/public/projects/` |
| مقالات | `storage/app/public/articles/` |
| تنظیمات | `storage/app/public/settings/` |
| همکاران | `storage/app/public/partners/` |
| کتابخانه رسانه | `storage/app/public/media/` |

بندانگشتی‌ها در `.../thumbs/` کنار همان نام فایل هستند. جداول پنل از بندانگشتی استفاده می‌کنند؛ سایت عمومی تصویر کامل را نشان می‌دهد.

ویدیوها همچنان با فرمت اصلی در `media/سال/ماه/` ذخیره می‌شوند.

### تنظیم کیفیت و ابعاد

مقادیر در `config/image.php` هستند (قابل‌override با `.env`):

| کلید | پیش‌فرض | نقش |
|---|---|---|
| `IMAGE_QUALITY` | `80` | کیفیت WebP اصلی |
| `IMAGE_THUMBNAIL_QUALITY` | `75` | کیفیت بندانگشتی |
| `IMAGE_MAX_WIDTH` / `IMAGE_MAX_HEIGHT` | `1920` | سقف ابعاد (نسبت حفظ می‌شود) |
| `IMAGE_THUMBNAIL_WIDTH` | `400` | حداکثر عرض بندانگشتی |
| `IMAGE_MAX_KILOBYTES` | `51200` | سقف حجم آپلود (کیلوبایت) |

### مهاجرت تصاویر قدیمی

دستور زیر را فقط دستی اجرا کنید؛ هنگام استقرار خودکار نیست.

```bash
php artisan images:convert-webp
```

آپلودهای قبلی روی دیسک `public` به WebP تبدیل و مسیر پایگاه‌داده به‌روز می‌شود. فایل‌های کاتالوگ داخل `images/` و `assets/` دست نخورده می‌مانند.

## دارایی‌ها

قالب عمومی از `assets/` خوانده می‌شود (`/assets/css/...`, `/assets/js/...`).
تصاویر کاتالوگ از `images/` خوانده می‌شوند.
CSS/JS پنل در `assets/admin/` است.

این بازآرایی طراحی سایت را عوض نمی‌کند.

## ذخیره‌سازی و آپلود

| مسیر دیسک | نشانی عمومی |
|---|---|
| `storage/app/public/...` | `/storage/...` |
| `storage/uploads` | میانبر همان پوشه |
| `storage/logs` | خصوصی |
| `storage/framework` | خصوصی |

`.htaccess` پوشه‌های حساس را می‌بندد و `/storage/` را به `storage/app/public` نگاشت می‌کند.

## استقرار cPanel

1. `composer install --no-dev --optimize-autoloader` را روی دستگاه خود اجرا کنید (یا روی سرور اگر Composer دارید).
2. کل پروژه را داخل `public_html` (یا ریشهٔ دامنه) استخراج کنید. ریشهٔ سند باید همین پوشه باشد، نه یک زیرپوشهٔ `public/`.
3. PHP را روی 8.2+ بگذارید.
4. `.env` را از `.env.production.example` بسازید و `APP_URL`، `APP_KEY` و `DB_*` را تنظیم کنید.
5. جداول را با `php artisan migrate --force` بسازید یا SQL را در phpMyAdmin وارد کنید.
6. دسترسی پوشه‌ها: `storage/` و `bootstrap/cache` باید قابل نوشتن باشند (معمولاً 755/775).
7. در cPanel یک Cron بگذارید:

```
php /home/USER/public_html/scripts/cron.php
```

یا HTTP:

```
https://alwinco.ir/cron/YOUR_CRON_SECRET
```

8. `mod_rewrite` باید روشن باشد. `.htaccess` ریشه مسیرهای تمیز، HTTPS در لایهٔ برنامهٔ production، و حفاظت از `.env` / `vendor` / `src` را انجام می‌دهد.

بستهٔ آماده:

```bash
php artisan deploy:package
```

فایل زیپ و SQL در `scripts/` نوشته می‌شود.

## تست

```bash
php artisan test
```
