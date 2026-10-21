<?php

/**
 * This file is part of escpos-php: PHP receipt printer library for use with
 * ESC/POS-compatible thermal and impact printers.
 *
 * Copyright (c) 2014-2026 Michael Billington < michael.billington@gmail.com >,
 * incorporating modifications by others. See CONTRIBUTORS.md for a full list.
 *
 * This software is distributed under the terms of the MIT license. See LICENSE.md
 * for details.
 */

declare(strict_types=1);

namespace Mike42\Escpos\PrintConnectors;

/**
 * Print connector that writes to nowhere, but allows the user to retrieve the
 * buffered data. Useful for capturing raw ESC/POS output in memory - for
 * inspection, for sending over your own transport, or for testing.
 */
class MemoryPrintConnector implements PrintConnector
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

    public function finalize(): void
    {
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
    public function read(int $len): bool|string
    {
        return $len >= strlen($this -> readData) ? $this -> readData : substr($this -> readData, 0, $len);
    }

    public function write(string $data): void
    {
        $this -> buffer[] = $data;
    }
}
