@props([
    'title' => null,
    'description' => null,
    'bodyClass' => 'body-wrapper body-digital-agency',
    'cmsPage' => null,
    'page' => null,
    'jsonLd' => null,
])
@php
    use App\Support\Site;
    $settings = $siteSettings ?? Site::settings();
    $title = $title ?: ($page?->seo_title ?? ($settings['site_name'] ?? 'ALWIN'));
    $description = $description ?: ($page?->seo_description ?? ($settings['seo_description_default'] ?? 'ALWIN — تولید و نصب پنجره دوجداره UPVC'));
    $cmsPage = $cmsPage ?: ($page?->key ?? null);
    $assetV = '5.2';
    $logoUrl = Site::mediaUrl($settings['logo'] ?? null, 'assets/img/brand/logo.png');
    $faviconUrl = Site::mediaUrl($settings['favicon'] ?? null, 'assets/imgs/logo/favicon.png');
    $ogImage = Site::mediaUrl($settings['og_image'] ?? ($settings['logo'] ?? null), 'assets/img/brand/logo.png');
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl" @if($cmsPage) data-cms-page="{{ $cmsPage }}" @endif>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="description" content="{{ $description }}">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $description }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <title>{{ $title }}</title>
  @if($jsonLd)
    <script type="application/ld+json">{!! $jsonLd !!}</script>
  @endif
  {{ $head ?? '' }}
  <link rel="canonical" href="{{ url()->current() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=block" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/swiper-bundle.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/meanmenu.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/magnific-popup.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/animate.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ $assetV }}">
  <link rel="stylesheet" href="{{ asset('assets/css/alwin.css') }}?v={{ $assetV }}">
  {{ $styles ?? '' }}
  <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}?v={{ $assetV }}">
</head>
<body class="{{ $bodyClass }}">
  @include('partials.chrome')
  @include('partials.sidebar')
  @include('partials.header')

  <div class="has-smooth" id="has_smooth"></div>
  <div id="smooth-wrapper">
    <div id="smooth-content">
      <main>
        {{ $slot }}
      </main>
      @include('partials.footer')
    </div>
  </div>

  @include('partials.calculator')
  @include('partials.scripts', ['extraScripts' => $scripts ?? ''])
</body>
</html>
