<?php

use HeloStore\AutoImage\ImageResizeManager;
use HeloStore\AutoImage\ImageResizeTest;
use Tygh\Registry;

if ($mode == 'test') {

    $width = Registry::get('settings.Thumbnails.product_lists_thumbnail_width');
    $height = Registry::get('settings.Thumbnails.product_lists_thumbnail_height');

    $referrer = fn_url('addons.update?addon=autoimage_lite');
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $runtime = Registry::get('runtime');
        $dispatch = $runtime['controller'] . '.' . $runtime['mode'];
        if (strstr($_SERVER['HTTP_REFERER'], $dispatch) === false) {
            $referrer = $_SERVER['HTTP_REFERER'];
        }
    }

    $target = !empty($_REQUEST['target']) && in_array($_REQUEST['target'], array('products', 'stock')) ?
        $_REQUEST['target'] :
        'stock';

    $results = array();
    if ($target == 'stock') {
        $results = \HeloStore\AutoImage\ImageResizeTest::instance()->testStockPhotos($width, $height);
    } else if ($target == 'products') {
        $results = ImageResizeTest::instance()->testProductsPhotos($width, $height);
    }
    $methods = ImageResizeManager::instance()->getMethods();

    \Tygh\Tygh::$app['view']->assign('results', $results);
    \Tygh\Tygh::$app['view']->assign('methods', $methods);
    \Tygh\Tygh::$app['view']->assign('referrer', $referrer);
    \Tygh\Tygh::$app['view']->assign('width', $width);
    \Tygh\Tygh::$app['view']->assign('height', $height);
}