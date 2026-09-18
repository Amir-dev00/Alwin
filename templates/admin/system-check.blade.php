@extends('admin.layout')
@section('title', 'سلامت سیستم')
@section('content')
  <section class="page-head fly-item">
    <div>
      <p class="dash-kicker">سیستم</p>
      <h1>سلامت استقرار</h1>
      <p>{{ $build['version'] ? 'ALWIN v'.$build['version'] : 'ALWIN' }}
        @if(!empty($build['build_date'])) · {{ $build['build_date'] }} @endif
        @if(!empty($build['git_commit'])) · <code>{{ $build['git_commit'] }}</code> @endif
      </p>
    </div>
  </section>

  <div class="card fly-item">
    <header>
      <div>
        <h2>وضعیت وابستگی‌ها</h2>
        <p>بدون نمایش رمز یا اطلاعات حساس</p>
      </div>
    </header>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>مورد</th><th>وضعیت</th><th>جزئیات</th></tr>
        </thead>
        <tbody>
          @foreach($checks as $check)
            <tr>
              <td>{{ $check['label'] }}</td>
              <td>
                @if($check['ok'])
                  <span class="live-pill"><span class="live-dot"></span> OK</span>
                @else
                  <strong>Error</strong>
                @endif
              </td>
              <td dir="ltr">{{ $check['detail'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="card fly-item">
    <header>
      <div>
        <h2>به‌روزرسانی پایگاه‌داده</h2>
        <p>فقط مهاجرت‌های باقی‌مانده و افزودنی. جدول‌ها حذف نمی‌شوند و دادهٔ فعلی پاک نمی‌شود. قبل از مهاجرت پرخطر از پایگاه‌داده نسخه پشتیبان بگیرید.</p>
      </div>
    </header>
    <form method="post" action="{{ route('admin.system.migrate') }}">
      @csrf
      <button class="btn btn-primary" type="submit" onclick="return confirm('فقط مهاجرت‌های معلق اجرا شوند؟ دادهٔ فعلی پاک نمی‌شود.')">اجرای مهاجرت‌های معلق</button>
    </form>
  </div>

  <div class="card fly-item">
    <header>
      <div>
        <h2>یک کرون</h2>
        <p>در cPanel فقط همین یک دستور را هر دقیقه بگذارید</p>
      </div>
    </header>
    <p dir="ltr"><code>* * * * * {{ $cronHint }}</code></p>
    @if(config('alwin.cron_secret'))
      <p>جایگزین HTTP (اگر SSH در دسترس نبود): <code dir="ltr">{{ url('/cron/'.config('alwin.cron_secret')) }}</code></p>
    @endif
  </div>

  <div class="card fly-item">
    <header>
      <div>
        <h2>خطاهای اخیر</h2>
        <p>۴۰ خط آخر laravel.log — بدون stack trace عمومی</p>
      </div>
    </header>
    @if($logs === [])
      <p>لاگی برای نمایش نیست.</p>
    @else
      <pre class="log-tail" dir="ltr">{{ implode("\n", $logs) }}</pre>
    @endif
  </div>
@endsection
