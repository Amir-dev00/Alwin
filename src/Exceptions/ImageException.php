<?php

namespace App\Exceptions;

use RuntimeException;

class ImageException extends RuntimeException
{
    public static function invalid(string $message = 'فایل تصویر معتبر نیست.'): self
    {
        return new self($message);
    }

    public static function unsupported(): self
    {
        return new self('این نوع تصویر پشتیبانی نمی‌شود.');
    }

    public static function tooLarge(): self
    {
        return new self('حجم یا ابعاد تصویر بیش از حد مجاز است.');
    }

    public static function processingFailed(): self
    {
        return new self('پردازش تصویر ناموفق بود.');
    }
}
