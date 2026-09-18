@once
  @push('boot')
    <script>
      window.__ADMIN_MEDIA__ = {!! 
        json_encode(
          ($media ?? collect())->map(function($m) {
            return [
              'id' => $m->id,
              'url' => $m->publicUrl(),
              'thumb_url' => $m->thumbnailUrl(),
              'name' => $m->original_name,
              'kind' => $m->kind,
              'alt' => $m->alt,
            ];
          })->values()->all(),
          JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        )
      !!};
 
 
      window.__ADMIN_MEDIA_UPLOAD__ = @json(route('admin.media.store'));
    </script>
  @endpush
@endonce
