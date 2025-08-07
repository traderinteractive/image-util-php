<?php

namespace TraderInteractive\Util;

use Imagick;
use InvalidArgumentException;
use TraderInteractive\Util;

final class Image
{
    /**
     * @var string
     */
    const DEFAULT_COLOR = 'white';

    /**
     * @var bool
     */
    const DEFAULT_UPSIZE = false;

    /**
     * @var bool
     */
    const DEFAULT_BESTFIT = false;

    /**
     * @var int
     */
    const DEFAULT_MAX_WIDTH = 10000;

    /**
     * @var int
     */
    const DEFAULT_MAX_HEIGHT = 10000;

    /**
     * @var bool
     */
    const DEFAULT_BLUR_BACKGROUND = false;

    /**
     * @var float
     */
    const DEFAULT_BLUR_VALUE = 15.0;

    /**
     * @var array
     */
    const DEFAULT_OPTIONS = [
        'color' => self::DEFAULT_COLOR,
        'upsize' => self::DEFAULT_UPSIZE,
        'bestfit' => self::DEFAULT_BESTFIT,
        'maxWidth' => self::DEFAULT_MAX_WIDTH,
        'maxHeight' => self::DEFAULT_MAX_HEIGHT,
        'blurBackground' => self::DEFAULT_BLUR_BACKGROUND,
        'blurValue' => self::DEFAULT_BLUR_VALUE,
    ];

    /**
     * @param Imagick $source    The image magick object to resize
     * @param int     $boxWidth  The final width of the image.
     * @param int     $boxHeight The final height of the image.
     * @param array   $options   Options for the resize operation.
     *
     * @return Imagick
     *
     * @throws \Exception Thrown if options are invalid.
     */
    public static function resize(Imagick $source, int $boxWidth, int $boxHeight, array $options = []) : Imagick
    {
        $options += self::DEFAULT_OPTIONS;

        $color = $options['color'];
        Util::ensure(true, is_string($color), InvalidArgumentException::class, ['$options["color"] was not a string']);

        $upsize = $options['upsize'];
        Util::ensure(true, is_bool($upsize), InvalidArgumentException::class, ['$options["upsize"] was not a bool']);

        $bestfit = $options['bestfit'];
        Util::ensure(true, is_bool($bestfit), InvalidArgumentException::class, ['$options["bestfit"] was not a bool']);

        $blurBackground = $options['blurBackground'];
        Util::ensure(
            true,
            is_bool($blurBackground),
            InvalidArgumentException::class,
            ['$options["blurBackground"] was not a bool']
        );

        $blurValue = $options['blurValue'];
        Util::ensure(
            true,
            is_float($blurValue),
            InvalidArgumentException::class,
            ['$options["blurValue"] was not a float']
        );
        $maxWidth = $options['maxWidth'];
        Util::ensure(true, is_int($maxWidth), InvalidArgumentException::class, ['$options["maxWidth"] was not an int']);

        $maxHeight = $options['maxHeight'];
        Util::ensure(
            true,
            is_int($maxHeight),
            InvalidArgumentException::class,
            ['$options["maxHeight"] was not an int']
        );


        if ($boxWidth > $maxWidth || $boxWidth <= 0) {
            throw new InvalidArgumentException('a $boxSizes width was not between 0 and $options["maxWidth"]');
        }

        if ($boxHeight > $maxHeight || $boxHeight <= 0) {
            throw new InvalidArgumentException('a $boxSizes height was not between 0 and $options["maxHeight"]');
        }

        $clone = clone $source;

        self::rotateImage($clone);

        $width = $clone->getImageWidth();
        $height = $clone->getImageHeight();

        //ratio over 1 is horizontal, under 1 is vertical
        $boxRatio = $boxWidth / $boxHeight;
        //height should be positive since I didnt find a way you could get zero into imagick
        $originalRatio = $width / $height;

        $targetWidth = null;
        $targetHeight = null;
        $targetX = null;
        $targetY = null;
        if ($width < $boxWidth && $height < $boxHeight && !$upsize) {
            $targetWidth = $width;
            $targetHeight = $height;
            $targetX = ($boxWidth - $width) / 2;
            $targetY = ($boxHeight - $height) / 2;
        } else {
            //if box is more vertical than original
            if ($boxRatio < $originalRatio) {
                $targetWidth = $boxWidth;
                $targetHeight = (int)((double)$boxWidth / $originalRatio);
                $targetX = 0;
                $targetY = ($boxHeight - $targetHeight) / 2;
            } else {
                $targetWidth = (int)((double)$boxHeight * $originalRatio);
                $targetHeight = $boxHeight;
                $targetX = ($boxWidth - $targetWidth) / 2;
                $targetY = 0;
            }
        }

        $widthReduced = false;
        if ($width > $targetWidth) {
            $width = $targetWidth;
            $widthReduced = true;
        }

        $heightReduced = false;
        if ($height > $targetHeight) {
            $height = $targetHeight;
            $heightReduced = true;
        }

        if ($widthReduced || $heightReduced) {
            if ($clone->resizeImage($width, $height, \Imagick::FILTER_BOX, 1.0) !== true) {
                //cumbersome to test
                throw new \Exception('Imagick::resizeImage() did not return true');//@codeCoverageIgnore
            }
        }

        if ($upsize && ($width < $targetWidth || $height < $targetHeight)) {
            if ($clone->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_CUBIC, 1.0, $bestfit) !== true) {
                //cumbersome to test
                throw new \Exception('Imagick::resizeImage() did not return true');//@codeCoverageIgnore
            }
        }

        if ($clone->getImageHeight() === $boxHeight && $clone->getImageWidth() === $boxWidth) {
            return $clone;
        }

        //put image in box
        $canvas = self::getBackgroundCanvas($clone, $color, $blurBackground, $blurValue, $boxWidth, $boxHeight);
        if ($canvas->compositeImage($clone, Imagick::COMPOSITE_DEFAULT, (int)$targetX, (int)$targetY) !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::compositeImage() did not return true');//@codeCoverageIgnore
        }

        //reason we are not supporting the options in self::write() here is because format, and strip headers are
        //only relevant once written Imagick::stripImage() doesnt even have an effect until written
        //also the user can just call that function with the resultant $canvas
        return $canvas;
    }

    /**
     * resizes images into a bounding box. Maintains aspect ratio, extra space filled with given color.
     *
     * @param \Imagick $source source image to resize. Will not modify
     * @param array $boxSizes resulting bounding boxes. Each value should be an array with width and height, both
     *                        integers
     * @param array $options options
     *     string color (default white) background color. Any supported from
     *         http://www.imagemagick.org/script/color.php#color_names
     *     bool upsize (default false) true to upsize the original image or false to upsize just the bounding box
     *     bool bestfit (default false) true to resize with the best fit option.
     *     int maxWidth (default 10000) max width allowed for $boxWidth
     *     int maxHeight (default 10000) max height allowed for $boxHeight
     *     bool blurBackground (default false) true to create a composite resized image placed over an enlarged blurred
     *                         image of the original.
     *
     * @return array array of \Imagick objects resized. Keys maintained from $boxSizes
     *
     * @throws InvalidArgumentException if $options["color"] was not a string
     * @throws InvalidArgumentException if $options["upsize"] was not a bool
     * @throws InvalidArgumentException if $options["bestfit"] was not a bool
     * @throws InvalidArgumentException if $options["maxWidth"] was not an int
     * @throws InvalidArgumentException if $options["maxHeight"] was not an int
     * @throws InvalidArgumentException if a width in a $boxSizes value was not an int
     * @throws InvalidArgumentException if a height in a $boxSizes value was not an int
     * @throws InvalidArgumentException if a $boxSizes width was not between 0 and $options["maxWidth"]
     * @throws InvalidArgumentException if a $boxSizes height was not between 0 and $options["maxHeight"]
     * @throws \Exception
     */
    public static function resizeMulti(\Imagick $source, array $boxSizes, array $options = []) : array
    {
        //algorithm inspired from http://today.java.net/pub/a/today/2007/04/03/perils-of-image-getscaledinstance.html
        //use of 2x2 binning is arguably the best quality one will get downsizing and is what lots of hardware does in
        //the photography field, while being reasonably fast. Upsizing is more subjective but you can't get much
        //better than bicubic which is what is used here.

        $options = $options + self::DEFAULT_OPTIONS;
        $color = $options['color'];
        Util::ensure(true, is_string($color), InvalidArgumentException::class, ['$options["color"] was not a string']);

        $upsize = $options['upsize'];
        Util::ensure(true, is_bool($upsize), InvalidArgumentException::class, ['$options["upsize"] was not a bool']);

        $bestfit = $options['bestfit'];
        Util::ensure(true, is_bool($bestfit), InvalidArgumentException::class, ['$options["bestfit"] was not a bool']);

        $blurBackground = $options['blurBackground'];
        Util::ensure(
            true,
            is_bool($blurBackground),
            InvalidArgumentException::class,
            ['$options["blurBackground"] was not a bool']
        );

        $blurValue = $options['blurValue'];
        Util::ensure(
            true,
            is_float($blurValue),
            InvalidArgumentException::class,
            ['$options["blurValue"] was not a float']
        );
        $maxWidth = $options['maxWidth'];
        Util::ensure(true, is_int($maxWidth), InvalidArgumentException::class, ['$options["maxWidth"] was not an int']);

        $maxHeight = $options['maxHeight'];
        Util::ensure(
            true,
            is_int($maxHeight),
            InvalidArgumentException::class,
            ['$options["maxHeight"] was not an int']
        );

        foreach ($boxSizes as $boxSizeKey => $boxSize) {
            if (!isset($boxSize['width']) || !is_int($boxSize['width'])) {
                throw new InvalidArgumentException('a width in a $boxSizes value was not an int');
            }

            if (!isset($boxSize['height']) || !is_int($boxSize['height'])) {
                throw new InvalidArgumentException('a height in a $boxSizes value was not an int');
            }

            if ($boxSize['width'] > $maxWidth || $boxSize['width'] <= 0) {
                throw new InvalidArgumentException('a $boxSizes width was not between 0 and $options["maxWidth"]');
            }

            if ($boxSize['height'] > $maxHeight || $boxSize['height'] <= 0) {
                throw new InvalidArgumentException('a $boxSizes height was not between 0 and $options["maxHeight"]');
            }
        }

        $results = [];
        $cloneCache = [];
        foreach ($boxSizes as $boxSizeKey => $boxSize) {
            $boxWidth = $boxSize['width'];
            $boxHeight = $boxSize['height'];

            $clone = clone $source;

            self::rotateImage($clone);

            $width = $clone->getImageWidth();
            $height = $clone->getImageHeight();

            //ratio over 1 is horizontal, under 1 is vertical
            $boxRatio = $boxWidth / $boxHeight;
            //height should be positive since I didnt find a way you could get zero into imagick
            $originalRatio = $width / $height;

            $targetWidth = null;
            $targetHeight = null;
            $targetX = null;
            $targetY = null;
            if ($width < $boxWidth && $height < $boxHeight && !$upsize) {
                $targetWidth = $width;
                $targetHeight = $height;
                $targetX = ($boxWidth - $width) / 2;
                $targetY = ($boxHeight - $height) / 2;
            } else {
                //if box is more vertical than original
                if ($boxRatio < $originalRatio) {
                    $targetWidth = $boxWidth;
                    $targetHeight = (int)((double)$boxWidth / $originalRatio);
                    $targetX = 0;
                    $targetY = ($boxHeight - $targetHeight) / 2;
                } else {
                    $targetWidth = (int)((double)$boxHeight * $originalRatio);
                    $targetHeight = $boxHeight;
                    $targetX = ($boxWidth - $targetWidth) / 2;
                    $targetY = 0;
                }
            }

            //do iterative downsize by halfs (2x2 binning is a common name) on dimensions that are bigger than target
            //width and height
            while (true) {
                $widthReduced = false;
                $widthIsHalf = false;
                if ($width > $targetWidth) {
                    $width = (int)($width / 2);
                    $widthReduced = true;
                    $widthIsHalf = true;
                    if ($width < $targetWidth) {
                        $width = $targetWidth;
                        $widthIsHalf = false;
                    }
                }

                $heightReduced = false;
                $heightIsHalf = false;
                if ($height > $targetHeight) {
                    $height = (int)($height / 2);
                    $heightReduced = true;
                    $heightIsHalf = true;
                    if ($height < $targetHeight) {
                        $height = $targetHeight;
                        $heightIsHalf = false;
                    }
                }

                if (!$widthReduced && !$heightReduced) {
                    break;
                }

                $cacheKey = "{$width}x{$height}";
                if (isset($cloneCache[$cacheKey])) {
                    $clone = clone $cloneCache[$cacheKey];
                    continue;
                }

                if ($clone->resizeImage($width, $height, \Imagick::FILTER_BOX, 1.0) !== true) {
                    //cumbersome to test
                    throw new \Exception('Imagick::resizeImage() did not return true');//@codeCoverageIgnore
                }

                if ($widthIsHalf && $heightIsHalf) {
                    $cloneCache[$cacheKey] = clone $clone;
                }
            }

            if ($upsize && ($width < $targetWidth || $height < $targetHeight)) {
                if ($clone->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_CUBIC, 1.0, $bestfit) !== true) {
                    //cumbersome to test
                    throw new \Exception('Imagick::resizeImage() did not return true');//@codeCoverageIgnore
                }
            }

            if ($clone->getImageHeight() === $boxHeight && $clone->getImageWidth() === $boxWidth) {
                $results[$boxSizeKey] = $clone;
                continue;
            }

            //put image in box
            $canvas = self::getBackgroundCanvas($source, $color, $blurBackground, $blurValue, $boxWidth, $boxHeight);
            if ($canvas->compositeImage($clone, \Imagick::COMPOSITE_ATOP, (int)$targetX, (int)$targetY) !== true) {
                //cumbersome to test
                throw new \Exception('Imagick::compositeImage() did not return true');//@codeCoverageIgnore
            }

            //reason we are not supporting the options in self::write() here is because format, and strip headers are
            //only relevant once written Imagick::stripImage() doesnt even have an effect until written
            //also the user can just call that function with the resultant $canvas
            $results[$boxSizeKey] = $canvas;
        }

        foreach ($cloneCache as $clone) {
            $clone->destroy();
        }

        return $results;
    }

    private static function getBackgroundCanvas(
        \Imagick $source,
        string $color,
        bool $blurBackground,
        float $blurValue,
        int $boxWidth,
        int $boxHeight
    ) : \Imagick {
        if ($blurBackground || $color === 'blur') {
            return self::getBlurredBackgroundCanvas($source, $blurValue, $boxWidth, $boxHeight);
        }

        return self::getColoredBackgroundCanvas($color, $boxWidth, $boxHeight);
    }

    private static function getColoredBackgroundCanvas(string $color, int $boxWidth, int $boxHeight)
    {
        $canvas = new \Imagick();
        $imageCreated = $canvas->newImage($boxWidth, $boxHeight, $color);
        Util::ensure(true, $imageCreated, 'Imagick::newImage() did not return true');
        return $canvas;
    }

    private static function getBlurredBackgroundCanvas(
        \Imagick $source,
        float $blurValue,
        int $boxWidth,
        int $boxHeight
    ) : \Imagick {
        $canvas = clone $source;
        self::rotateImage($canvas);
        $canvas->resizeImage($boxWidth, $boxHeight, \Imagick::FILTER_BOX, $blurValue, false);
        return $canvas;
    }

    /**
     * write $source to $destPath with $options applied
     *
     * @param \Imagick $source source image. Will not modify
     * @param string $destPath destination image path
     * @param array $options options
     *     string format        (default jpeg) Any from http://www.imagemagick.org/script/formats.php#supported
     *     int    directoryMode (default 0777) chmod mode for any parent directories created
     *     int    fileMode      (default 0777) chmod mode for the resized image file
     *     bool   stripHeaders  (default true) whether to strip headers (exif, etc). Is only reflected in $destPath,
     *                                         not returned clone
     *
     * @return void
     *
     * @throws InvalidArgumentException if $destPath was not a string
     * @throws InvalidArgumentException if $options["format"] was not a string
     * @throws InvalidArgumentException if $options["directoryMode"] was not an int
     * @throws InvalidArgumentException if $options["fileMode"] was not an int
     * @throws InvalidArgumentException if $options["stripHeaders"] was not a bool
     * @throws \Exception
     */
    public static function write(\Imagick $source, string $destPath, array $options = [])
    {
        $format = 'jpeg';
        if (array_key_exists('format', $options)) {
            $format = $options['format'];
            if (!is_string($format)) {
                throw new InvalidArgumentException('$options["format"] was not a string');
            }
        }

        $directoryMode = 0777;
        if (array_key_exists('directoryMode', $options)) {
            $directoryMode = $options['directoryMode'];
            if (!is_int($directoryMode)) {
                throw new InvalidArgumentException('$options["directoryMode"] was not an int');
            }
        }

        $fileMode = 0777;
        if (array_key_exists('fileMode', $options)) {
            $fileMode = $options['fileMode'];
            if (!is_int($fileMode)) {
                throw new InvalidArgumentException('$options["fileMode"] was not an int');
            }
        }

        $stripHeaders = true;
        if (array_key_exists('stripHeaders', $options)) {
            $stripHeaders = $options['stripHeaders'];
            if ($stripHeaders !== false && $stripHeaders !== true) {
                throw new InvalidArgumentException('$options["stripHeaders"] was not a bool');
            }
        }

        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            $oldUmask = umask(0);
            if (!mkdir($destDir, $directoryMode, true)) {
                //cumbersome to test
                throw new \Exception('mkdir() returned false');//@codeCoverageIgnore
            }

            umask($oldUmask);
        }

        $clone = clone $source;

        if ($clone->setImageFormat($format) !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::setImageFormat() did not return true');//@codeCoverageIgnore
        }

        if ($stripHeaders && $clone->stripImage() !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::stripImage() did not return true');//@codeCoverageIgnore
        }

        if ($clone->writeImage($destPath) !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::writeImage() did not return true');//@codeCoverageIgnore
        }

        if (!chmod($destPath, $fileMode)) {
            //cumbersome to test
            throw new \Exception('chmod() returned false');//@codeCoverageIgnore
        }
    }

    /**
     * Strips the headers (exif, etc) from an image at the given path.
     *
     * @param string $path The image path.
     * @return void
     * @throws InvalidArgumentException if $path is not a string
     * @throws \Exception if there is a failure stripping the headers
     * @throws \Exception if there is a failure writing the image back to path
     */
    public static function stripHeaders(string $path)
    {
        $imagick = new \Imagick($path);
        if ($imagick->stripImage() !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::stripImage() did not return true');//@codeCoverageIgnore
        }

        if ($imagick->writeImage($path) !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::writeImage() did not return true');//@codeCoverageIgnore
        }
    }

    /**
     * Rotate an image according to EXIF header.
     *
     * @param \Imagick $imagick
     *
     * @return void
     */
    public static function rotateImage(\Imagick $imagick)
    {
        $orientation = $imagick->getImageOrientation();
        switch ($orientation) {
            case \Imagick::ORIENTATION_BOTTOMRIGHT:
                $imagick->rotateimage('#fff', 180);
                $imagick->stripImage();
                break;
            case \Imagick::ORIENTATION_RIGHTTOP:
                $imagick->rotateimage('#fff', 90);
                $imagick->stripImage();
                break;
            case \Imagick::ORIENTATION_LEFTBOTTOM:
                $imagick->rotateimage('#fff', -90);
                $imagick->stripImage();
                break;
        }
    }
}
