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

namespace Mike42\Escpos\Experimental\Unifont;

class ColumnFormatGlyph
{
    public $width;
    public $data;

    public function segment(int $maxWidth)
    {
        if ($this->width <= $maxWidth) {
            return [$this];
        }
        $dataChunks = str_split($this->data, $maxWidth * 3);
        $ret = [];
        foreach ($dataChunks as $chunk) {
            $g = new ColumnFormatGlyph();
            $g->data = $chunk;
            $g->width = strlen($chunk) / 3;
            $ret[] = $g;
        }
        return $ret;
    }
}
