<?php
/**
 * This file is part of escpos-php: PHP receipt printer library for use with
 * ESC/POS-compatible thermal and impact printers.
 *
 * Copyright (c) 2014-16 Michael Billington < michael.billington@gmail.com >,
 * incorporating modifications by others. See CONTRIBUTORS.md for a full list.
 *
 * This software is distributed under the terms of the MIT license. See LICENSE.md
 * for details.
 */

namespace Mike42\Escpos;

use Mike42\Escpos\EscposImage;

/**
 * Implementation of EscposImage using only native PHP.
 */
class NativeEscposImage extends EscposImage
{
    
    /**
     * @param string $filename
     *  Path to load image from disk. Use 'null' to get an empty image.
     * @param string $allow_optimisations
     *  True to use library-specific speed optimisations.
     * @throws Exception
     *  Where image loading failed (eg. unsupported format, no such file, permission error).
     */
    function __construct($filename = null, $allow_optimisations = true)
    {
        // TODO. wbmp, pbm, bmp files.
        throw new \BadMethodCallException("Native bitmaps not yet supported. Please convert the file to a supported raster format.");
    }
}
