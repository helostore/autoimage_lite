<?php

use HeloStore\AutoImage\ImageResizeManager;
use HeloStore\AutoImage\ImageResizeTest;
use Tygh\Registry;

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

\Tygh\Tygh::$app['view']->assign('referrer', $referrer);
\Tygh\Tygh::$app['view']->assign('width', $width);
\Tygh\Tygh::$app['view']->assign('height', $height);

$imageManipulator = ImageResizeManager::instance();
$imageTest = ImageResizeTest::instance();
$methods = ImageResizeManager::instance()->getAvailableMethods();
\Tygh\Tygh::$app['view']->assign('methods', $methods);

/**
 * Test a single resizing method on multiple images.
 */
if ($mode == 'test_method') {
	$target = !empty($_REQUEST['target']) && in_array($_REQUEST['target'], array('products', 'stock')) ?
		$_REQUEST['target'] :
		'stock';

	$methodSlug = ! empty( $_REQUEST['method'] ) ? $_REQUEST['method'] : 'basic';
	if ( ! $imageManipulator->isValidMethod( $methodSlug ) ) {
		return array( CONTROLLER_STATUS_NO_PAGE );
	}
	$method = $imageManipulator->getMethod( $methodSlug );

	if ($target == 'stock') {
		$files = $imageTest->findStockPhotos();
	} else if ($target == 'products') {
		$files = $imageTest->findImages(280);
	}
	$results = $imageTest->testMethod( $method, $files, $width, $height );
	\Tygh\Tygh::$app['view']->assign('results', $results);
	\Tygh\Tygh::$app['view']->assign('methodSlug', $methodSlug);
	\Tygh\Tygh::$app['view']->assign('target', $target);
}

/**
 * Test all resizing methods on multiple images.
 */
if ($mode == 'test') {
    $target = !empty($_REQUEST['target']) && in_array($_REQUEST['target'], array('products', 'stock')) ?
        $_REQUEST['target'] :
        'stock';

    if ($target == 'stock') {
	    $files = $imageTest->findStockPhotos();
    } else if ($target == 'products') {
	    $files = $imageTest->findImages();
    }
	$results = $imageTest->testMethods( $files, $width, $height );

    \Tygh\Tygh::$app['view']->assign('results', $results);
}
