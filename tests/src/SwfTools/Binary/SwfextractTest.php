<?php

namespace SwfTools\Binary;

class SwfextractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Swfextract
     */
    protected $object;

    protected function setUp()
    {
        $this->object = Swfextract::load(new \SwfTools\Configuration());
    }

    /**
     * @covers SwfTools\Binary\Swfextract::listEmbedded
     */
    public function testListEmbedded()
    {
        $flash = new \SplFileObject(__DIR__ . '/../../../files/flashfile.swf');
        $embed = $this->object->listEmbedded($flash);

        $this->assertTrue(strpos($embed, 'Objects in file ') !== false);
    }

    /**
     * @covers SwfTools\Binary\Swfextract::listEmbedded
     * @expectedException \SwfTools\Exception\RuntimeException
     */
    public function testListEmbeddedWrongFile()
    {
        $wrongFile = new \SplFileInfo(__DIR__ . '/../../../files/unknownflashfile.swf');
        $this->object->listEmbedded($wrongFile);
    }

    /**
     * @covers SwfTools\Binary\Swfextract::extract
     */
    public function testExtract()
    {
        $file = __DIR__ . '/../../../files/flashfile.swf';
        $flash     = new \SwfTools\Processor\FlashFile();
        $flash->open($file);
        $embeddeds = $flash->listEmbeddedObjects();

        $embedded = null;

        foreach ($embeddeds as $e) {
            if ($e->getType() === \SwfTools\EmbeddedObject::TYPE_JPEG) {
                $embedded = $e;
                break;
            }
        }

        $dest_file = __DIR__ . '/../../../files/tmp.jpg';

        $this->object->extract($file, $embedded, $dest_file);
        $sizes = getimagesize($dest_file);
        $this->assertTrue(file_exists($dest_file));

        unlink($dest_file);

        try {
            $this->object->extract($file, $embedded, '');
            $this->fail('Should fail on invalid destination');
        } catch (\SwfTools\Exception\InvalidArgumentException $exception) {

        }

        $this->assertEquals(1440, $sizes[0]);
        $this->assertEquals(420, $sizes[1]);

        $fakeFile = __DIR__ . '/../../../files/nofile';

        try {
            $this->object->extract($fakeFile, $embedded, $dest_file);
            $this->fail('Swfrender should file on an unexistent file');
        } catch (\SwfTools\Exception\RuntimeException $exception) {

        }
    }

    public function testExtractWrongDest()
    {

    }

    /**
     * @covers SwfTools\Binary\Swfextract::load
     */
    public function testLoad()
    {
        $swfextract = Swfextract::load(new \SwfTools\Configuration());

        $this->assertInstanceOf('SwfTools\Binary\Swfextract', $swfextract);
    }

}
