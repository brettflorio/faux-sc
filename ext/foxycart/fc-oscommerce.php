<?php
define('SESSION_BLOCK_SPIDERS', false); // Don't let osCommerce block the datafeed POST

require_once 'config.php';
require_once 'class.rc4crypt.php';
require_once 'class.xmlparser.php';
require_once 'oscommerce.class.php';


$_POST['FoxyData'] or die("error"); // Make sure we got passed some FoxyData

function fatal_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
  if ($errno < E_WARN) die($errstr);
	return true;
}
set_error_handler('fatal_error_handler');


$osc = OSCommerce::instance();

/**
 * Utilities for reading and parsing the FoxyCart data feed and moving data
 *  to osCommerce.
 */
class FoxydataUtils {
  /**
   * @param $osc    An instance of an OSCommerce class; used for doing
   *                address zone and country code lookups.
   */
  public function __construct($osc) {
    $this->osc = $osc;
  }

/**
 * Mapping of datafeed fields to osC customer fields.
 */
  public static $CustomerFieldMap = array(
   'customer_email' => 'customers_email_address',
   'customer_first_name' => 'customers_firstname',
   'customer_last_name' => 'customers_lastname',
   'customer_phone' => 'customers_telephone',
   'customer_password' => 'customers_password');

/**
 * Mapping of datafeed address fields to osC address fields.
 */
  public static $CustomerAddressFieldMap = array(
    'first_name' => 'firstname',
    'company' => 'company',
    'last_name' => 'lastname',
    'address1' => 'street_address',
    'address2' => 'suburb',
    'postal_code' => 'postcode',
    'city' => 'city',
    'state' => 'state',
    'country' => 'country_id'
  );

  /**
   * Translate an array with keys corresponding to the FoxyCart customer field names
   *  to an array mapping the equivalent osCommerce customer keys to the customer
   *  field values.
   *
   *  @see $CustomerFieldMap
   */
  function mapCustomerToDB($customer_fields) {
    $rtn = array();

    foreach (FoxydataUtils::$CustomerFieldMap as $feed_field => $db_field)
      $rtn[$db_field] = $customer_fields[$feed_field];

    $rtn['customers_password'] = $rtn['customers_password'] . ':'; // No salt.

    return $rtn;
  }

  /**
   * Translate an array with keys corresponding to the FoxyCart address field names
   * into an array mapping the equivalent osCommerce address keys to the address
   * values.
   *
   *  @see $customer_address_field_mapping
   *  @param $which    Which osCommerce fields to map to.  See:
   *                      OSCommerce::ADDRESS_BOOK,
   *                      OSCommerce::ORDER_CUSTOMER,
   *                      OSCommerce::ORDER_BILLING,
   *                      OSCommerce::ORDER_SHIPPING.
   *                    
   */
  function mapAddressToDB($address_fields, $prefix=OSCommerce::ADDRESS_BOOK) {
    $rtn = array();

    foreach (FoxydataUtils::$CustomerAddressFieldMap as $feed_field => $db_field)
      $rtn[$prefix.$db_field] = $address_fields[$feed_field];

    $country = $this->osc->findCountryByCode($address_fields['country']);
    $rtn[$prefix.'country_id'] = $country['countries_id'];

    $zone = $this->osc->findZoneByNameAndCountryID(
     $address_fields['state'], $rtn[$prefix.'country_id']);

    $rtn[$prefix.'zone_id'] = $zone['zone_id'];

    return $rtn;
  }
}

$utils = new FoxydataUtils($osc);

$decryptor = new rc4crypt();
$FoxyData = $decryptor->decrypt(DATAFEED_KEY, urldecode($_POST["FoxyData"]));

$data = new XMLParser($FoxyData);   // Parse that XML.
$data->Parse();
/**
 * Wrapper class to make retrieving name / value pairs from an XML feed much
 *  more concise.  Create with an XMLTag (the result of parsing an XML
 *  file), then retrieve properties with, e.g., $wrapper->customers_email_address.
 */
class PropertyWrapper {
  public function __construct(XMLTag $data) {
    $this->data = $data;
  }

  public function __get($field) {
    $rtn = '';

    if (isset($this->data->$field)) {
      $propertyNode = $this->data->$field;
      if (count($propertyNode[0]->tagChildren) == 0) {
        $rtn = $propertyNode[0]->tagData;
      }
    }

    return $rtn;
  }
}


foreach ($data->document->transactions[0]->transaction as $tx) {
  $trans = new PropertyWrapper($tx);
  $customer_fields = array();

  foreach (FoxyDataUtils::$CustomerFieldMap as $feed_field => $db_field) {
    $customer_fields[$feed_field] = $trans->$feed_field;
  }
  $customer_billing_address = array();
  $customer_shipping_address = array();

  foreach (FoxyDataUtils::$CustomerAddressFieldMap as $feed_field => $db_field) {
    $billing_field = 'customer_'.$feed_field;
    $shipping_field = 'shipping_'.$feed_field;

    $customer_billing_address[$feed_field] = $trans->$billing_field;
    $customer_shipping_address[$feed_field] = $trans->$shipping_field;
  }

  if (!$customer_shipping_address['first_name'])
    $customer_shipping_address = $customer_billing_address;

  $customer_exists = $osc->customerExists($customer_fields['customer_email']);

  if ($customer_exists) {
    $osc->updateCustomer($utils->mapCustomerToDB($customer_fields));
    $osc->updateCustomerAddresses($utils->mapAddressToDB($customer_billing_address),
     $utils->mapAddressToDB($customer_shipping_address));
  }
  else {
    $osc->createCustomer($utils->mapCustomerToDB($customer_fields),
     $utils->mapAddressToDB($customer_billing_address));
  }

  $customer = $osc->findCustomer($customer_fields['customer_email']);

  $order = $osc->createOrderForCustomer($customer);
  $order->setCustomerAddress($utils->mapAddressToDB($customer_billing_address,
    OSCommerce::ORDER_CUSTOMER));
  $order->setBillingAddress($utils->mapAddressToDB($customer_billing_address,
    OSCommerce::ORDER_BILLING));
  $order->setShippingAddress($utils->mapAddressToDB($customer_shipping_address,
    OSCommerce::ORDER_SHIPPING));

  $order->setPaymentMethod('foxycart');
  $order->setDatePurchased($trans->transaction_date);

  $order->setTax($trans->tax_total);
  $order->setShippingTotal($trans->shipping_total);
  $order->setTotal($trans->order_total);

  $order->setSubtotal($order->getTotal() - $order->getTax() - $order->getShippingTotal());

  $sessionID = '';

  foreach ($tx->custom_fields[0]->custom_field as $customField) {
    $fieldName = $customField->custom_field_name[0]->tagData;
    $fieldValue = $customField->custom_field_value[0]->tagData;

    if ($fieldName == 'Comment') {
      $order->setComment($fieldValue);
    }
    if ($fieldName == 'sessionID') {
      $sessionID = $fieldValue;
    }
    if ($fieldName == 'basket') {
      $order->setProducts($osc->loadCartFromString($fieldValue));
    }
  }

  $order->saveToDB();

  $osc->torchCartForCustomer($customer);   // Burn the baskets, we don't need 'em.
  if ($sessionID)
    $osc->torchCartInSession($sessionID);
}

print "foxy";
?>
