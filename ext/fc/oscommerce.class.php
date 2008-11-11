<?php
define('OSC_INCLUDES_PATH', '../../');
ini_set('include_path', ini_get('include_path') . ':' . OSC_INCLUDES_PATH);

require_once(OSC_INCLUDES_PATH . 'includes/configure.php');
require(DIR_WS_FUNCTIONS . 'general.php');
require(DIR_WS_FUNCTIONS . 'database.php');
require(DIR_WS_INCLUDES . 'database_tables.php');

tep_db_connect() or die('Unable to connect to database server!');


/**
 * Contains actions and routines for interacting with an osCommerce installation
 * on this same webserver. You MUST require() the target osCommerce install's
 * configure.php and database utilities before loading this class.  See the
 *  require statements at the top of this file.
 */
class OSCommerce {
  public const ADDRESS_BOOK = 'entry_';
  public const ORDER_CUSTOMER = 'customer_';
  public const ORDER_BILLING = 'billing_';
  public const ORDER_SHIPPING = 'shipping_';

  protected static $instance = null;

  /**
   * Obtain the single instance of this OSCommerce install.
   */
  public static function instance() {
    if (!OSCommerce::$instance)
      OSCommerce::$instance = new OSCommerce();

    return OSCommerce::$instance;
  }

  protected function __construct() {}


  public function createOrderForCustomer($customer) {
    return new OSCommerce_Order($customer);
  }

  public function findCustomer($customer_email) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_CUSTOMERS .
      ' WHERE customers_email_address = "'.tep_db_prepare_input($customer_email).'"');

    return (tep_db_num_rows($result) > 0) ? tep_db_fetch_array($result) : null;
  }

  public function customerExists($customerEmail) {
    return $this->findCustomer($customerEmail) != null;
  }

  /**
   * Pretty much cribbed from osC's create_account.php.
   */
  public function createCustomer($customerFields, $defaultAddress) {
    tep_db_perform(TABLE_CUSTOMERS, $customerFields);
    $customerID = tep_db_insert_id();


    $defaultAddress['customers_id'] = $customerID;
    tep_db_perform(TABLE_ADDRESS_BOOK, $defaultAddress);

    $addressID = tep_db_insert_id();
    tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$addressID . "' where customers_id = '" . (int)$customerID . "'");

    tep_db_query("INSERT INTO " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customerID . "', '0', now())");
  }

  public function updateCustomer($customerFields) {
    $customer = $this->findCustomer($customerFields['customers_email_address']);

    foreach ($customerFields as $field => $value)
      $customer[$field] = $value;

    tep_db_perform(TABLE_CUSTOMERS, $customer, 'UPDATE');
  }

  public function updateCustomerAddresses($billing, $shipping) {
    die('not happening: address update not implemented.');
  }

  public function lookupCountryByCode($name) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_COUNTRIES .
     ' WHERE countries_iso_code_2 = "' . tep_db_input($name) . '"');
    $country = tep_db_fetch_array($result);

    return $country;
  }

  public function lookupCountryByID($id) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_COUNTRIES .
     ' WHERE id = "' . tep_db_input($id) . '"');
    $country = tep_db_fetch_array($result);

    return $country;
  }

  public function lookupZoneByNameAndCode($name, $country_id) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_ZONES . ' ' .
     'WHERE zone_code = "' . tep_db_input($name) . '" AND '.
     'zone_country_id = "' . tep_db_input($country_id) . '"');
    $zone = tep_db_fetch_array($result);

    return $zone;
  }
}

class OSCommerce_Order {
  protected static $addressToOrderFieldMapping = array(
   'customers_name' => 'entry_firstname + entry_lastname',  // Must calc value.
   'customers_company' => 'entry_company',
   'customers_street_address' => 'entry_street_address',
   'customers_suburb' => 'entry_suburb',
   'customers_city' => 'entry_city',
   'customers_postcode' => 'entry_postcode',
   'customers_state' => 'entry_state',
   'customers_country' => 'entry_country_id', // Must calc value
   'customers_telephone' => 'telephone',
   'customers_email_address' => 'email_address',
   'customers_address_format_id' => 'tep_get_address_format_id' // Must calc value.
 );

  protected $fields = array();

  public function __construct($customer) {
    global $customer_field_mapping;

    $this->fields['customers_id'] = $customer['customer_id'];

    foreach ($customer_field_mapping as $feedField => $dbField)
      $this->fields[$dbField] = $customer[$dbField];

    $this->fields['customers_name'] =
     $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];

    unset($this->fields['customers_firstname']);
    unset($this->fields['customers_lastname']);
  }

  public function setCustomerAddress($address) {
  }

  protected function setAddress($address, $prefix=OSCommerce::CUSTOMER) {
    global $customer_address_field_mapping;

    foreach ($customer_address_field_mapping as $feedField => $dbField) {
      $this->fields[$prefix.$dbField] = $address[$dbField];
    }
  }

//  public function setAddress($order, $field_prefix, $address) {
//    $billingFields = array('customers_company' => $billingAddress['entry_company'],
//     'customers_street_address' => $billingAddress['entry_street_address'],
//     'customers_suburb' => $billingAddress['entry_suburb'],
//     'customers_city' => $billingAddress['entry_city'],
//     'customers_postcode' => $billingAddress['entry_postcode'], 
//     'customers_state' => $billingAddress['entry_state'], 
//     'customers_country' => $billingAddress['entry_country_id']['title'], 
//     'customers_telephone' => $billingAddress['telephone'], 
//     'customers_email_address' => $billingAddress['email_address'],
//     'customers_address_format_id' => $billingAddress['format_id'], );
//
//    foreach ($billingFields as $field => $value)
//      $order[$field] = $value;
//  }
}

?>
