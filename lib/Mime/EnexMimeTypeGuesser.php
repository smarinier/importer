<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Mime;

use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class EnexMimeTypeGuesser implements MimeTypeGuesserInterface {

	public function isGuesserSupported(): bool {
		// return true when the guesser is supported (might depend on the OS for instance)
		return true;
	}

	public function guessMimeType(string $path): ?string {
		// inspect the contents of the file stored in $path to guess its
		// type and return a valid MIME type ... or null if unknown
		if (!is_file($path) || !is_readable($path) || ($data = file_get_contents($path, 500)) === false) {
			throw new InvalidArgumentException(sprintf('The "%s" file does not exist or is not readable.', $path));
		}

		if (strpos($data, "<?xml") === false) {
			return null;
		}
		if (strpos($data, "<en-export") === false) {
			return null;
		}

		return 'application/enex+xml';
	}
}
