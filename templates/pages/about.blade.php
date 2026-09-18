@php
    use App\Support\Site;
    $officePhones = Site::lines($siteSettings['office_phones'] ?? null, "021-44245247\n021-44245429\n021-44252799");
    $factoryPhones = Site::lines($siteSettings['factory_phones'] ?? null, "021-65583141\n021-65583140");
@endphp
<x-layouts.app :page="$page" cms-page="about" body-class="body-wrapper body-digital-agency body-about">
  <x-slot:styles>
    <link rel="stylesheet" href="{{ asset('assets/css/about.css') }}?v=1.2">
  </x-slot:styles>
  <x-slot:scripts>
    <script src="{{ asset('assets/js/about.js') }}?v=1.0"></script>
    <script src="{{ asset('assets/js/contact.js') }}?v=1.3"></script>
  </x-slot:scripts>

  @php
    $formCopy = [
        'err_name' => $blocks['callback_err_name'] ?? 'نام را کامل وارد کنید.',
        'err_phone' => $blocks['callback_err_phone'] ?? 'شماره تماس معتبر وارد کنید.',
        'err_fix' => $blocks['callback_err_fix'] ?? 'لطفاً موارد مشخص‌شده را اصلاح کنید.',
        'submitting' => $blocks['callback_submitting'] ?? 'در حال ثبت...',
        'success' => $blocks['callback_success'] ?? 'درخواست ثبت شد. کارشناسان آلوین به‌زودی تماس می‌گیرند.',
        'error' => $blocks['callback_error'] ?? 'ثبت نشد. لطفاً دوباره تلاش کنید یا با {phone} تماس بگیرید.',
        'phone' => $siteSettings['phone_display'] ?? ($siteSettings['phone'] ?? '۰۹۹۰۹۷۷۷۰۹۰'),
    ];
  @endphp
  <script type="application/json" id="alwin-form-copy">{!! json_encode($formCopy, JSON_UNESCAPED_UNICODE) !!}</script>

  <section class="about-hero">
    <div class="container large">
      <div class="about-hero__inner section-spacing-top">
        <p class="about-hero__legal fade-anim" data-delay="0.05" data-cms="legal_name">{{ $blocks['legal_name'] ?? 'شرکت بهینه گستر نمای آفتاب' }}</p>
        <h1 class="about-hero__title fade-anim" data-delay="0.1" data-cms="hero_title">{{ $blocks['hero_title'] ?? 'از ثبت ۱۳۹۲ تا تولید حرفه‌ای در و پنجره' }}</h1>
        <p class="about-hero__lead fade-anim" data-delay="0.2" data-cms="hero_lead">{{ $blocks['hero_lead'] ?? 'آلوین با مدرن‌ترین ماشین‌آلات روز دنیا، تولید در و پنجره‌های UPVC، آلومینیوم و انواع توری را آغاز کرد — و مسیر کیفیت را با نمایندگی برندهای معتبر ادامه داد.' }}</p>
        <div class="about-hero__actions fade-anim" data-delay="0.3">
          <a href="#calling-form" class="rr-btn hover-bg-theme">
            <span class="btn-wrap">
              <span class="text-one" data-cms="cta_contact">{{ $blocks['cta_contact'] ?? 'تماس با ما' }}</span>
              <span class="text-two" data-cms="cta_contact">{{ $blocks['cta_contact'] ?? 'تماس با ما' }}</span>
            </span>
          </a>
          <a href="#about-story" class="rr-btn rr-btn--ghost">
            <span class="btn-wrap">
              <span class="text-one" data-cms="cta_story">{{ $blocks['cta_story'] ?? 'داستان ما' }}</span>
              <span class="text-two" data-cms="cta_story">{{ $blocks['cta_story'] ?? 'داستان ما' }}</span>
            </span>
          </a>
        </div>
        <div class="about-hero__facts fade-anim" data-delay="0.4">
          <div class="about-hero__fact">
            <span class="about-hero__fact-value" data-cms="fact_1_value">{{ $blocks['fact_1_value'] ?? '۱۳۹۲' }}</span>
            <span class="about-hero__fact-label" data-cms="fact_1_label">{{ $blocks['fact_1_label'] ?? 'سال تأسیس' }}</span>
          </div>
          <div class="about-hero__fact">
            <span class="about-hero__fact-value" data-cms="fact_2_value">{{ $blocks['fact_2_value'] ?? '۴۴۶۴۱۹' }}</span>
            <span class="about-hero__fact-label" data-cms="fact_2_label">{{ $blocks['fact_2_label'] ?? 'شماره ثبت' }}</span>
          </div>
          <div class="about-hero__fact">
            <span class="about-hero__fact-value" data-cms="fact_3_value">{{ $blocks['fact_3_value'] ?? '۱۰ سال' }}</span>
            <span class="about-hero__fact-label" data-cms="fact_3_label">{{ $blocks['fact_3_label'] ?? 'گارانتی پروفیل' }}</span>
          </div>
        </div>
        <span class="about-hero__mark" aria-hidden="true">ALWIN</span>
      </div>
    </div>
  </section>

  <section class="about-story" id="about-story">
    <div class="container large">
      <div class="about-story__grid">
        <div class="about-reveal">
          <span class="about-eyebrow" data-cms="story_eyebrow">{{ $blocks['story_eyebrow'] ?? 'هویت شرکت' }}</span>
          <h2 class="about-section-title" data-cms="story_title">{{ $blocks['story_title'] ?? 'بهینه گستر نمای آفتاب؛ نام تجاری آلوین' }}</h2>
          <div class="about-story__body" data-cms="story_html" data-cms-html="true">
            {!! $blocks['story_html'] ?? '<p>شرکت <strong>بهینه گستر نمای آفتاب</strong> با نام تجاری <strong>آلوین (ALWIN)</strong> در سال ۱۳۹۲ با شماره ثبت <strong>۴۴۶۴۱۹</strong> ثبت شد و با بهره‌گیری از مدرن‌ترین ماشین‌آلات روز دنیا، فعالیت خود را در زمینه تولید <strong>در و پنجره‌های UPVC، آلومینیوم و انواع توری</strong> آغاز نمود.</p><p>این مجموعه در راستای توسعه و پیشبرد اهداف خود موفق به اخذ نمایندگی از شرکت <strong>وین‌تک</strong> با درجه کیفی <strong>A</strong> و همچنین اخذ نمایندگی از شرکت <strong>ویستابست</strong> از سال ۹۹ گردیده است.</p><p>امید است با استعانت و یاری از پروردگار یکتا و تأییدات الهی، با ساختار کارگروهی، تکیه بر سرمایه‌های اجتماعی، بهره‌گیری از تجربه مدیران و پیاده‌سازی استراتژی‌ها در عمل، بتوانیم بیش از پیش رضایت مشتریان خود را فراهم نماییم.</p>' !!}
          </div>
        </div>
        <aside class="about-story__aside about-reveal">
          <div class="about-story__chip">
            <span class="about-story__chip-icon" aria-hidden="true"><i class="fa-solid fa-industry"></i></span>
            <span>
              <span class="about-story__chip-title" data-cms="chip_1_title">{{ $blocks['chip_1_title'] ?? 'تولید تخصصی' }}</span>
              <span class="about-story__chip-text" data-cms="chip_1_text">{{ $blocks['chip_1_text'] ?? 'در و پنجره UPVC، آلومینیوم و انواع توری با ماشین‌آلات مدرن' }}</span>
            </span>
          </div>
          <div class="about-story__chip">
            <span class="about-story__chip-icon" aria-hidden="true"><i class="fa-solid fa-certificate"></i></span>
            <span>
              <span class="about-story__chip-title" data-cms="chip_2_title">{{ $blocks['chip_2_title'] ?? 'وین‌تک درجه A' }}</span>
              <span class="about-story__chip-text" data-cms="chip_2_text">{{ $blocks['chip_2_text'] ?? 'اخذ نمایندگی شرکت وین‌تک با بالاترین درجه کیفی' }}</span>
            </span>
          </div>
          <div class="about-story__chip">
            <span class="about-story__chip-icon" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
            <span>
              <span class="about-story__chip-title" data-cms="chip_3_title">{{ $blocks['chip_3_title'] ?? 'ویستابست از ۹۹' }}</span>
              <span class="about-story__chip-text" data-cms="chip_3_text">{{ $blocks['chip_3_text'] ?? 'نمایندگی رسمی ویستابست در غرب تهران و کرج' }}</span>
            </span>
          </div>
          <div class="about-story__chip">
            <span class="about-story__chip-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
            <span>
              <span class="about-story__chip-title" data-cms="chip_4_title">{{ $blocks['chip_4_title'] ?? 'ضمانت و خدمات' }}</span>
              <span class="about-story__chip-text" data-cms="chip_4_text">{{ $blocks['chip_4_text'] ?? 'محصولات همراه با گارانتی و خدمات پس از فروش' }}</span>
            </span>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="about-path" aria-labelledby="path-title">
    <div class="container large">
      <div class="about-path__head about-reveal">
        <span class="about-eyebrow" data-cms="path_eyebrow">{{ $blocks['path_eyebrow'] ?? 'مسیر رشد' }}</span>
        <h2 class="about-section-title" id="path-title" data-cms="path_title">{{ $blocks['path_title'] ?? 'از تأسیس تا نمایندگی‌های رسمی' }}</h2>
        <p class="about-section-lead" data-cms="path_lead">{{ $blocks['path_lead'] ?? 'نقاط کلیدی در شکل‌گیری هویت صنعتی آلوین، بر اساس مسیر واقعی شرکت.' }}</p>
      </div>
      <ol class="about-path__list">
        <li class="about-path__item about-reveal">
          <span class="about-path__year" data-cms="path_1_year">{{ $blocks['path_1_year'] ?? '۱۳۹۲' }}</span>
          <h3 class="about-path__title" data-cms="path_1_title">{{ $blocks['path_1_title'] ?? 'ثبت شرکت و آغاز تولید' }}</h3>
          <p class="about-path__text" data-cms="path_1_text">{{ $blocks['path_1_text'] ?? 'ثبت شرکت بهینه گستر نمای آفتاب با شماره ۴۴۶۴۱۹ و شروع تولید در و پنجره UPVC، آلومینیوم و توری.' }}</p>
        </li>
        <li class="about-path__item about-reveal">
          <span class="about-path__year" data-cms="path_2_year">{{ $blocks['path_2_year'] ?? 'وین‌تک' }}</span>
          <h3 class="about-path__title" data-cms="path_2_title">{{ $blocks['path_2_title'] ?? 'نمایندگی درجه کیفی A' }}</h3>
          <p class="about-path__text" data-cms="path_2_text">{{ $blocks['path_2_text'] ?? 'اخذ نمایندگی شرکت وین‌تک با درجه کیفی A در مسیر توسعه و ارتقای استاندارد تولید.' }}</p>
        </li>
        <li class="about-path__item about-reveal">
          <span class="about-path__year" data-cms="path_3_year">{{ $blocks['path_3_year'] ?? 'از سال ۹۹' }}</span>
          <h3 class="about-path__title" data-cms="path_3_title">{{ $blocks['path_3_title'] ?? 'نمایندگی ویستابست' }}</h3>
          <p class="about-path__text" data-cms="path_3_text">{{ $blocks['path_3_text'] ?? 'اخذ نمایندگی ویستابست و سپس نمایندگی رسمی در غرب تهران و کرج، با ضمانت و خدمات پس از فروش.' }}</p>
        </li>
      </ol>
    </div>
  </section>

  <section class="about-dealers" aria-labelledby="dealers-title">
    <div class="container large">
      <div class="about-reveal">
        <span class="about-eyebrow" data-cms="dealers_eyebrow">{{ $blocks['dealers_eyebrow'] ?? 'شراکت‌های رسمی' }}</span>
        <h2 class="about-section-title" id="dealers-title" data-cms="dealers_title">{{ $blocks['dealers_title'] ?? 'نمایندگی برندهای معتبر پروفیل' }}</h2>
        <p class="about-section-lead" data-cms="dealers_lead">{{ $blocks['dealers_lead'] ?? 'آلوین مسیر کیفیت را با نمایندگی برندهایی طی کرده که استاندارد ساخت و نصب را جدی می‌گیرند.' }}</p>
      </div>
      <div class="about-dealers__grid">
        <article class="about-dealer about-reveal">
          <span class="about-dealer__tag" data-cms="dealer_1_tag">{{ $blocks['dealer_1_tag'] ?? 'نمایندگی' }}</span>
          <h3 class="about-dealer__name" data-cms="dealer_1_name">{{ $blocks['dealer_1_name'] ?? 'وین‌تک — درجه A' }}</h3>
          <p class="about-dealer__text" data-cms="dealer_1_text">{{ $blocks['dealer_1_text'] ?? 'اخذ نمایندگی شرکت وین‌تک با درجه کیفی A، در راستای توسعه و پیشبرد اهداف تولیدی مجموعه.' }}</p>
        </article>
        <article class="about-dealer about-reveal">
          <span class="about-dealer__tag" data-cms="dealer_2_tag">{{ $blocks['dealer_2_tag'] ?? 'نمایندگی رسمی' }}</span>
          <h3 class="about-dealer__name" data-cms="dealer_2_name">{{ $blocks['dealer_2_name'] ?? 'ویستابست — غرب تهران و کرج' }}</h3>
          <p class="about-dealer__text" data-cms="dealer_2_text">{{ $blocks['dealer_2_text'] ?? 'نمایندگی رسمی شرکت ویستابست در غرب تهران و کرج؛ محصولات مطابق استانداردهای فنی ویستابست، همراه با ضمانت و خدمات پس از فروش.' }}</p>
        </article>
      </div>
    </div>
  </section>

  <section class="about-vista" aria-labelledby="vista-title">
    <div class="container large">
      <div class="about-vista__inner">
        <div class="about-reveal">
          <span class="about-eyebrow" data-cms="vista_eyebrow">{{ $blocks['vista_eyebrow'] ?? 'چرا ویستابست؟' }}</span>
          <h2 class="about-vista__title" id="vista-title" data-cms="vista_title">{{ $blocks['vista_title'] ?? 'کیفیت پروفیل‌ها و پنجره‌های ویستابست' }}</h2>
          <p class="about-vista__intro" data-cms="vista_intro">{{ $blocks['vista_intro'] ?? 'در راستای دیدگاه کیفیت‌محور شرکت ویستابست، حمایت از حقوق مصرف‌کنندگان و آشنایی تولیدکنندگان پنجره با استانداردهای ساخت و نصب، این شرکت دفترچه الزامات فنی ویستابست را منتشر نموده است.' }}</p>
        </div>
        <ul class="about-vista__points about-reveal">
          @foreach(Site::lines($blocks['vista_points'] ?? null, "بهره‌گیری از دانش فنی شرکت‌های معتبر بین‌المللی در تأمین مواد اولیه و استفاده از ماشین‌آلات مدرن.\nتولید پروفیل‌هایی با ماندگاری و کیفیت بالا در شرایط آب‌وهوایی مختلف ایران.\nنظارت واحد کنترل کیفیت بر فرآیند تولید و انجام آزمون‌های تخصصی در آزمایشگاه مجهز.\nتضمین کیفیت محصولات مطابق با استانداردهای ملی و بین‌المللی.") as $point)
            <li>{{ $point }}</li>
          @endforeach
        </ul>
      </div>
    </div>
    <span class="about-vista__watermark" aria-hidden="true">VISTA</span>
  </section>

  <section class="about-warranty" aria-labelledby="warranty-title">
    <div class="container large">
      <div class="about-warranty__head about-reveal">
        <span class="about-eyebrow" data-cms="warranty_eyebrow">{{ $blocks['warranty_eyebrow'] ?? 'تعهد پس از فروش' }}</span>
        <h2 class="about-section-title" id="warranty-title" data-cms="warranty_title">{{ $blocks['warranty_title'] ?? 'گارانتی و خدمات پس از فروش' }}</h2>
        <p class="about-section-lead" data-cms="warranty_lead">{{ $blocks['warranty_lead'] ?? 'پوشش گارانتی آلوین بر اساس تعهدات اعلام‌شده برای پروفیل، یراق، شیشه و رگلاژ.' }}</p>
      </div>
      <div class="about-warranty__grid">
        <div class="about-warranty__item about-reveal"><span class="about-warranty__years">{{ $blocks['warranty_1_years'] ?? '۱۰' }}<span class="about-warranty__unit">{{ $blocks['warranty_unit'] ?? 'سال' }}</span></span><span class="about-warranty__label" data-cms="warranty_1_label">{{ $blocks['warranty_1_label'] ?? 'گارانتی پروفیل' }}</span></div>
        <div class="about-warranty__item about-reveal"><span class="about-warranty__years">{{ $blocks['warranty_2_years'] ?? '۵' }}<span class="about-warranty__unit">{{ $blocks['warranty_unit'] ?? 'سال' }}</span></span><span class="about-warranty__label" data-cms="warranty_2_label">{{ $blocks['warranty_2_label'] ?? 'یراق‌آلات' }}</span></div>
        <div class="about-warranty__item about-reveal"><span class="about-warranty__years">{{ $blocks['warranty_3_years'] ?? '۵' }}<span class="about-warranty__unit">{{ $blocks['warranty_unit'] ?? 'سال' }}</span></span><span class="about-warranty__label" data-cms="warranty_3_label">{{ $blocks['warranty_3_label'] ?? 'شیشه' }}</span></div>
        <div class="about-warranty__item about-reveal"><span class="about-warranty__years">{{ $blocks['warranty_4_years'] ?? '۱' }}<span class="about-warranty__unit">{{ $blocks['warranty_unit'] ?? 'سال' }}</span></span><span class="about-warranty__label" data-cms="warranty_4_label">{{ $blocks['warranty_4_label'] ?? 'رگلاژ و آب‌بندی مجدد' }}</span></div>
      </div>
    </div>
  </section>

  <section class="about-locations" aria-labelledby="locations-title">
    <div class="container large">
      <div class="about-reveal" style="margin-bottom: 1.5rem;">
        <span class="about-eyebrow" data-cms="locations_eyebrow">{{ $blocks['locations_eyebrow'] ?? 'حضور ما' }}</span>
        <h2 class="about-section-title" id="locations-title" data-cms="locations_title">{{ $blocks['locations_title'] ?? 'دفتر مرکزی و کارخانه' }}</h2>
      </div>
      <div class="about-locations__grid">
        <article class="about-place about-reveal">
          <span class="about-place__type" data-cms="office_type">{{ $blocks['office_type'] ?? 'دفتر مرکزی' }}</span>
          <h3 class="about-place__title" data-cms="office_place_title">{{ $blocks['office_place_title'] ?? 'تهران — ساختمان آوند' }}</h3>
          <p class="about-place__address">{{ $siteSettings['address'] ?? 'بزرگراه اشرفی اصفهانی، ابتدای جلال آل احمد، پلاک ۱۸۲، ساختمان آوند، طبقه ۵' }}</p>
          <ul class="about-place__phones">
            @foreach($officePhones as $n)
              <li><a href="{{ Site::phoneHref($n) }}">{{ $n }}</a></li>
            @endforeach
          </ul>
          <p class="about-place__hotline">{{ $blocks['office_hotline_label'] ?? 'خط ویژه سفارشات و مشاوره' }}: <a href="{{ Site::phoneHref($siteSettings['phone'] ?? '09909777090') }}">{{ $siteSettings['phone_display'] ?? ($siteSettings['phone'] ?? '09909777090') }}</a></p>
          <a class="about-place__email" href="mailto:{{ $siteSettings['email'] ?? 'info@alwinco.ir' }}">{{ $siteSettings['email'] ?? 'info@alwinco.ir' }}</a>
        </article>
        <article class="about-place about-reveal">
          <span class="about-place__type" data-cms="factory_type">{{ $blocks['factory_type'] ?? 'کارخانه' }}</span>
          <h3 class="about-place__title">{{ $siteSettings['factory_title'] ?? 'صفادشت — بلوار قبچاق' }}</h3>
          <p class="about-place__address">{{ $siteSettings['factory_address'] ?? 'جاده ملارد - صفادشت، بلوار قبچاق، نبش شهدای پنجم، پلاک ۱' }}</p>
          <ul class="about-place__phones">
            @foreach($factoryPhones as $n)
              <li><a href="{{ Site::phoneHref($n) }}">{{ $n }}</a></li>
            @endforeach
          </ul>
        </article>
      </div>
    </div>
  </section>

  <section class="about-cta" id="calling-form">
    <div class="container large">
      <div class="about-cta__inner about-reveal">
        <div>
          <span class="about-eyebrow" data-cms="callback_eyebrow">{{ $blocks['callback_eyebrow'] ?? 'درخواست تماس' }}</span>
          <h2 class="about-cta__title" data-cms="callback_title">{{ $blocks['callback_title'] ?? 'آماده همکاری با پروژه‌ی بعدی شما هستیم' }}</h2>
          <p class="about-cta__text" data-cms="callback_text">{{ $blocks['callback_text'] ?? 'نام و شماره را بگذارید؛ کارشناس آلوین برای مشاوره، بازدید یا ثبت سفارش با شما تماس می‌گیرد.' }}</p>
          <div class="about-cta__actions">
            <a href="{{ Site::phoneHref($siteSettings['phone'] ?? '09909777090') }}" class="rr-btn rr-btn--ghost">
              <span class="btn-wrap">
                <span class="text-one">{{ $siteSettings['phone_display'] ?? ($siteSettings['phone'] ?? '۰۹۹۰۹۷۷۷۰۹۰') }}</span>
                <span class="text-two">{{ $siteSettings['phone_display'] ?? ($siteSettings['phone'] ?? '۰۹۹۰۹۷۷۷۰۹۰') }}</span>
              </span>
            </a>
          </div>
        </div>
        <form class="about-call" id="callingForm" novalidate>
          <input class="about-call__hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
          <input type="hidden" name="source" value="about">
          <input type="hidden" name="subject" value="callback">
          <div class="about-call__row">
            <label class="about-call__field" for="calling-name">
              <span data-cms="callback_name_label">{{ $blocks['callback_name_label'] ?? 'نام و نام خانوادگی' }}</span>
              <input id="calling-name" name="name" type="text" autocomplete="name" required placeholder="{{ $blocks['callback_name_label'] ?? 'نام شما' }}">
              <small class="about-call__hint" id="calling-hint-name" role="alert"></small>
            </label>
            <label class="about-call__field" for="calling-phone">
              <span data-cms="callback_phone_label">{{ $blocks['callback_phone_label'] ?? 'شماره تماس' }}</span>
              <input id="calling-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="{{ $blocks['callback_phone_placeholder'] ?? '09xxxxxxxxx' }}" dir="ltr">
              <small class="about-call__hint" id="calling-hint-phone" role="alert"></small>
            </label>
          </div>
          <label class="about-call__field" for="calling-message">
            <span>{{ $blocks['callback_message_label'] ?? 'توضیح سفارش' }} <em>{{ $blocks['callback_message_optional'] ?? '(اختیاری)' }}</em></span>
            <textarea id="calling-message" name="message" rows="3" placeholder="{{ $blocks['callback_message_placeholder'] ?? 'نوع پنجره، ابعاد تقریبی یا زمان مناسب تماس…' }}"></textarea>
          </label>
          <div class="about-call__foot">
            <button type="submit" class="rr-btn hover-bg-theme" id="callingFormSubmit">
              <span class="btn-wrap">
                <span class="text-one" data-cms="callback_submit">{{ $blocks['callback_submit'] ?? 'ثبت درخواست تماس' }}</span>
                <span class="text-two" data-cms="callback_submit">{{ $blocks['callback_submit'] ?? 'ثبت درخواست تماس' }}</span>
              </span>
            </button>
            <p class="about-call__status" id="callingFormStatus" role="status" aria-live="polite"></p>
          </div>
        </form>
      </div>
    </div>
  </section>
</x-layouts.app>
