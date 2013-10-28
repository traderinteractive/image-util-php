<?php

namespace DominionEnterprises\Util;

final class Image
{
    /**
     * resizes an image into a bounding box. Maintains aspect ratio, extra space filled with given color.
     *
     * @param \Imagick $source source image to resize. Will not modify
     * @param int $boxWidth width of the resulting bounding box
     * @param int $boxHeight height of the resulting bounding box
     * @param array $options options
     *     string color (default white) background color. Any supported from
     *         http://www.imagemagick.org/script/color.php#color_names
     *     bool upsize (default false) true to upsize the original image or false to upsize just the bounding box
     *     int maxWidth (default 10000) max width allowed for $boxWidth
     *     int maxHeight (default 10000) max height allowed for $boxHeight
     *
     * @return \Imagick the image resized
     *
     * @throws \InvalidArgumentException if $options["color"] was not a string
     * @throws \InvalidArgumentException if $options["upsize"] was not a bool
     * @throws \InvalidArgumentException if $options["maxWidth"] was not an int
     * @throws \InvalidArgumentException if $options["maxHeight"] was not an int
     * @throws \InvalidArgumentException if $boxWidth was not between 0 and $options["maxWidth"]
     * @throws \InvalidArgumentException if $boxHeight was not between 0 and $options["maxHeight"]
     * @throws \Exception
     */
    public static function resize(\Imagick $source, $boxWidth, $boxHeight, array $options = array())
    {
        //algorithm inspiration from http://today.java.net/pub/a/today/2007/04/03/perils-of-image-getscaledinstance.html
        //use of 2x2 binning is arguably the best quality one will get downsizing and is what lots of hardware does in the photography field,
        //while being reasonably fast
        //upsizing is more subjective but you can't get much better than bicubic which is what is used here.

        $color = 'white';
        if (array_key_exists('color', $options)) {
            $color = $options['color'];
            if (!is_string($color)) {
                throw new \InvalidArgumentException('$options["color"] was not a string');
            }
        }

        $upsize = false;
        if (array_key_exists('upsize', $options)) {
            $upsize = $options['upsize'];
            if ($upsize !== true && $upsize !== false) {
                throw new \InvalidArgumentException('$options["upsize"] was not a bool');
            }
        }

        $maxWidth = 10000;
        if (array_key_exists('maxWidth', $options)) {
            $maxWidth = $options['maxWidth'];
            if (!is_int($maxWidth)) {
                throw new \InvalidArgumentException('$options["maxWidth"] was not an int');
            }
        }

        $maxHeight = 10000;
        if (array_key_exists('maxHeight', $options)) {
            $maxHeight = $options['maxHeight'];
            if (!is_int($maxHeight)) {
                throw new \InvalidArgumentException('$options["maxHeight"] was not an int');
            }
        }

        if ($boxWidth > $maxWidth || $boxWidth <= 0) {
            throw new \InvalidArgumentException('$boxWidth was not between 0 and $options["maxWidth"]');
        }

        if ($boxHeight > $maxHeight || $boxHeight <= 0) {
            throw new \InvalidArgumentException('$boxHeight was not between 0 and $options["maxHeight"]');
        }

        $clone = clone $source;

        $width = $clone->getImageWidth();
        $height = $clone->getImageHeight();

        //ratio over 1 is horizontal, under 1 is vertical
        $boxRatio = (double)$boxWidth / (double)$boxHeight;
        $originalRatio = (double)$width / (double)$height;//height should be positive since I didnt find a way you could get zero into imagick

        $targetWidth = null;
        $targetHeight = null;
        $targetX = null;
        $targetY = null;

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

        //do downsize on dimensions that need it by halfs (2x2 binning is a common name)
        while (true) {
            $widthReduced = false;
            if ($width > $targetWidth) {
                $widthReduced = true;
                $width /= 2;
                if ($width < $targetWidth) {
                    $width = $targetWidth;
                }
            }

            $heightReduced = false;
            if ($height > $targetHeight) {
                $heightReduced = true;
                $height /= 2;
                if ($height < $targetHeight) {
                    $height = $targetHeight;
                }
            }

            if (!$widthReduced && !$heightReduced) {
                break;
            }

            if ($clone->resizeImage($width, $height, \Imagick::FILTER_BOX, 1.0) !== true) {
                //cumbersome to test
                throw new \Exception('Imagick::resizeImage() did not return true');//@codeCoverageIgnore
            }
        }

        //upsize or set image edges if either dimension wasnt a downsize (because it didnt get to target width/height)
        if ($width !== $targetWidth || $height !== $targetHeight) {
            if ($upsize) {
                if ($clone->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_CUBIC, 1.0) !== true) {
                    //cumbersome to test
                    throw new \Exception('Imagick::resizeImage() did not return true');//@codeCoverageIgnore
                }
            } else {
                if ($boxRatio < $originalRatio) {
                    $targetX = ($targetWidth - $width) / 2;
                    $targetY = ($boxHeight - $height) / 2;
                } else {
                    $targetX = ($boxWidth - $width) / 2;
                    $targetY = ($targetHeight - $height) / 2;
                }
            }
        }

        //put image in box
        $canvas = new \Imagick();
        if ($canvas->newImage($boxWidth, $boxHeight, $color) !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::newImage() did not return true');//@codeCoverageIgnore
        }

        if ($canvas->compositeImage($clone, \Imagick::COMPOSITE_ATOP, $targetX, $targetY) !== true) {
            //cumbersome to test
            throw new \Exception('Imagick::compositeImage() did not return true');//@codeCoverageIgnore
        }

        //reason we are not supporting the options in self::write() here is because format, and strip headers are only relevant once written
        //Imagick::stripImage() doesnt even have an effect until written
        //also the user can just call that fuction with the resultant $canvas
        return $canvas;
    }

    /**
     * write $source to $destPath with $options applied
     *
     * @param \Imagick $source source image. Will not modify
     * @param string $destPath destination image path
     * @param array $options options
     *     string format (default jpeg) format. Any supported from http://www.imagemagick.org/script/formats.php#supported
     *     int directoryMode (default 0777) chmod mode for any parent directories created
     *     int fileMode (default 0777) chmod mode for the resized image file
     *     bool stripHeaders (default true) whether to strip headers (exif, etc). Is only reflected in $destPath, not returned clone
     *
     * @return void
     *
     * @throws \InvalidArgumentException if $destPath was not a string
     * @throws \InvalidArgumentException if $options["format"] was not a string
     * @throws \InvalidArgumentException if $options["directoryMode"] was not an int
     * @throws \InvalidArgumentException if $options["fileMode"] was not an int
     * @throws \InvalidArgumentException if $options["stripHeaders"] was not a bool
     * @throws \Exception
     */
    public static function write(\Imagick $source, $destPath, array $options = array())
    {
        if (!is_string($destPath)) {
            throw new \InvalidArgumentException('$destPath was not a string');
        }

        $format = 'jpeg';
        if (array_key_exists('format', $options)) {
            $format = $options['format'];
            if (!is_string($format)) {
                throw new \InvalidArgumentException('$options["format"] was not a string');
            }
        }

        $directoryMode = 0777;
        if (array_key_exists('directoryMode', $options)) {
            $directoryMode = $options['directoryMode'];
            if (!is_int($directoryMode)) {
                throw new \InvalidArgumentException('$options["directoryMode"] was not an int');
            }
        }

        $fileMode = 0777;
        if (array_key_exists('fileMode', $options)) {
            $fileMode = $options['fileMode'];
            if (!is_int($fileMode)) {
                throw new \InvalidArgumentException('$options["fileMode"] was not an int');
            }
        }

        $stripHeaders = true;
        if (array_key_exists('stripHeaders', $options)) {
            $stripHeaders = $options['stripHeaders'];
            if ($stripHeaders !== false && $stripHeaders !== true) {
                throw new \InvalidArgumentException('$options["stripHeaders"] was not a bool');
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
     * @throws \InvalidArgumentException if $path is not a string
     * @throws \Exception if there is a failure stripping the headers
     * @throws \Exception if there is a failure writing the image back to path
     */
    public static function stripHeaders($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('$path was not a string');
        }

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
}
