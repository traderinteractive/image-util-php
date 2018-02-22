<?php

namespace TraderInteractive\Util;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Util\Image
 */
final class ImageTest extends TestCase
{
    private $_sourceFilesDir;
    private $_tempDir;

    public function setUp()
    {
        $this->_sourceFilesDir = __DIR__ . '/_files';
        $this->_tempDir = sys_get_temp_dir() . '/imageUtilTest';
        if (is_dir($this->_tempDir)) {
            foreach (glob("{$this->_tempDir}/*") as $file) {
                unlink($file);
            }

            rmdir($this->_tempDir);
        }
    }

    /**
     * Downsize ratio 2.0 to 0.25
     *
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     */
    public function resizeDownsizeToMoreVerticalAspect()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $imagick = Image::resize($source, 10, 40, ['color' => 'white', 'maxWidth' => 10000, 'maxHeight' => 10000]);

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
     * @covers ::resize
     * @covers ::resizeMulti
     */
    public function resizeDownsizeToMoreHorizontalAspect()
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
     * @covers ::resize
     * @covers ::resizeMulti
     */
    public function resizeUpsizeToMoreHorizontalAspectWithoutGrow()
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
     * @covers ::resize
     * @covers ::resizeMulti
     */
    public function resizeUpsizeToMoreHorizontalAspectWithGrow()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $imagick = Image::resize($source, 400, 100, ['upsize' => true]);

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
     * @covers ::resize
     * @covers ::resizeMulti
     */
    public function resizeUpsizeToMoreVerticalAspect()
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
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a $boxSizes width was not between 0 and $options["maxWidth"]
     */
    public function resizeZeroBoxWidth()
    {
        Image::resize(new \Imagick(), 0, 10);
    }

    /**
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a $boxSizes width was not between 0 and $options["maxWidth"]
     */
    public function resizeLargeBoxWidth()
    {
        Image::resize(new \Imagick(), 10001, 10, ['maxWidth' => 10000]);
    }

    /**
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a $boxSizes height was not between 0 and $options["maxHeight"]
     */
    public function resizeZeroBoxHeight()
    {
        Image::resize(new \Imagick(), 10, 0);
    }

    /**
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a $boxSizes height was not between 0 and $options["maxHeight"]
     */
    public function resizeLargeBoxHeight()
    {
        Image::resize(new \Imagick(), 10, 10001, ['maxHeight' => 10000]);
    }

    /**
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["color"] was not a string
     */
    public function resizeNonStringColor()
    {
        Image::resize(new \Imagick(), 10, 10, ['color' => 0]);
    }

    /**
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["maxWidth"] was not an int
     */
    public function resizeonIntMaxWidth()
    {
        Image::resize(new \Imagick(), 10, 10, ['maxWidth' => 'not int']);
    }

    /**
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["maxHeight"] was not an int
     */
    public function resizeNonIntMaxHeight()
    {
        Image::resize(new \Imagick(), 10, 10, ['maxHeight' => 'not int']);
    }

    /**
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["upsize"] was not a bool
     */
    public function resizeNonBoolUpsize()
    {
        Image::resize(new \Imagick(), 10, 10, ['upsize' => 'not bool']);
    }

    /**
     * Verify images are rotated according to EXIF header
     * @test
     * @covers ::resize
     * @covers ::resizeMulti
     */
    public function resizeOrientation()
    {
        $files = [
            "{$this->_sourceFilesDir}/bottom-right.jpg",
            "{$this->_sourceFilesDir}/left-bottom.jpg",
            "{$this->_sourceFilesDir}/right-top.jpg",
            "{$this->_sourceFilesDir}/top-left.jpg",
        ];

        $imageResults = [];

        foreach ($files as $file) {
            $source = new \Imagick($file);
            $imageWidth = $source->getimagewidth();
            $imageHeight = $source->getimageheight();
            $imageResults[] = Image::resize($source, $imageWidth, $imageHeight, []);
        }

        $this->assertSame(
            ['r' => 254, 'g' => 0, 'b' => 0, 'a' => 1],
            $imageResults[0]->getImagePixelColor(0, 0)->getColor()
        );
        $this->assertSame(
            ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 1],
            $imageResults[1]->getImagePixelColor(0, 0)->getColor()
        );
        $this->assertSame(
            ['r' => 0, 'g' => 255, 'b' => 1, 'a' => 1],
            $imageResults[2]->getImagePixelColor(0, 0)->getColor()
        );
        $this->assertSame(
            ['r' => 0, 'g' => 0, 'b' => 254, 'a' => 1],
            $imageResults[3]->getImagePixelColor(0, 0)->getColor()
        );
    }

    /**
     * Downsize ratio 2.0 to 0.25 and 2.0 to 4.0
     *
     * @test
     * @covers ::resizeMulti
     */
    public function resizeMultiDownsizeToMoreVerticalAndMoreHorizontalAspect()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(100, 50);

        $results = Image::resizeMulti($source, [['width' => 10, 'height' => 40], ['width' => 40, 'height' => 10]]);
        $imagickOne = $results[0];
        $imagickTwo = $results[1];

        //making sure source didnt resize
        $this->assertSame(100, $source->getImageWidth());
        $this->assertSame(50, $source->getImageHeight());

        //check $imagick1

        $this->assertSame(10, $imagickOne->getImageWidth());
        $this->assertSame(40, $imagickOne->getImageHeight());

        $oneWhiteBarTop = $imagickOne->getImagePixelColor(4, 16)->getHsl();
        $oneWhiteBarBottom = $imagickOne->getImagePixelColor(4, 22)->getHsl();

        $oneImageLeft = $imagickOne->getImagePixelColor(0, 19)->getHsl();
        $oneImageRight = $imagickOne->getImagePixelColor(9, 19)->getHsl();
        $oneImageTop = $imagickOne->getImagePixelColor(4, 17)->getHsl();
        $oneImageBottom = $imagickOne->getImagePixelColor(4, 21)->getHsl();

        $this->assertGreaterThan(0.9, $oneWhiteBarTop['luminosity']);
        $this->assertGreaterThan(0.9, $oneWhiteBarBottom['luminosity']);

        $this->assertLessThan(0.1, $oneImageLeft['luminosity']);
        $this->assertLessThan(0.1, $oneImageRight['luminosity']);
        $this->assertLessThan(0.1, $oneImageTop['luminosity']);
        $this->assertLessThan(0.1, $oneImageBottom['luminosity']);

        //check $imagick2

        $this->assertSame(40, $imagickTwo->getImageWidth());
        $this->assertSame(10, $imagickTwo->getImageHeight());

        $twoWhiteBarLeft = $imagickTwo->getImagePixelColor(9, 4)->getHsl();
        $twoWhiteBarRight = $imagickTwo->getImagePixelColor(30, 4)->getHsl();

        $twoImageLeft = $imagickTwo->getImagePixelColor(10, 4)->getHsl();
        $twoImageRight = $imagickTwo->getImagePixelColor(29, 4)->getHsl();
        $twoImageTop = $imagickTwo->getImagePixelColor(19, 0)->getHsl();
        $twoImageBottom = $imagickTwo->getImagePixelColor(19, 9)->getHsl();

        $this->assertGreaterThan(0.9, $twoWhiteBarLeft['luminosity']);
        $this->assertGreaterThan(0.9, $twoWhiteBarRight['luminosity']);

        $this->assertLessThan(0.1, $twoImageLeft['luminosity']);
        $this->assertLessThan(0.1, $twoImageRight['luminosity']);
        $this->assertLessThan(0.1, $twoImageTop['luminosity']);
        $this->assertLessThan(0.1, $twoImageBottom['luminosity']);
    }

    /**
     * @test
     * @covers ::resizeMulti
     */
    public function resizeMultiPerformance()
    {
        $source = new \Imagick('pattern:gray0');
        $source->scaleImage(2000, 500);

        $count = 10;

        $beforeSingle = microtime(true);
        for ($i = 0; $i < $count; ++$i) {
            Image::resize($source, 1100, 400);
            Image::resize($source, 100, 400);
            Image::resize($source, 10, 40);
        }

        $singleTime = microtime(true) - $beforeSingle;

        $beforeMulti = microtime(true);
        for ($i = 0; $i < $count; ++$i) {
            Image::resizeMulti(
                $source,
                [['width' => 1100, 'height' => 400], ['width' => 100, 'height' => 400], ['width' => 10, 'height' => 40]]
            );
        }

        $multiTime = microtime(true) - $beforeMulti;

        $this->assertLessThan($singleTime, $multiTime * 0.75);
    }

    /**
     * @test
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a width in a $boxSizes value was not an int
     */
    public function resizeMultiNonIntWidth()
    {
        Image::resizeMulti(new \Imagick(), [['width' => true, 'height' => 10]]);
    }

    /**
     * @test
     * @covers ::resizeMulti
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a height in a $boxSizes value was not an int
     */
    public function resizeMultiNonIntHeight()
    {
        Image::resizeMulti(new \Imagick(), [['width' => 10, 'height' => true]]);
    }

    /**
     * @test
     * @covers ::write
     */
    public function write()
    {
        $destPath = "{$this->_tempDir}/dest.jpeg";

        $source = new \Imagick("{$this->_sourceFilesDir}/exif.jpg");
        $source->setImageFormat('png');

        Image::write($source, $destPath, ['format' => 'jpeg', 'directoryMode' => 0775, 'fileMode' => 0776, 'stripHeaders' => true]);

        $destImage = new \Imagick($destPath);

        $this->assertSame(0, count($destImage->getImageProperties('exif:*')));
        $this->assertSame('JPEG', $destImage->getImageFormat());

        $directoryPermissions = substr(sprintf('%o', fileperms($this->_tempDir)), -4);
        $filePermissions = substr(sprintf('%o', fileperms($destPath)), -4);

        $this->assertSame('0775', $directoryPermissions);
        $this->assertSame('0776', $filePermissions);
    }

    /**
     * @test
     * @covers ::write
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["directoryMode"] was not an int
     */
    public function writeNonIntDirectoryMode()
    {
        Image::write(new \Imagick(), 'not under test', ['directoryMode' => 'not int']);
    }

    /**
     * @test
     * @covers ::write
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["fileMode"] was not an int
     */
    public function writeNonIntFileMode()
    {
        Image::write(new \Imagick(), 'not under test', ['fileMode' => 'not int']);
    }

    /**
     * @test
     * @covers ::write
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["format"] was not a string
     */
    public function writeNonStringFormat()
    {
        Image::write(new \Imagick(), 'not under test', ['format' => true]);
    }

    /**
     * @test
     * @covers ::write
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $options["stripHeaders"] was not a bool
     */
    public function writeNonBoolStripHeaders()
    {
        Image::write(new \Imagick(), 'not under test', ['stripHeaders' => 'not bool']);
    }

    /**
     * Verify that stripHeaders strips exif headers.
     *
     * @test
     * @covers ::stripHeaders
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
     * Verify that stripHeaders fails with a missing image.
     *
     * @test
     * @covers ::stripHeaders
     * @expectedException \ImagickException
     */
    public function stripHeadersMissingImage()
    {
        Image::stripHeaders("{$this->_tempDir}/doesnotexist.jpg");
    }
}
