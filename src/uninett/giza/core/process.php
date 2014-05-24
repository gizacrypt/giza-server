<?php namespace uninett\giza\core;

use \LogicException;
use \RuntimeException;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class Process {

	protected $process;
	protected $descriptor;
	protected $pipes;

	protected $exitCode;

	public function __construct($process, $descriptor, $pipes) {
		if (!is_resource($process)) {
			throw new RuntimeException('Process must be a resource, got a '.typeof($process));
		}
		$this->process = $process;
		$this->descriptor = $descriptor;
		$this->pipes = $pipes;
	}

	public function sendInput($input, $pipeNumber = -1) {
		if (!is_int($pipeNumber) || $pipeNumber < 0) {
			foreach($this->descriptor as $id => $stream) {
				if ($stream[0] === 'pipe' && in_array($stream[1], ['r'], true)) {
					$pipeNumber = $id;
					break;
				}
			}
			if (!is_int($pipeNumber) || $pipeNumber < 0) {
				throw new LogicException('No pipe available for reading');
			}
		}
		fwrite($this->pipes[$pipeNumber], $input);
	}
	public function closeInput($pipeNumber = -1) {
		if (!is_int($pipeNumber) || $pipeNumber < 0) {
			foreach($this->descriptor as $id => $stream) {
				if ($stream[0] === 'pipe' && in_array($stream[1], ['r'], true)) {
					$pipeNumber = $id;
					break;
				}
			}
			if (!is_int($pipeNumber) || $pipeNumber < 0) {
				throw new LogicException('No pipe available for reading');
			}
		}
		fclose($this->pipes[$pipeNumber]);
	}
	public function receiveOutput($pipeNumber = -1) {
		if (!is_int($pipeNumber) || $pipeNumber < 0) {
			foreach($this->descriptor as $id => $stream) {
				if ($stream[0] === 'pipe' && in_array($stream[1], ['w','a'], true)) {
					$pipeNumber = $id;
					break;
				}
			}
			if (!is_int($pipeNumber) || $pipeNumber < 0) {
				throw new LogicException('No pipe available for writing');
			}
		}
		return stream_get_contents($this->pipes[$pipeNumber]);
	}

	public function close() {
		if ($this->exitCode >= 0) {
			return $this->exitCode;
		}
		$status = proc_get_status($process);
		if (!$status['running']) {
			return $this->exitCode = $status['exitcode'];
		}
		foreach(array_keys($this->descriptor) as $pipe) {
			fclose($this->pipes[$pipe]);
		}
		return $this->exitCode = proc_close($process);
	}

}
