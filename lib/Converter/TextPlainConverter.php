<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Converter;

use OCA\Importer\Importer\ImporterInterface;

/**
 * TextPlain converter *
 */
class TextPlainConverter implements ConverterInterface {
	/**
	 * Returns mimetype input handled by this converter
	 */
	public function mimeType(): string {
		return 'text/plain';
	}

	/**
	 * Returns true if this converter supports this output type
	 */
	public function supportConversion(string $mimeType): bool {
		if ($mimeType == self::MARKDOWN_MIME_TYPE) {
			return true;
		}
		return false;
	}
	
	/**
	 * Proceed import
	 */
	public function import(string $pathFile, ImporterInterface $importer) : void {
		$content = file_get_contents(($pathFile));
		$importer->createFile($pathFile, $content, self::MARKDOWN_MIME_TYPE);
	}
}
