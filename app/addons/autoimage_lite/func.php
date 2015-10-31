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

use Tygh\Registry;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @param $th_filename
 * @param $_lazy
 *
 * @throws \Tygh\Exceptions\DeveloperException
 */
function fn_autoimage_lite_generate_thumbnail_post(&$th_filename, $_lazy)
{
    if (!is_array($_lazy) || defined('NO_AUTOIMAGE')) {
        return;
    }

    list($image_path, $lazy, $filename, $width, $height) = $_lazy;
    $absolutePath = Storage::instance('images')->getAbsolutePath($image_path);
    list(, , $mime_type,$tmp_path) = fn_get_image_size($absolutePath);

    if (!empty($tmp_path)) {


        require_once AUTOIMAGE_LITE_ADDON_DIR . '/vendor/WideImage/WideImage.php';
        $im = WideImage::load($tmp_path);
        /** @var WideImage_Image $im */
        $im = $im->resize($width, $height, 'outside')->crop('center', 'center', $width, $height);

        $convertToFormat = Registry::get('settings.Thumbnails.convert_to');
        if (Registry::get('settings.Thumbnails.convert_to') != 'original') {
            $format = $convertToFormat;
        } else {
            $format  = fn_get_image_extension($mime_type);
        }
        $cont = $im->asString($format);

        // if previous method failed, fallback to CS-Cart's default method
        if (empty($cont)) {
            list($cont, $format) = fn_resize_image($tmp_path, $width, $height, Registry::get('settings.Thumbnails.thumbnail_background_color'));{}{}
        }



        if (!empty($cont)) {
            list(, $th_filename) = Storage::instance('images')->put($filename, array(
                'contents' => $cont,
                'caching' => true
            ));
        }
    }

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
    $lazy = func_get_args();
    $image_path = '';
}

/**
 * @param $status
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
}

/**
 *
 */
function fn_autoimage_lite_uninstall()
{
	fn_autoimage_lite_hint('D');
}