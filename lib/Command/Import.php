<?php
// SPDX-FileCopyrightText: Sebastien Marinier <seb@smarinier.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Importer\Command;

use Exception;
use OCA\Importer\Converter\ConverterInterface;
use OCA\Importer\Converter\Converters;
use OCA\Importer\Importer\NCImporter;
use OCA\Importer\Mime\EnexMimeTypeGuesser;
use OCP\App\IAppManager;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Mime\MimeTypes;

class Import extends Command {

	public function __construct(
		private IAppManager $appManager,
		private IUserManager $userManager,
		private IRootFolder $rootFolder) {
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('importer:import')
			->setDescription('Import files')
			->addOption(
				'no',
				'N',
				InputOption::VALUE_NONE,
				"don't import. Just show information about imported files."
			)
			->addOption(
				'file',
				'F',
				InputOption::VALUE_REQUIRED,
				"use file"
			)
			->addOption(
				'user',
				'U',
				InputOption::VALUE_REQUIRED,
				"User owner of the created files"
			)
			->addOption(
				'directory',
				'D',
				InputOption::VALUE_REQUIRED,
				"Target directory"
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {

		// checks
		if (!$this->checkTextApplication()) {
			throw new Exception('Text application must be installed and active in order to allow import actions.');
		}

		// start
		$mimeTypes = new MimeTypes();
		$mimeTypes->registerGuesser(new EnexMimeTypeGuesser());

		// option "no"
		$noAction = $input->getOption('no');

		// option file
		$inputFile = $input->getOption('file');
		if ($inputFile) {
			$mimeType = $mimeTypes->guessMimeType($inputFile);
			$output->writeln(sprintf('%s file %s (%s)', $noAction ? 'Simulate import' : 'Import', $inputFile, $mimeType));
		}

		// option user
		$user = null;
		$userFolder = null;
		$userOption = $input->getOption('user');
		if ($userOption) {
			$user = $this->userManager->get($userOption);
			if (is_null($user) || !$user->isEnabled()) {
				throw new Exception(sprintf('User %s does not exist or is not enabled', $userOption));
			}
			$userFolder = $this->rootFolder->getUserFolder($user->getUID());
			if (is_null($userFolder)) {
				throw new Exception('This user does not have a writable folder');
			}
		}

		// option Directory
		$targetNode = null;
		$targetDirectory = $input->getOption('directory');
		if ($targetDirectory && !is_null($userFolder)) {
			try {
				$targetNode = $userFolder->get($targetDirectory);
			} catch(Exception $e) {
				$questionHelper = $this->getHelper('question');
				$question = new ConfirmationQuestion('This folder does not exists. Do you want to create it? [y/N] ', false);

				if (!$questionHelper->ask($input, $output, $question)) {
					return 0;
				}
				$targetNode = $this->createRecurseFolder($userFolder, $targetDirectory);
			}
			if (!($targetNode instanceof \OCP\Files\Folder)) {
				throw new Exception(sprintf('%s is not a writable directory', $targetDirectory));
			}
			if ($targetNode && $output->isVerbose()) {
				$output->writeln(sprintf('%s in %s', $noAction ? 'Should import' : 'Importing', $targetNode->getPath()));
			}
		} else {
			$targetNode = $userFolder->get('/');
		}

		// for
		$importers = new Converters();
		$targetMimeType = ConverterInterface::MARKDOWN_MIME_TYPE;
		if ($inputFile) {
			// some checks
			if (is_null($user)) {
				throw new Exception('A user (-U) is required when creating documents');
			}
			if (is_null($targetNode)) {
				throw new Exception('A destination folder (-D) is required when creating documents');
			}

			$converter = $importers->findConverter($mimeType, $targetMimeType);
			if (is_null($converter)) {
				$output->writeln('** can\'t import: '.$inputFile);
			} else {
				$importer = new NCImporter($targetNode, $user->getUID(), function ($ex) use ($output) {
					if ($ex instanceof \Exception) {
						$output->writeln("Error while importing : " . $ex->getMessage());
					} else {
						if ($output->isVerbose()) {
							$output->writeln($ex);
						}
					}
				});
				if ($noAction) {
					$importer->setSimulateMode(true);
				}
				$converter->import($inputFile, $importer);
				$output->writeln(sprintf('%d files created', $importer->getNbFiles()));
				$nbAttachments = $importer->getNbAttachments();
				if ($nbAttachments) {
					$output->writeln(sprintf('%d attachments created', $nbAttachments));
				}
			}
		}

		if ($output->isVerbose()) {
			$output->writeln('');
		}

		return 0;
	}

	/**
	 * @return array level, message
	 */
	private function checkTextApplication() : bool {
		return $this->appManager->isInstalled('text');
	}

	/**
	 * Create recursively folders from a given path
	 */
	private function createRecurseFolder(Folder $userFolder, string $targetDirectory): ?Folder {
		if ($targetDirectory == '/') {
			return $userFolder->get('/');
		}
		$parent = dirname($targetDirectory);
		$current = basename($targetDirectory);
		try {
			$parentNode = $userFolder->get($parent);
		} catch(Exception $e) {
			if ($parent == $current) {
				$parent == '/';
			}
			$parentNode = $this->createRecurseFolder($userFolder, $parent);
		}
		if ($parentNode && $parentNode instanceof Folder) {
			$currentNode = $parentNode->newFolder($current);
			return $currentNode;
		}
		return null;
	}
}
