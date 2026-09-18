<section class="price-studio fly-item" style="--i:2" data-price-studio>
  <script type="application/json" id="pricing-catalog">{!! json_encode($catalog, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
  <div class="price-studio__grid">
    <article class="card price-studio__form">
      <header>
        <div>
          <h2>آزمایش زنده</h2>
          <p>همان فرمول سایت؛ با تفکیک پروفیل، شیشه، یراق و توری.</p>
        </div>
      </header>
      <div class="form-grid">
        <label class="field wide">
          <span>مدل</span>
          <select data-ps="model">
            @foreach($models as $m)
              <option value="{{ $m->catalog_id }}" @selected($m->catalog_id === 2)>{{ $m->catalog_id }}. {{ $m->name }}</option>
            @endforeach
          </select>
        </label>
        <label class="field">
          <span>پروفیل</span>
          <select data-ps="profile">
            @php
              $studioGroups = $brands->groupBy(fn ($b) => $b->material ?: 'upvc');
              $studioLabels = ['aluminum' => 'آلومینیوم', 'upvc' => 'یو پی وی سی (UPVC)'];
            @endphp
            @foreach($studioLabels as $mat => $label)
              @if($studioGroups->has($mat))
                <optgroup label="{{ $label }}">
                  @foreach($studioGroups[$mat] as $b)
                    <option value="{{ $b->key }}" @selected($b->key === 'wintech')">{{ $b->name }}</option>
                  @endforeach
                </optgroup>
              @endif
            @endforeach
          </select>
        </label>
        <label class="field">
          <span>شیشه</span>
          <select data-ps="glass">
            @foreach($glasses as $g)
              <option value="{{ $g->key }}">{{ $g->name }}</option>
            @endforeach
          </select>
        </label>
        <label class="field">
          <span>برند یراق</span>
          <select data-ps="origin">
            <option value="turk">ترک</option>
            <option value="germany">آلمانی</option>
          </select>
        </label>
        <label class="field" data-ps-type-wrap>
          <span>نوع یراق</span>
          <select data-ps="type">
            @foreach($hardwares as $h)
              <option value="{{ $h->key }}">{{ $h->name }}</option>
            @endforeach
          </select>
        </label>
        <label class="field">
          <span>عرض (سانتی‌متر)</span>
          <input type="number" min="20" max="800" value="120" data-ps="width">
        </label>
        <label class="field">
          <span>ارتفاع (سانتی‌متر)</span>
          <input type="number" min="20" max="800" value="150" data-ps="height">
        </label>
        <label class="field">
          <span>تعداد</span>
          <input type="number" min="1" max="99" value="1" data-ps="qty">
        </label>
      </div>
    </article>

    <article class="card price-quote" data-ps-quote>
      <p class="price-quote__kicker">برآورد</p>
      <strong class="price-quote__total" data-ps-total>—</strong>
      <p class="price-quote__model" data-ps-model-name></p>
      <div class="price-quote__bars" data-ps-bars></div>
      <ul class="price-quote__sum">
        <li><span>پروفیل</span><b data-ps-sum="profile">—</b></li>
        <li><span>شیشه</span><b data-ps-sum="glass">—</b></li>
        <li><span>یراق</span><b data-ps-sum="hardware">—</b></li>
        <li><span>توری</span><b data-ps-sum="screen">—</b></li>
      </ul>
      <div class="price-quote__lines" data-ps-lines></div>
      <p class="price-quote__hint">قیمت سایت پس از ذخیره نرخ‌ها بلافاصله با همین موتور به‌روز می‌شود.</p>
    </article>
  </div>
</section>
