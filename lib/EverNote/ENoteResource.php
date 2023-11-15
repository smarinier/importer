<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\EverNote;

/**
 * ENoteResource follow the <resource> tag from Enex XML
 */
class ENoteResource {

	private string $fileName = '';
	private string $mimeType = '';
	private string $data = '';
	private string $encoding = '';
	private string $hashData = '';
	private array $fileInfo = [];

	public function setFileName(string $fileName): void {
		$this->fileName = $fileName;
	}

	public function getFileName(): string {
		return $this->fileName;
	}

	public function setMimeType(string $inMimeType): void {
		$this->mimeType = $inMimeType;
	}

	public function setEncoding(string $inEncoding): void {
		$this->encoding = $inEncoding;
	}

	public function setData(string $inData): void {
		if ($this->encoding == 'base64') {
			$this->data = base64_decode($inData);
		} else {
			$this->data = $inData;
		}
		$this->hashData = md5($this->data, false);
	}

	public function getHash(): string {
		return $this->hashData;
	}

	public function getContent(): string {
		return $this->data;
	}

	public function lighten(): void {
		$this->data = '';
	}

	public function setFileInfo(array $info): void {
		$this->fileInfo = $info;
	}

	public function getFilePath(): string {
		$path = '';
		if (isset($this->fileInfo['dirname'])) {
			$path .= urlencode($this->fileInfo['dirname']).'/';
		}
		if (isset($this->fileInfo['name'])) {
			$path .= urlencode($this->fileInfo['name']);
		}
		return str_replace('+', '%20', $path);
	}

	public function __toString() {
		return sprintf("Mime: %s\nEncoding: %s\nData length:%d\nHash data:%s\n", $this->mimeType, $this->encoding, strlen($this->data), $this->hashData);
	}
};
