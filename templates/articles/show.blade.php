@php
    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $article->title,
        'description' => $article->excerpt,
        'datePublished' => optional($article->published_at)?->toIso8601String(),
        'dateModified' => optional($article->updated_at)?->toIso8601String(),
        'author' => ['@type' => 'Organization', 'name' => $article->author ?: 'آلوین'],
        'mainEntityOfPage' => route('articles.show', $article),
        'image' => $article->coverUrl() ? url($article->coverUrl()) : null,
        'publisher' => ['@type' => 'Organization', 'name' => 'آلوین پنجره', 'url' => url('/')],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<x-layouts.app
  :title="$article->seo_title ?: $article->title"
  :description="$article->seo_description ?: $article->excerpt"
  :json-ld="$jsonLd"
  :page="$page ?? null"
  cms-page="articles"
  body-class="body-wrapper body-page-inner body-digital-agency"
>
  <x-slot:styles>
    <link rel="stylesheet" href="{{ asset('assets/css/articles.css') }}?v=1.4">
  </x-slot:styles>

  <section class="article-detail section-spacing-top">
    <div class="container">
      <nav class="articles-breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ route('home') }}">{{ $blocks['crumb_home'] ?? 'خانه' }}</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('articles.index') }}">{{ $blocks['crumb_current'] ?? 'مقالات' }}</a>
        <span aria-hidden="true">/</span>
        <span>{{ $article->title }}</span>
      </nav>
      <header class="article-detail__header">
        <span class="article-card__category">{{ $blocks['card_category'] ?? 'مقالات' }}</span>
        <h1 class="article-detail__title">{{ $article->title }}</h1>
        <div class="article-detail__meta">
          @if($article->published_at)
            <time datetime="{{ $article->published_at->toIso8601String() }}">{{ $blocks['published_label'] ?? 'انتشار:' }} {{ $article->publishedLabel() }}</time>
          @endif
          @if($article->author)
            <span>{{ $blocks['author_label'] ?? 'نویسنده:' }} {{ $article->author }}</span>
          @endif
        </div>
      </header>
      @if($article->coverUrl())
        <div class="article-detail__cover">
          <img src="{{ $article->coverUrl() }}" alt="{{ $article->cover_alt ?: $article->title }}">
        </div>
      @endif
      <div class="article-content">
        {!! $article->body !!}
      </div>
      <p class="article-back"><a href="{{ route('articles.index') }}">{{ $blocks['back_label'] ?? 'بازگشت به مقالات' }}</a></p>

      @if($more->isNotEmpty())
        <aside class="article-more" style="margin-top:3rem">
          <h2 class="about-section-title">{{ $blocks['related_title'] ?? 'مقالات مرتبط' }}</h2>
          <div class="articles-grid" style="margin-top:1.5rem">
            @foreach($more as $item)
              <article class="article-card">
                <a class="article-card__link" href="{{ route('articles.show', $item) }}">
                  @if($item->coverUrl())
                    <div class="article-card__thumb">
                      <img src="{{ $item->coverUrl() }}" alt="{{ $item->title }}" width="640" height="360" loading="lazy">
                    </div>
                  @endif
                  <div class="article-card__body">
                    <h2 class="article-card__title">{{ $item->title }}</h2>
                  </div>
                </a>
              </article>
            @endforeach
          </div>
        </aside>
      @endif
    </div>
  </section>
</x-layouts.app>
