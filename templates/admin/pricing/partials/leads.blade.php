<section class="card fly-item" style="--i:2">
  <header>
    <div>
      <h2>استعلام‌های ماشین‌حساب</h2>
      <p>درخواست‌هایی که مشتری پس از برآورد قیمت ثبت کرده است.</p>
    </div>
  </header>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>زمان</th>
          <th>مشتری</th>
          <th>مدل</th>
          <th>ابعاد</th>
          <th>برآورد</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads as $lead)
          <tr>
            <td><span class="cell-muted">{{ $lead->created_at?->timezone('Asia/Tehran')->format('Y/m/d H:i') }}</span></td>
            <td>
              <strong>{{ $lead->name }}</strong>
              <small dir="ltr">{{ $lead->phone }}</small>
            </td>
            <td>{{ $lead->model_name ?: '—' }}</td>
            <td>
              <span class="cell-muted">
                {{ $lead->width_cm ?: '—' }} × {{ $lead->height_cm ?: '—' }}
                @if($lead->quantity > 1) · {{ $lead->quantity }} عدد @endif
              </span>
            </td>
            <td><strong>{{ $lead->estimate ? number_format($lead->estimate).' تومان' : '—' }}</strong></td>
            <td class="col-actions">
              <form method="post" action="{{ route('admin.pricing.leads.toggle', $lead) }}">
                @csrf
                <button class="btn btn-sm {{ $lead->status === 'done' ? 'btn-quiet' : 'btn-ghost' }}" type="submit">
                  {{ $lead->status === 'done' ? 'انجام شد' : 'علامت انجام' }}
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6"><div class="empty empty-soft">هنوز استعلامی ثبت نشده.</div></td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>
