<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'importer';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
