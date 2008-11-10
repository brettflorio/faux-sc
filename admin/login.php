<?php
/*
  $Id: login.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/functions/password_funcs.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'process':
        $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
        $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

        $check_query = tep_db_query("select id, user_name, user_password from " . TABLE_ADMINISTRATORS . " where user_name = '" . tep_db_input($username) . "'");

        if (tep_db_num_rows($check_query) == 1) {
          $check = tep_db_fetch_array($check_query);

          if (tep_validate_password($password, $check['user_password'])) {
            tep_session_register('admin');

            $admin = array('id' => $check['id'],
                           'username' => $check['user_name']);

            if (tep_session_is_registered('redirect_origin')) {
              $page = $redirect_origin['page'];
              $get_string = '';

              if (function_exists('http_build_query')) {
                $get_string = http_build_query($redirect_origin['get']);
              }

              tep_session_unregister('redirect_origin');

              tep_redirect(tep_href_link($page, $get_string));
            } else {
              tep_redirect(tep_href_link(FILENAME_DEFAULT));
            }
          }
        }

        $messageStack->add(ERROR_INVALID_ADMINISTRATOR, 'error');

        break;

      case 'logoff':
        tep_session_unregister('selected_box');
        tep_session_unregister('admin');
        tep_redirect(tep_href_link(FILENAME_DEFAULT));

        break;

      case 'create':
        $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");

        if (tep_db_num_rows($check_query) == 0) {
          $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
          $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

          tep_db_query('insert into ' . TABLE_ADMINISTRATORS . ' (user_name, user_password) values ("' . $username . '", "' . tep_encrypt_password($password) . '")');
        }

        tep_redirect(tep_href_link(FILENAME_LOGIN));

        break;
    }
  }

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }

  $admins_check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");
  if (tep_db_num_rows($admins_check_query) < 1) {
    $messageStack->add(TEXT_CREATE_FIRST_ADMINISTRATOR, 'warning');
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<meta name="robots" content="noindex,nofollow">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0" height="40">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
        <td class="pageHeading" align="right"><?php echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get') . tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"') . tep_hide_session_id() . '</form>'; ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td>

<?php
  $heading = array();
  $contents = array();

  if (tep_db_num_rows($admins_check_query) > 0) {
    $heading[] = array('text' => '<b>' . HEADING_TITLE . '</b>');

    $contents = array('form' => tep_draw_form('login', FILENAME_LOGIN, 'action=process'));
    $contents[] = array('text' => TEXT_USERNAME . '<br>' . tep_draw_input_field('username'));
    $contents[] = array('text' => '<br>' . TEXT_PASSWORD . '<br>' . tep_draw_password_field('password'));
    $contents[] = array('align' => 'center', 'text' => '<br><input type="submit" value="' . BUTTON_LOGIN . '" />');
  } else {
    $heading[] = array('text' => '<b>' . HEADING_TITLE . '</b>');

    $contents = array('form' => tep_draw_form('login', FILENAME_LOGIN, 'action=create'));
    $contents[] = array('text' => TEXT_CREATE_FIRST_ADMINISTRATOR);
    $contents[] = array('text' => '<br>' . TEXT_USERNAME . '<br>' . tep_draw_input_field('username'));
    $contents[] = array('text' => '<br>' . TEXT_PASSWORD . '<br>' . tep_draw_password_field('password'));
    $contents[] = array('align' => 'center', 'text' => '<br><input type="submit" value="' . BUTTON_CREATE_ADMINISTRATOR . '" />');
  }

  $box = new box;
  echo $box->infoBox($heading, $contents);
?>

    </td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
