<?php

declare(strict_types=1);

namespace OCA\Importer\HTMLToMarkdown;


use OCA\Importer\EverNote\ENoteMediaConverter;
use League\HTMLToMarkdown\ElementInterface;

/**
 * A helper class to convert HTML to Markdown.
 *
 * @author Sébastien Marinier <seb@smarinier.net>
 */
class ENoteHTMLConverter extends HTMLToMarkdownConverter
{
    /**
     * Constructor
     *
     * @param Environment|array<string, mixed> $options Environment object or configuration options
     */
    public function __construct($options = [], array $ressources = []) {
		parent::__construct($options);

		$env = $this->getEnvironment();
		$env->addConverter(new ENoteMediaConverter($ressources));
	}

    /**
     * Convert Children
     *
     * Recursive function to drill into the DOM and convert each node into Markdown from the inside out.
     *
     * Finds children of each node and convert those to #text nodes containing their Markdown equivalent,
     * starting with the innermost element and working up to the outermost element.
     */
    protected function convertChildren(ElementInterface $element): void
    {
		$tagName = $element->getTagName();
		if ($element->getTagName() === 'div') {
			$style = $element->getAttribute('style');
			if (!empty($style) && preg_match('/-en-codeblock:true/', $style)) {
				$element = new ChangeTagElement($element, 'pre');
			}
		}

		parent::convertChildren($element);
    }
}
