<?php
/*
  $Id: paypal_uk_direct.php 1800 2008-01-11 16:33:02Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_TEXT_TITLE', 'PayPal Website Payments Pro (UK) Direct Payments');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_TEXT_PUBLIC_TITLE', 'Tarjeta de Cr&eacute;dito or tarjeta del banco (procesado con seguridad de PayPal)');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_TEXT_DESCRIPTION', '<b>Attencion: PayPal necesita el PayPal Website Payments Pro (UK) Express Checkout M&oacute;dulo de Pago cuando este M&oacute;dulo esta instalado.</b><br /><br /><img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.paypal.com/mrb/pal=PS2X9Q773CKG4" target="_blank" style="text-decoration: underline; font-weight: bold;">Visita la web de PayPal</a>&nbsp;<a href="javascript:toggleDivBlock(\'paypalDirectUKInfo\');">(info)</a><span id="paypalDirectUKInfo" style="display: none;"><br><i>Con el uso del Link para usar PayPal osCommerce dar a cada Cliente nuevo un pequeno Bonus.</i></span>');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_OWNER', 'Titular de la tarjeta:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_TYPE', 'Tipo de tarjeta:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_NUMBER', 'N&uacute;mero de tarjeta:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_VALID_FROM', 'Tarjeta valida desde:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_VALID_FROM_INFO', '(si est&aacute; disponible)');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_EXPIRES', 'Fecha de caducidad:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_CVC', 'C&oacute;digo de seguridad:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_ISSUE_NUMBER', 'Issue n&uacute;mero de tarjeta:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_ISSUE_NUMBER_INFO', '(es solo para tarjeta de Maestro y Solo)');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_ALL_FIELDS_REQUIRED', 'Error: Todos los datos son obligatorios.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_GENERAL', 'Error: A general problem has occurred with the transaction. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_CFG_ERROR', 'Error: Payment module configuration error. Please verify the login credentials.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_ADDRESS', 'Error: A match of the Shipping Address City, State, and Postal Code failed. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_DECLINED', 'Error: This transaction has been declined. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_INVALID_CREDIT_CARD', 'Error: The provided credit card information is invalid. Please try again.');
?>
