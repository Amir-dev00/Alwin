<x-layouts.app :page="$page" cms-page="articles" body-class="body-wrapper body-digital-agency">
  <x-slot:styles>
    <link rel="stylesheet" href="{{ asset('assets/css/articles.css') }}?v=1.6">
  </x-slot:styles>

  <section class="page-title-area articles-hero">
    <div class="container large">
      <div class="page-title-area-inner section-spacing-top">
        <nav class="articles-breadcrumb" aria-label="مسیر صفحه">
          <a href="{{ route('home') }}">{{ $blocks['crumb_home'] ?? 'خانه' }}</a>
          <span aria-hidden="true">/</span>
          <span>{{ $blocks['crumb_current'] ?? 'مقالات' }}</span>
        </nav>
        <div class="page-title-wrapper">
          <h1 class="section-title" data-cms="hero_title">{{ $blocks['hero_title'] ?? 'مقالات آلوین' }}</h1>
          <p class="articles-hero__lead" data-cms="hero_lead">{{ $blocks['hero_lead'] ?? 'راهنماها و نکات تخصصی درباره پنجره دوجداره UPVC' }}</p>
        </div>
      </div>
    </div>
  </section>

  <section class="articles-listing section-spacing-bottom" style="padding-top: 1.5rem;">
    <div class="container large">
      <div class="articles-grid" data-total="{{ $articles->count() }}">
        @forelse($articles as $article)
          <article class="article-card" @if($loop->iteration > 9) hidden data-extra @endif>
            <a class="article-card__link" href="{{ route('articles.show', $article) }}">
              @if($article->coverUrl())
                <div class="article-card__thumb">
                  <img src="{{ $article->coverUrl() }}" alt="{{ $article->cover_alt ?: $article->title }}" width="640" height="360" loading="lazy" decoding="async">
                </div>
              @endif
              <div class="article-card__body">
                <span class="article-card__category">{{ $blocks['card_category'] ?? 'مقالات' }}</span>
                <h2 class="article-card__title">{{ $article->title }}</h2>
                <p class="article-card__excerpt">{{ $article->excerpt }}</p>
                @if($article->published_at)
                  <time class="article-card__date" datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->publishedLabel() }}</time>
                @endif
              </div>
            </a>
          </article>
        @empty
          <p>{{ $blocks['empty_text'] ?? 'مقاله‌ای منتشر نشده است.' }}</p>
        @endforelse
      </div>
      @if($articles->count() > 9)
        <div class="articles-more" data-articles-more-wrap>
          <button type="button" class="rr-btn hover-bg-theme" data-articles-more>
            <span class="btn-wrap">
              <span class="text-one">{{ $blocks['load_more'] ?? 'نمایش مقالات بیشتر' }}</span>
              <span class="text-two">{{ $blocks['load_more'] ?? 'نمایش مقالات بیشتر' }}</span>
            </span>
          </button>
        </div>
        <div class="articles-more" hidden data-articles-less-wrap>
          <button type="button" class="rr-btn hover-bg-theme" data-articles-less>
            <span class="btn-wrap">
              <span class="text-one">{{ $blocks['load_less'] ?? 'نمایش مقالات کمتر' }}</span>
              <span class="text-two">{{ $blocks['load_less'] ?? 'نمایش مقالات کمتر' }}</span>
            </span>
          </button>
        </div>
        <script>
          (function () {
            var extra = document.querySelectorAll('.articles-grid .article-card[data-extra]');
            var moreWrap = document.querySelector('[data-articles-more-wrap]');
            var lessWrap = document.querySelector('[data-articles-less-wrap]');
            var listing = document.querySelector('.articles-listing');
            document.querySelector('[data-articles-more]')?.addEventListener('click', function () {
              extra.forEach(function (card) { card.hidden = false; });
              if (moreWrap) moreWrap.hidden = true;
              if (lessWrap) lessWrap.hidden = false;
            });
            document.querySelector('[data-articles-less]')?.addEventListener('click', function () {
              extra.forEach(function (card) { card.hidden = true; });
              if (moreWrap) moreWrap.hidden = false;
              if (lessWrap) lessWrap.hidden = true;
              listing?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
          })();
        </script>
      @endif
    </div>
  </section>
</x-layouts.app>
