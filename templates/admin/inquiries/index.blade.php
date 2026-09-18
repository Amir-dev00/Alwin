@extends('admin.layout')
@section('title', 'سفارش‌ها')
@section('content')
  <div class="page-head fly-item">
    <div>
      <p class="page-kicker">درخواست مشتری</p>
      <h1>سفارش‌ها و درخواست تماس</h1>
      <p>فرم تماس سایت و درخواست تماس صفحه درباره ما اینجا جمع می‌شود. موارد جدید را تا تماس با مشتری باز بگذارید.</p>
    </div>
    @if($newCount)
      <div class="page-head-actions">
        <span class="btn btn-ghost">{{ $newCount }} درخواست جدید</span>
      </div>
    @endif
  </div>

  <form class="filters filters-bar fly-item" method="get" style="--i:1">
    <div class="filters-main">
      <select name="status">
        <option value="">همه</option>
        <option value="new" @selected($status === 'new')>جدید</option>
        <option value="done" @selected($status === 'done')>انجام‌شده</option>
      </select>
    </div>
    <button class="btn btn-ghost" type="submit">اعمال فیلتر</button>
  </form>

  <div class="table-wrap fly-item" style="--i:2">
    <table class="data-table">
      <thead>
        <tr>
          <th>زمان</th>
          <th>مشتری</th>
          <th>موضوع</th>
          <th>منبع</th>
          <th>پیام</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr class="{{ $item->isNew() ? 'is-new' : '' }}">
            <td>
              <span class="cell-muted">{{ $item->created_at?->timezone('Asia/Tehran')->format('Y/m/d H:i') }}</span>
              @if($item->isNew())
                <small class="inquiry-pill">جدید</small>
              @endif
            </td>
            <td>
              <strong>{{ $item->name }}</strong>
              <small dir="ltr"><a href="tel:{{ $item->phone }}">{{ $item->phone }}</a></small>
              @if($item->email)
                <small><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></small>
              @endif
            </td>
            <td>{{ $item->subjectLabel() }}</td>
            <td><span class="cell-muted">{{ $item->sourceLabel() }}</span></td>
            <td>{{ $item->message ? \Illuminate\Support\Str::limit($item->message, 90) : '—' }}</td>
            <td class="col-actions">
              <form method="post" action="{{ route('admin.inquiries.toggle', $item) }}">
                @csrf
                <button class="btn btn-sm {{ $item->isNew() ? 'btn-ghost' : 'btn-quiet' }}" type="submit">
                  {{ $item->isNew() ? 'علامت انجام' : 'بازگشت به جدید' }}
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6"><div class="empty empty-soft">هنوز درخواستی ثبت نشده.</div></td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links() }}
@endsection
