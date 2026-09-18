<form method="post" action="{{ route('admin.pricing.glasses.update') }}" class="easy-form fly-item" style="--i:2" data-unsaved>
  @csrf @method('put')
  <section class="card">
    <header>
      <div>
        <h2>شیشه دوجداره</h2>
        <p>قیمت هر متر مربع. متراژ شیشه هر مدل از روی «عرض × ارتفاع − کسر ثابت» حساب می‌شود.</p>
      </div>
      <button class="btn btn-primary" type="submit">ذخیره شیشه</button>
    </header>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>نام</th>
            <th>تومان / متر مربع</th>
            <th>فعال</th>
          </tr>
        </thead>
        <tbody>
          @foreach($glasses as $g)
            <tr>
              <td><input name="glasses[{{ $g->id }}][name]" value="{{ $g->name }}" required></td>
              <td><input name="glasses[{{ $g->id }}][price_per_sqm]" type="number" min="0" step="1" dir="ltr" value="{{ $g->price_per_sqm }}" required></td>
              <td>
                <label class="inline chip-check">
                  <input type="checkbox" name="glasses[{{ $g->id }}][is_active]" value="1" @checked($g->is_active)> روی سایت
                </label>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
  <div class="save-dock" data-save-dock>
    <span data-save-hint>نرخ شیشه فقط مدل‌هایی را که شیشه دارند تغییر می‌دهد.</span>
    <button class="btn btn-primary" type="submit">ذخیره شیشه</button>
  </div>
</form>

<form method="post" action="{{ route('admin.pricing.glasses.store') }}" class="card fly-item" style="--i:3">
  @csrf
  <header><h2>نوع شیشه جدید</h2></header>
  <div class="form-grid">
    <label class="field"><span>نام</span><input name="name" required placeholder="مثلاً رفلکس سبز"></label>
    <label class="field"><span>تومان / متر مربع</span><input name="price_per_sqm" type="number" min="0" required dir="ltr"></label>
  </div>
  <div class="page-head-actions" style="margin-top:14px">
    <button class="btn btn-ghost" type="submit">افزودن شیشه</button>
  </div>
</form>
