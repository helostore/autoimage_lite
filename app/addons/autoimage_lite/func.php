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

use HeloStore\AutoImage\ImageResizeManager;
use Tygh\Registry;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once __DIR__ . '/vendor/autoload.php';

function fn_autoimage_lite_image_to_display_post(&$image_data, $images, $image_width, $image_height) {
    if (empty($images)) {
        return array();
    }
    if (empty($image_data)) {
        return array();
    }

    static $controller = null;
    static $mode = null;
    static $forceResizeOnProductDetailsPage = null;
    if ($controller === null) {
        $controller = Registry::get('runtime.controller');
        $mode = Registry::get('runtime.mode');
        $forceResizeOnProductDetailsPage = Registry::get('addons.autoimage_lite.force_resizing_on_product_details_page') === 'Y';
    }
    $doForceResizeOnProductDetailsPage = false;
    if ($controller === 'products' && $mode === 'view' && $forceResizeOnProductDetailsPage === true) {
        $doForceResizeOnProductDetailsPage = true;
    }
    if (!$doForceResizeOnProductDetailsPage) {
        return array();
    }

    static $current_product_images = null;
    static $current_product_images_ids = null;
    if ($current_product_images === null) {
        // The ID of the product being browsed on product details page
        $main_product_id = $_REQUEST['product_id'];
        $current_product_images = array();
        $main_image = fn_get_image_pairs($main_product_id, 'product', 'M', true, true, CART_LANGUAGE);
        if (!empty($main_image)) {
            $current_product_images[] = $main_image;
        }
        $additional_images = fn_get_image_pairs($main_product_id, 'product', 'A', true, true, CART_LANGUAGE);
        if (!empty($additional_images)) {
            foreach ($additional_images as $additional_image) {
                $current_product_images[] = $additional_image;
            }
        }
        $current_product_images_ids = array();
        foreach ($current_product_images as $current_product_image) {
            $current_product_images_ids[] = $current_product_image['detailed_id'];
        }
    }

    if (empty($current_product_images_ids)) {
        return array();
    }
    if (empty($images['detailed']) || empty($images['detailed_id'])) {
        return array();
    }
    if (!in_array($images['detailed_id'], $current_product_images_ids)) {
        return array();
    }

    // image pair passed
    if (!empty($images['icon']) || !empty($images['detailed'])) {
        if (!empty($images['icon'])) {
            $absolute_path = $images['icon']['absolute_path'];
            $relative_path = $images['icon']['relative_path'];
        } else {
            $absolute_path = $images['detailed']['absolute_path'];
            $relative_path = $images['detailed']['relative_path'];
        }

        $detailed_image_path = !empty($images['detailed']['image_path']) ? $images['detailed']['image_path'] : '';
        $alt = !empty($images['icon']['alt']) ? $images['icon']['alt'] : $images['detailed']['alt'];

        // single image passed only
    } else {
        $alt = $images['alt'];
        $detailed_image_path = '';
        $absolute_path = $images['absolute_path'];
        $relative_path = $images['relative_path'];
    }
    $image_width = Registry::get('settings.Thumbnails.product_details_thumbnail_width');
    $image_height = Registry::get('settings.Thumbnails.product_details_thumbnail_height');
    $image_path = fn_generate_thumbnail($relative_path, $image_width, $image_height, Registry::get('config.tweaks.lazy_thumbnails'));

    $image_data = array(
        'image_path' => $image_path,
        'detailed_image_path' => $detailed_image_path,
        'alt' => $alt,
        'width' => $image_width,
        'height' => $image_height,
        'absolute_path' => $absolute_path,
        'generate_image' => strpos($image_path, '&image_path=') !== false // FIXME: dirty checking
    );
}

/**
 * @param $image_path
 * @param $lazy
 * @param $filename
 * @param $width
 * @param $height
 */
function fn_autoimage_lite_generate_thumbnail_file_pre(&$image_path, &$lazy, $filename, $width, $height)
{
    if (defined('NO_AUTOIMAGE')) {
        return;
    }

    if (ImageResizeManager::instance()->isOriginalMethod()) {
        return;
    }
    // Trick CS-Cart into skipping the default image processing by temporarily moving args into $lazy variable
    // @TODO: ditch this dirty hack once CS-Cart introduces a proper hook
    $lazy = func_get_args();
    $image_path = '';
}

/**
 * @param $th_filename
 * @param $_lazy
 * @throws \Tygh\Exceptions\DeveloperException
 */
function fn_autoimage_lite_generate_thumbnail_post(&$th_filename, $_lazy)
{
    if (defined('NO_AUTOIMAGE')) {
        return;
    }
    $resizeManager = ImageResizeManager::instance();
    if ($resizeManager->isOriginalMethod()) {
        return;
    }

    // Check if the trick below was applied or not. If not, do nothing.
    if (!is_array($_lazy)) {
        return;
    }
    list($imagePath, $lazy, $thumbRelativeFilePath, $width, $height) = $_lazy;
    $inputAbsoluteFilePath = Storage::instance('images')->getAbsolutePath($imagePath);

    $imagesPath = Storage::instance('images')->getAbsolutePath('');
    $outputAbsoluteFilePath = $imagesPath . $thumbRelativeFilePath;

    $newThumbPath = $resizeManager->process($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height);

    if (!empty($newThumbPath)) {
        $th_filename = $thumbRelativeFilePath;
    }
}

/**
 * @param $status
 *
 * @return bool
 */
function fn_autoimage_lite_hint($status)
{
    $status = strtolower($status);
    $titleKey = 'auto_image_hint_title_' . $status;
    $messageKey = 'auto_image_hint_message_' . $status;

    $redirectUrl = urldecode('addons.manage');
    $message = __($messageKey);
    $message = str_replace('[link]', fn_url('storage.clear_thumbnails?redirect_url=' . $redirectUrl), $message);
    fn_set_notification('N', __($titleKey), $message, 'K');

	return true;
}
function fn_autoimage_lite_uninstall()
{
	fn_autoimage_lite_hint('D');
	if (class_exists('\HeloStore\ADLS\LicenseClient', true)) {
		\HeloStore\ADLS\LicenseClient::process(\HeloStore\ADLS\LicenseClient::CONTEXT_UNINSTALL);
	}
}
function fn_autoimage_lite_install()
{
	if (class_exists('\HeloStore\ADLS\LicenseClient', true)) {
		\HeloStore\ADLS\LicenseClient::process(\HeloStore\ADLS\LicenseClient::CONTEXT_INSTALL);
	}
}

function fn_autoimage_lite_preview()
{
    $url = fn_url('autoimage_lite.test');

	$methods = ImageResizeManager::instance()->getMethods();
	list(, $result) = ImageResizeManager::instance()->checkDependencies( $methods );
	$list = '';
	if ( ! empty( $result ) ) {
		foreach ( $result as $message ) {
			$list .= '<li style="color: orange;">' . $message . '</li>';
		}
	}

	$status = Registry::get('addons.autoimage_lite.status');

    return '
        <div class="control-group setting-wide autoimage_lite "><div class="controls">' . $list . '</div></div>' .

		'<div class="control-group setting-wide autoimage_lite "><label class="control-label "></label>
			<div class="controls">' . ($status == 'A' ?
		        __('autoimage_lite.settings.preview', array('[url]' => $url))
		        : '') . '</div></div>'
	;
}
