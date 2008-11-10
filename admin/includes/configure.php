<?php
  define('HTTP_SERVER', 'http://fauxsc.dev');
  define('HTTP_CATALOG_SERVER', 'http://fauxsc.dev');
  define('HTTPS_CATALOG_SERVER', 'http://fauxsc.dev');
  define('ENABLE_SSL_CATALOG', 'false');
  define('DIR_FS_DOCUMENT_ROOT', '/home/projects/fauxsc/catalog/');
  define('DIR_WS_ADMIN', '/admin/');
  define('DIR_FS_ADMIN', '/home/projects/fauxsc/catalog/admin/');
  define('DIR_WS_CATALOG', '/');
  define('DIR_FS_CATALOG', '/home/projects/fauxsc/catalog/');
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_CATALOG_IMAGES', DIR_WS_CATALOG . 'images/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');
  define('DIR_WS_CATALOG_LANGUAGES', DIR_WS_CATALOG . 'includes/languages/');
  define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG . 'includes/languages/');
  define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');
  define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');
  define('DIR_FS_BACKUP', DIR_FS_ADMIN . 'backups/');

  define('DB_SERVER', 'localhost');
  define('DB_SERVER_USERNAME', 'fauxsc');
  define('DB_SERVER_PASSWORD', 'bl0op3r.');
  define('DB_DATABASE', 'fauxsc_development');
  define('USE_PCONNECT', 'false');
  define('STORE_SESSIONS', 'mysql');
?>