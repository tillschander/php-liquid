<?php

namespace Keepsuit\Liquid;

use Keepsuit\Liquid\Contracts\CanBeRendered;
use Keepsuit\Liquid\Exceptions\SyntaxException;
use Keepsuit\Liquid\Parser\Tokenizer;

class Document implements CanBeRendered
{
    public function __construct(
        protected readonly ParseContext $parseContext,
        protected BlockBodySection $body,
    ) {
    }

    public static function parse(Tokenizer $tokenizer, ParseContext $parseContext): Document
    {
        try {
            $bodySections = BlockParser::forDocument()->parse($tokenizer, $parseContext);
        } catch (SyntaxException $exception) {
            if (in_array($exception->tagName, ['else', 'end'])) {
                $exception = SyntaxException::unexpectedOuterTag($parseContext, $exception->tagName ?? '');
            }

            $exception->lineNumber = $parseContext->lineNumber;

            throw $exception;
        }

        return new Document(
            parseContext: $parseContext,
            body: $bodySections[0] ?? new BlockBodySection()
        );
    }

    /**
     * @return array<Tag|Variable|string>
     */
    public function nodeList(): array
    {
        return $this->body->nodeList();
    }

    public function render(Context $context): string
    {
        return $this->body->render($context);
    }
}
