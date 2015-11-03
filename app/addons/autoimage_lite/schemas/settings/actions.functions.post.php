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

/**
 * @param null $newStatus
 * @param null $oldStatus
 * @param null $onInstall
 *
 * @return bool
 */


if (($autoLoader = dirname(__FILE__).'/../../vendor/autoload.php') && file_exists($autoLoader)) {
	require_once $autoLoader;
}

function fn_settings_actions_addons_autoimage_lite(&$newStatus = null, $oldStatus = null, &$onInstall = null)
{
	if (in_array($newStatus, array('A', 'D'))) {


		if ($newStatus == 'A') {
			if (\HeloStore\ADLS\LicenseClient::activate()) {
				fn_autoimage_lite_hint($newStatus);
			} else {
				$newStatus = 'D';
			}
		} else {
			fn_autoimage_lite_hint($newStatus);
			\HeloStore\ADLS\LicenseClient::deactivate();
		}
    }

    return true;
}