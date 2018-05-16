<?php

/**
 * @file
 * Configuration of Ungerboeck Helpers.
 */
function ungerboeck_helpers_settings() {
  // Add form to get screenshot server URL.
  $form['ungerboeck_helpers'] = array(
    '#type' => 'fieldset',
    '#title' => t('Configure Ungerboeck Events'),
  );

  $form['ungerboeck_helpers']['ungerboeck_helpers_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#default_value' => variable_get('ungerboeck_helpers_username', 'rubiapiuser'),
    '#description' => t('This is the username of the account that will be reading from Ungerboeck'),
  );

  $form['ungerboeck_helpers']['ungerboeck_helpers_password'] = array(
    '#type' => 'password',
    '#title' => t('Password'),
    '#default_value' => t(''),
    '#description' => t('Password for the above account. Note: this is not a secure password storage facility, so you should be using an account that has basically no rights. This field is blank even if a password is already set, that\'s OK.'),
  );

  $form['ungerboeck_helpers']['ungerboeck_helpers_server'] = array(
    '#type' => 'textfield',
    '#title' => t('Server running the Ungerboeck API'),
    '#default_value' => variable_get('ungerboeck_helpers_server', 'https://registration.extension.iastate.edu'),
    '#description' => t('The URL of server running the Ungerboeck API, for ISU, the above URL shouldn\'t have to change.'),
  );

  return system_settings_form($form);
}


/**
 * Validate the settings form
 */
function ungerboeck_helpers_settings_validate($form, &$form_state) {
  $saved_password = variable_get('ungerboeck_helpers_password', '');
  $new_password = $form_state['values']['ungerboeck_helpers_password'];

  if (empty($new_password)) {
      $form_state['values']['ungerboeck_helpers_password'] = $saved_password;
  }
  else {
    if ($new_password != $saved_password) {
      $form_state['values']['ungerboeck_helpers_password'] = encrypt($new_password);
    }
  }
}
