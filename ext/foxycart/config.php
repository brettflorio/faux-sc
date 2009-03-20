<?php

/**
 * Your datafeed key -- must match what you set up in FoxyCart.
 */
define('DATAFEED_KEY', 'CHANGE THIS TEXT to your own datafeed keyphrase');

/**
 * Where to find the osCommerce catalog's includes directory.  If you installed this
 * in the default location, this should work, otherwise change to the absolute
 * path of your osCommerce installation.
 */
define('OSC_INCLUDES_PATH', dirname(__FILE__) . '/../../');

/**
 * Your FoxyCart store domain.  This might be "yourstore.foxycart.com" or you might
 * have set up your store on a subdomain, like: "store.yourstore.com." 
 */
define('FOXYCART_DOMAIN', 'example.foxycart.com');


/**
 * Category for products with non-zero weight.
 */
define('PRODUCT_CATEGORY', 'DEFAULT');

/**
 * If true, products with zero weight will be added to a different category.  This allows
 * for "free shipping" rules.
 */
define('USE_FREE_SHIPPING', true);

/**
 * Category for products with zero weight.
 */
define('PRODUCT_FREE_SHIPPING_CATEGORY', 'FREESHIP');
 
############ No changes below this line, please. ###############################

define('FOXYCART_CART_URL', 'https://'.FOXYCART_DOMAIN.'/cart');
ini_set('include_path', ini_get('include_path') . ':' . OSC_INCLUDES_PATH);

?>
