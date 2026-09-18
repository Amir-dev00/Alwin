<form method="post" action="{{ route('admin.pricing.hardwares.update') }}" class="easy-form fly-item" style="--i:2" data-unsaved>
  @csrf @method('put')
  <section class="card">
    <header>
      <div>
        <h2>یراق‌آلات</h2>
        <p>قیمت واحد. برای پنجره‌های لولایی، مشتری بین تک‌حالته و دوحالته انتخاب می‌کند؛ بقیه مدل‌ها نوع ثابت دارند.</p>
      </div>
      <button class="btn btn-primary" type="submit">ذخیره یراق</button>
    </header>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>نوع</th>
            <th>ترک</th>
            <th>آلمانی</th>
            <th>فعال</th>
          </tr>
        </thead>
        <tbody>
          @foreach($hardwares as $h)
            <tr>
              <td><input name="hardwares[{{ $h->id }}][name]" value="{{ $h->name }}" required></td>
              <td><input name="hardwares[{{ $h->id }}][price_turk]" type="number" min="0" step="1" dir="ltr" value="{{ $h->price_turk }}" required></td>
              <td><input name="hardwares[{{ $h->id }}][price_germany]" type="number" min="0" step="1" dir="ltr" value="{{ $h->price_germany }}" required></td>
              <td>
                <label class="inline chip-check">
                  <input type="checkbox" name="hardwares[{{ $h->id }}][is_active]" value="1" @checked($h->is_active)> روی سایت
                </label>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
  <div class="save-dock" data-save-dock>
    <span data-save-hint>یراق آکاردئونی معمولاً به‌ازای کل درب است، نه هر لنگه.</span>
    <button class="btn btn-primary" type="submit">ذخیره یراق</button>
  </div>
</form>

<form method="post" action="{{ route('admin.pricing.hardwares.store') }}" class="card fly-item" style="--i:3">
  @csrf
  <header><h2>نوع یراق جدید</h2></header>
  <div class="form-grid">
    <label class="field wide"><span>نام</span><input name="name" required></label>
    <label class="field"><span>ترک</span><input name="price_turk" type="number" min="0" required dir="ltr"></label>
    <label class="field"><span>آلمانی</span><input name="price_germany" type="number" min="0" required dir="ltr"></label>
  </div>
  <div class="page-head-actions" style="margin-top:14px">
    <button class="btn btn-ghost" type="submit">افزودن یراق</button>
  </div>
</form>
