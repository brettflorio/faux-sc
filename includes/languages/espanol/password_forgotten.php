<?php
/*
  $Id: password_forgotten.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE_1', 'Entrar');
define('NAVBAR_TITLE_2', 'Constrase&ntilde;a Olvidada');

define('HEADING_TITLE', 'He olvidado mi Contrase&ntilde;a!');

define('TEXT_MAIN', 'Si ha olvidado su contrase&ntilde;a, introduzca su direcci&oacute;n de e-mail y le enviaremos un mensaje por e-mail con una contrase&ntilde;a nueva.');

define('TEXT_NO_EMAIL_ADDRESS_FOUND', 'Error: Ese E-Mail no figura en nuestros datos, int&eacute;ntelo de nuevo.');

define('EMAIL_PASSWORD_REMINDER_SUBJECT', STORE_NAME . ' - Nueva Contrase�a');
define('EMAIL_PASSWORD_REMINDER_BODY', 'Ha solicitado una Nueva Contrase�a desde ' . $REMOTE_ADDR . '.' . "\n\n" . 'Su nueva contrase�a para \'' . STORE_NAME . '\' es:' . "\n\n" . '   %s' . "\n\n");

define('SUCCESS_PASSWORD_SENT', 'Exito: Se ha enviado una nueva contrase&ntilde;a a su e-mail');
?>
