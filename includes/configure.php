<?php
  define('HTTP_SERVER', 'http://fauxsc.dev');
  define('HTTPS_SERVER', 'http://fauxsc.dev');
  define('ENABLE_SSL', false);
  define('HTTP_COOKIE_DOMAIN', 'fauxsc.dev');
  define('HTTPS_COOKIE_DOMAIN', 'fauxsc.dev');
  define('HTTP_COOKIE_PATH', '/');
  define('HTTPS_COOKIE_PATH', '/');
  define('DIR_WS_HTTP_CATALOG', '/');
  define('DIR_WS_HTTPS_CATALOG', '/');
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');

  define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
  define('DIR_FS_CATALOG', '/home/projects/fauxsc/catalog/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');

  define('DB_SERVER', 'localhost');
  define('DB_SERVER_USERNAME', 'fauxsc');
  define('DB_SERVER_PASSWORD', 'bl0op3r.');
  define('DB_DATABASE', 'fauxsc_development');
  define('USE_PCONNECT', 'false');
  define('STORE_SESSIONS', 'mysql');
?>