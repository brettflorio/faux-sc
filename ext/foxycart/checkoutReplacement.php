<?php
  /**
   * Include this script in the <head> section of the checkout_shipping.php page.
   * Shows 'expedited checkout' as an option in a thickbox, or allows the customer
   *  to close the thickbox and use the osCommerce checkout process.
   */

$thickboxContent = <<<HTML
<div id="hijack">
  <p>Hey, we've just rocket-fueled our checkout process by using FoxyCart.  Click
   the logo below to check out.</p>
  ^^foxy_checkout_button^^

  <p>Need to pay with PayPal?</p>
  <p><a href="#" onclick="tb_remove(); return false;">Click here to use our standard checkout.</a></p>
</div>
HTML;

/**
 * Create a form for the send-off to FoxyCart based on the current osCommerce
 *  cart contents.  Not strictly necessary -- we could just send an aggregate
 *  total and weight -- but this way, the customers see what they're buying
 *  and that's just foxy.
 */
function getFoxyForm() {
  global $customer_id;
  $osc = OSCommerce::instance();

  $cart = $osc->loadCartForCustomer(array('customers_id' => $customer_id));
  ob_start();
?>
  <form action="<?php echo FOXYCART_CART_URL ?>" method="POST">
<?php  $ndx = 0;
       foreach ($cart as $product) {
        $fieldPrefix = (($ndx++ > 0) ? $ndx.':' : '');
?>
<input type="hidden" name="<?php echo $fieldPrefix ?>code" value="<?php echo htmlentities($product['id']) ?>"/>
<input type="hidden" name="<?php echo $fieldPrefix ?>name" value="<?php echo htmlentities($product['name']) ?>"/>
<input type="hidden" name="<?php echo $fieldPrefix ?>price" value="<?php echo htmlentities($product['final_price']) ?>"/>
<input type="hidden" name="<?php echo $fieldPrefix ?>weight" value="<?php echo htmlentities($product['weight']) ?>"/>
<input type="hidden" name="<?php echo $fieldPrefix ?>quantity" value="<?php echo htmlentities($product['quantity']) ?>"/>
<?php } ?>
    <input type="hidden" name="h:sessionID" value="<?php echo session_id() ?>"/>
    <input type="hidden" name="empty" value="true"/>
    <input type="hidden" name="cart" value="checkout"/>
    <input type="submit" name="x:submit"/>
  </form>';
<?php

  return ob_get_clean();
}

$filteredContent =
 str_replace('^^foxy_checkout_button^^', getFoxyForm(), $thickboxContent);
$filteredContent = str_replace("'", "\\'", $filteredContent);
$filteredContent = str_replace("\n", "", $filteredContent);
?>
<script type="text/javascript" src="/ext/foxycart/jquery-latest.pack.js"></script>
<script type="text/javascript" src="/ext/foxycart/thickbox-compressed.js"></script>
<link rel="stylesheet" type="text/css" href="/ext/foxycart/thickbox.css" media="all"/>

<script type="text/javascript"><!--
// <[!CDATA[
  $(document).ready(function () {
    $('body').append('<?php echo $filteredContent ?>');
    tb_show('test', '#TB_inline?height=300&width=500&inlineId=hijack&modal=true');
  });

// ]]>-->
</script>
