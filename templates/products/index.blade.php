@php
    use App\Support\Site;
    $pageCopy = [
        'tab_all' => $blocks['tab_all'] ?? 'همه محصولات',
        'load_more' => $blocks['load_more'] ?? 'نمایش محصولات بیشتر',
        'load_less' => $blocks['load_less'] ?? 'نمایش محصولات کمتر',
        'count_template' => $blocks['count_template'] ?? 'نمایش {shown} از {total} محصول',
        'load_error' => $blocks['load_error'] ?? 'بارگذاری محصولات با خطا مواجه شد.',
    ];
@endphp
<x-layouts.app :page="$page" cms-page="services" body-class="body-wrapper body-digital-agency body-products">
  <x-slot:styles>
    <link rel="stylesheet" href="{{ asset('assets/css/products.css') }}?v=1.3">
  </x-slot:styles>
  <x-slot:scripts>
    <script src="{{ asset('assets/js/products.js') }}?v=2.4"></script>
  </x-slot:scripts>
  <script type="application/json" id="alwin-page-copy">{!! json_encode($pageCopy, JSON_UNESCAPED_UNICODE) !!}</script>

  <section class="products-hero-area">
    <div class="container large">
      <div class="products-hero-inner section-spacing-top">
        <div class="section-header fade-anim">
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h1 class="section-title" data-cms="hero_title">{{ $blocks['hero_title'] ?? 'محصولات آلوین' }}</h1>
            </div>
          </div>
          <div class="text-wrapper">
            <p class="text" data-cms="hero_lead">{{ $blocks['hero_lead'] ?? 'تمام مدل‌های پنجره و درب دوجداره UPVC و توری‌های پنجره را در یک‌جا ببینید.' }}</p>
          </div>
        </div>
        <div class="products-hero-meta fade-anim" data-delay="0.2">
          <div class="products-hero-stat">
            <span class="num">{{ Site::fa($productCount) }}</span>
            <span class="lbl" data-cms="stat_products_label">{{ $blocks['stat_products_label'] ?? 'محصول' }}</span>
          </div>
          <div class="products-hero-stat">
            <span class="num" data-cms="stat_1_number">{{ $blocks['stat_1_number'] ?? '۱۵+' }}</span>
            <span class="lbl" data-cms="stat_1_label">{{ $blocks['stat_1_label'] ?? 'سال تجربه تولید' }}</span>
          </div>
          <div class="products-hero-stat">
            <span class="num" data-cms="stat_2_number">{{ $blocks['stat_2_number'] ?? '۵۰۰+' }}</span>
            <span class="lbl" data-cms="stat_2_label">{{ $blocks['stat_2_label'] ?? 'پروژه نصب‌شده' }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="products-filter-area">
    <div class="container large">
      <div class="products-filter fade-anim">
        <div class="products-tabs" id="productsTabs" role="tablist" aria-label="نوع محصول"></div>
        <div class="products-search">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          <input type="search" id="productsSearch" placeholder="{{ $blocks['search_placeholder'] ?? 'جستجو در مدل‌ها، مثلاً «کشویی» و ...' }}" autocomplete="off">
        </div>
      </div>
      <div class="products-chips" id="productsChips" role="tablist" aria-label="دسته محصول"></div>
    </div>
  </section>

  <section class="products-grid-area">
    <div class="container large">
      <div class="products-grid-area-inner section-spacing-bottom">
        <div class="products-grid-header">
          <p class="products-result-count" id="productsCount"></p>
        </div>
        <div class="products-grid" id="productsGrid"></div>
        <p class="products-empty" id="productsEmpty" hidden>{{ $blocks['empty_text'] ?? 'موردی با این مشخصات پیدا نشد. جستجوی دیگری را امتحان کنید یا فیلترها را پاک کنید.' }}</p>
        <div class="products-loadmore-wrap" id="productsLoadMoreWrap" hidden>
          <button type="button" class="rr-btn btn-border hover-bg-theme" id="productsLoadMore">
            <span class="btn-wrap">
              <span class="text-one">{{ $blocks['load_more'] ?? 'نمایش محصولات بیشتر' }}</span>
              <span class="text-two">{{ $blocks['load_more'] ?? 'نمایش محصولات بیشتر' }}</span>
            </span>
          </button>
        </div>
      </div>
    </div>
  </section>

  <section class="trust-badges-area">
    <div class="container large">
      <div class="trust-badges-area-inner section-spacing">
        <div class="section-header fade-anim">
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h2 class="section-title" style="margin-bottom: 50px;" data-cms="trust_title">{{ $blocks['trust_title'] ?? 'چرا آلوین؟' }}</h2>
            </div>
          </div>
        </div>
        <div class="trust-badges-wrapper fade-anim">
          <div class="trust-badge">
            <span class="icon"><i class="fa-solid fa-award"></i></span>
            <h3 data-cms="trust_1_title">{{ $blocks['trust_1_title'] ?? 'پروفیل معتبر اروپایی' }}</h3>
            <p data-cms="trust_1_text">{{ $blocks['trust_1_text'] ?? 'تولید با پروفیل‌های ویستابست، وینتک، پلاس پن و وین پلاس با استاندارد اروپایی.' }}</p>
          </div>
          <div class="trust-badge">
            <span class="icon"><i class="fa-solid fa-gears"></i></span>
            <h3 data-cms="trust_2_title">{{ $blocks['trust_2_title'] ?? 'یراق‌آلات آلمانی و ترک' }}</h3>
            <p data-cms="trust_2_text">{{ $blocks['trust_2_text'] ?? 'امکان انتخاب یراق‌آلات آلمانی یا ترک متناسب با بودجه و نیاز شما.' }}</p>
          </div>
          <div class="trust-badge">
            <span class="icon"><i class="fa-solid fa-shield-halved"></i></span>
            <h3 data-cms="trust_3_title">{{ $blocks['trust_3_title'] ?? 'گارانتی و خدمات پس از فروش' }}</h3>
            <p data-cms="trust_3_text">{{ $blocks['trust_3_text'] ?? 'پشتیبانی، گارانتی معتبر محصول و خدمات تعمیر و نگهداری در سراسر کشور.' }}</p>
          </div>
          <div class="trust-badge">
            <span class="icon"><i class="fa-solid fa-user-check"></i></span>
            <h3 data-cms="trust_4_title">{{ $blocks['trust_4_title'] ?? 'بازدید و مشاوره رایگان' }}</h3>
            <p data-cms="trust_4_text">{{ $blocks['trust_4_text'] ?? 'کارشناسان ما پیش از سفارش، رایگان بازدید و ابعاد دقیق را اندازه‌گیری می‌کنند.' }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-layouts.app>
