<?php

namespace Keepsuit\Liquid\Tags;

use Keepsuit\Liquid\Context;
use Keepsuit\Liquid\ContinueInterrupt;
use Keepsuit\Liquid\Tag;

class ContinueTag extends Tag
{
    public static function tagName(): string
    {
        return 'continue';
    }

    public function render(Context $context): string
    {
        $context->pushInterrupt(new ContinueInterrupt());

        return '';
    }
}