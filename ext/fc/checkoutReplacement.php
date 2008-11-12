<?php
//require_once('oscommerce.class.php');

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

function getFoxyForm() {
  return <<<HTML
  <form action="" method="POST">
    <input type="submit"/>
  </form>
HTML;
}

$filteredContent =
 str_replace('^^foxy_checkout_button^^', getFoxyForm(), $thickboxContent);
$filteredContent = str_replace("'", "\\'", $filteredContent);
$filteredContent = str_replace("\n", "", $filteredContent);
?>
<script type="text/javascript" src="/ext/fc/jquery-latest.pack.js"></script>
<script type="text/javascript" src="/ext/fc/thickbox-compressed.js"></script>
<link rel="stylesheet" type="text/css" href="/ext/fc/thickbox.css" media="all"/>

<script type="text/javascript"><!--
// <[!CDATA[
  $(document).ready(function () {
    $('body').append('<?php echo $filteredContent ?>');
    tb_show('test', '#TB_inline?height=300&width=500&inlineId=hijack&modal=true');
  });

// ]]>-->
</script>
