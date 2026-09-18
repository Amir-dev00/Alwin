@extends('admin.layout')
@section('title', 'صفحات')
@section('content')
  <div class="page-head">
    <div>
      <h1>محتوای صفحات</h1>
        <p>متن گوگل و بلوک‌های هر صفحه. صفحه اصلی را از منوی «صفحه اصلی» هم می‌توانید باز کنید.</p>
    </div>
  </div>
  <div class="card-grid">
    @foreach($pages as $page)
      <a class="panel-card" href="{{ route('admin.pages.edit', $page) }}">
        <strong>{{ $page->title }}</strong>
        <small>{{ $page->path }}</small>
        <span>{{ $page->blocks_count }} بخش قابل ویرایش</span>
      </a>
    @endforeach
  </div>
@endsection
