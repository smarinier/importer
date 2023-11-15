<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Converter;

use OCA\Importer\EverNote\ENote;
use OCA\Importer\EverNote\ENoteResource;
use OCA\Importer\Importer\ImporterInterface;
use OCP\Files\File;

/**
 * Enex converter *
 */
class EnexConverter implements ConverterInterface {
	private ?ENote $currentNote = null;
	private ?ENoteResource $currentResource = null;

	private string $cur_data = '';
	private ?File $currentFile = null;
	private ?ImporterInterface $importer = null;

	/**
	 * Returns mimetype input handled by this converter
	 */
	public function mimeType(): string {
		return 'application/enex+xml';
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
	 * DateTime is good enough to convert ISO time
	 */
	private function getTimeFromISO(string $inDate): \DateTime {
		return new \DateTime($inDate);
	}
   
	/**
	 * XML parsing : handle opening tag
	 */
	public function _start_element_handler($parser, $name, $attribs) {

		switch($name) {
			case 'NOTE':
				$this->currentNote = new ENote();
				break;
			case 'TITLE':
			case 'CONTENT':
			case 'CREATED':
			case 'UPDATED':
				break;
			case 'RESOURCE':
				$this->currentResource = new ENoteResource();
				break;
			case 'DATA':
				if ($this->currentResource && isset($attribs['ENCODING'])) {
					$this->currentResource->setEncoding($attribs['ENCODING']);
				}
				break;
		}
		$this->cur_data = '';
	}

	/**
	 * XML parsing : handle closing tag
	 */
	public function _stop_element_handler($parser, $name) {
		switch($name) {
			case 'NOTE':
				$this->saveNote($this->currentNote);
				$this->currentNote = null;
				break;
			case 'TITLE':
				if ($this->currentNote) {
					$this->currentNote->setTitle($this->cur_data);
				}
				break;
			case 'CONTENT':
				if ($this->currentNote) {
					$this->currentNote->setContent($this->cur_data);
				}
				break;
			case 'CREATED':
				if ($this->currentNote) {
					$this->currentNote->setCreated($this->getTimeFromISO($this->cur_data));
				}
				break;
			case 'UPDATED':
				if ($this->currentNote) {
					$this->currentNote->setUpdated($this->getTimeFromISO($this->cur_data));
				}
				break;
			case 'RESOURCE':
				$this->saveCurrentResource();
				if ($this->currentNote) {
					$this->currentNote->addResource($this->currentResource);
				}
				$this->currentResource = null;
				break;
			case 'MIME':
				if ($this->currentResource) {
					$this->currentResource->setMimeType($this->cur_data);
				}
				break;
			case 'DATA':
				if ($this->currentResource) {
					$this->currentResource->setData($this->cur_data);
				}
				break;
			case 'FILE-NAME':
				if ($this->currentResource) {
					$this->currentResource->setFileName($this->cur_data);
				}
				break;
		}
		$this->cur_data = '';
	}

	/**
	 * XML parsing : handle data in tags
	 */
	public function _data_handler($parser, $data) {
		$this->cur_data .= $data;
	}

	/**
	 * Create the note
	 * @note: this may be necessary when we need the file id to link the attachment
	 */
	private function createCurrentNote(string $data = '' ): void {
		if (!is_null($this->currentNote) && !is_null($this->importer)) {
			$this->currentFile = $this->importer->createFile($this->currentNote->getTitle().'.md', $data, self::MARKDOWN_MIME_TYPE);
			$this->currentNote->setImported(true);
		}
	}

	/**
	 * Save the note content
	 */
	private function saveNote(?ENote $note): void {
		if (is_null($note)) {
			return;
		}

		if ($note->isImported()) {
			if (!is_null($this->currentFile)) {
				$this->currentFile->putContent($note->getMarkDownContent());
			}
		} else {
			$this->createCurrentNote( $note->getMarkDownContent());
		}
		// change modification time
		if (!is_null($this->currentFile)) {
			$this->currentFile->touch($note->getLastModified());
		}
		$this->currentFile = null; // not not use it later
	}

	/**
	 * Save the attachment now
	 */
	private function saveCurrentResource(): void {
		if (is_null($this->currentResource) || is_null($this->currentNote)) {
			return;
		}
		if (!$this->currentNote->isImported()) {
			$this->createCurrentNote();
		}
		if ($this->importer) {
			$fileId = is_null($this->currentFile) ? 0 : $this->currentFile->getId();
			$this->currentResource->setFileInfo($this->importer->createAttachment($fileId, $this->currentResource->getFileName(), $this->currentResource->getContent()));
		}
		// free some memory
		$this->currentResource->lighten();
	}

	/**
	 * Proceed import
	 */
	public function import(string $pathFile, ImporterInterface $importer) : void {
		if (!($fp = fopen($pathFile, "rb"))) {
			throw new \Exception(sprintf('Can\'t open and read file %s', $pathFile));
		}
		$this->importer = $importer;

		$parser = xml_parser_create('UTF-8');
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, '_start_element_handler', '_stop_element_handler');
		xml_set_character_data_handler($parser, '_data_handler');

		while ($data = fread($fp, 32000)) { // reasonable size
			if (!xml_parse($parser, $data, feof($fp))) {
				throw new \Exception(sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser)));
			}
		}

		fclose($fp);
		xml_parser_free($parser);
		unset($parser);
	}
}
