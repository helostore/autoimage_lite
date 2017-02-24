<?php
/**
 * Created by PhpStorm.
 * User: WSergio
 * Date: 2017-02-24
 * Time: 12:59
 */

namespace HeloStore\AutoImage\Method;


use Tygh\Registry;
use Tygh\Storage;
use WideImage;
use WideImage_Image;

class Basic
{
    /**
     * @var string
     */
    protected $imagePath;

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     *
     * @return $this
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * @param string
     */
    public function __construct($imagePath = null)
    {
        if ($imagePath && is_string($imagePath)) {
            $this->setImagePath($imagePath);
        }
    }

    /**
     * @param $path
     *
     * @return bool
     */
    private function isAbsolutePath($path)
    {
        if ($path === null || $path === '') return false;

        return $path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function normalizePathSeparators($path){
        return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @param $outputPath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    public function resizeAndCrop($outputPath, $width, $height)
    {
        // We need relative path
        if ($this->isAbsolutePath($outputPath)) {
            $imagesPath = $this->normalizePathSeparators(Storage::instance('images')->getAbsolutePath(''));
            $outputRelativeFilePath = str_replace($imagesPath, '', $outputPath);
            $outputRelativeFilePath = str_replace(DIRECTORY_SEPARATOR, '/', $outputRelativeFilePath);
            $outputRelativeFilePath = trim($outputRelativeFilePath, '/');

        } else {
            $outputRelativeFilePath = $outputPath;
        }
        $inputAbsolutePath = $this->getImagePath();
        
        list(, , $mime_type, $tmp_path) = fn_get_image_size($inputAbsolutePath);
        if (!empty($tmp_path)) {
            $im = WideImage::load($tmp_path);

            /** @var WideImage_Image $im */
            $im = $im->resize($width, $height, 'outside')->crop('center', 'center', $width, $height);

            $convertToFormat = Registry::get('settings.Thumbnails.convert_to');
            if (Registry::get('settings.Thumbnails.convert_to') != 'original') {
                $format = $convertToFormat;
            } else {
                $format = fn_get_image_extension($mime_type);
            }
            $content = $im->asString($format);

            // if previous method failed, fallback to CS-Cart's default method
            if (empty($content)) {
                list($content, $format) = fn_resize_image($tmp_path, $width, $height, Registry::get('settings.Thumbnails.thumbnail_background_color'));
            }

            if (!empty($content)) {
                list(, $thumbFilename) = Storage::instance('images')->put($outputRelativeFilePath, array(
                    'contents' => $content,
                    'overwrite' => true,
                    'caching' => true
                ));

                return $thumbFilename;
            }
        }

        return false;
    }
}