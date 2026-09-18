@php
  $materialLabels = ['aluminum' => 'آلومینیوم', 'upvc' => 'یو پی وی سی (UPVC)'];
  $brandGroups = $brands->groupBy(fn ($b) => $b->material ?: 'upvc');
  $orderedBrands = collect();
  foreach (['aluminum', 'upvc'] as $mat) {
      $orderedBrands = $orderedBrands->concat($brandGroups->get($mat, collect()));
  }
  foreach ($brandGroups as $mat => $rows) {
      if (! isset($materialLabels[$mat])) {
          $orderedBrands = $orderedBrands->concat($rows);
      }
  }
@endphp

<form method="post" action="{{ route('admin.pricing.brands.update') }}" class="easy-form fly-item" style="--i:2" data-unsaved>
  @csrf @method('put')
  <section class="card">
    <header>
      <div>
        <h2>نرخ پروفیل به‌ازای متر طول</h2>
        <p>خانه خالی یعنی این برند آن قطعه را ندارد و از برند جایگزین (معمولاً وینتک) استفاده می‌شود. پنل بر حسب متر مربع است.</p>
      </div>
      <button class="btn btn-primary" type="submit">ذخیره نرخ پروفیل</button>
    </header>
    <div class="table-wrap price-matrix-wrap">
      <table class="data-table price-matrix">
        <thead>
          <tr>
            <th rowspan="2">قطعه</th>
            @foreach(['aluminum', 'upvc'] as $mat)
              @if($brandGroups->has($mat))
                <th colspan="{{ $brandGroups[$mat]->count() }}">{{ $materialLabels[$mat] }}</th>
              @endif
            @endforeach
          </tr>
          <tr>
            @foreach($orderedBrands as $b)
              <th>{{ $b->name }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($components as $c)
            <tr>
              <td>
                <strong>{{ $c->name }}</strong>
                <small>{{ $c->unit === 'sqm' ? 'تومان / متر مربع' : 'تومان / متر طول' }}</small>
              </td>
              @foreach($orderedBrands as $b)
                <td>
                  <input name="prices[{{ $b->id }}][{{ $c->key }}]" type="number" min="0" step="1" dir="ltr"
                    value="{{ $priceMap[$b->id][$c->key] ?? '' }}" placeholder="—">
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
  <div class="save-dock" data-save-dock>
    <span data-save-hint>تغییر نرخ پروفیل روی همه مدل‌های سایت اثر می‌گذارد.</span>
    <button class="btn btn-primary" type="submit">ذخیره نرخ پروفیل</button>
  </div>
</form>
