@extends('admin.layout')
@section('title', 'قیمت‌گذاری')
@section('content')
  @php
    $tabs = [
      'studio' => 'آزمایش‌گر',
      'profiles' => 'پروفیل',
      'glass' => 'شیشه',
      'hardware' => 'یراق',
      'models' => 'مدل‌ها',
      'settings' => 'تنظیمات',
      'leads' => 'استعلام‌ها',
    ];
    $priceMap = [];
    foreach ($brands as $brand) {
      foreach ($brand->prices as $p) {
        $priceMap[$brand->id][$p->component_key] = $p->price;
      }
    }
  @endphp

  <div class="page-head fly-item">
    <div>
      <p class="page-kicker">ماشین‌حساب سایت</p>
      <h1>موتور قیمت</h1>
      <p>نرخ پروفیل، شیشه و یراق از همین‌جا به محاسبه آنلاین وصل می‌شود. فرمول: مصرف پروفیل × نرخ متر طول + متراژ شیشه × نرخ متر مربع + تعداد یراق × نرخ واحد.</p>
    </div>
    <div class="page-head-actions">
      @if($leadCount)
        <a class="btn btn-ghost" href="{{ route('admin.pricing.index', ['tab' => 'leads']) }}">{{ $leadCount }} استعلام جدید</a>
      @endif
      <a class="btn btn-quiet" href="/" target="_blank" rel="noopener">پیش‌نمایش ماشین‌حساب</a>
    </div>
  </div>

  <nav class="price-tabs fly-item" style="--i:1" aria-label="بخش‌های قیمت‌گذاری">
    @foreach($tabs as $key => $label)
      <a class="{{ $tab === $key ? 'is-active' : '' }}" href="{{ route('admin.pricing.index', ['tab' => $key]) }}">
        {{ $label }}
        @if($key === 'leads' && $leadCount)
          <em>{{ $leadCount }}</em>
        @endif
      </a>
    @endforeach
  </nav>

  @if($tab === 'studio')
    @include('admin.pricing.partials.studio')
  @elseif($tab === 'profiles')
    @include('admin.pricing.partials.profiles', ['priceMap' => $priceMap])
  @elseif($tab === 'glass')
    @include('admin.pricing.partials.glass')
  @elseif($tab === 'hardware')
    @include('admin.pricing.partials.hardware')
  @elseif($tab === 'models')
    @include('admin.pricing.partials.models')
  @elseif($tab === 'settings')
    @include('admin.pricing.partials.settings')
  @else
    @include('admin.pricing.partials.leads')
  @endif
@endsection

@push('scripts')
  <script src="{{ asset('assets/admin/js/pricing.js') }}?v=1.0"></script>
@endpush
