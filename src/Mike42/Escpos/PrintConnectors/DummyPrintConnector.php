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
 * buffered data.
 *
 * @deprecated Renamed to {@see MemoryPrintConnector}. This subclass is retained
 *  for backwards compatibility and will be removed in a future major version.
 *  Use MemoryPrintConnector in new code.
 */
class DummyPrintConnector extends MemoryPrintConnector
{
}
