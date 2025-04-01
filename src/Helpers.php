<?php
namespace Arhx\Builder;

use Illuminate\Support\Str;

class Helpers{
    public static function nowrap(string $text): string
    {
        return Str::replace([' ','-'],[' ','‑'], $text);
    }
}
