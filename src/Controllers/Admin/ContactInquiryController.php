<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->isEditor(), 403);

        $status = $request->string('status')->toString();
        $q = ContactInquiry::query()->latest();
        if (in_array($status, [ContactInquiry::STATUS_NEW, ContactInquiry::STATUS_DONE], true)) {
            $q->where('status', $status);
        }

        return view('admin.inquiries.index', [
            'items' => $q->paginate(30)->withQueryString(),
            'newCount' => ContactInquiry::query()->where('status', ContactInquiry::STATUS_NEW)->count(),
            'status' => $status,
        ]);
    }

    public function toggle(ContactInquiry $inquiry): RedirectResponse
    {
        abort_unless(auth()->user()?->isEditor(), 403);

        $inquiry->status = $inquiry->isNew()
            ? ContactInquiry::STATUS_DONE
            : ContactInquiry::STATUS_NEW;
        $inquiry->save();

        return redirect()
            ->route('admin.inquiries.index')
            ->with('success', $inquiry->isNew() ? 'درخواست به حالت جدید برگشت.' : 'درخواست انجام‌شده علامت خورد.');
    }
}
