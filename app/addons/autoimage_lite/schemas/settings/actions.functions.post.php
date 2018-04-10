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

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @param null $newStatus
 * @param null $oldStatus
 * @param null $onInstall
 *
 * @return bool
 */
function fn_settings_actions_addons_autoimage_lite(&$newStatus = null, $oldStatus = null, &$onInstall = null)
{
	if (in_array($newStatus, array('A', 'D'))) {
		if ($newStatus == 'A') {
			if (class_exists('\HeloStore\ADLS\LicenseClient', true)) {
				if (\HeloStore\ADLS\LicenseClient::activate()) {
					return fn_autoimage_lite_hint($newStatus);
				}
			} else {
				fn_set_notification('W', __('warning'), __('my_sidekick_is_not_present'), 'K');
			}
			$newStatus = 'D';
		} else {
			fn_autoimage_lite_hint($newStatus);
			if (class_exists('\HeloStore\ADLS\LicenseClient', true)) {
				\HeloStore\ADLS\LicenseClient::deactivate();
			}
		}
    }

    return true;
}

/**
 * @param $newValue
 * @param $oldValue
 */

function fn_settings_actions_addons_autoimage_lite_method($newValue, $oldValue)
{
	if ($newValue != $oldValue) {
        $message = __('auto_image_hint_message_method_updated');
        if (!empty($message) && substr($message, 0, 1) !== '_') {
            fn_autoimage_lite_hint('method_updated');
        }
	}
}

/**
 * @return array
 */
function fn_settings_variants_addons_autoimage_lite_method() {
	$availableMethods = ImageResizeManager::instance()->getAvailableMethods();
	$list = array();
	foreach ( $availableMethods as $method ) {
		$list[ $method['slug'] ] = $method['label'];
	}
	return $list;
}
