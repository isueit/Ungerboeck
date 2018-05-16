<?php

/**
 * @file
 * Configuration of Ungerboeck Events.
 */
function ungerboeck_events_settings() {
  // Add form to get screenshot server URL.
  $form['ungerboeck_events'] = array(
    '#type' => 'fieldset',
    '#title' => t('Configure Ungerboeck Events'),
  );

  $form['ungerboeck_events']['ungerboeck_events_numberofblocks'] = array(
    '#type' => 'textfield',
    '#title' => t('Number of blocks to create'),
    '#default_value' => variable_get('ungerboeck_events_numberofblocks', '1'),
  );

  return system_settings_form($form);
}

