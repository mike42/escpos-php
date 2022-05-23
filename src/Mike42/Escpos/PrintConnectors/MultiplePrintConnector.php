<?php

/**
 * This file is part of escpos-php: PHP receipt printer library for use with
 * ESC/POS-compatible thermal and impact printers.
 *
 * Copyright (c) 2014-20 Michael Billington < michael.billington@gmail.com >,
 * incorporating modifications by others. See CONTRIBUTORS.md for a full list.
 *
 * This software is distributed under the terms of the MIT license. See LICENSE.md
 * for details.
 */

declare(strict_types=1);

namespace Mike42\Escpos\PrintConnectors;

/**
 * Wrap multiple connectors up, to print to several printers at the same time.
 */
class MultiplePrintConnector implements PrintConnector
{
    private $connectors;

    public function __construct(PrintConnector ...$connectors)
    {
        $this -> connectors = $connectors;
    }

    public function finalize()
    {
        foreach ($this -> connectors as $connector) {
            $connector -> finalize();
        }
    }

    public function read($len)
    {
        // Cannot write
        return false;
    }

    public function write($data)
    {
        foreach ($this -> connectors as $connector) {
            $connector -> write($data);
        }
    }

    public function __destruct()
    {
        // Do nothing
    }
}
