<?php

namespace Keepsuit\Liquid\Tags;

use Keepsuit\Liquid\BlockBodySection;
use Keepsuit\Liquid\Condition;
use Keepsuit\Liquid\Context;
use Keepsuit\Liquid\ElseCondition;
use Keepsuit\Liquid\HasParseTreeVisitorChildren;
use Keepsuit\Liquid\Parser;
use Keepsuit\Liquid\ParserSwitching;
use Keepsuit\Liquid\TagBlock;
use Keepsuit\Liquid\Tokenizer;
use Keepsuit\Liquid\TokenType;

class IfTag extends TagBlock implements HasParseTreeVisitorChildren
{
    use ParserSwitching;

    /** @var Condition[] */
    protected array $conditions = [];

    public static function tagName(): string
    {
        return 'if';
    }

    public function parse(Tokenizer $tokenizer): static
    {
        parent::parse($tokenizer);

        $this->conditions = array_map(fn (BlockBodySection $block) => $this->parseBodySection($block), $this->bodySections);

        return $this;
    }

    public function nodeList(): array
    {
        return array_map(fn (Condition $block) => $block->attachment, $this->conditions);
    }

    protected function isSubTag(string $tagName): bool
    {
        return in_array($tagName, ['else', 'elsif'], true);
    }

    protected function parseBodySection(BlockBodySection $section): Condition
    {
        assert($section->startDelimiter() !== null);

        $condition = match (true) {
            $section->startDelimiter()->tag === 'else' => new ElseCondition(),
            default => $this->strictParseWithErrorModeFallback($section->startDelimiter()->markup, $this->parseContext),
        };

        assert($condition instanceof Condition);

        $condition->attach($section);

        return $condition;
    }

    protected function strictParse(string $markup): mixed
    {
        $parser = new Parser($markup);

        $condition = $this->parseBinaryComparison($parser);
        $parser->consume(TokenType::EndOfString);

        return $condition;
    }

    protected function laxParse(string $markup): mixed
    {
        throw new \RuntimeException('Not implemented');
    }

    protected function parseBinaryComparison(Parser $parser): Condition
    {
        $condition = $this->parseComparison($parser);
        $firstCondition = $condition;

        while ($operator = $parser->idOrFalse('and') ?: $parser->idOrFalse('or')) {
            $childCondition = $this->parseComparison($parser);
            $condition->{$operator}($childCondition);
            $condition = $childCondition;
        }

        return $firstCondition;
    }

    protected function parseComparison(Parser $parser): Condition
    {
        $a = $this->parseExpression($parser->expression());

        if ($operator = $parser->consumeOrFalse(TokenType::Comparison)) {
            $b = $this->parseExpression($parser->expression());

            return new Condition($a, $operator, $b);
        } else {
            return new Condition($a);
        }
    }

    public function parseTreeVisitorChildren(): array
    {
        return $this->conditions;
    }

    public function render(Context $context): string
    {
        $output = '';
        foreach ($this->conditions as $condition) {
            $result = $condition->evaluate($context);

            if ($result) {
                return $condition->attachment?->render($context) ?? '';
            }
        }

        return $output;
    }
}
