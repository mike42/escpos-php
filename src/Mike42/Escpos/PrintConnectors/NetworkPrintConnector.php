<?php declare(strict_types=1);
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

namespace Mike42\Escpos\PrintConnectors;

use Exception;

/**
 * PrintConnector for directly opening a network socket to a printer to send it commands.
 */
class NetworkPrintConnector extends FilePrintConnector
{
    /**
     * Construct a new NetworkPrintConnector
     *
     * @param string $ip IP address or hostname to use.
     * @param int $port The port number to connect on.
     * @param int $timeout The connection timeout, in seconds.
     * @throws Exception Where the socket cannot be opened.
     */
    public function __construct(string $ip, int $port = 9100, int $timeout = -1)
    {
        // Note: Once the minimum PHP version is PHP 7.0 or higher, we can type $timeout as '?int' to make it optional
        // instead of using -1.
        if ($timeout == -1) {
            $this -> fp = @fsockopen($ip, $port, $errno, $errstr);
        } else {
            $this -> fp = @fsockopen($ip, $port, $errno, $errstr, (float)$timeout);
        }
        if ($this -> fp === false) {
            throw new Exception("Cannot initialise NetworkPrintConnector: " . $errstr);
        }
    }
}
