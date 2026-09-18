@php
  $name = $name ?? 'image_id';
  $label = $label ?? 'تصویر';
  $value = old($name, $value ?? '');
  $preview = $preview ?? '';
  $filename = $filename ?? '';
  $accept = $accept ?? '.webp,.jpg,.jpeg,.png,.gif,.bmp,image/webp,image/jpeg,image/png,image/gif,image/bmp';
  $collection = $collection ?? 'media';
  $hint = $hint ?? 'فایل را بکشید و اینجا رها کنید';
  $help = $help ?? 'WebP، JPG یا PNG · حداکثر ۵۰ مگابایت';
  $badge = $badge ?? null;
  $required = !empty($required);
  if ($value && isset($media)) {
      $found = $media->firstWhere('id', (int) $value);
      if ($found) {
          $preview = $found->publicUrl();
          $filename = $found->original_name;
      }
  }
@endphp
<div
  class="media-picker"
  data-media-picker
  data-upload-url="{{ route('admin.media.store') }}"
  data-collection="{{ $collection }}"
  data-accept="{{ $accept }}"
>
  <div class="media-picker__head">
    <span class="media-picker__title">{{ $label }}</span>
    @if($badge)
      <span class="media-picker__badge">{{ $badge }}</span>
    @endif
  </div>

  <input
    type="hidden"
    name="{{ $name }}"
    value="{{ $value }}"
    @if($required) required @endif
    data-media-value
  >

  <div class="media-picker__stage{{ $preview ? ' has-file' : '' }}" data-media-stage tabindex="0" role="button" aria-label="{{ $label }}">
    <input type="file" accept="{{ $accept }}" hidden data-media-file>

    <div class="media-picker__empty" data-media-empty {{ $preview ? 'hidden' : '' }}>
      <div class="media-picker__icon" aria-hidden="true">
        <svg viewBox="0 0 48 48" width="40" height="40" fill="none">
          <rect x="6" y="10" width="36" height="28" rx="6" stroke="currentColor" stroke-width="2"/>
          <path d="M16 28l6-7 5 6 4-4 7 9H16z" fill="currentColor" opacity=".35"/>
          <circle cx="18" cy="18" r="2.5" fill="currentColor"/>
          <path d="M24 4v8M20 8h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <strong>{{ $hint }}</strong>
      <small>{{ $help }}</small>
      <div class="media-picker__actions">
        <button type="button" class="btn btn-primary btn-sm" data-media-browse-file>انتخاب فایل</button>
        <button type="button" class="btn btn-ghost btn-sm" data-media-open-library>از کتابخانه</button>
      </div>
    </div>

    <div class="media-picker__filled" data-media-filled {{ $preview ? '' : 'hidden' }}>
      <div class="media-picker__preview-wrap">
        <img src="{{ $preview }}" alt="" data-media-preview>
      </div>
      <div class="media-picker__meta">
        <strong data-media-name>{{ $filename ?: 'تصویر انتخاب‌شده' }}</strong>
        <div class="media-picker__actions">
          <button type="button" class="btn btn-ghost btn-sm" data-media-browse-file>تعویض فایل</button>
          <button type="button" class="btn btn-ghost btn-sm" data-media-open-library>کتابخانه</button>
          <button type="button" class="btn btn-quiet btn-sm" data-media-clear>پاک کردن</button>
        </div>
      </div>
    </div>

    <div class="media-picker__progress" hidden data-media-progress>
      <span></span>
    </div>
    <p class="media-picker__error" hidden data-media-error></p>
  </div>
</div>
