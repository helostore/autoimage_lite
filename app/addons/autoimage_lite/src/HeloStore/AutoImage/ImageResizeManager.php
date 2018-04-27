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
	private $methods = array();

	/**
	 * ImageResizeManager constructor.
	 */
	public function __construct() {
		$this->methods = array(
			'original' => array(
				'slug'     => 'original',
				'label'    => 'Original',
				'callable' => array( $this, 'original' ),
				'hidden'   => true,
			),
			'basic'    => array(
				'slug'     => 'basic',
				'label'    => 'Basic',
				'callable' => array( $this, 'basic' ),
			),
			'hybrid'   => array(
				'slug'     => 'hybrid',
				'label'    => 'Advanced Hybrid',
				'callable' => array( $this, 'hybrid' ),
			),
			'entropy'  => array(
				'slug'       => 'entropy',
				'label'      => 'Advanced Entropy',
				'callable'   => array( $this, 'entropy' ),
				'dependency' => array(
					'extensions' => array( 'imagick' )
				)
			),
			'balanced' => array(
				'slug'       => 'balanced',
				'label'      => 'Advanced Balanced',
				'callable'   => array( $this, 'balanced' ),
				'dependency' => array(
					'extensions' => array( 'imagick' )
				)
			)
		);
	}

	public function getMethod($method) {
		return $this->methods[ $method ];
	}
	/**
	 * @param $method
	 *
	 * @return bool
	 */
	public function isValidMethod($method) {
		return isset( $this->methods[ $method ] );
	}

	public function getMethods() {
		return $this->methods;
	}
	/**
	 * @return array
	 */
	public function getAvailableMethods()
	{
		list ( $available, ) = $this->checkDependencies( $this->methods );

		return $available;
	}

	public function checkDependencies($methods) {
		$result = array();
		$availableMethods = array();
		foreach ( $methods as $k => $method ) {
			$ok = true;
			if ( ! empty( $method['dependency'] ) ) {
				if ( ! empty( $method['dependency']['extensions'] ) ) {

					foreach ( $method['dependency']['extensions'] as $extension ) {
						if ( ! extension_loaded( $extension ) ) {
							$result[] = $method['label'] . ' method is not available: PHP extension ' . $extension . ' is not installed';
							$ok = false;
						}
					}
				}
			}
			if ( $ok ) {
				$availableMethods[$k] = $method;
			}
		}

		return array($availableMethods, $result);
	}

    /**
     * Get selected method
     *
     * @return mixed|null
     */
    public function getSelectedMethod()
    {
        static $method = null;
        if ($method == null) {
            $method = Registry::get('addons.autoimage_lite.method');
        }

        return $method;
    }

    /**
     * @return bool
     */
    public function isOriginalMethod()
    {
        return $this->getSelectedMethod() === 'original';
    }

    /**
     * @return bool
     */
    public function isDefaultMethod()
    {
        return $this->getSelectedMethod() === 'default';
    }

    /**
     * Process image using selected method
     *
     * @param $inputAbsoluteFilePath
     * @param $outputAbsoluteFilePath
     * @param $width
     * @param $height
     *
     * @return bool|string
     */
    public function process($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height)
    {
        $method = $this->getSelectedMethod();

        // Check image format/extension, and if it's not a supported format, don't process it.
        list($w, $h, $mime_type, $tmp_path) = fn_get_image_size($inputAbsoluteFilePath);
        if (empty($mime_type)) {
            return false;
        }
        $format = fn_get_image_extension($mime_type);
        if (empty($format)) {
            return false;
        }

        if ($method == 'default') {
            return '';
        } else if ($method == 'basic') {
            return $this->basic($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height);
        } else if ($method == 'balanced') {
            return $this->balanced($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height);
        } else if ($method == 'hybrid') {
            return $this->hybrid($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height);
        } else if ($method == 'entropy') {
            return $this->entropy($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height);
        }

        return false;
    }


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
        if (!fn_mkdir(dirname($outputAbsoluteFilePath))) {
            return false;
        }
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
        if (!fn_mkdir(dirname($outputAbsoluteFilePath))) {
            return false;
        }

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
        if (!fn_mkdir(dirname($outputAbsoluteFilePath))) {
            return false;
        }

	    if (! extension_loaded( 'imagick' ) ) {
		    return false;
	    }

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
        if (!fn_mkdir(dirname($outputAbsoluteFilePath))) {
            return false;
        }

	    if (! extension_loaded( 'imagick' ) ) {
		    return false;
	    }

        $cropper = new CropBalanced($inputAbsoluteFilePath);
        $croppedImage = $cropper->resizeAndCrop($width, $height);

        return $croppedImage->writeimage($outputAbsoluteFilePath);
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
