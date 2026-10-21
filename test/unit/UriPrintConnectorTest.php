<?php
use Mike42\Escpos\PrintConnectors\UriPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class UriPrintConnectorTest extends PHPUnit\Framework\TestCase
{
    public function testFile()
    {
        $filename = tempnam(sys_get_temp_dir(), "escpos-php-");
        // Make connector, write some data
        $connector = UriPrintConnector::get("file://" . $filename);
        $connector -> write("AAA");
        $connector -> finalize();
        $this -> assertEquals("AAA", file_get_contents($filename));
        $this -> assertEquals('Mike42\Escpos\PrintConnectors\FilePrintConnector', get_class($connector));
        unlink($filename);
    }

    public function testSmb()
    {
        // An smb:// URI should be accepted and resolve to a WindowsPrintConnector.
        $connector = UriPrintConnector::get("smb://windows/printer");
        $this -> assertInstanceOf(WindowsPrintConnector::class, $connector);
        // Hack: swallow the "not finalized" notice from the destructor, since we
        // never finalize() (can't print to a real SMB share from a unit test).
        set_error_handler(function () {
            return true;
        }, E_USER_NOTICE);
        unset($connector);
        restore_error_handler();
    }

    public function testBadUri()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Malformed connector URI");
        $connector = UriPrintConnector::get("foooooo");
    }

    public function testNetwork()
    {
        $this->expectExceptionMessage("Connection refused");
        $this->expectException(Exception::class);
        // Port should be closed so we can catch an error and move on
        $connector = UriPrintConnector::get("tcp://localhost:45987/");
    }

    public function testUnsupportedUri()
    {
        $this->expectExceptionMessage("URI sheme is not supported: ldap://");
        $this->expectException(InvalidArgumentException::class);
        // Try to print to something silly
        $connector = UriPrintConnector::get("ldap://host:1234/");
    }
}
