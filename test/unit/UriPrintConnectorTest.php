<?php
use Mike42\Escpos\PrintConnectors\UriPrintConnector;
use PHPUnit\Framework\Error\Notice;

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
        $this->expectNotice();
        $this->expectNoticeMessage("not finalized");
        $connector = UriPrintConnector::get("smb://windows/printer");
        $this -> assertEquals('Mike42\Escpos\PrintConnectors\WindowsPrintConnector', get_class($connector));
        // We expect that this will throw an exception, we can't
        // realistically print to a real printer in this test though... :)
        $connector -> __destruct();
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
