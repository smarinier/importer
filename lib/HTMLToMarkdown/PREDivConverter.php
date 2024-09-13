<?php

declare(strict_types=1);

namespace OCA\Importer\HTMLToMarkdown;

use League\HTMLToMarkdown\Converter\DivConverter;
use League\HTMLToMarkdown\ElementInterface;

class PREDivConverter extends DivConverter
{
    public function convert(ElementInterface $element): string
    {
        if ($this->config->getOption('strip_tags', false)) {
            return $element->getValue() . "\n";
        }

        return parent::convert($element);
    }
}
