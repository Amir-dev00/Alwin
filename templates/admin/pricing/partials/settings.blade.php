<form method="post" action="{{ route('admin.pricing.settings.update') }}" class="easy-form fly-item" style="--i:2" data-unsaved>
  @csrf @method('put')
  <section class="card">
    <header>
      <div>
        <h2>تنظیمات موتور</h2>
        <p>این اعداد روی همه مدل‌ها اعمال می‌شوند.</p>
      </div>
      <button class="btn btn-primary" type="submit">ذخیره تنظیمات</button>
    </header>
    <div class="form-grid">
      <label class="field">
        <span>درصد پرت برش پروفیل</span>
        <input name="waste_percent" type="number" min="0" max="40" step="0.5" dir="ltr" value="{{ $settings['waste_percent']->value ?? 0 }}">
        <small class="field-help">مثلاً ۵ یعنی پنج درصد به متر طول پروفیل اضافه می‌شود.</small>
      </label>
      <label class="field">
        <span>گرد کردن قیمت نهایی</span>
        <input name="round_to" type="number" min="1" step="1" dir="ltr" value="{{ $settings['round_to']->value ?? 1000 }}">
        <small class="field-help">معمولاً ۱۰۰۰ تومان.</small>
      </label>
      <label class="field">
        <span>برند جایگزین نرخ خالی</span>
        <select name="fallback_brand">
          @foreach($brands as $b)
            <option value="{{ $b->key }}" @selected(($settings['fallback_brand']->value ?? 'wintech') === $b->key)>{{ $b->name }}</option>
          @endforeach
        </select>
        <small class="field-help">اگر خانه‌ای در جدول پروفیل خالی باشد از این برند خوانده می‌شود.</small>
      </label>
    </div>
  </section>
</form>
