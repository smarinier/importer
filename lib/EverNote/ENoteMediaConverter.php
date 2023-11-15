<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\EverNote;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

/**
 * ENoteMediaConverter is used to convert <en-media> tag into MarkDown object reference
 */

class ENoteMediaConverter implements ConverterInterface {

	public function __construct(private array $resources) {
	}

	public function convert(ElementInterface $element): string {
		// $type   = $element->getAttribute('type');
		$hash = $element->getAttribute('hash');

		$resource = $this->findResourceByHash($hash);
		if ($resource) {

			return '![' . $resource->getFileName() . '](' . $resource->getFilePath() . ')';
		}
		return ''; // nothing to do if something is wrong
	}

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['en-media'];
	}

	private function findResourceByHash(string $hash): ?ENoteResource {
		/** @var ENoteResource $resource */
		foreach($this->resources as $resource) {
			if ($resource->getHash() == $hash) {
				return $resource;
			}
		}
		return null;
	}
}
