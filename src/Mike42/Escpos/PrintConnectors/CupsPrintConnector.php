<?php

namespace Mike42\Escpos\PrintConnectors;

use Exception;
use BadMethodCallException;

class CupsPrintConnector implements PrintConnector {
	
	/**
	 *
	 * @var array Buffer of accumilated data.
	 */
	private $buffer;
	
	/**
	 *
	 * @var string The name of the target printer.
	 */
	private $printerName;
	
	/**
	 * Construct new CUPS print connector.
	 *
	 * @param string $dest
	 *        	The CUPS printer name to print to. This must be loaded using a raw driver.
	 * @throws BadMethodCallException
	 */
	public function __construct($dest) {
		$valid = $this->getLocalPrinters ();
		if (count ( $valid ) == 0) {
			throw new BadMethodCallException ( "You do not have any printers installed on this system via CUPS. Check 'lpr -a'." );
		}
		
		if (array_search ( $dest, $valid, true ) === false) {
			throw new BadMethodCallException ( "'$dest' is not a printer on this system. Printers are: [" . implode ( ", ", $valid ) . "]" );
		}
		$this->buffer = array ();
		$this->printerName = $dest;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Mike42\Escpos\PrintConnectors\PrintConnector::__destruct()
	 */
	public function __destruct() {
		if ($this->buffer !== null) {
			trigger_error ( "Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE );
		}
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Mike42\Escpos\PrintConnectors\PrintConnector::finalize()
	 */
	public function finalize() {
		$data = implode ( $this->buffer );
		$this->buffer = null;
		
		// Build command to work on data
		$tmpfname = tempnam ( sys_get_temp_dir (), 'print-' );
		file_put_contents ( $tmpfname, $data );
		$cmd = sprintf ( "lp -d %s %s",
				escapeshellarg ( $this->printerName ),
				escapeshellarg ( $tmpfname ) );
		try {
			$this->getCmdOutput ( $cmd );
		} catch (Exception $e) {
			unlink ( $tmpfname );
			throw $e;
		}
		unlink ( $tmpfname );
	}
	
	/**
	 * Run a command and throw an exception if it fails, or return the output if it works.
	 * (Basically exec() with good error handling)
	 * 
	 * @param string $cmd
	 *        	Command to run
	 */
	protected function getCmdOutput($cmd) {
		$descriptors = array (
				1 => array (
						"pipe",
						"w" 
				),
				2 => array (
						"pipe",
						"w" 
				) 
		);
		$process = proc_open ( $cmd, $descriptors, $fd );
		if (! is_resource ( $process )) {
			throw new Exception ( "Command '$cmd' failed to start." );
		}
		/* Read stdout */
		$outputStr = stream_get_contents ( $fd [1] );
		fclose ( $fd [1] );
		/* Read stderr */
		$errorStr = stream_get_contents ( $fd [2] );
		fclose ( $fd [2] );
		/* Finish up */
		$retval = proc_close ( $process );
		if ($retval != 0) {
			throw new Exception ( "Command $cmd failed: $errorStr" );
		}
		return $outputStr;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Mike42\Escpos\PrintConnectors\PrintConnector::read()
	 */
	public function read($len) {
		return false;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Mike42\Escpos\PrintConnectors\PrintConnector::write()
	 */
	public function write($data) {
		$this->buffer [] = $data;
	}
	
	/**
	 * Load a list of CUPS printers.
	 *
	 * @return array A list of printer names installed on this system. Any item on this list is valid for constructing a printer.
	 */
	protected function getLocalPrinters() {
		$outpStr = $this->getCmdOutput ( "lpstat -a" );
		$outpLines = explode ( "\n", trim ( $outpStr ) );
		foreach ( $outpLines as $line ) {
			$ret [] = $this->chopLpstatLine ( $line );
		}
		return $ret;
	}
	
	/**
	 * Get the item before the first space in a string
	 *
	 * @param string $line        	
	 * @return string the string, up to the first space, or the whole string if it contains no spaces.
	 */
	private function chopLpstatLine($line) {
		if (($pos = strpos ( $line, " " )) === false) {
			return $line;
		} else {
			return substr ( $line, 0, $pos );
		}
	}
}