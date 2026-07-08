<?php

declare(strict_types=1);

namespace Mike42\Escpos;

/**
 * Enum representing supported Chinese character encodings for ESC/POS printers
 * These encodings are used to convert between Unicode text and printer-compatible character sets
 */
enum ChineseEncoding: string
{
    case GBK = 'GBK';       // Extended GB2312 encoding, widely used in mainland China
    case BIG5 = 'BIG-5';    // Traditional Chinese encoding, common in Taiwan and Hong Kong
    case GB18030 = 'GB18030'; // Modern Unicode-compatible Chinese encoding that covers all CJK characters
}
