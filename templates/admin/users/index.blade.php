@extends('admin.layout')
@section('title', 'کاربران')
@section('content')
  <div class="page-head">
    <div>
      <h1>کاربران پنل</h1>
      <p>ثبت‌نام عمومی وجود ندارد. فقط مدیر کل کاربر می‌سازد.</p>
    </div>
  </div>
  <div class="grid-2">
    <section class="card">
      <header><h2>کاربر جدید</h2></header>
      <form method="post" action="{{ route('admin.users.store') }}" class="stack">
        @csrf
        <label class="field"><span>نام</span><input name="name" required placeholder="مثلاً مریم احمدی"></label>
        <label class="field"><span>ایمیل</span><input type="email" name="email" required placeholder="name@alwinco.ir" dir="ltr"></label>
        <label class="field">
          <span>رمز عبور</span>
          <input type="password" name="password" minlength="10" required>
          <small class="field-help">حداقل ۱۰ حرف. ترکیبی از حرف و عدد بهتر است.</small>
        </label>
        <label class="field">
          <span>دسترسی</span>
          <select name="role">
            <option value="editor">ویراستار — محتوا را عوض می‌کند</option>
            <option value="super_admin">مدیر کل — کاربران را هم می‌سازد</option>
          </select>
        </label>
        <button class="btn btn-primary" type="submit">ایجاد</button>
      </form>
    </section>
    <section class="card">
      <header><h2>فهرست</h2></header>
      @foreach($items as $item)
        <form method="post" action="{{ route('admin.users.update', $item) }}" class="user-row">
          @csrf @method('put')
          <input name="name" value="{{ $item->name }}" required>
          <input type="email" name="email" value="{{ $item->email }}" required>
          <select name="role">
            <option value="editor" @selected($item->role==='editor')>ویراستار</option>
            <option value="super_admin" @selected($item->role==='super_admin')>مدیر کل</option>
          </select>
          <input type="password" name="password" placeholder="رمز جدید (اختیاری)" minlength="10">
          <label class="inline"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> فعال</label>
          <button class="btn btn-ghost" type="submit">ذخیره</button>
        </form>
      @endforeach
    </section>
  </div>
@endsection
