@php $v = '4.8'; @endphp
<script src="{{ asset('assets/vendor/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('assets/vendor/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/gsap.min.js') }}"></script>
<script src="{{ asset('assets/vendor/ScrollTrigger.min.js') }}"></script>
<script src="{{ asset('assets/vendor/ScrollSmoother.min.js') }}"></script>
<script src="{{ asset('assets/vendor/ScrollToPlugin.min.js') }}"></script>
<script src="{{ asset('assets/vendor/SplitText.min.js') }}"></script>
<script src="{{ asset('assets/vendor/TextPlugin.js') }}"></script>
<script src="{{ asset('assets/vendor/customEase.js') }}"></script>
<script src="{{ asset('assets/vendor/Flip.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery.meanmenu.min.js') }}"></script>
<script src="{{ asset('assets/vendor/backToTop.js') }}"></script>
<script src="{{ asset('assets/vendor/matter.js') }}"></script>
<script>
  (function () {
    if (!window.matchMedia("(max-width: 1199px)").matches) return;
    document.querySelectorAll("[data-t-throwable-scene]").forEach(function (el) {
      el.removeAttribute("data-t-throwable-scene");
      el.querySelectorAll("[data-t-throwable-el]").forEach(function (item) {
        item.style.transform = "";
        item.style.opacity = "";
      });
    });
  })();
</script>
<script src="{{ asset('assets/vendor/throwable.js') }}"></script>
<script src="{{ asset('assets/js/alwin-init.js') }}?v={{ $v }}"></script>
<script src="{{ asset('assets/js/main.js') }}?v={{ $v }}"></script>
<script src="{{ asset('assets/js/alwin-preloader.js') }}?v={{ $v }}"></script>
<script>
window.ALWIN_CONFIG = {
  apiUrl: @json(rtrim((string) config('alwin.api_url', '/api/v1'), '/')),
  copy: @json($calculatorCopy ?? []),
  pageCopy: @json($pageCopy ?? new \stdClass())
};
</script>
<script src="{{ asset('assets/js/config.js') }}?v={{ $v }}"></script>
<script src="{{ asset('assets/js/pricing-data.js') }}?v=2.1"></script>
<script src="{{ asset('assets/js/pricing-engine.js') }}?v=1.0"></script>
<script src="{{ asset('assets/js/calculator.js') }}?v=2.2"></script>
<script src="{{ asset('assets/js/cms-client.js') }}?v={{ $v }}"></script>
{{ $extraScripts ?? $slot ?? '' }}
