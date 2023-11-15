<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Converter;

use OCA\Importer\Importer\ImporterInterface;

/**
 * Convert a file *
 */
interface ConverterInterface {
	public const MARKDOWN_MIME_TYPE = 'text/markdown';

	/**
	 * Returns mimetype input handled by this converter
	 */
	public function mimeType(): string;

	/**
	 * Returns true if this converter supports this output type
	 */
	public function supportConversion(string $mimeType): bool;

	/**
	 * Proceed import
	 */
	public function import(string $pathFile, ImporterInterface $importer) : void;

}
