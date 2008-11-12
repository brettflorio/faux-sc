<?php
require_once('config.php');

require_once(OSC_INCLUDES_PATH . 'includes/configure.php');
require_once(OSC_INCLUDES_PATH . 'includes/application_top.php');

define('DEFAULT_LANGUAGE_ID', '1');


/**
 * Contains actions and routines for interacting with an osCommerce installation
 * on this same webserver. You MUST require() the target osCommerce install's
 * configure.php and database utilities before loading this class.  See the
 *  require statements at the top of this file.
 */
class OSCommerce {
  const ADDRESS_BOOK = 'entry_';
  const ORDER_CUSTOMER = 'customers_';
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

      $productID = $products['products_id'];
      $productInCart = $cart[$productID];

      $productFields = $this->findProductByID($products['products_id']);
      $productFields['quantity'] = $productInCart['qty'];

      if (isset($productInCart['attributes'])) {
        $productFields['final_price'] += $this->attributesPrice($productID, $productInCart);
        $productFields['attributes'] = $productInCart['attributes'];
      }

      $cart[$products['products_id']] = $productFields;
    }

    return $cart;
  }

  /**
   * Ripped from osCommerce shopping cart.
   */
  protected function attributesPrice($productID, $productInCart) {
    $attributesPrice = 0;
    $attributes = $productInCart['attributes'];

    reset($productInCart['attributes']);
    while (list($option, $value) = each($productInCart['attributes'])) {
      $attribute_price_query = tep_db_query("select options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$productID . "' and options_id = '" . (int)$option . "' and options_values_id = '" . (int)$value . "'");
      $attribute_price = tep_db_fetch_array($attribute_price_query);
      if ($attribute_price['price_prefix'] == '+') {
        $attributesPrice += $attribute_price['options_values_price'];
      } else {
        $attributesPrice -= $attribute_price['options_values_price'];
      }
    }

    return $attributesPrice;
  }

  public function findProductByID($productID) {
    $query = tep_db_query("select p.products_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$productID . "' and pd.products_id = p.products_id and pd.language_id = '" . DEFAULT_LANGUAGE_ID . "'");

    $product = tep_db_fetch_array($query);
    $prid = $product['products_id'];
    $product_price = $product['products_price'];

    $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$prid . "' and status = '1'");
    if (tep_db_num_rows($specials_query)) {
      $specials = tep_db_fetch_array($specials_query);
      $product_price = $specials['specials_new_products_price'];
    }

    return array('id' => $prid,
                 'name' => $product['products_name'],
                 'model' => $product['products_model'],
                 'image' => $product['products_image'],
                 'price' => $product_price,
                 'weight' => $product['products_weight'],
                 'final_price' => ($product_price),
                 'tax_class_id' => $product['products_tax_class_id']);
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

    unset($this->fields['customers_password']);

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
    unset($this->fields[$prefix.'zone_id']);
  }

  public function getBillingAddress() {
    return $this->getAddress(OSCommerce::ORDER_BILLING);
  }

  public function getShippingAddress() {
    return $this->getAddress(OSCommerce::ORDER_SHIPPING);
  }

  /**
   * Translate from the prefixed order address fields back to a non-prefixed
   * hash.  This is used by, for example, the tep_address_label function.
   */
  protected function getAddress($prefix) {
    $fields = array();

    foreach ($this->fields as $field => $value) {
      if (strpos($field, $prefix) === 0) // Strip off address prefix, store.
        $fields[substr($field, strlen($prefix))] = $value;
    }

    return $fields;
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

  public function setShippingTotal($total) { $this->shipping_total = $total; }
  public function getShippingTotal() { return $this->shipping_total;}

  public function setSubtotal($subtotal) { $this->subtotal = $subtotal; }
  public function getSubtotal() { return $this->subtotal; }

  public function setTax($tax) { $this->tax = $tax; }
  public function getTax() { return $this->tax; }

  public function setTotal($total) { $this->total = $total; }
  public function getTotal() { return $this->total;}

  public function setComment($comment) { $this->comment = $comment; }
  public function getComment() { return $this->comment;}

  public function setProducts($products) { $this->products = $products; }

  public function saveToDB() {
    if ($this->fields['orders_id'] == OSCommerce_Order::NEW_RECORD) {
      unset($this->fields['orders_id']);

      tep_db_perform(TABLE_ORDERS, $this->fields);
      $this->fields['orders_id'] = $orderID = tep_db_insert_id();

      $totalPrice = 0;
      $productsOrdered = '';  // Text summary of products ordered; for email.

      foreach ($this->products as $productID => $fields) {
        $orderProduct = array('orders_id' => $orderID, 
                              'products_id' => $fields['id'], 
                              'products_model' => $fields['model'], 
                              'products_name' => $fields['name'], 
                              'products_price' => $fields['price'], 
                              'final_price' => $fields['final_price'], 
                              'products_tax' => 0, 
                              'products_quantity' => $fields['quantity']);
        tep_db_perform(TABLE_ORDERS_PRODUCTS, $orderProduct);
        $orderProductID = tep_db_insert_id();

        $productsAttributesText = '';

        if (isset($fields['attributes'])) {
          foreach ($fields['attributes'] as $option_id => $value_id) {
            $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $fields['id'] . "' and pa.options_id = '" . $option_id . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $value_id . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . DEFAULT_LANGUAGE_ID . "' and poval.language_id = '" . DEFAULT_LANGUAGE_ID . "'");

            $attributes_values = tep_db_fetch_array($attributes);

            $sql_data_array = array('orders_id' => $orderID, 
                                    'orders_products_id' => $orderProductID, 
                                    'products_options' => $attributes_values['products_options_name'],
                                    'products_options_values' => $attributes_values['products_options_values_name'], 
                                    'options_values_price' => $attributes_values['options_values_price'], 
                                    'price_prefix' => $attributes_values['price_prefix']);
            tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
            $productsAttributesText .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }

        $productsOrdered .= $fields['quantity'] . ' x ' . $fields['name'] . ' (' . $fields['model'] . ') = ' . sprintf('$ %.2f', $fields['quantity'] * $fields['final_price']) . $productsAttributesText . "\n";
      }

      $orderTotals = array();
      $count = 0;
      $orderTotals[] = array('orders_id' => $orderID,
                             'title' => 'Subtotal',
                             'text' => 'Subtotal',
                             'value' => $this->getSubtotal(), 
                             'text' =>  sprintf('$%.2f', $this->getSubtotal()),
                             'class' => 'ot_subtotal', 
                             'sort_order' => count($orderTotals));
      tep_db_perform(TABLE_ORDERS_TOTAL, $orderTotals[$count++]);

      $orderTotals[] = array('orders_id' => $orderID,
                             'title' => 'Tax',
                             'text' => 'Tax',
                             'value' => $this->getTax(), 
                             'text' =>  sprintf('$%.2f', $this->getTax()),
                             'class' => 'ot_tax', 
                             'sort_order' => count($orderTotals));
      tep_db_perform(TABLE_ORDERS_TOTAL, $orderTotals[$count++]);

      $orderTotals[] = array('orders_id' => $orderID,
                             'title' => 'Shipping',
                             'text' => 'Shipping',
                             'text' =>  sprintf('$%.2f', $this->getShippingTotal()),
                             'value' => $this->getShippingTotal(), 
                             'class' => 'ot_shipping', 
                             'sort_order' => count($orderTotals));
      tep_db_perform(TABLE_ORDERS_TOTAL, $orderTotals[$count++]);

      $orderTotals[] = array('orders_id' => $orderID,
                             'title' => 'Total',
                             'text' =>  sprintf('$%.2f', $this->getTotal()),
                             'value' => $this->getTotal(), 
                             'class' => 'ot_total', 
                             'sort_order' => count($orderTotals));
      tep_db_perform(TABLE_ORDERS_TOTAL, $orderTotals[$count++]);

      reset($orderTotals);

      $this->sendOrderEmail($productsOrdered, $orderTotals);
    }
    else {
      throw new Exception("not happening: order update not implemented.");
    }
  }

  /**
   * Cribbed (again) from osCommerce. Their design philosophy must be: Always
   * Repeat Yourself (At Least If You Want To Do Anything That's Not Baked In.) --
   * ARY(ATLIFYWTDATNBI)
   */
  public function sendOrderEmail($productsOrdered, $orderTotals) {
    include(OSC_INCLUDES_PATH . 'includes/languages/english/checkout_process.php');

    $emailText = STORE_NAME . "\n" . 
                   EMAIL_SEPARATOR . "\n" . 
                   EMAIL_TEXT_ORDER_NUMBER . ' ' . $this->fields['orders_id'] . "\n" .
                   EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->fields['orders_id'], 'SSL', false) . "\n" .
                   EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
    if ($this->fields['comments']) {
      $emailText .= tep_db_output($this->fields['comments']) . "\n\n";
    }
    $emailText .= EMAIL_TEXT_PRODUCTS . "\n" . 
                    EMAIL_SEPARATOR . "\n" . 
                    $productsOrdered . 
                    EMAIL_SEPARATOR . "\n";

    for ($i=0, $n=sizeof($orderTotals); $i<$n; $i++) {
      $emailText .= strip_tags($orderTotals[$i]['title']) . ' ' . strip_tags($orderTotals[$i]['text']) . "\n";
    }

    extract($this->getShippingAddress());
    $emailText .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" . 
      EMAIL_SEPARATOR . "\n" .
"$name
$street_address\n" .
($suburb ? $suburb."\n" : "") .
"$city, $state $postcode
$country\n\n";
                     

    extract($this->getShippingAddress());
    $emailText .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                    EMAIL_SEPARATOR . "\n" .
"$name
$street_address\n" .
($suburb ? $suburb."\n" : "") .
"$city, $state $postcode
$country\n\n";
    print_r($this);
    die($emailText);


    tep_mail($this->fields['customers_firstname'] . ' ' . $order->customer['customers_lastname'], $order->customer['customers_email_address'], EMAIL_TEXT_SUBJECT, $emailText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

    if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
      tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $emailText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    }
  }
}

?>
