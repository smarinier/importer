<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Importer;

use OCA\Text\Service\AttachmentService;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Server;
use Symfony\Component\Mime\MimeTypes;

/**
 * NextCloud importer
 */
class NCImporter implements ImporterInterface {

	private AttachmentService $attachmentService;
	private MimeTypes $mimeTypes;
	private int $nb_files = 0;	// created files
	private int $nb_attachments = 0; // attachments
	private bool $simulate = false;

	public function __construct(private Folder $targetFolder, private string $userId, private ?\Closure $logMessage = null) {
		$this->attachmentService = Server::get(AttachmentService::class);
		$this->mimeTypes = Server::get(MimeTypes::class);
	}

	public function setSimulateMode(bool $simulate) : void {
		$this->simulate = $simulate;
	}

	/**
	 * Return how many files were created
	 */
	public function getNbFiles(): int {
		return $this->nb_files;
	}

	/**
	 * Return how attachments were created
	 */
	public function getNbAttachments(): int {
		return $this->nb_files;
	}

	/**
	 * build a file name according to its mime type
	 */
	protected function getTargetName(string $fileName, string $mimeType) : string {

		$fileNameParts = explode('.', $fileName);
		$ext = end($fileNameParts);
		$shortName = basename($fileName, $ext);
		$extensions = $this->mimeTypes->getExtensions($mimeType);
		if (!empty($extensions)) {
			$ext = $extensions[0]; // first is best
		}

		return $shortName . $ext;
	}

	/**
	 * Returns created File
	 */
	public function createFile(string $fileName, string $data, string $targetMimeType): ?File {
		$shortName = $this->getTargetName($fileName, $targetMimeType);
		$targetPath = $this->targetFolder->getNonExistingName($shortName);
		if ($this->simulate) {
			$file = null;
		} else {
			$file = $this->targetFolder->newFile($targetPath, $data);
		}
		$callback = $this->logMessage;
		if (!is_null($callback)) {
			$callback('> '.$targetPath);
		}
		$this->nb_files++;
		return $file;
	}

	/**
	 * Returns created attachment info (see AttachmentService::uploadAttachment)
	 */
	public function createAttachment(int $fileId, string $attachmentName, string $data): array {
		if ($this->simulate) {
			$fileInfo = [ 'dirname' => '_simulate_', 'name' => $attachmentName];
		} else {
			$fileInfo = $this->attachmentService->uploadAttachment($fileId, $attachmentName, $data, $this->userId);
		}
		$callback = $this->logMessage;
		if (!is_null($callback)) {
			$callback('  -- '.$fileInfo['dirname'].'/'.$fileInfo['name']);
		}
		$this->nb_attachments++;
		return $fileInfo;
	}
}
