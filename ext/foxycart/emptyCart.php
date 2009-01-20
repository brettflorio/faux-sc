<?php
require_once 'config.php';
require_once 'class.rc4crypt.php';
require_once 'class.xmlparser.php';
require_once 'oscommerce.class.php';


if (isset($_REQUEST['checkout_complete'])) {
  $osc = OSCommerce::instance();

  $customer = $osc->findCurrentCustomer();

  if ($customer) {
    $osc->torchCartForCustomer($customer);   // Burn the baskets, we don't need 'em.
    $osc->torchCartInSession(session_id());
  }
}
?>
