<?php namespace uninett\giza\core;

use \DomainException;
use \FilesystemIterator;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \RuntimeException;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class GPG {

	const PROCESS_READS = 1; // stdin
	const PROCESS_WRITES = 2; // stdout
	const PROCESS_ERROR = 4; // stderr
	const PROCESS_AUX = 8; // auxiliary

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
		if ($mask & self::PROCESS_AUX) {
			$result[3] = ['pipe', 'w'];
		}
		return $result;
	}

	public function __construct($directory = NULL) {
		if (is_null($directory)) {
			$this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gpg' . microtime(true);
			$this->isTemp = true;
			mkdir($this->dir);
			chmod($this->dir, 0700);
			register_shutdown_function([$this, 'finalize']);
		} elseif (!is_dir($directory)) {
			$this->dir = $directory;
			$this->isTemp = false;
			if (!mkdir($this->dir)) {
				throw new RuntimeException('GPG homedir does not exist and cannot be created.');
			}
			if (!chmod($this->dir, 0700)) {
				throw new RuntimeException('Unable to restrict permissions to GPG homedir.');
			}
		} else {
			$this->dir = $directory;
			$this->isTemp = false;
		}
	}

	protected function start(array $args, $descriptor = NULL, &$pipes = []) {
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

	public function verifyClear($clearSigned, &$keys) {
		$keys = [];
		$process = $this->start(['--no-tty', '--status-fd=3', '--quiet', '--decrypt'], self::PROCESS_READS|self::PROCESS_WRITES|self::PROCESS_ERROR|self::PROCESS_AUX);
		$process->sendInput($clearSigned);
		$process->closeInput();
		$log = preg_split('/\\s*\n/', $process->receiveOutput(3));
		$error = $process->receiveOutput(2);
		foreach(preg_grep('/^\[GNUPG:\] [A-Z]+SIG /', $log) as $line) {
			$segments = preg_split('/\\s/', $line);
			if ($segments[1] === 'GOODSIG') {
				$keys[] = $segments[2];
			} elseif (in_array($segments[1], ['BADSIG', 'ERRSIG'])) {
				throw new DomainException($error);
			}
		}
		if (!$keys) {
			throw new DomainException($error);
		}
		$verified = $process->receiveOutput(1);
		$process->close();
		return $verified;
	}

	public function finalize() {
		if (!$this->isTemp || !is_dir($this->dir)) {
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
