<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Converter;

use OCA\Importer\HTMLToMarkdown\HTMLToMarkdownConverter;
use OCA\Importer\Importer\ImporterInterface;

/**
 * HTML converter *
 */
class HTMLConverter implements ConverterInterface {
	/**
	 * Returns mimetype input handled by this converter
	 */
	public function mimeType(): string {
		return 'text/html';
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
		$converter = new HTMLToMarkdownConverter([
			'header_style' => 'atx',
			'strip_tags' => true
		]);
		$markDown = $converter->convert($content);
		$importer->createFile($pathFile, $markDown, self::MARKDOWN_MIME_TYPE);
	}
}
