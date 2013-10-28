<?php

namespace DominionEnterprises\Util;

final class ImageTest extends \PHPUnit_Framework_TestCase
{
    private $_sourceFilesDir;
    private $_tempDir;

    public function setUp()
    {
        $this->_sourceFilesDir = dirname(dirname(__DIR__)) . '/_files';
        $this->_tempDir = sys_get_temp_dir() . '/imageUtilTest';
        foreach (glob("{$this->_tempDir}/*") as $file) {
            unlink($file);
        }

        if (is_dir($this->_tempDir)) {
            rmdir($this->_tempDir);
        }
    }

    /**
     * Downsize ratio 2.0 to 0.25
     *
     * @test
     */
    public function resize_downsizeToMoreVerticalAspect()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $imagick = Image::resize(
            $source,
            10,
            40,
            array('color' => 'white', 'maxWidth' => 10000, 'maxHeight' => 10000, 'format' => 'jpeg', 'stripHeaders' => true)
        );

        //making sure source didnt resize
        $this->assertSame(100, $source->getImageWidth());
        $this->assertSame(50, $source->getImageHeight());

        $this->assertSame(10, $imagick->getImageWidth());
        $this->assertSame(40, $imagick->getImageHeight());

        $whiteBarTop = $imagick->getImagePixelColor(4, 16)->getHsl();
        $whiteBarBottom = $imagick->getImagePixelColor(4, 22)->getHsl();

        $imageLeft = $imagick->getImagePixelColor(0, 19)->getHsl();
        $imageRight = $imagick->getImagePixelColor(9, 19)->getHsl();
        $imageTop = $imagick->getImagePixelColor(4, 17)->getHsl();
        $imageBottom = $imagick->getImagePixelColor(4, 21)->getHsl();

        $this->assertGreaterThan(0.9, $whiteBarTop['luminosity']);
        $this->assertGreaterThan(0.9, $whiteBarBottom['luminosity']);

        $this->assertLessThan(0.1, $imageLeft['luminosity']);
        $this->assertLessThan(0.1, $imageRight['luminosity']);
        $this->assertLessThan(0.1, $imageTop['luminosity']);
        $this->assertLessThan(0.1, $imageBottom['luminosity']);
    }

    /**
     * Downsize ratio 2.0 to 4.0
     *
     * @test
     */
    public function resize_downsizeToMoreHorizontalAspect()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $imagick = Image::resize($source, 40, 10);

        //making sure source didnt resize
        $this->assertSame(100, $source->getImageWidth());
        $this->assertSame(50, $source->getImageHeight());

        $this->assertSame(40, $imagick->getImageWidth());
        $this->assertSame(10, $imagick->getImageHeight());

        $whiteBarLeft = $imagick->getImagePixelColor(9, 4)->getHsl();
        $whiteBarRight = $imagick->getImagePixelColor(30, 4)->getHsl();

        $imageLeft = $imagick->getImagePixelColor(10, 4)->getHsl();
        $imageRight = $imagick->getImagePixelColor(29, 4)->getHsl();
        $imageTop = $imagick->getImagePixelColor(19, 0)->getHsl();
        $imageBottom = $imagick->getImagePixelColor(19, 9)->getHsl();

        $this->assertGreaterThan(0.9, $whiteBarLeft['luminosity']);
        $this->assertGreaterThan(0.9, $whiteBarRight['luminosity']);

        $this->assertLessThan(0.1, $imageLeft['luminosity']);
        $this->assertLessThan(0.1, $imageRight['luminosity']);
        $this->assertLessThan(0.1, $imageTop['luminosity']);
        $this->assertLessThan(0.1, $imageBottom['luminosity']);
    }

    /**
     * Upsize ratio 2.0 to 4.0
     *
     * @test
     */
    public function resize_upsizeToMoreHorizontalAspectWithoutGrow()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $imagick = Image::resize($source, 400, 100);

        //making sure source didnt resize
        $this->assertSame(100, $source->getImageWidth());
        $this->assertSame(50, $source->getImageHeight());

        $this->assertSame(400, $imagick->getImageWidth());
        $this->assertSame(100, $imagick->getImageHeight());

        $whiteBarLeft = $imagick->getImagePixelColor(99, 49)->getHsl();
        $whiteBarRight = $imagick->getImagePixelColor(300, 49)->getHsl();

        $imageTop = $imagick->getImagePixelColor(200, 26)->getHsl();
        $imageBottom = $imagick->getImagePixelColor(200, 74)->getHsl();
        $imageLeft = $imagick->getImagePixelColor(151, 50)->getHsl();
        $imageRight = $imagick->getImagePixelColor(249, 50)->getHsl();

        $this->assertGreaterThan(0.9, $whiteBarLeft['luminosity']);
        $this->assertGreaterThan(0.9, $whiteBarRight['luminosity']);

        $this->assertLessThan(0.1, $imageLeft['luminosity']);
        $this->assertLessThan(0.1, $imageRight['luminosity']);
        $this->assertLessThan(0.1, $imageTop['luminosity']);
        $this->assertLessThan(0.1, $imageBottom['luminosity']);
    }

    /**
     * Upsize ratio 2.0 to 4.0
     *
     * @test
     */
    public function resize_upsizeToMoreHorizontalAspectWithGrow()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $imagick = Image::resize($source, 400, 100, array('upsize' => true));

        //making sure source didnt resize
        $this->assertSame(100, $source->getImageWidth());
        $this->assertSame(50, $source->getImageHeight());

        $this->assertSame(400, $imagick->getImageWidth());
        $this->assertSame(100, $imagick->getImageHeight());

        $whiteBarLeft = $imagick->getImagePixelColor(99, 49)->getHsl();
        $whiteBarRight = $imagick->getImagePixelColor(300, 49)->getHsl();

        $imageTop = $imagick->getImagePixelColor(249, 0)->getHsl();
        $imageBottom = $imagick->getImagePixelColor(249, 99)->getHsl();
        $imageLeft = $imagick->getImagePixelColor(100, 49)->getHsl();
        $imageRight = $imagick->getImagePixelColor(299, 49)->getHsl();

        $this->assertGreaterThan(0.9, $whiteBarLeft['luminosity']);
        $this->assertGreaterThan(0.9, $whiteBarRight['luminosity']);

        $this->assertLessThan(0.1, $imageLeft['luminosity']);
        $this->assertLessThan(0.1, $imageRight['luminosity']);
        $this->assertLessThan(0.1, $imageTop['luminosity']);
        $this->assertLessThan(0.1, $imageBottom['luminosity']);
    }

    /**
     * Upsize ratio 2.0 to 4.0
     *
     * @test
     */
    public function resize_upsizeToMoreVerticalAspect()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $imagick = Image::resize($source, 200, 400);

        //making sure source didnt resize
        $this->assertSame(100, $source->getImageWidth());
        $this->assertSame(50, $source->getImageHeight());

        $this->assertSame(200, $imagick->getImageWidth());
        $this->assertSame(400, $imagick->getImageHeight());

        $whiteBarLeft = $imagick->getImagePixelColor(49, 200)->getHsl();
        $whiteBarRight = $imagick->getImagePixelColor(151, 200)->getHsl();

        $imageTop = $imagick->getImagePixelColor(100, 176)->getHsl();
        $imageBottom = $imagick->getImagePixelColor(100, 224)->getHsl();
        $imageLeft = $imagick->getImagePixelColor(51, 200)->getHsl();
        $imageRight = $imagick->getImagePixelColor(149, 200)->getHsl();

        $this->assertGreaterThan(0.9, $whiteBarLeft['luminosity']);
        $this->assertGreaterThan(0.9, $whiteBarRight['luminosity']);

        $this->assertLessThan(0.1, $imageLeft['luminosity']);
        $this->assertLessThan(0.1, $imageRight['luminosity']);
        $this->assertLessThan(0.1, $imageTop['luminosity']);
        $this->assertLessThan(0.1, $imageBottom['luminosity']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $boxWidth was not between 0 and $options["maxWidth"]
     */
    public function resize_zeroBoxWidth()
    {
        Image::resize(new \Imagick(), 0, 10);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $boxWidth was not between 0 and $options["maxWidth"]
     */
    public function resize_largeBoxWidth()
    {
        Image::resize(new \Imagick(), 10001, 10, array('maxWidth' => 10000));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $boxHeight was not between 0 and $options["maxHeight"]
     */
    public function resize_zeroBoxHeight()
    {
        Image::resize(new \Imagick(), 10, 0);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $boxHeight was not between 0 and $options["maxHeight"]
     */
    public function resize_largeBoxHeight()
    {
        Image::resize(new \Imagick(), 10, 10001, array('maxHeight' => 10000));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["color"] was not a string
     */
    public function resize_nonStringColor()
    {
        Image::resize(new \Imagick(), 10, 10, array('color' => 0));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["maxWidth"] was not an int
     */
    public function resize_nonIntMaxWidth()
    {
        Image::resize(new \Imagick(), 10, 10, array('maxWidth' => 'not int'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["maxHeight"] was not an int
     */
    public function resize_nonIntMaxHeight()
    {
        Image::resize(new \Imagick(), 10, 10, array('maxHeight' => 'not int'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["upsize"] was not a bool
     */
    public function resize_nonBoolUpsize()
    {
        Image::resize(new \Imagick(), 10, 10, array('upsize' => 'not bool'));
    }

    /**
     * @test
     */
    public function write()
    {
        $destPath = "{$this->_tempDir}/dest.jpeg";

        $source = new \Imagick('pattern:gray0');
        $source->setImageFormat('png');
        $source->setImageProperty('exif:test', 'test');
        $this->assertSame('test', $source->getImageProperty('exif:test'));

        Image::write(
            $source,
            $destPath,
            array('format' => 'jpeg', 'directoryMode' => 0775, 'fileMode' => 0776, 'stripHeaders' => true)
        );

        $destImage = new \Imagick($destPath);

        $this->assertFalse($destImage->getImageProperty('exif:test'));
        $this->assertSame('JPEG', $destImage->getImageFormat());

        $directoryPermissions = substr(sprintf('%o', fileperms($this->_tempDir)), -4);
        $filePermissions = substr(sprintf('%o', fileperms($destPath)), -4);

        $this->assertSame('0775', $directoryPermissions);
        $this->assertSame('0776', $filePermissions);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["directoryMode"] was not an int
     */
    public function write_nonIntDirectoryMode()
    {
        Image::write(new \Imagick(), 'not under test', array('directoryMode' => 'not int'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["fileMode"] was not an int
     */
    public function write_nonIntFileMode()
    {
        Image::write(new \Imagick(), 'not under test', array('fileMode' => 'not int'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $destPath was not a string
     */
    public function write_nonStringDestPath()
    {
        Image::write(new \Imagick(), true);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["format"] was not a string
     */
    public function write_nonStringFormat()
    {
        Image::write(new \Imagick(), 'not under test', array('format' => true));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["stripHeaders"] was not a bool
     */
    public function write_nonBoolStripHeaders()
    {
        Image::write(new \Imagick(), 'not under test', array('stripHeaders' => 'not bool'));
    }

    /**
     * Verify that stripHeaders strips exif headers.
     *
     * @test
     */
    public function stripHeaders()
    {
        $path = "{$this->_tempDir}/stripHeaders.jpg";

        mkdir($this->_tempDir);
        copy("{$this->_sourceFilesDir}/exif.jpg", $path);

        Image::stripHeaders($path);

        $imagick = new \Imagick($path);
        $this->assertSame(0, count($imagick->getImageProperties('exif:*')));
    }

    /**
     * Verify that stripHeaders fails with a non-string path.
     *
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function stripHeaders_nonstringPath()
    {
        Image::stripHeaders(true);
    }

    /**
     * Verify that stripHeaders fails with a missing image.
     *
     * @test
     * @expectedException \ImagickException
     */
    public function stripHeaders_missingImage()
    {
        Image::stripHeaders("{$this->_tempDir}/doesnotexist.jpg");
    }
}
