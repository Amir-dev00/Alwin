<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_it_keeps_safe_markup_and_strips_scripts(): void
    {
        $html = HtmlSanitizer::clean('<p>سلام <strong>آلوین</strong><script>alert(1)</script><a href="javascript:alert(1)">x</a></p>');
        $this->assertStringContainsString('<strong>آلوین</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }
}
