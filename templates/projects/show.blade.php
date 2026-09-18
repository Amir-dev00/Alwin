<x-layouts.app
  :title="$project->seo_title ?: ($project->title.' | ALWIN')"
  :description="$project->seo_description ?: strip_tags((string) $project->summary)"
  :page="$page ?? null"
  cms-page="project"
  body-class="body-wrapper body-digital-agency body-project-detail"
>
  <x-slot:styles>
    <link rel="stylesheet" href="{{ asset('assets/css/project-detail.css') }}?v=1.4">
  </x-slot:styles>

  <section class="project-details-area">
    <div class="project-details-area-inner">
      <div class="container large">
        <nav class="articles-breadcrumb" aria-label="{{ $blocks['crumb_aria'] ?? 'مسیر صفحه' }}" style="padding-top: 2rem;">
          <a href="{{ route('home') }}">{{ $blocks['crumb_home'] ?? 'خانه' }}</a>
          <span aria-hidden="true">/</span>
          <a href="{{ route('projects.index') }}">{{ $blocks['crumb_projects'] ?? 'پروژه‌ها' }}</a>
          <span aria-hidden="true">/</span>
          <span>{{ $project->title }}</span>
        </nav>
        <div class="section-header fade-anim">
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h1 class="section-title">{{ $project->title }}</h1>
              @if($project->type_label)
                <span class="project-type-tag">{{ $project->type_label }}</span>
              @endif
            </div>
          </div>
        </div>
        <div class="meta-wrapper fade-anim">
          @if($project->location)<span>{{ $project->location }}</span>@endif
          @if($project->client_name)<span>{{ $project->client_name }}</span>@endif
        </div>
      </div>

      @if($project->image)
        <div class="image-wrapper parallax-view fade-anim project-detail-hero-image">
          <picture>
            <source type="image/webp" srcset="{{ $project->image->publicUrl() }}">
            <img class="w-100" src="{{ $project->image->publicUrl() }}" alt="{{ $project->alt_text ?: $project->title }}" data-speed="0.8" width="1920" height="900">
          </picture>
        </div>
      @endif

      <div class="container large">
        <div class="section-info fade-anim">
          <div class="title-wrapper">
            <h2 class="title">{{ $project->summary ? ($blocks['summary_title'] ?? 'شرح پروژه') : $project->title }}</h2>
          </div>
          <div class="content">
            <div class="text-wrapper">
              @if($project->description)
                {!! $project->description !!}
              @elseif($project->summary)
                {!! $project->summary !!}
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="container large">
        <div class="pagination fade-anim">
          @if($prev)
            <a href="{{ route('projects.show', $prev) }}" class="project-nav-prev">
              <span>{{ $blocks['nav_prev'] ?? 'پروژه قبلی' }}<br><small class="label">{{ $prev->title }}</small></span>
            </a>
          @else
            <span></span>
          @endif
          <a href="{{ route('projects.index') }}">{{ $blocks['nav_all'] ?? 'همه پروژه‌ها' }}</a>
          @if($next)
            <a href="{{ route('projects.show', $next) }}" class="project-nav-next">
              <span>{{ $blocks['nav_next'] ?? 'پروژه بعدی' }}<br><small class="label">{{ $next->title }}</small></span>
            </a>
          @else
            <span></span>
          @endif
        </div>
      </div>
    </div>
  </section>

  <div class="p-relative overflow-hidden">
    <section class="cta-area">
      <div class="cta-area-inner section-spacing">
        <div class="area-bg"></div>
        <div class="container large">
          <div class="section-content">
            <div class="section-title-wrapper">
              <div class="title-wrapper">
                <h2 class="section-title font-instrumentsans-medium"><a href="#" data-calculator-open>{!! $blocks['cta_html'] ?? 'محاسبه<br>قیمت' !!}</a></h2>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</x-layouts.app>
