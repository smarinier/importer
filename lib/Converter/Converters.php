<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Converter;

/**
 * Manages Converters
 *
 */
class Converters {

	/**
	 * @var ConverterInterface[]
	 */
	private $converters = [];

	public function __construct() {
		$this->registerConverter(new EnexConverter());
		$this->registerConverter(new TextPlainConverter());
		$this->registerConverter(new HTMLConverter());
	}

	/**
	 * Registers a converter.
	 *
	 */
	public function registerConverter(ConverterInterface $converter): void {
		$this->converters[] = $converter;
	}

	/**
	 * Search converter that macth input type and target mimetype
	 */
	public function findConverter(string $inMimeType, string $outMimeType): ?ConverterInterface {
		foreach ($this->converters as $converter) {
			if ($converter->mimeType() != $inMimeType) {
				continue;
			}
			if (!$converter->supportConversion($outMimeType)) {
				continue;
			}
			return $converter;
		}
		return null;
	}
}
