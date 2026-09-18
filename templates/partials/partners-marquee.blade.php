@if($partners->isNotEmpty())
  <section class="brand-clients" aria-labelledby="brand-clients-heading">
    <div class="container large">
      <div class="section-header brand-clients__header fade-anim">
        <div class="section-title-wrapper">
          <div class="title-wrapper">
            <h2 class="section-title brand-clients__title" id="brand-clients-heading" data-cms="clients_marquee_title">{{ $blocks['clients_marquee_title'] ?? 'برخی از مشتریان ما' }}</h2>
          </div>
        </div>
      </div>
    </div>
    <div class="text-slider-box brand-logo-marquee fade-anim">
      <div class="text-slider">
        <div class="swiper text-slider-active">
          <div class="swiper-wrapper">
            @foreach([1, 2] as $loopPass)
              @foreach($partners as $partner)
                <div class="swiper-slide">
                  <div class="text-slider-item brand-logo-item">
                    <img src="{{ $partner->image?->publicUrl() }}" alt="{{ $loopPass === 2 ? '' : ($partner->alt_text ?: $partner->name) }}" width="180" height="72" loading="lazy" decoding="async" @if($loopPass === 2) aria-hidden="true" @endif>
                  </div>
                </div>
              @endforeach
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>
@endif
