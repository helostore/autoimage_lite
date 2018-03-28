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
use stojg\crop\CropFace;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Storage;
use WideImage;
use WideImage_Image;

/**
 * Class ImageManager
 *
 * @package HeloStore\AutoImage
 */
class ImageResizeTest extends Singleton
{
    public function normalizePathSeparators($path){
        return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
    }

	public function findImages( $limit = 20, $langCode = CART_LANGUAGE ) {
		$objectType = 'product';
		$pairType = 'M';

		$items = db_get_array(
			"SELECT 
                link.*, 
                image.image_path, 
                descr.description AS alt, 
                image.image_x, 
                image.image_y, 
                image.image_id as images_image_id
            FROM ?:images_links AS link
            LEFT JOIN ?:images AS image
                ON link.detailed_id = image.image_id
            LEFT JOIN ?:common_descriptions AS descr
                ON descr.object_id = image.image_id 
                AND descr.object_holder = 'images' 
                AND descr.lang_code = ?s
            WHERE 
                link.object_type = ?s 
                AND link.type = ?s
            ORDER BY link.position, link.pair_id
            LIMIT 0,?i
            ",
			$langCode, $objectType, $pairType, $limit
		);

		$inputFilesPaths = array();
		foreach ($items as &$item) {
			fn_attach_absolute_image_paths($item, 'detailed');
			$inputFilesPaths[] = $item['absolute_path'];
		}
		unset($item);

		return $inputFilesPaths;
	}

	public function findStockPhotos() {
		$addonsPath = $this->normalizePathSeparators(Registry::get('config.dir.addons'));
		$inputPath = $this->normalizePathSeparators($addonsPath . 'autoimage_lite' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stock');

		$inputFilesPaths = glob($inputPath . '/*');

		return $inputFilesPaths;
	}

	public function testMethod( $method, $files, $width, $height ) {
		$results = array();
		$outputPath = $this->getOutputPath();
		foreach ($files as $inputFilePath) {
			if (!is_file($inputFilePath)) {
				continue;
			}

			$results[] = $this->testFile( $method, $inputFilePath, $outputPath, $width, $height );
		}

		return $results;
	}

	public function getOutputPath() {
		$imagesPath = $this->normalizePathSeparators(Storage::instance('images')->getAbsolutePath(''));
		$subImagesPath = 'autoimage';
		$outputPath = $imagesPath . $subImagesPath;

		return $outputPath;
	}

    public function testMethods($inputFilesPaths, $width, $height)
    {
        $outputPath = $this->getOutputPath();

        if (!fn_mkdir($outputPath)) {
            throw new \Exception('AutoImage: unable to create workspace directory in: `' . $outputPath . '`, no writing permissions.');
        }
        $imageResizeManager = ImageResizeManager::instance();
        $methods = $imageResizeManager->getAvailableMethods();
        $results = array();

        foreach ($inputFilesPaths as $inputFilePath) {
            if (!is_file($inputFilePath)) {
                continue;
            }

            $processedFiles = array();
            foreach ($methods as $key => $method) {
	            $processedFiles[ $key ] = $this->testFile( $method, $inputFilePath, $outputPath, $width, $height );
            }
            $results[] = $processedFiles;
        }

        return $results;
    }

	public function testFile( $method, $inputFilePath, $outputPath, $width, $height ) {
		if (!is_callable($method['callable'])) {
			return false;
		}
		$rootPath = $this->normalizePathSeparators(Registry::get('config.dir.root'));
		$outputRelativePath = str_replace($rootPath, '', $outputPath);
		$outputUrl = Registry::get('config.current_location') . str_replace(DIRECTORY_SEPARATOR, '/', $outputRelativePath);

		$fileInfo = pathinfo($inputFilePath);
		$outputFileName = $fileInfo['filename'] . "-" . $method['slug'] . "." . $fileInfo['extension'];
		$outputAbsoluteFilePath = $outputPath . DIRECTORY_SEPARATOR . $outputFileName;
		$outputFileName = $fileInfo['filename'] . "-" . $method['slug'] . "." . $fileInfo['extension'];
		$outputAbsoluteFilePath = $outputPath . DIRECTORY_SEPARATOR . $outputFileName;
		$result = call_user_func($method['callable'], $inputFilePath, $outputAbsoluteFilePath, $width, $height);
//                aa("$key($outputFileName) = $result");
		$version = mt_rand(1, 99999);

		return array(
			'url'     => $outputUrl . '/' . $outputFileName . '?' . $version,
			'label'   => $method['label'],
			'success' => $result
		);
	}

}
