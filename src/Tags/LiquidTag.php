<?php

namespace Keepsuit\Liquid\Tags;

use Keepsuit\Liquid\BlockBodySection;
use Keepsuit\Liquid\Context;
use Keepsuit\Liquid\Parser\BlockParser;
use Keepsuit\Liquid\Parser\Tokenizer;
use Keepsuit\Liquid\Tag;

class LiquidTag extends Tag
{
    /**
     * @var BlockBodySection[]
     */
    protected array $bodySections;

    public static function tagName(): string
    {
        return 'liquid';
    }

    public function parse(Tokenizer $tokenizer): static
    {
        $liquidTokenizer = $this->parseContext->newTokenizer(
            markup: $this->markup,
            startLineNumber: $this->lineNumber,
            forLiquidTag: true
        );

        $this->bodySections = BlockParser::forDocument()->parse($liquidTokenizer, $this->parseContext);

        return $this;
    }

    public function render(Context $context): string
    {
        $output = '';

        foreach ($this->bodySections as $bodySection) {
            $output .= $bodySection->render($context);
        }

        return $output;
    }
}
