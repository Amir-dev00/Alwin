<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>آلوین — گذاشتن سایت روی cPanel</title>
  <style>
    html, body {
      direction: rtl;
      text-align: right;
      font-family: Tahoma, "Vazirmatn", sans-serif;
      line-height: 1.85;
      max-width: 760px;
      margin: 0 auto;
      padding: 24px 20px 64px;
      color: #1a1a1a;
    }
    h1, h2 { line-height: 1.45; }
    h1 { font-size: 1.55rem; }
    h2 { font-size: 1.2rem; margin-top: 2rem; }
    table { border-collapse: collapse; width: 100%; margin: 1rem 0; }
    th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: right; vertical-align: top; }
    th { background: #f3f3f3; }
    pre, code {
      direction: ltr;
      text-align: left;
      unicode-bidi: isolate;
      font-family: Consolas, "Courier New", monospace;
    }
    pre {
      background: #f6f8fa;
      padding: 12px 14px;
      overflow-x: auto;
      border-radius: 6px;
    }
    code { background: #f6f8fa; padding: 0 4px; border-radius: 4px; }
    pre code { background: none; padding: 0; }
    hr { border: 0; border-top: 1px solid #ddd; margin: 2rem 0; }
    ul, ol { padding-right: 1.4rem; padding-left: 0; }
    a { color: #004385; }
  </style>
</head>
<body>

<h1>آلوین — گذاشتن سایت روی cPanel</h1>

<p>نسخه انگلیسی: <code>Cpanel_deployment_guide.md</code></p>

<p>روی سرور به Terminal، SSH، Composer یا <code>php artisan</code> <strong>نیازی نیست</strong>.</p>

<p>اول روی <strong>کامپیوتر ویندوز</strong> کار را انجام دهید. بعد در cPanel فقط از File Manager، MySQL، phpMyAdmin، MultiPHP و Cron Jobs استفاده کنید.</p>

<p>ریشه سایت <code>public_html</code> است. بعد از آپلود باید <code>index.php</code> همان‌جا باشد.<br>
دامنه را به پوشه <code>public</code> وصل نکنید.</p>

<hr>

<h2>چه چیزهایی لازم است</h2>

<ul>
  <li>PHP <strong>8.2</strong> یا <strong>8.3</strong></li>
  <li>یک دیتابیس MySQL و یک کاربر</li>
  <li>File Manager (یا FTP) و phpMyAdmin</li>
</ul>

<hr>

<h2>۱. روی کامپیوتر خود</h2>

<ol>
  <li>مطمئن شوید پوشه <code>vendor</code> وجود دارد (از <code>composer install</code>).</li>
  <li>فایل <code>.env.production.example</code> را کپی کنید و نام کپی را <code>.env</code> بگذارید.<br>
  فایل <code>.env</code> محلی SQLite را استفاده نکنید.</li>
  <li>روی کامپیوتر کلید برنامه را بسازید:

<pre><code>php artisan key:generate --show
</code></pre>

  مقدار <code>base64:...</code> را در <code>.env</code> به‌صورت <code>APP_KEY=</code> بگذارید.</li>
  <li>اگر هنوز فایل SQL ندارید:

<pre><code>php artisan deploy:package --sql-only --dir=.
</code></pre>

  فایل <code>alwin-production.sql</code> ساخته می‌شود.</li>
  <li>در File Explorer نمایش <strong>فایل‌های مخفی</strong> را روشن کنید تا <code>.htaccess</code> و <code>.env</code> دیده شوند.</li>
  <li>این‌ها را داخل <code>alwin-cpanel.zip</code> زیپ کنید:
    <ul>
      <li><code>.env</code></li>
      <li><code>.htaccess</code></li>
      <li><code>index.php</code></li>
      <li><code>vendor</code></li>
      <li><code>src</code></li>
      <li><code>templates</code></li>
      <li><code>includes</code></li>
      <li><code>api</code></li>
      <li><code>config</code></li>
      <li><code>bootstrap</code></li>
      <li><code>assets</code></li>
      <li><code>images</code></li>
      <li><code>storage</code></li>
      <li><code>robots.txt</code>، <code>sitemap.xml</code>، <code>sitemap-articles.xml</code>، <code>favicon.ico</code> (اگر دارید)</li>
    </ul>
  </li>
</ol>

<p>این‌ها را زیپ نکنید: <code>.git</code>، <code>tests</code>، <code>node_modules</code>، فایل‌های SQLite محلی، لاگ‌ها، یا <code>.env</code> محلی.</p>

<p>اگر <code>vendor</code> برای File Manager خیلی بزرگ بود، با FileZilla (FTP) آپلود کنید.</p>

<hr>

<h2>۲. آپلود</h2>

<ol>
  <li><strong>File Manager</strong> را باز کنید.</li>
  <li><strong>Settings</strong> → گزینه <strong>Show Hidden Files</strong> را روشن کنید → Save.</li>
  <li>پوشه <code>public_html</code> را باز کنید.</li>
  <li>اگر <code>index.html</code> هست حذف کنید.</li>
  <li>زیپ را Upload کنید → راست‌کلیک → <strong>Extract</strong>.</li>
  <li>اگر مسیر <code>public_html/Alwin/index.php</code> شد، همه چیز را <strong>یک پوشه بالاتر</strong> ببرید.</li>
  <li>در <code>public_html</code> باید این‌ها را ببینید: <code>index.php</code>، <code>.htaccess</code>، <code>.env</code>، <code>vendor</code>، <code>src</code>، <code>templates</code>، <code>assets</code>، <code>images</code>، <code>storage</code>.</li>
</ol>

<p>اگر این پوشه‌ها نبودند بسازید:</p>

<pre><code>storage/app/public
storage/app/private
storage/framework/cache/data
storage/framework/sessions
storage/framework/views
storage/logs
bootstrap/cache
</code></pre>

<p>پوشه‌ها را <code>755</code> و فایل‌ها را <code>644</code> کنید.<br>
<code>storage</code> و <code>bootstrap/cache</code> را <code>755</code> یا <code>775</code> کنید (PHP باید بتواند آنجا بنویسد).</p>

<hr>

<h2>۳. PHP</h2>

<ol>
  <li>cPanel → <strong>MultiPHP Manager</strong>.</li>
  <li>دامنه را انتخاب کنید → PHP <strong>8.2</strong> یا <strong>8.3</strong> → Apply.</li>
  <li>اختیاری — <strong>MultiPHP INI Editor</strong>:
    <ul>
      <li><code>upload_max_filesize</code> = <code>64M</code></li>
      <li><code>post_max_size</code> = <code>64M</code></li>
      <li><code>memory_limit</code> = <code>256M</code></li>
    </ul>
  </li>
</ol>

<hr>

<h2>۴. دیتابیس</h2>

<ol>
  <li>cPanel → <strong>MySQL Databases</strong>.</li>
  <li>یک دیتابیس و یک کاربر بسازید. به کاربر <strong>ALL PRIVILEGES</strong> بدهید.</li>
  <li>نام <strong>کامل</strong> را کپی کنید (مثل <code>cpaneluser_alwin</code>).</li>
  <li><strong>phpMyAdmin</strong> را باز کنید → همان دیتابیس را انتخاب کنید → <strong>Import</strong> → فایل <code>alwin-production.sql</code>.</li>
</ol>

<p>این فایل SQL را <strong>فقط داخل دیتابیس خالی</strong> وارد کنید (نصب اول).<br>
<strong>هرگز</strong> آن را روی سایت زنده‌ای که محصول، ادمین یا محتوای CMS دارد وارد نکنید. این دامپ جدول‌ها را DROP می‌کند.</p>

<p><code>DB_HOST</code> معمولاً <code>127.0.0.1</code> است.</p>

<hr>

<h2>۵. ویرایش <code>.env</code> روی سرور</h2>

<p>File Manager → نمایش فایل‌های مخفی → <code>.env</code> → Edit.</p>

<p>حداقل این‌ها را عوض کنید:</p>

<pre><code>APP_ENV=production
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
</code></pre>

<ul>
  <li>آدرس واقعی <code>https://</code> را بگذارید. آخرش اسلش نگذارید.</li>
  <li>از MySQL استفاده کنید، نه SQLite.</li>
  <li>ذخیره کنید.</li>
</ul>

<hr>

<h2>۶. Cron</h2>

<p>cPanel → <strong>Cron Jobs</strong>:</p>

<pre><code>*/5 * * * * curl -fsS "https://YOUR-DOMAIN.com/cron/YOUR_CRON_SECRET" >/dev/null 2>&1
</code></pre>

<p>همان مقدار <code>CRON_SECRET</code> داخل <code>.env</code> را بگذارید.<br>
اگر <code>curl</code> نبود:</p>

<pre><code>*/5 * * * * wget -q -O /dev/null "https://YOUR-DOMAIN.com/cron/YOUR_CRON_SECRET"
</code></pre>

<hr>

<h2>۷. بررسی</h2>

<table>
  <thead>
    <tr><th>این آدرس را باز کنید</th><th>باید ببینید</th></tr>
  </thead>
  <tbody>
    <tr><td><code>https://YOUR-DOMAIN.com/</code></td><td>صفحه اصلی</td></tr>
    <tr><td><code>https://YOUR-DOMAIN.com/admin/login</code></td><td>ورود ادمین</td></tr>
    <tr><td><code>https://YOUR-DOMAIN.com/.env</code></td><td><strong>403 / Forbidden</strong>، نه خود فایل</td></tr>
  </tbody>
</table>

<p>با کاربر ادمینی که داخل فایل SQL است وارد شوید.</p>

<p><strong>اگر کار نکرد</strong></p>
<ul>
  <li>صفحه پیش‌فرض cPanel → <code>index.html</code> را حذف کنید.</li>
  <li>CSS یا عکس نیست → <code>assets</code> و <code>images</code> باید کنار <code>index.php</code> باشند.</li>
  <li>خطای ۵۰۰ → یک بار <code>APP_DEBUG=true</code> کنید، خطا را بخوانید، بعد دوباره <code>false</code> کنید. PHP 8.2+، نام‌های MySQL در <code>.env</code>، قابل‌نوشتن بودن <code>storage</code> و آپلود شدن <code>.htaccess</code> را هم چک کنید.</li>
</ul>

<hr>

<h2>به‌روزرسانی بعدی (سایت موجود)</h2>

<p>دیتابیس زنده منبع حقیقت است. آن را عوض نکنید.</p>

<ol>
  <li>اول از دیتابیس زنده پشتیبان بگیرید (Export در phpMyAdmin).</li>
  <li>کد جدید را آپلود کنید: <code>src</code>، <code>templates</code>، <code>includes</code>، <code>api</code>، <code>config</code>، <code>assets</code>.<br>
  <code>vendor</code> را فقط اگر پکیج‌ها روی کامپیوتر عوض شده باشند عوض کنید.</li>
  <li>همان دیتابیس MySQL را نگه دارید. <code>alwin-production.sql</code> را دوباره وارد نکنید.</li>
  <li>فقط مهاجرت‌های معلق را اجرا کنید (ادمین → سلامت سیستم، یا <code>php artisan migrate --force</code>). هرگز <code>migrate:fresh</code>، <code>db:wipe</code> یا سیدری که محتوا را ریست کند اجرا نکنید.</li>
  <li>سایت و پنل ادمین را تست کنید. محصولات، پروژه‌ها، مقالات، تصاویر و ورود ادمین باید سر جایشان باشند.</li>
</ol>

<p><strong>هرگز عوض نکنید:</strong> <code>.env</code>، <code>storage/app/public</code>، عکس‌های آپلودشده، یا دیتابیس زنده.</p>

<p>بعد فایل‌های داخل <code>storage/framework/views</code> را پاک کنید (خود پوشه را نگه دارید).</p>

<hr>

<h2>چک‌لیست</h2>

<ol>
  <li>زیپ شامل <code>vendor</code>، <code>.htaccess</code> و <code>.env</code> تولیدی با <code>APP_KEY</code> باشد.</li>
  <li>Extract طوری باشد که <code>index.php</code> در <code>public_html</code> باشد.</li>
  <li>فایل‌های مخفی روشن باشد. <code>.htaccess</code> و <code>.env</code> دیده شوند.</li>
  <li>PHP 8.2 یا 8.3.</li>
  <li>نصب اول: <code>alwin-production.sql</code> فقط داخل دیتابیس <strong>خالی</strong>. سایت موجود: هرگز دوباره وارد نکنید.</li>
  <li>در <code>.env</code> دامنه واقعی و نام‌های MySQL درست باشد.</li>
  <li><code>storage</code> قابل نوشتن باشد.</li>
  <li>آدرس Cron همان <code>CRON_SECRET</code> را داشته باشد.</li>
  <li>صفحه اصلی، ورود ادمین، و مسدود بودن <code>/.env</code> کار کند.</li>
  <li>دفعه بعد <code>.env</code> و عکس‌های آپلودشده را بازنویسی نکنید.</li>
</ol>

</body>
</html>
