@php
  $groups = $models->groupBy(fn ($m) => $m->tab.'|'.$m->category_key);
  $groupLabels = [
    'windows|fixed' => 'پنجره ثابت',
    'windows|casement' => 'پنجره لولایی',
    'windows|sliding' => 'پنجره کشویی',
    'windows|french' => 'پنجره فرانسوی',
    'doors|switch' => 'درب سوییچی',
    'doors|accordion' => 'درب آکاردئونی',
    'doors|special' => 'سایر درب‌ها',
    'screens|screens' => 'توری',
  ];
@endphp

<form method="post" action="{{ route('admin.pricing.models.update') }}" class="easy-form fly-item" style="--i:2" data-unsaved>
  @csrf @method('put')
  <div class="page-head" style="margin-bottom:8px">
    <div>
      <p class="page-kicker">{{ $models->count() }} مدل</p>
      <h2 style="margin:0;font-size:20px">ساختار و مصرف</h2>
      <p>کسر شیشه همان (عرض × ارتفاع) منهای این عدد است. مصرف پروفیل به‌صورت ضریب عرض و ارتفاع به متر است.</p>
    </div>
    <button class="btn btn-primary" type="submit">ذخیره مدل‌ها</button>
  </div>

  @foreach($groups as $key => $rows)
    <section class="card price-model-group">
      <header>
        <h2>{{ $groupLabels[$key] ?? $key }}</h2>
        <p>{{ $rows->count() }} مدل</p>
      </header>
      <div class="price-model-list">
        @foreach($rows as $m)
          <article class="price-model">
            <div class="price-model__head">
              <img src="/{{ ltrim($m->image ?: 'assets/img/pricing/'.$m->catalog_id.'.webp', '/') }}" alt="" width="64" height="48">
              <div>
                <strong>{{ $m->catalog_id }}. {{ $m->name }}</strong>
                <small>{{ $families[$m->family] ?? $m->family }} · {{ $m->lites }} لت · {{ $m->sashes }} بازشو</small>
              </div>
              <label class="inline chip-check">
                <input type="hidden" name="models[{{ $m->id }}][is_active]" value="0">
                <input type="checkbox" name="models[{{ $m->id }}][is_active]" value="1" @checked($m->is_active)> فعال
              </label>
            </div>
            <div class="price-model__grid">
              <label class="field">
                <span>کسر شیشه (متر مربع)</span>
                <input name="models[{{ $m->id }}][glass_deduction]" type="number" step="0.1" dir="ltr" value="{{ $m->glass_deduction }}" placeholder="بدون شیشه">
              </label>
              <label class="field">
                <span>تعداد یراق</span>
                <input name="models[{{ $m->id }}][hardware_qty]" type="number" min="0" max="8" value="{{ $m->hardware_qty }}">
              </label>
              <label class="field">
                <span>حالت یراق</span>
                <select name="models[{{ $m->id }}][hardware_mode]">
                  <option value="none" @selected($m->hardware_mode === 'none')>بدون یراق</option>
                  <option value="casement" @selected($m->hardware_mode === 'casement')>انتخاب تک‌حالته / دوحالته</option>
                  <option value="fixed" @selected($m->hardware_mode === 'fixed')>نوع ثابت</option>
                </select>
              </label>
              <label class="field">
                <span>نوع یراق ثابت</span>
                <select name="models[{{ $m->id }}][hardware_type_key]">
                  @foreach($hardwares as $h)
                    <option value="{{ $h->key }}" @selected($m->hardware_type_key === $h->key)>{{ $h->name }}</option>
                  @endforeach
                </select>
              </label>
              <label class="field">
                <span>خانواده ساخت</span>
                <select name="models[{{ $m->id }}][family]">
                  @foreach($families as $fk => $fl)
                    <option value="{{ $fk }}" @selected($m->family === $fk)>{{ $fl }}</option>
                  @endforeach
                </select>
              </label>
              <label class="field">
                <span>تعداد لت</span>
                <input name="models[{{ $m->id }}][lites]" type="number" min="1" max="8" value="{{ $m->lites }}">
              </label>
              <label class="field">
                <span>تعداد بازشو</span>
                <input name="models[{{ $m->id }}][sashes]" type="number" min="0" max="8" value="{{ $m->sashes }}">
              </label>
              <label class="field">
                <span>نسبت پنل</span>
                <input name="models[{{ $m->id }}][panel_ratio]" type="number" min="0" max="1" step="0.05" dir="ltr" value="{{ $m->panel_ratio }}">
              </label>
              <label class="field">
                <span>نرخ توری (تومان / متر مربع)</span>
                <input name="models[{{ $m->id }}][area_rate]" type="number" min="0" step="1000" dir="ltr" value="{{ $m->area_rate }}" placeholder="فقط توری">
              </label>
              <label class="switch-card" style="margin:0">
                <input type="checkbox" name="models[{{ $m->id }}][transom_top]" value="1" @checked($m->transom_top)>
                <div><strong>کتیبه بالا</strong><small>وادار افقی بالایی</small></div>
              </label>
              <label class="switch-card" style="margin:0">
                <input type="checkbox" name="models[{{ $m->id }}][transom_bottom]" value="1" @checked($m->transom_bottom)>
                <div><strong>کتیبه پایین</strong><small>وادار افقی پایینی</small></div>
              </label>
            </div>
            <details class="price-recipe">
              <summary>مصرف پروفیل {{ $m->recipe_locked ? '· ویرایش‌شده' : '· خودکار' }}</summary>
              <div class="price-recipe__grid">
                @foreach($components as $c)
                  @php $r = $m->recipe[$c->key] ?? ['w'=>0,'h'=>0,'c'=>0,'area'=>0]; @endphp
                  <div class="price-recipe__row">
                    <span>{{ $c->name }}</span>
                    <label>عرض<input name="models[{{ $m->id }}][recipe][{{ $c->key }}][w]" type="number" step="0.01" dir="ltr" value="{{ $r['w'] ?? 0 }}"></label>
                    <label>ارتفاع<input name="models[{{ $m->id }}][recipe][{{ $c->key }}][h]" type="number" step="0.01" dir="ltr" value="{{ $r['h'] ?? 0 }}"></label>
                    <label>ثابت<input name="models[{{ $m->id }}][recipe][{{ $c->key }}][c]" type="number" step="0.01" dir="ltr" value="{{ $r['c'] ?? 0 }}"></label>
                    @if($c->unit === 'sqm')
                      <label>مساحت<input name="models[{{ $m->id }}][recipe][{{ $c->key }}][area]" type="number" step="0.01" dir="ltr" value="{{ $r['area'] ?? 0 }}"></label>
                    @else
                      <input type="hidden" name="models[{{ $m->id }}][recipe][{{ $c->key }}][area]" value="{{ $r['area'] ?? 0 }}">
                    @endif
                  </div>
                @endforeach
              </div>
              <p class="field-help">متر طول = (ضریب عرض × عرض متر) + (ضریب ارتفاع × ارتفاع متر) + مقدار ثابت. ذخیره این جدول، مصرف را قفل می‌کند.</p>
            </details>
          </article>
        @endforeach
      </div>
    </section>
  @endforeach

  <div class="save-dock" data-save-dock>
    <span data-save-hint>برای برگرداندن مصرف یک مدل به حالت خودکار، از دکمه بازنشانی پایین هر گروه استفاده کنید.</span>
    <button class="btn btn-primary" type="submit">ذخیره مدل‌ها</button>
  </div>
</form>

<section class="card fly-item" style="--i:3">
  <header>
    <div>
      <h2>بازنشانی مصرف پروفیل</h2>
      <p>مصرف را از روی لت / بازشو / کتیبه دوباره می‌سازد و قفل ویرایش را برمی‌دارد.</p>
    </div>
  </header>
  <div class="price-rebuild">
    @foreach($models as $m)
      <form method="post" action="{{ route('admin.pricing.models.rebuild', $m) }}">
        @csrf
        <button class="btn btn-quiet btn-sm" type="submit">{{ $m->catalog_id }}. بازنشانی</button>
      </form>
    @endforeach
  </div>
</section>
