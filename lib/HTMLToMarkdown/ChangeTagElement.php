<?php

declare(strict_types=1);

namespace OCA\Importer\HTMLToMarkdown;

use League\HTMLToMarkdown\Element;

class ChangeTagElement extends Element {

	protected string $tagName;

	function __construct(Element $sourceElement, string $newTagName) {
		parent::__construct($sourceElement->node);
		$this->tagName = $newTagName;
	}

	public function getTagName(): string
	{
		return $this->tagName;
	}
}
