@php
    use App\Support\Site;
    $phone = $siteSettings['phone'] ?? '09909777090';
    $email = $siteSettings['email'] ?? 'info@alwinco.ir';
    $address = $siteSettings['address'] ?? 'بزرگراه اشرفی اصفهانی، ابتدای جلال آل احمد، پلاک ۱۸۲، ساختمان آوند، طبقه ۵';
    $website = $siteSettings['website_display'] ?? 'www.alwinco.ir';
    $officePhones = Site::lines($siteSettings['office_phones'] ?? null, "021-44245247\n021-44245429\n021-44252799");
@endphp
<x-layouts.app :page="$page" cms-page="contact" body-class="body-wrapper body-digital-agency body-contact">
  <x-slot:styles>
    <link rel="stylesheet" href="{{ asset('assets/css/contact.css') }}?v=1.0">
  </x-slot:styles>
  <x-slot:scripts>
    <script src="{{ asset('assets/js/contact.js') }}?v=1.3"></script>
  </x-slot:scripts>

  @php
    $formCopy = [
        'err_name' => $blocks['form_err_name'] ?? 'نام را کامل وارد کنید.',
        'err_phone' => $blocks['form_err_phone'] ?? 'شماره تماس معتبر وارد کنید.',
        'err_email' => $blocks['form_err_email'] ?? 'ایمیل معتبر نیست.',
        'err_subject' => $blocks['form_err_subject'] ?? 'موضوع را انتخاب کنید.',
        'err_message' => $blocks['form_err_message'] ?? 'پیام حداقل ۱۰ کاراکتر باشد.',
        'err_fix' => $blocks['form_err_fix'] ?? 'لطفاً موارد مشخص‌شده را اصلاح کنید.',
        'submitting' => $blocks['form_submitting'] ?? 'در حال ثبت...',
        'success' => $blocks['form_success'] ?? 'پیام شما ثبت شد. کارشناسان آلوین به‌زودی پاسخ می‌دهند.',
        'error' => $blocks['form_error'] ?? 'ثبت نشد. لطفاً دوباره تلاش کنید.',
        'phone' => $siteSettings['phone_display'] ?? $phone,
    ];
  @endphp
  <script type="application/json" id="alwin-form-copy">{!! json_encode($formCopy, JSON_UNESCAPED_UNICODE) !!}</script>

  <section class="contact-hero">
    <div class="container large">
      <div class="contact-hero__inner section-spacing-top">
        <h1 class="contact-hero__title fade-anim" data-delay="0.1" style="margin-top: 30px;" data-cms="hero_title">{{ $blocks['hero_title'] ?? 'مشاوره مستقیم با تیم تولید و نصب' }}</h1>
        <p class="contact-hero__lead fade-anim" data-delay="0.2" data-cms="hero_lead">{{ $blocks['hero_lead'] ?? 'برای سفارش، بازدید فنی یا دریافت قیمت شفاف، از خط ویژه یا فرم زیر با آلوین در ارتباط باشید.' }}</p>
        <div class="contact-hero__actions fade-anim" data-delay="0.3">
          <a href="{{ Site::phoneHref($phone) }}" class="rr-btn hover-bg-theme">
            <span class="btn-wrap">
              <span class="text-one" data-cms="cta_call">{{ $blocks['cta_call'] ?? 'تماس با خط ویژه' }}</span>
              <span class="text-two" data-cms="cta_call">{{ $blocks['cta_call'] ?? 'تماس با خط ویژه' }}</span>
            </span>
          </a>
          <a href="#contact-form" class="rr-btn rr-btn--ghost">
            <span class="btn-wrap">
              <span class="text-one" data-cms="cta_form">{{ $blocks['cta_form'] ?? 'ارسال پیام' }}</span>
              <span class="text-two" data-cms="cta_form">{{ $blocks['cta_form'] ?? 'ارسال پیام' }}</span>
            </span>
          </a>
        </div>
        <span class="contact-hero__mark" aria-hidden="true">ALWIN</span>
      </div>
    </div>
  </section>

  <section class="contact-hotline" aria-label="{{ $blocks['hotline_label'] ?? 'خط ویژه سفارشات و مشاوره' }}">
    <div class="container large">
      <div class="contact-hotline__panel contact-reveal">
        <div>
          <span class="contact-hotline__label" data-cms="hotline_label">{{ $blocks['hotline_label'] ?? 'خط ویژه سفارشات و مشاوره' }}</span>
          <a class="contact-hotline__number" href="{{ Site::phoneHref($phone) }}">{{ $siteSettings['phone_display'] ?? $phone }}</a>
        </div>
        <div class="contact-hotline__cta">
          <a href="{{ Site::phoneHref($phone) }}" class="rr-btn">
            <span class="btn-wrap">
              <span class="text-one" data-cms="hotline_button">{{ $blocks['hotline_button'] ?? 'همین حالا تماس بگیرید' }}</span>
              <span class="text-two" data-cms="hotline_button">{{ $blocks['hotline_button'] ?? 'همین حالا تماس بگیرید' }}</span>
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="contact-main" id="contact-form">
    <div class="container large">
      <div class="contact-layout">
        <aside class="contact-aside contact-reveal">
          <span class="contact-aside__eyebrow" data-cms="aside_eyebrow">{{ $blocks['aside_eyebrow'] ?? 'راه‌های ارتباطی' }}</span>
          <h2 class="contact-aside__title" data-cms="aside_title">{{ $blocks['aside_title'] ?? 'دفتر مرکزی و مسیرهای تماس' }}</h2>
          <p class="contact-aside__text" data-cms="aside_text">{{ $blocks['aside_text'] ?? 'اطلاعات زیر از دفتر مرکزی آلوین است. برای پیگیری سریع‌تر، خط ویژه سفارشات و مشاوره را در اولویت قرار دهید.' }}</p>
          <div class="contact-channels">
            <a class="contact-channel" href="{{ Site::phoneHref($phone) }}">
              <span class="contact-channel__icon" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
              <span>
                <span class="contact-channel__label" data-cms="channel_hotline">{{ $blocks['channel_hotline'] ?? 'خط ویژه' }}</span>
                <span class="contact-channel__value contact-channel__value--ltr">{{ $siteSettings['phone_display'] ?? $phone }}</span>
              </span>
            </a>
            <div class="contact-channel">
              <span class="contact-channel__icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
              <span>
                <span class="contact-channel__label" data-cms="channel_office_phones">{{ $blocks['channel_office_phones'] ?? 'تلفن‌های دفتر' }}</span>
                <ul class="contact-channel__list">
                  @foreach($officePhones as $n)
                    <li><a href="{{ Site::phoneHref($n) }}">{{ $n }}</a></li>
                  @endforeach
                </ul>
              </span>
            </div>
            <a class="contact-channel" href="mailto:{{ $email }}">
              <span class="contact-channel__icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
              <span>
                <span class="contact-channel__label" data-cms="channel_email">{{ $blocks['channel_email'] ?? 'ایمیل' }}</span>
                <span class="contact-channel__value">{{ $email }}</span>
              </span>
            </a>
            <a class="contact-channel" href="{{ url('/') }}">
              <span class="contact-channel__icon" aria-hidden="true"><i class="fa-solid fa-globe"></i></span>
              <span>
                <span class="contact-channel__label" data-cms="channel_website">{{ $blocks['channel_website'] ?? 'وب‌سایت' }}</span>
                <span class="contact-channel__value">{{ $website }}</span>
              </span>
            </a>
            <div class="contact-channel">
              <span class="contact-channel__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
              <span>
                <span class="contact-channel__label" data-cms="channel_address">{{ $blocks['channel_address'] ?? 'آدرس دفتر مرکزی' }}</span>
                <span class="contact-channel__value">{{ $address }}</span>
              </span>
            </div>
          </div>
        </aside>

        <div class="contact-form-panel contact-reveal">
          <h2 class="contact-form-panel__title" data-cms="form_title">{{ $blocks['form_title'] ?? 'فرم تماس' }}</h2>
          <p class="contact-form-panel__lead" data-cms="form_lead">{{ $blocks['form_lead'] ?? 'پیام خود را بنویسید؛ کارشناسان آلوین در کوتاه‌ترین زمان پاسخ می‌دهند.' }}</p>
          <form class="contact-form" id="contactForm" novalidate>
            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="about-call__hp" style="position:absolute;left:-9999px;opacity:0;height:0;width:0">
            <input type="hidden" name="source" value="contact">
            <div class="contact-form__row">
              <div class="contact-field">
                <label for="contact-name">{{ $blocks['form_name_label'] ?? 'نام و نام خانوادگی' }} <span class="req" aria-hidden="true">*</span></label>
                <input id="contact-name" name="name" type="text" autocomplete="name" required placeholder="{{ $blocks['form_name_label'] ?? 'نام شما' }}">
                <span class="contact-field__hint" id="hint-name" role="alert"></span>
              </div>
              <div class="contact-field">
                <label for="contact-phone">{{ $blocks['form_phone_label'] ?? 'شماره تماس' }} <span class="req" aria-hidden="true">*</span></label>
                <input id="contact-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required placeholder="{{ $blocks['form_phone_placeholder'] ?? '09xxxxxxxxx' }}" dir="ltr">
                <span class="contact-field__hint" id="hint-phone" role="alert"></span>
              </div>
            </div>
            <div class="contact-form__row">
              <div class="contact-field">
                <label for="contact-email">{{ $blocks['form_email_label'] ?? 'ایمیل' }}</label>
                <input id="contact-email" name="email" type="email" autocomplete="email" placeholder="optional@email.com" dir="ltr">
                <span class="contact-field__hint" id="hint-email" role="alert"></span>
              </div>
              <div class="contact-field">
                <label for="contact-subject">{{ $blocks['form_subject_label'] ?? 'موضوع' }} <span class="req" aria-hidden="true">*</span></label>
                <select id="contact-subject" name="subject" required>
                  <option value="" selected disabled>{{ $blocks['form_subject_placeholder'] ?? 'انتخاب کنید' }}</option>
                  <option value="quote">{{ $blocks['form_subject_quote'] ?? 'درخواست قیمت / مشاوره' }}</option>
                  <option value="visit">{{ $blocks['form_subject_visit'] ?? 'بازدید فنی و اندازه‌گیری' }}</option>
                  <option value="order">{{ $blocks['form_subject_order'] ?? 'پیگیری سفارش' }}</option>
                  <option value="support">{{ $blocks['form_subject_support'] ?? 'پشتیبانی پس از نصب' }}</option>
                  <option value="other">{{ $blocks['form_subject_other'] ?? 'سایر' }}</option>
                </select>
                <span class="contact-field__hint" id="hint-subject" role="alert"></span>
              </div>
            </div>
            <div class="contact-field">
              <label for="contact-message">{{ $blocks['form_message_label'] ?? 'پیام' }} <span class="req" aria-hidden="true">*</span></label>
              <textarea id="contact-message" name="message" required placeholder="{{ $blocks['form_message_placeholder'] ?? 'شرح کوتاه درخواست شما…' }}"></textarea>
              <span class="contact-field__hint" id="hint-message" role="alert"></span>
            </div>
            <div class="contact-form__foot">
              <button type="submit" class="rr-btn hover-bg-theme contact-form__submit" id="contactFormSubmit">
                <span class="btn-wrap">
                  <span class="text-one" data-cms="form_submit">{{ $blocks['form_submit'] ?? 'ارسال پیام' }}</span>
                  <span class="text-two" data-cms="form_submit">{{ $blocks['form_submit'] ?? 'ارسال پیام' }}</span>
                </span>
              </button>
              <p class="contact-form__status" id="contactFormStatus" role="status" aria-live="polite"></p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <section class="contact-office" aria-labelledby="office-title">
    <div class="container large">
      <div class="contact-office__inner">
        <div class="contact-reveal">
          <span class="contact-office__eyebrow" data-cms="office_eyebrow">{{ $blocks['office_eyebrow'] ?? 'دفتر مرکزی' }}</span>
          <h2 class="contact-office__title" id="office-title" data-cms="office_title">{{ $blocks['office_title'] ?? 'آدرس مراجعه حضوری' }}</h2>
          <p class="contact-office__address">{{ $address }}</p>
        </div>
        <div class="contact-office__meta contact-reveal">
          <a href="mailto:{{ $email }}">{{ $email }}</a>
          <a href="{{ url('/') }}">{{ $website }}</a>
          <a href="https://maps.google.com/?q={{ urlencode($address) }}" target="_blank" rel="noopener noreferrer">{{ $blocks['map_link_label'] ?? 'مسیریابی در نقشه' }}</a>
        </div>
      </div>
    </div>
    <span class="contact-office__watermark" aria-hidden="true">ALWIN</span>
  </section>
</x-layouts.app>
