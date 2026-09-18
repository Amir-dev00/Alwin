@php
    $c = $calculatorCopy ?? [];
@endphp
<div id="alwin-calculator" class="alwin-calc" aria-hidden="true" role="dialog" aria-labelledby="calc-title">
  <div class="alwin-calc__backdrop"></div>
  <div class="alwin-calc__panel">
    <div class="alwin-calc__head">
      <h2 id="calc-title" class="alwin-calc__title">{{ $c['calc_title'] ?? 'محاسبه آنلاین قیمت' }}</h2>
      <button type="button" class="alwin-calc__close" aria-label="{{ $c['calc_close'] ?? 'بستن' }}">&times;</button>
    </div>
    <div class="alwin-calc__crumb" hidden></div>
    <div class="alwin-calc__steps">
      <div class="alwin-calc__step is-active" data-step-indicator="1"><span class="alwin-calc__step-label">{{ $c['calc_step_1'] ?? 'جنس' }}</span><span class="alwin-calc__step-num">۱</span></div>
      <span class="alwin-calc__step-sep"></span>
      <div class="alwin-calc__step" data-step-indicator="2"><span class="alwin-calc__step-label">{{ $c['calc_step_2'] ?? 'دسته' }}</span><span class="alwin-calc__step-num">۲</span></div>
      <span class="alwin-calc__step-sep"></span>
      <div class="alwin-calc__step" data-step-indicator="3"><span class="alwin-calc__step-label">{{ $c['calc_step_3'] ?? 'مدل' }}</span><span class="alwin-calc__step-num">۳</span></div>
      <span class="alwin-calc__step-sep"></span>
      <div class="alwin-calc__step" data-step-indicator="4"><span class="alwin-calc__step-label">{{ $c['calc_step_4'] ?? 'جزئیات' }}</span><span class="alwin-calc__step-num">۴</span></div>
      <span class="alwin-calc__step-sep"></span>
      <div class="alwin-calc__step" data-step-indicator="5"><span class="alwin-calc__step-label">{{ $c['calc_step_5'] ?? 'تماس' }}</span><span class="alwin-calc__step-num">۵</span></div>
    </div>
    <div class="alwin-calc__body">
      <div class="alwin-calc__step-panel" data-step="1">
        <p class="alwin-calc__lead">{{ $c['calc_lead_1'] ?? 'جنس پروفیل را انتخاب کنید' }}</p>
        <div class="alwin-calc__tabs"></div>
      </div>
      <div class="alwin-calc__step-panel" data-step="2" hidden>
        <p class="alwin-calc__lead alwin-calc__lead--category">{{ $c['calc_lead_2'] ?? 'دسته محصول را انتخاب کنید' }}</p>
        <div class="alwin-calc__categories"></div>
      </div>
      <div class="alwin-calc__step-panel" data-step="3" hidden>
        <p class="alwin-calc__lead alwin-calc__lead--model">{{ $c['calc_lead_3'] ?? 'مدل دقیق را انتخاب کنید' }}</p>
        <div class="alwin-calc__search-wrap">
          <input type="search" class="alwin-calc__search" placeholder="{{ $c['calc_search_placeholder'] ?? 'جستجو در مدل‌ها...' }}" autocomplete="off">
        </div>
        <div class="alwin-calc__models"></div>
      </div>
      <div class="alwin-calc__step-panel" data-step="4" hidden>
        <div class="alwin-calc__selected-model"></div>
        <div class="alwin-calc__options alwin-calc__options--compact">
          <div class="alwin-calc__field">
            <label for="calc-profile">{{ $c['calc_label_profile'] ?? 'پروفیل' }}</label>
            <select id="calc-profile" class="alwin-calc__select"></select>
          </div>
          <div class="alwin-calc__field" id="calc-glass-wrap">
            <label for="calc-glass">{{ $c['calc_label_glass'] ?? 'شیشه' }}</label>
            <select id="calc-glass" class="alwin-calc__select"></select>
          </div>
          <div class="alwin-calc__field" id="calc-hardware-wrap">
            <label for="calc-hardware">{{ $c['calc_label_hardware'] ?? 'یراق' }}</label>
            <select id="calc-hardware" class="alwin-calc__select"></select>
          </div>
          <div class="alwin-calc__field" id="calc-hw-type-wrap" hidden>
            <label for="calc-hardware-type">{{ $c['calc_label_hardware_type'] ?? 'نوع بازشو' }}</label>
            <select id="calc-hardware-type" class="alwin-calc__select"></select>
          </div>
        </div>
        <div class="alwin-calc__fields alwin-calc__fields--dims alwin-calc__fields--compact">
          <div class="alwin-calc__field"><label for="calc-width">{{ $c['calc_label_width'] ?? 'عرض (cm)' }}</label><input id="calc-width" type="number" min="1" inputmode="numeric" placeholder="{{ $c['calc_width_placeholder'] ?? '۱۲۰' }}"></div>
          <div class="alwin-calc__field"><label for="calc-height">{{ $c['calc_label_height'] ?? 'ارتفاع (cm)' }}</label><input id="calc-height" type="number" min="1" inputmode="numeric" placeholder="{{ $c['calc_height_placeholder'] ?? '۱۵۰' }}"></div>
        </div>
        <div class="alwin-calc__estimate alwin-calc__estimate--full">
          <div class="alwin-calc__estimate-top">
            <div>
              <span class="alwin-calc__estimate-label">{{ $c['calc_estimate_label'] ?? 'برآورد قیمت' }}</span>
              <span class="alwin-calc__estimate-hint">{{ $c['calc_estimate_hint'] ?? 'تقریبی — تأیید نهایی پس از بازدید' }}</span>
            </div>
            <span class="alwin-calc__estimate-value">—</span>
          </div>
          <div class="alwin-calc__estimate-qty">
            <label for="calc-quantity">{{ $c['calc_quantity_label'] ?? 'تعداد' }}</label>
            <input id="calc-quantity" type="number" min="1" value="1">
          </div>
          <div class="alwin-calc__breakdown" hidden></div>
        </div>
        <p class="alwin-calc__inline-error" hidden></p>
      </div>
      <div class="alwin-calc__step-panel" data-step="5" hidden>
        <p class="alwin-calc__lead">{{ $c['calc_lead_5'] ?? 'برای دریافت مشاوره رایگان، اطلاعات تماس را وارد کنید' }}</p>
        <div class="alwin-calc__summary"></div>
        <div class="alwin-calc__fields">
          <div class="alwin-calc__field"><label for="calc-name">{{ $c['calc_name_label'] ?? 'نام' }}</label><input id="calc-name" type="text" placeholder="{{ $c['calc_name_placeholder'] ?? 'نام شما' }}" autocomplete="name"></div>
          <div class="alwin-calc__field"><label for="calc-phone">{{ $c['calc_phone_label'] ?? 'شماره تماس' }}</label><input id="calc-phone" type="tel" placeholder="{{ $c['calc_phone_placeholder'] ?? '09xxxxxxxxx' }}" autocomplete="tel"></div>
        </div>
        <p class="alwin-calc__error"></p>
      </div>
      <div class="alwin-calc__step-panel alwin-calc__panel--success" data-step="success" hidden>
        <div class="alwin-calc__success-icon">✓</div>
        <h3 class="alwin-calc__success-title">{{ $c['calc_success_title'] ?? 'درخواست شما ثبت شد' }}</h3>
        <p class="alwin-calc__success-text">{{ $c['calc_success_text'] ?? 'کارشناسان ALWIN به زودی با شما تماس می‌گیرند.' }}</p>
        <div class="alwin-calc__success-actions">
          <button type="button" class="rr-btn hover-bg-theme alwin-calc__close-btn">
            <span class="btn-wrap"><span class="text-one">{{ $c['calc_btn_close'] ?? 'بستن' }}</span><span class="text-two">{{ $c['calc_btn_close'] ?? 'بستن' }}</span></span>
          </button>
        </div>
      </div>
    </div>
    <div class="alwin-calc__foot">
      <button type="button" class="alwin-calc__btn-back" hidden>{{ $c['calc_btn_back'] ?? 'قبلی' }}</button>
      <button type="button" class="alwin-calc__btn-next" hidden>{{ $c['calc_btn_next'] ?? 'ادامه' }}</button>
      <button type="button" class="alwin-calc__btn-submit" hidden>{{ $c['calc_btn_submit'] ?? 'ثبت درخواست' }}</button>
    </div>
  </div>
</div>
