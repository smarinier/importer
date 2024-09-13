<?php

declare(strict_types=1);

namespace OCA\Importer\HTMLToMarkdown;

use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\Converter\ConverterInterface;

class PRETextConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        return $element->getValue();
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['#text'];
    }
}
