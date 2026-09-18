<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>ورود | پنل آلوین</title>
  <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}?v=3.1">
</head>
<body class="login-body">
  <main class="login-shell fly-stage">
    <section class="login-visual" aria-hidden="true">
      <div>
        <div class="mark">A</div>
        <p class="login-kicker" style="color:#7ec8ef;margin-top:28px">ALWIN CMS</p>
        <h2>مدیریت محتوای زنده وب‌سایت آلوین</h2>
        <p>محصولات، پروژه‌ها و صفحات را ویرایش کنید؛ تغییرات بلافاصله روی سایت دیده می‌شوند.</p>
        <ul>
          <li>محصولات و تصاویر باز/بسته</li>
          <li>پروژه‌ها و گالری</li>
          <li>منو، فوتر و رسانه</li>
        </ul>
      </div>
      <p class="login-foot">ALWIN CMS · امن و اختصاصی</p>
    </section>
    <section class="login-card">
      <p class="login-kicker">خوش آمدید</p>
      <h1>ورود به پنل مدیریت</h1>
      <p class="login-lead">فقط مدیران مجاز. ثبت‌نام عمومی وجود ندارد.</p>
      @if ($errors->any())
        <div class="toast toast-error" role="alert">{{ $errors->first() }}</div>
      @endif
      <form method="post" action="{{ route('admin.login.store') }}" class="stack">
        @csrf
        <label>
          <span>ایمیل</span>
          <input type="email" name="email" value="{{ old('email', 'admin@alwinco.ir') }}" required autocomplete="username" autofocus>
        </label>
        <label>
          <span>رمز عبور</span>
          <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </label>
        <label class="inline">
          <input type="checkbox" name="remember" value="1">
          <span>مرا به خاطر بسپار</span>
        </label>
        <button type="submit" class="btn btn-primary">ورود به داشبورد</button>
      </form>
      @if(config('app.env') === 'local')
        <div class="login-hint">
          آدرس پنل: <code>{{ url('/admin/login') }}</code><br>
          پیش‌فرض محلی: <code>{{ env('ADMIN_EMAIL', 'admin@alwinco.ir') }}</code> / <code>{{ env('ADMIN_PASSWORD', 'ChangeMe!Alwin2026') }}</code>
        </div>
      @endif
    </section>
  </main>
</body>
</html>
