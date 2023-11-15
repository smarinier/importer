<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\EverNote;

use League\HTMLToMarkdown\HtmlConverter as HTMLToMarkDownConverter;

/**
 * ENote follow the <note> tag from Enex XML
 */
class ENote {

	private string $title = '';
	private string $content = '';
	private \DateTime $created;
	private \DateTime $updated;
	private array $resources = [];
	private bool $imported = false;

	public function isImported(): bool {
		return $this->imported;
	}

	public function setImported(bool $imported): void {
		$this->imported = $imported;
	}

	public function setTitle(string $inTitle): void {
		$this->title = $inTitle;
	}

	public function getTitle() : string {
		return $this->title;
	}

	public function setContent(string $inContent) {
		$this->content = $inContent;
	}

	public function getContent() : string {
		return $this->content;
	}

	public function getMarkDownContent(): string {

		$converter = new HTMLToMarkDownConverter([
			'header_style' => 'atx',
			'strip_tags' => true
		]);
		$converter->getEnvironment()->addConverter(new ENoteMediaConverter($this->getResources()));
		$markDown = $converter->convert($this->content);

		// remove XML header
		$markDown = \preg_replace('/<\?xml [^>]+>/', '', $markDown); // Strip doctype declaration
		return $markDown;
	}

	public function setCreated(\DateTime $inDate): void {
		$this->created = $inDate;
	}

	public function setUpdated(\DateTime $inDate): void {
		$this->updated = $inDate;
	}

	public function getLastModified(): int {
		return $this->updated->getTimestamp();
	}

	public function addResource(ENoteResource $resource): void {
		$this->resources[] = $resource;
	}

	public function getResources(): array {
		return $this->resources;
	}

	public function __toString() {
		return sprintf("Title: %s\nCreated: %s\nUpdated: %s\nContent length:%d\n", $this->title, $this->created->format('Y-m-d H:i:s'), $this->updated->format('Y-m-d H:i:s'), strlen($this->content));
	}
};
