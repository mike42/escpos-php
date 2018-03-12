<?php
namespace Mike42\Escpos\PrintConnectors;

class UriPrintConnector
{
    const URI_ASSEMBLER_PATTERN = "~^(.+):/{2}(.+?)(?::(\d{1,4}))?$~";

    public static function get($uri)
    {
        $allowed_protocols = array("file", "tcp", "ipp", "smb", "usb", "com", "lpt");

        $is_uri = preg_match(self::URI_ASSEMBLER_PATTERN, $uri, $uri_parts);

        if ($is_uri === 1) {
            $protocol = $uri_parts[1];
            $printer = $uri_parts[2];
            $port = isset($uri_parts[3]) ? $uri_parts[3] : 9100;

            if (in_array($protocol, $allowed_protocols)) {
                switch ($protocol) {
                    case "file":
                        return new FilePrintConnector($printer);
                        break;

                    case "tcp":
                        return new NetworkPrintConnector($printer, $port);
                        break;

                    case "ipp":
                        return new CupsPrintConnector($printer);
                        break;
                    
                    case "smb":
                        return new WindowsPrintConnector($uri);
                        break;

                    case "usb":
                    case "com":
                    case "lpt":
                        return new WindowsPrintConnector($printer);
                        break;
                }
            } else {
                throw new \Exception("Connector protocol not supported: {$protocol}");
            }
        } else {
            throw new \Exception("Malformed connector URI: {$uri}");
        }
    }
}
