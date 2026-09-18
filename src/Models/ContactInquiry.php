<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_DONE = 'done';

    public const SUBJECTS = [
        'callback' => 'درخواست تماس',
        'quote' => 'درخواست قیمت / مشاوره',
        'visit' => 'بازدید فنی و اندازه‌گیری',
        'order' => 'پیگیری سفارش',
        'support' => 'پشتیبانی پس از نصب',
        'other' => 'سایر',
    ];

    public const SOURCES = [
        'about' => 'صفحه درباره ما',
        'contact' => 'صفحه تماس',
    ];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'subject',
        'message',
        'source',
        'status',
        'ip_address',
    ];

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? $this->subject;
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }
}
