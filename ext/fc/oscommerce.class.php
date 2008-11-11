<?php
define('OSC_INCLUDES_PATH', '../../');
ini_set('include_path', ini_get('include_path') . ':' . OSC_INCLUDES_PATH);

require_once(OSC_INCLUDES_PATH . 'includes/configure.php');
require_once(OSC_INCLUDES_PATH . 'includes/application_top.php');


/**
 * Contains actions and routines for interacting with an osCommerce installation
 * on this same webserver. You MUST require() the target osCommerce install's
 * configure.php and database utilities before loading this class.  See the
 *  require statements at the top of this file.
 */
class OSCommerce {
  const ADDRESS_BOOK = 'entry_';
  const ORDER_CUSTOMER = 'customer_';
  const ORDER_BILLING = 'billing_';
  const ORDER_SHIPPING = 'delivery_';

/**
 * osC customer fields.
 */
  public static $CustomerFields = array('customers_email_address',
    'customers_firstname', 'customers_lastname',
   'customers_telephone', 'customers_password');

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
    return new OSCommerce_Order($this, $customer);
  }

  public function findCustomer($customerEmail) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_CUSTOMERS .
      ' WHERE customers_email_address = "'.tep_db_prepare_input($customerEmail).'"');

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

    tep_db_perform(TABLE_CUSTOMERS, $customer, 'update',
     'customers_id = "' . tep_db_input($customer['customers_id']) . '"');
  }

  public function updateCustomerAddresses($billing, $shipping) {
    print ('not happening: address update not implemented.');
  }

  public function findCountryByCode($name) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_COUNTRIES .
     ' WHERE countries_iso_code_2 = "' . tep_db_input($name) . '"');
    $country = tep_db_fetch_array($result);

    return $country;
  }

  public function findCountryByID($id) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_COUNTRIES .
     ' WHERE countries_id = "' . tep_db_input($id) . '"');
    $country = tep_db_fetch_array($result);

    return $country;
  }

  public function findZoneByNameAndCountryID($name, $country_id) {
    $result = tep_db_query('SELECT * FROM ' . TABLE_ZONES . ' ' .
     'WHERE zone_code = "' . tep_db_input($name) . '" AND '.
     'zone_country_id = "' . tep_db_input($country_id) . '"');
    $zone = tep_db_fetch_array($result);

    return $zone;
  }

  /**
   * Creates a hash of a given customer's stored cart in osCommerce.  Returns a hash
   * of the products in a customer's cart structured as follows:
   *
   * Given $productsID, the unique key of a product in the cart, and 
   *  $productsOptionsID the ID of an option of that product:
   *
   * $cart[$productsID]['qty'] // the quantity of that product in the cart
   * $cart[$productsID]['attributes'] // if set, that product's attributes
   * $cart[$productsID]['attributes'][$productsOptionsID] // \
   *                                  // the value of the selected product option
   *  
   */
  public function loadCartForCustomer($customer) {
    $customerID = $customer['customers_id'];
    $cart = array();

    $productsQuery = tep_db_query("select products_id, customers_basket_quantity from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customerID . "'");
    while ($products = tep_db_fetch_array($productsQuery)) {
      $cart[$products['products_id']] =
        array('qty' => $products['customers_basket_quantity']);

      $attributesQuery = tep_db_query("select products_options_id, products_options_value_id from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customerID . "' and products_id = '" . tep_db_input($products['products_id']) . "'");
      while ($attributes = tep_db_fetch_array($attributesQuery)) {
        $cart[$products['products_id']]['attributes'][$attributes['products_options_id']] = $attributes['products_options_value_id'];
      }
    }

    return $cart;
  }
}

class OSCommerce_Order {
  const NEW_RECORD = -1;

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
  protected $products = array();

  public function __construct($osc, $customer) {
    global $currencies;

    $this->osc = $osc;

    $this->fields['orders_id'] = OSCommerce_Order::NEW_RECORD;
    $this->fields['customers_id'] = $customer['customers_id'];

    foreach (OSCommerce::$CustomerFields as $fields)
      $this->fields[$fields] = $customer[$fields];

    $this->fields['customers_name'] =
     $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];

    unset($this->fields['customers_firstname']);
    unset($this->fields['customers_lastname']);

    $this->fields['orders_status'] = DEFAULT_ORDERS_STATUS_ID;
    $this->fields['currency'] = DEFAULT_CURRENCY;
    $this->fields['currency_value'] =
     $currencies->currencies[DEFAULT_CURRENCY]['value'];
    $this->fields['cc_type'] = '';
    $this->fields['cc_owner'] = '';
    $this->fields['cc_number'] = '';
    $this->fields['cc_expires'] = '';

    $this->fields['subtotal'] = 0;
    $this->fields['tax'] = 0;
    $this->fields['comments'] = '';
  }

  public function setCustomerAddress($address) {
    $this->setAddress($address, OSCommerce::ORDER_CUSTOMER);
  }
  public function setBillingAddress($address) {
    $this->setAddress($address, OSCommerce::ORDER_BILLING);
  }
  public function setShippingAddress($address) {
    $this->setAddress($address, OSCommerce::ORDER_SHIPPING);
  }

  protected function setAddress($address, $prefix=OSCommerce::ORDER_CUSTOMER) {
    global $currencies;

    foreach ($address as $addressField => $value)
      $this->fields[$addressField] = $value;

    $this->fields[$prefix.'name'] =
     "{$this->fields[$prefix.'firstname']} {$this->fields[$prefix.'lastname']}";

    unset($this->fields[$prefix.'firstname']);
    unset($this->fields[$prefix.'lastname']);

    $country_id = $address[$prefix.'country_id'];
    $country = $this->osc->findCountryByID($country_id);

    $this->fields[$prefix.'country'] = $country['countries_name'];
    $this->fields[$prefix.'address_format_id'] = $country['address_format_id'];

    unset($this->fields[$prefix.'country_id']);
  }

  protected function __call($name, $args) {
    if (method_exists($this, $name))
      return call_user_func(array($this, $name), $args);
    else if (strpos($name, 'get') == 0 || strpos($name, 'set') == 0) {
      $op = substr($name, 0, strlen('get'));
      $fieldName = substr($name, strlen('get'));

      $fieldName = preg_replace('/([a-z])([A-Z])+/', '$1_$2', $fieldName);
      $fieldName = strtolower($fieldName);

      if ($op == 'get')
        return $this->fields[$fieldName];
      else if ($op == 'set')
        $this->fields[$fieldName] = $args[0];
      else
        throw new Exception('method not found: ' . $name);
    }
  }

  public function setProducts($products) { $this->products = $products; }

  public function saveToDB() {
    if ($this->fields['orders_id'] == OSCommerce_Order::NEW_RECORD) {
      tep_db_perform(TABLE_ORDERS, $this->orders);
    }
    else {
      throw new Exception("not happening: order update not implemented.");
    }
  }
}

?>
