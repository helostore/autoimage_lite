<?php
/**
 * HELOstore
 *
 * This source file is part of a commercial software. Only users who have purchased a valid license through
 * https://helostore.com/ and accepted to the terms of the License Agreement can install this product.
 *
 * @category   Add-ons
 * @package    HELOstore
 * @copyright  Copyright (c) 2015-2016 HELOstore. (https://helostore.com/)
 * @license    https://helostore.com/legal/license-agreement/   License Agreement
 * @version    $Id$
 */

namespace HeloStore\AutoImage;
use HeloStore\AutoImage\Method\Basic;
use HeloStore\AutoImage\Method\SmartGd;
use stojg\crop\CropBalanced;
use stojg\crop\CropEntropy;
use Tygh\Registry;

/**
 * Class ImageResizeManager
 *
 * @package HeloStore\AutoImage
 */
class ImageResizeManager extends Singleton
{
    /**
     * Stub for testing purposes only
     * @param $inputAbsoluteFilePath
     * @param $outputAbsoluteFilePath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    public function original($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height)
    {
        if (!file_exists($outputAbsoluteFilePath)) {
            return fn_copy($inputAbsoluteFilePath, $outputAbsoluteFilePath);
        }

        return true;
    }
    /**
     * Basic method based on cropping at center
     *
     * @param $inputAbsoluteFilePath
     * @param $outputAbsoluteFilePath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    public function basic($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height)
    {
        $method = new Basic($inputAbsoluteFilePath);
        
        return $method->resizeAndCrop($outputAbsoluteFilePath, $width, $height);
    }

    /**
     * Entropy / Color methods hybrid
     *
     * @param $inputAbsoluteFilePath
     * @param $outputAbsoluteFilePath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    public function hybrid($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height)
    {

        $mimeType = mime_content_type($inputAbsoluteFilePath);
        $ext = fn_get_image_extension($mimeType);

        if ($ext == 'gif') {
            $gdInputImage = imagecreatefromgif($inputAbsoluteFilePath);
        } elseif ($ext == 'jpg') {
            $gdInputImage = imagecreatefromjpeg($inputAbsoluteFilePath);
        } elseif ($ext == 'png') {
            $gdInputImage = imagecreatefrompng($inputAbsoluteFilePath);
        } else {
            return false;
        }

        if (empty($gdInputImage)) {
                return false;
        }
        $smartGd = new SmartGd($gdInputImage);
        $gdOutputImage = $smartGd->get_resized($width, $height);
        if (!$gdOutputImage) {
            return false;
        }
        $result = imagejpeg($gdOutputImage, $outputAbsoluteFilePath);
        if (!$result) {
            return false;
        }
        imagedestroy($gdInputImage);
        imagedestroy($gdOutputImage);

        return true;
    }

    /**
     * Entropy based
     *
     * @param $inputAbsoluteFilePath
     * @param $outputAbsoluteFilePath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    public function entropy($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height)
    {
        $cropper = new CropEntropy($inputAbsoluteFilePath);
        $croppedImage = $cropper->resizeAndCrop($width, $height);

        return $croppedImage->writeimage($outputAbsoluteFilePath);
    }

    /**
     * Similar with the hybrid method, method based on entropy and interest point
     *
     * @param $inputAbsoluteFilePath
     * @param $outputAbsoluteFilePath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    public function balanced($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height)
    {
        $cropper = new CropBalanced($inputAbsoluteFilePath);
        $croppedImage = $cropper->resizeAndCrop($width, $height);

        return $croppedImage->writeimage($outputAbsoluteFilePath);
    }


    public function getAllMethods()
    {
        return array(
            'original' => array(
                'label' =>  'Original',
                'callable' =>  array($this, 'original'),
                'hidden' =>  true,
            ),
            'basic' => array(
                'label' =>  'Basic',
                'callable' =>  array($this, 'basic'),
            ),
            'hybrid' => array(
                'label' =>  'Advanced Hybrid',
                'callable' =>  array($this, 'hybrid'),
            ),
            'entropy' => array(
                'label' =>  'Advanced Entropy',
                'callable' =>  array($this, 'entropy'),
            ),
            'balanced' => array(
                'label' =>  'Advanced Balanced',
                'callable' =>  array($this, 'balanced'),
            )
        );
    }

    public function getMethods()
    {
        $methods = $this->getAllMethods();

        return $methods;
    }

    /**
     * @return array
     */
    public function getCommonAspectRatios()
    {
        return array(
            "7680x4320" => "7680 x 4320 (8K UHDTV)",
            "5120x2880" => "5120 x 2880 (5K, iMac with retina screen)",
            "3840x2160" => "3840 × 2160 (4K UHDTV)",
            "2048x1536" => "2048 x 1536 (iPad with retina screen)",
            "1920x1200" => "1920 x 1200 (Widescreen computer monitor)",
            "1920x1080" => "1920 x 1080 (HD TV, iPhone 6 plus)",
            "1334x750" => "1334 x 750 (iPhone 6)",
            "1200x630" => "1200 x 630 (Facebook)",
            "1136x640" => "1136 x 640 (iPhone 5 screen)",
            "1024x768" => "1024 x 768 (iPad)",
            "1024x512" => "1024 x 512 (Twitter)",
            "960x640" => "960 x 640 (iPhone 4 screen)",
            "800x600" => "800 x 600",
            "728x90" => "728 x 90 (Common web banner ad size)",
            "720x486" => "720 x 486 (PAL)",
            "640x480" => "640 x 480 (VGA)",
            "576x486" => "576 x 486 (NTSC)",
            "320x480" => "320 x 480 (HVGA)"
        );
    }
}