<?php
/*
  $Id: paypal_uk_direct.php 1800 2008-01-11 16:33:02Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_TEXT_TITLE', 'PayPal Website Payments Pro (UK) Direct Payments');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_TEXT_PUBLIC_TITLE', 'Credit or Debit Card (Processed securely by PayPal)');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_TEXT_DESCRIPTION', '<b>Note: PayPal requires the PayPal Website Payments Pro (UK) Express Checkout payment module to be enabled if this module is activated.</b><br /><br /><img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.paypal.com/mrb/pal=PS2X9Q773CKG4" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit PayPal Website</a>&nbsp;<a href="javascript:toggleDivBlock(\'paypalDirectUKInfo\');">(info)</a><span id="paypalDirectUKInfo" style="display: none;"><br><i>Using the above link to signup at PayPal grants osCommerce a small financial bonus for referring a customer.</i></span>');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_OWNER', 'Card Owner:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_TYPE', 'Card Type:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_VALID_FROM', 'Card Valid From Date:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_VALID_FROM_INFO', '(if available)');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_EXPIRES', 'Card Expiry Date:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_CVC', 'Card Security Code (CVV2):');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_ISSUE_NUMBER', 'Card Issue Number:');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_CARD_ISSUE_NUMBER_INFO', '(for Maestro and Solo cards only)');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_ALL_FIELDS_REQUIRED', 'Error: All payment information fields are required.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_GENERAL', 'Error: A general problem has occurred with the transaction. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_CFG_ERROR', 'Error: Payment module configuration error. Please verify the login credentials.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_ADDRESS', 'Error: A match of the Shipping Address City, State, and Postal Code failed. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_DECLINED', 'Error: This transaction has been declined. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_UK_DIRECT_ERROR_INVALID_CREDIT_CARD', 'Error: The provided credit card information is invalid. Please try again.');
?>
