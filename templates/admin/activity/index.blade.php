@extends('admin.layout')
@section('title', 'تاریخچه')
@section('content')
  <div class="page-head">
    <div>
      <h1>تاریخچه فعالیت مدیران</h1>
      <p>ورود، ویرایش محتوا، بارگذاری و حذف‌ها.</p>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>زمان</th><th>کاربر</th><th>عمل</th><th>توضیح</th><th>IP</th></tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td>{{ $item->created_at?->timezone('Asia/Tehran')->format('Y-m-d H:i') }}</td>
            <td>{{ $item->user?->name ?: 'سیستم' }}</td>
            <td>{{ $item->action }}</td>
            <td>{{ $item->description }}</td>
            <td dir="ltr">{{ $item->ip_address }}</td>
          </tr>
        @empty
          <tr><td colspan="5"><div class="empty">سابقه‌ای نیست.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links() }}
@endsection
