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

class CodePage
{

    /**
     * The input encoding for generating character maps.
     */
    const INPUT_ENCODING = "UTF-8";
    
    private $data;
    
    private $iconv;
    
    private $id;
    
    public function __construct($id, array $codePageData)
    {
        $this -> data = isset($codePageData['data']) ? implode("", $codePageData['data']) : null;
        $this -> iconv = isset($codePageData['iconv']) ? $codePageData['iconv'] : null;
        $this -> id = $id;
    }

    public function getEncodingMap()
    {
        if ($this -> data !== null) {
            return $this -> data;
        }
        if ($this -> iconv !== null) {
            $this -> data = self::generateEncodingMap($this -> iconv);
            return $this -> data;
        }
        throw new \InvalidArgumentException("Cannot encode this code page");
    }

    protected static function generateEncodingMap($iconvName)
    {
        $charMap = array_fill(0, 128, " ");
        for ($char = 128; $char <= 255; $char++) {
            $utf8 = @iconv($iconvName, self::INPUT_ENCODING, chr($char));
            if ($utf8 == '') {
                continue;
            }
            if (iconv(self::INPUT_ENCODING, $iconvName, $utf8) != chr($char)) {
                // Avoid non-canonical conversions
                continue;
            }
            $charMap[$char - 128] = $utf8;
        }
        $charMapStr = implode("", $charMap);
        assert(mb_strlen($charMapStr, self::INPUT_ENCODING) == 128, "Generated data table was incorrect size");
        return $charMapStr;
    }

    public function getId()
    {
        return $this -> id;
    }
    
    
    public function getIconv()
    {
        return $this -> iconv;
    }
    
    public function canEncode()
    {
        return $this -> iconv !== null || $this -> data !== null;
    }

    public function canDecode()
    {
        return false;
    }
}
