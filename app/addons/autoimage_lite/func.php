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

/**
 * @param $th_filename
 * @param $_lazy
 *
 * @throws \Tygh\Exceptions\DeveloperException
 */
function fn_autoimage_lite_generate_thumbnail_post(&$th_filename, $_lazy)
{
    // Check if the trick below was applied or not. If not, do nothing.
    if (!is_array($_lazy) || defined('NO_AUTOIMAGE')) {
        return;
    }
    list($imagePath, $lazy, $thumbRelativeFilePath, $width, $height) = $_lazy;
    $inputAbsoluteFilePath = Storage::instance('images')->getAbsolutePath($imagePath);

    $imagesPath = Storage::instance('images')->getAbsolutePath('');
    $outputAbsoluteFilePath = $imagesPath . $thumbRelativeFilePath;

    $newThumbPath = ImageResizeManager::instance()->process($inputAbsoluteFilePath, $outputAbsoluteFilePath, $width, $height);

    if (!empty($newThumbPath)) {
        $th_filename = $thumbRelativeFilePath;
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

    $method = ImageResizeManager::instance()->getSelectedMethod();
    if ($method == 'default') {
        return;
    }
    // Trick CS-Cart into not going with the default processing; temporarily move args to $lazy variable
    $lazy = func_get_args();
    $image_path = '';
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

    return '<div class="control-group setting-wide autoimage_lite "><label class="control-label "></label>
        <div class="controls">' . __('autoimage_lite.settings.preview', array('[url]' => $url)) . '</div></div>';
}
function fn_settings_actions_addons_autoimage_lite_method($newValue, $oldValue)
{
    if ($newValue != $oldValue) {
        fn_autoimage_lite_hint('method_updated');
    }
}