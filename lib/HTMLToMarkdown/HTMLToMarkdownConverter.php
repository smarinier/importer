<?php

declare(strict_types=1);

namespace OCA\Importer\HTMLToMarkdown;

use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\PreConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

/**
 * A helper class to convert HTML to Markdown.
 *
 * @author Sébastien Marinier <seb@smarinier.net>
 */
class HTMLToMarkdownConverter extends HtmlConverter
{
    /**
     * Constructor
     *
     * @param Environment|array<string, mixed> $options Environment object or configuration options
     */
    public function __construct($options = []) {
		parent::__construct($options);

		$env = $this->getEnvironment();
		$env->addConverter(new PreformattedConverter());
		$env->addConverter(new CodeConverter());
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
        // Don't convert HTML code inside <code> and <pre> blocks to Markdown - that should stay as HTML
        // except if the current node is a code tag, which needs to be converted by the CodeConverter.
        /*
		if ($element->isDescendantOf(['pre', 'code']) && $element->getTagName() !== 'code') {
            return;
        }
		*/
		$tagName = $element->getTagName();

		$divConverter = $textConverter = null;
		if (($tagName == 'code')||($tagName == 'pre')) {
			$textConverter = $this->getEnvironment()->getConverterByTag('#text');
			$divConverter = $this->getEnvironment()->getConverterByTag('div');
			$this->getEnvironment()->addConverter(new PRETextConverter());
			$this->getEnvironment()->addConverter(new PREDivConverter());
		}

        // Give converter a chance to inspect/modify the DOM before children are converted
        $converter = $this->environment->getConverterByTag($tagName);
        if ($converter instanceof PreConverterInterface) {
            $converter->preConvert($element);
        }

        // If the node has children, convert those to Markdown first
        if ($element->hasChildren()) {
            foreach ($element->getChildren() as $child) {
                $this->convertChildren($child);
            }
        }

        // Now that child nodes have been converted, convert the original node
        $markdown = $this->convertToMarkdown($element);

        // Create a DOM text node containing the Markdown equivalent of the original node

        // Replace the old $node e.g. '<h3>Title</h3>' with the new $markdown_node e.g. '### Title'
        $element->setFinalMarkdown($markdown);

		if ($textConverter !== null) {
			$this->getEnvironment()->addConverter($textConverter);
		}
		if ($divConverter !== null) {
			$this->getEnvironment()->addConverter($divConverter);
		}
    }

    protected function sanitize(string $markdown): string
    {
		$markdown = parent::sanitize($markdown);

		/*
         * Removing unwanted tags. Tags should be added to the array in the order they are expected.
         * XML, html and body opening tags should be in that order. Same case with closing tags
         */
        $unwanted = ['<?xml ', '<html ', '<body ', '<head ', ];

		// SEB -> attributs et XML ?
        foreach ($unwanted as $tag) {
            // Opening tags
			$pos = \strpos($markdown, $tag);
            if ($pos !== false) {
				$end = \strpos($markdown, '>', $pos);
				if ($end !== false) {
					if ($pos > 0) {
						$markdown = \substr($markdown, 0, $pos).\substr($markdown, $end+1);
					} else {
						$markdown = \substr($markdown, $end+1);
					}
				}
            }
		}
		return $markdown;
	}
}
