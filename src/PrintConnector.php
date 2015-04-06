<?php
interface PrintConnector {
	/**
	 * @param string $data
	 */
	public function write($data);
	
	/**
	 * Finish using this print connector (close file, socket, send
	 * accumulated output, etc).
	 */
	public function finalize();
	
	/**
	 * Print connectors should cause a NOTICE if they are deconstructed
	 * when they have not been finalized.
	 */
	public function __destruct();
}
