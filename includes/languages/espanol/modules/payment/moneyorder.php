<?php
/*
  $Id: moneyorder.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_MONEYORDER_TEXT_TITLE', 'Cheque/Transferencia Bancaria');
  define('MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION', 'Pagadero a:&nbsp;' . MODULE_PAYMENT_MONEYORDER_PAYTO . '<br><br>Enviar a<br>' . nl2br(STORE_NAME_ADDRESS) . '<br><br>' . '&nbsp;Su pedido se enviar� en cuanto se reciba el pago.');
  define('MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER', "Pagadero a: ". MODULE_PAYMENT_MONEYORDER_PAYTO . "\n\nEnviar a\n" . STORE_NAME_ADDRESS . "\n\n" . 'Su pedido se enviar� en cuanto se reciba el pago.');
?>
