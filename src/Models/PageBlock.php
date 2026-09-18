<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    protected $fillable = ['page_id', 'key', 'type', 'label', 'value', 'sort_order'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function decodedValue(): mixed
    {
        if (in_array($this->type, ['json', 'repeater', 'list'], true)) {
            $decoded = json_decode((string) $this->value, true);
            return is_array($decoded) ? $decoded : [];
        }
        if ($this->type === 'html') {
            return HtmlSanitizer::clean($this->value);
        }
        return $this->value;
    }
}
