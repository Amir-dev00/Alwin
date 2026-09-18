@php
    use App\Support\Site;
    $pageCopy = [
        'filter_all' => $blocks['filter_all'] ?? 'همه',
        'filter_upvc' => $blocks['filter_upvc'] ?? 'پنجره UPVC',
        'filter_curtain' => $blocks['filter_curtain'] ?? 'نمای کرتین وال',
        'filter_balcony' => $blocks['filter_balcony'] ?? 'جام بالکن',
        'filter_glass' => $blocks['filter_glass'] ?? 'شیشه دوجداره',
        'load_more' => $blocks['load_more'] ?? 'نمایش پروژه‌های بیشتر',
        'load_less' => $blocks['load_less'] ?? 'نمایش پروژه‌های کمتر',
        'count_template' => $blocks['count_template'] ?? 'نمایش {shown} از {total} پروژه',
        'load_error' => $blocks['load_error'] ?? 'بارگذاری پروژه‌ها با خطا مواجه شد.',
    ];
@endphp
<x-layouts.app :page="$page" cms-page="portfolio" body-class="body-wrapper body-digital-agency body-portfolio">
  <x-slot:styles>
    <link rel="stylesheet" href="{{ asset('assets/css/portfolio.css') }}?v=1.2">
  </x-slot:styles>
  <x-slot:scripts>
    <script src="{{ asset('assets/js/portfolio.js') }}?v=2.1"></script>
  </x-slot:scripts>
  <script type="application/json" id="alwin-page-copy">{!! json_encode($pageCopy, JSON_UNESCAPED_UNICODE) !!}</script>

  <section class="portfolio-hero-area">
    <div class="container large">
      <div class="portfolio-hero-inner section-spacing-top">
        <div class="section-header fade-anim">
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h1 class="section-title portfolio-hero-title" data-cms="hero_title">{{ $blocks['hero_title'] ?? 'پروژه‌های اجرایی آلوین' }}</h1>
            </div>
          </div>
          <div class="text-wrapper">
            <p class="text" data-cms="hero_lead">{{ $blocks['hero_lead'] ?? 'نمونه‌ای از پروژه‌های نصب پنجره و درب UPVC، نمای کرتین وال، جام بالکن و شیشه دوجداره.' }}</p>
          </div>
        </div>
        <div class="portfolio-hero-meta fade-anim" data-delay="0.2">
          <div class="portfolio-hero-stat"><span class="num" data-cms="stat_1_number">{{ $blocks['stat_1_number'] ?? '+۵۰۰' }}</span><span class="lbl" data-cms="stat_1_label">{{ $blocks['stat_1_label'] ?? 'پروژه موفق' }}</span></div>
          <div class="portfolio-hero-stat"><span class="num" data-cms="stat_2_number">{{ $blocks['stat_2_number'] ?? '+۱۵' }}</span><span class="lbl" data-cms="stat_2_label">{{ $blocks['stat_2_label'] ?? 'سال تجربه' }}</span></div>
          <div class="portfolio-hero-stat"><span class="num">{{ Site::fa($projectCount) }}</span><span class="lbl" data-cms="stat_3_label">{{ $blocks['stat_3_label'] ?? 'نمونه‌کار برگزیده' }}</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="portfolio-filter-area">
    <div class="container large">
      <div class="portfolio-filters fade-anim" id="portfolioFilters" role="tablist" aria-label="فیلتر دسته‌بندی پروژه‌ها"></div>
    </div>
  </section>

  <section class="portfolio-grid-area">
    <div class="container large">
      <div class="portfolio-grid-area-inner section-spacing-bottom">
        <div class="portfolio-grid-header">
          <p class="portfolio-result-count" id="portfolioCount"></p>
        </div>
        <div class="portfolio-grid" id="portfolioGrid"></div>
        <p class="portfolio-empty" id="portfolioEmpty" hidden>{{ $blocks['empty_text'] ?? 'پروژه‌ای در این دسته یافت نشد. دسته دیگری را انتخاب کنید.' }}</p>
        <div class="portfolio-loadmore-wrap" id="portfolioLoadMoreWrap" hidden>
          <button type="button" class="rr-btn btn-border hover-bg-theme" id="portfolioLoadMore">
            <span class="btn-wrap">
              <span class="text-one">{{ $blocks['load_more'] ?? 'نمایش پروژه‌های بیشتر' }}</span>
              <span class="text-two">{{ $blocks['load_more'] ?? 'نمایش پروژه‌های بیشتر' }}</span>
            </span>
          </button>
        </div>
      </div>
    </div>
  </section>
</x-layouts.app>
