<?php
/**
 * AutoImage
 *
 * This source file is part of a commercial software. Only users who have purchased a valid license through
 * https://helostore.com/ and accepted to the terms of the License Agreement can install this product.
 *
 * @category   Zend
 * @package    Zend_Application
 * @copyright  Copyright (c) 2015-2016 HELOstore. (https://helostore.com/)
 * @license    https://helostore.com/legal/license-agreement/     License Agreement
 * @version    $Id$
 */

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'generate_thumbnail_post',
    'generate_thumbnail_file_pre'
);
