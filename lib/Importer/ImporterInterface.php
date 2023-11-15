<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Importer;

use OCP\Files\File;

/**
 * Import files
 */
interface ImporterInterface {
	/**
	 * Returns created File (or throw)
	 */
	public function createFile(string $fileName, string $data, string $targetMimeType): ?File;

	/**
	 * Returns created Attachment info (or throw)
	 */
	public function createAttachment(int $fileId, string $attachmentName, string $data): array;

}
