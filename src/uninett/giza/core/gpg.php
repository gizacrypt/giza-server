<?php namespace uninett\giza\core;

use \FilesystemIterator;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class GPG {

	const PROCESS_READS = 1;
	const PROCESS_WRITES = 2;
	const PROCESS_ERROR = 4;

	protected $dir;
	protected $isTemp;

	public static function getPipeDescriptor($mask) {
		$result = [];
		if ($mask & self::PROCESS_READS) {
			$result[0] = ['pipe', 'r'];
		}
		if ($mask & self::PROCESS_WRITES) {
			$result[1] = ['pipe', 'w'];
		}
		if ($mask & self::PROCESS_ERROR) {
			$result[2] = ['pipe', 'w'];
		}
		return $result;
	}

	public function __construct($directory = NULL) {
		if (is_null($directory)) {
			$this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gpg' . microtime(true);
			$this->isTemp = true;
			mkdir($this->dir);
			chmod($this->dir, 0700);
		} elseif (!is_dir($directory)) {
			$this->dir = $directory;
			$this->isTemp = false;
			mkdir($this->dir);
			chmod($this->dir, 0700);
		} else {
			$this->dir = $directory;
			$this->isTemp = false;
		}
	}

	public function start(array $args, $descriptor = NULL, &$pipes = []) {
		if (is_int($descriptor)) {
			$descriptor = self::getPipeDescriptor($descriptor);
		} elseif (is_null($descriptor)) {
			$descriptor = self::getPipeDescriptor(self::PROCESS_READS|self::PROCESS_WRITES|self::PROCESS_ERROR);
		}
		$args[] = '--homedir';
		$args[] = $this->dir;
		$args = ' '.implode(' ', array_map('escapeshellarg', $args));
		$process = proc_open(
			escapeshellcmd($GLOBALS['gizaConfig']['gpgBinary']).$args,
			$descriptor, $pipes, $this->dir
		);
		return new Process($process, $descriptor, $pipes);
	}

	public function importKey(PGPPublicKey $key) {
		$process = $this->start(['--import'], self::PROCESS_READS);
		$process->sendInput($key->getKey());
		$process->close();
	}

	public function listSigs() {
		$process = $this->start(['--list-sigs', '--with-colons'], self::PROCESS_WRITES);
		$result = preg_split("_\\s*\n\\s*_", $process->receiveOutput());
		$process->close();
		return $result;
	}

	public function finalize() {
		if (!$this->isTemp) {
			return;
		}
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($iterator as $filename => $fileInfo) {
			if ($fileInfo->isDir()) {
				rmdir($filename);
			} else {
				unlink($filename);
			}
		}
		rmdir($this->dir);
	}

}
