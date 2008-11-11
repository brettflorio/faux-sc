<?php
require_once 'class.rc4crypt.php';
require_once 'class.xmlparser.php';


$key = 'CHANGE THIS TEXT to your own datafeed keyphrase';

$_POST['FoxyData'] or die("error"); // Make sure we got passed some FoxyData

function fatal_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
	die($errstr);
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
   *  @see $customer_field_mapping
   */
  function mapCustomerToDB($osc, $customer_fields) {
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
      $rtn[$db_field] = $address_fields[$feed_field];

    $country = $this->osc->lookupCountryByCode($address_fields['country']);
    $rtn['entry_country_id'] = $country['country_id'];

    $zone = $this->osc->lookupZoneByNameAndCountryID(
     $address_fields['state'], $rtn['entry_country_id']);

    $rtn['entry_zone_id'] = $zone['zone_id'];

    return $rtn;
  }
}

$utils = new FoxydataUtils($osc);

$decryptor = new rc4crypt();
$FoxyData = $decryptor->decrypt($key, urldecode($_POST["FoxyData"]));

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
      $rtn = $propertyNode[0]->tagData;
    }

    return $rtn;
  }
}


foreach ($data->document->transactions[0]->transaction as $tx) {
  $trans = new PropertyWrapper($tx);
  $customer_fields = array();

  foreach ($customer_field_mapping as $feed_field => $db_field) {
    $customer_fields[$feed_field] = $trans->$feed_field;
  }

  $customer_billing_address = array();
  $customer_shipping_address = array();

  foreach ($customer_address_field_mapping as $feed_field => $db_field) {
    $billing_field = 'customer_'.$feed_field;
    $shipping_field = 'shipping_'.$feed_field;

    $customer_billing_address[$feed_field] = $trans->$billing_field;
    $customer_shipping_address[$feed_field] = $trans->$shipping_field;
  }

  $customer_exists = $osc->customerExists($customer_fields['customer_email']);

  if ($customer_exists) {
    $osc->updateCustomer($utils->mapCustomerToDB($customer_fields));
    $osc->updateCustomerAddresses($utils->mapAddressToDB($customer_billing_address),
     $utils->mapAddressToDB($customer_shipping_address));
  }
  else {
    $osc->createCustomer($utils->mapCustomerToDB($osc, $customer_fields),
     $utils->mapAddressToDB($osc, $customer_billing_address));
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

  foreach ($tx->custom_fields[0]->custom_field as $field) {
  }


  // orders -> orders_products -> orders_products_attributes

  $osc->torchCustomerBasket($customer);   // Burn the baskets, we don't need 'em.
}

print "foxy";
?>
