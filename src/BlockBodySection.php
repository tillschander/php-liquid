<?php

namespace Keepsuit\Liquid;

class BlockBodySection
{
    public function __construct(
        protected ?BlockBodySectionDelimiter $start = null,
        protected ?BlockBodySectionDelimiter $end = null,
        /** @var array<Tag|Variable|string> */
        protected array $nodeList = [],
    ) {
    }

    public function startDelimiter(): ?BlockBodySectionDelimiter
    {
        return $this->start;
    }

    public function endDelimiter(): ?BlockBodySectionDelimiter
    {
        return $this->end;
    }

    /**
     * @return array<Tag|Variable|string>
     */
    public function nodeList(): array
    {
        return $this->nodeList;
    }

    public function setStart(?BlockBodySectionDelimiter $start): BlockBodySection
    {
        $this->start = $start;

        return $this;
    }

    public function setEnd(?BlockBodySectionDelimiter $end): BlockBodySection
    {
        $this->end = $end;

        return $this;
    }

    public function pushNode(Variable|Tag|string $node): BlockBodySection
    {
        $this->nodeList[] = $node;

        return $this;
    }

    /**
     * @param  array<Tag|Variable|string>  $nodeList
     */
    public function setNodeList(array $nodeList): BlockBodySection
    {
        $this->nodeList = $nodeList;

        return $this;
    }
}