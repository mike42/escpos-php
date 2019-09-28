<?php
/**
 * This file added to escpos-php: PHP receipt printer library
 * License : UNLICENSE 
 * Modifed 402d from DummyPrintConnector
 * !! You may use it how you want !!
 **/

namespace Mike42\Escpos\PrintConnectors;

/**
 * Print connector that writes to nowhere, but allows the user to retrieve the
 * buffered data. Used for testing.
 */
final class RawbtPrintConnector implements PrintConnector
{
    /**
     * @var array $buffer
     *  Buffer of accumilated data.
     */
    private $buffer;

    /**
     * @var string data which the printer will provide on next read
     */
    private $readData;

    /**
     * Create new print connector
     */
    public function __construct()
    {
	ob_start();
        $this -> buffer = [];
    }

    public function clear()
    {
        $this -> buffer = [];
    }
    
    public function __destruct()
    {
        if ($this -> buffer !== null) {
            trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
        }
    }

    public function finalize()
    {
	 ob_end_clean();
         echo "intent:base64,".base64_encode($this -> getData())."#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
	 $this -> buffer = null;
    }

    /**
     * @return string Get the accumulated data that has been sent to this buffer.
     */
    public function getData()
    {
        return implode($this -> buffer);
    }

    /**
     * {@inheritDoc}
     * @see PrintConnector::read()
     */
    public function read($len)
    {
        return $len >= strlen($this -> readData) ? $this -> readData : substr($this -> readData, 0, $len);
    }

    public function write($data)
    {
        $this -> buffer[] = $data;
    }
}
