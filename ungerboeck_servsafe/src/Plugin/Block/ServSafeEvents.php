<?php

namespace Drupal\ungerboeck_servsafe\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "ungerboeck_servsafe_block",
 *   admin_label = @Translation("Ungerboeck - ServSafe Events"),
 * )
 */
class ServSafeEvents extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
  $token = ungerboeck_helpers_get_token();

  $event_list = ungerboeck_helpers_get_event_list($config['orgcode'], $config['search_string'], $token);
  $json_events = json_decode(strip_tags($event_list), TRUE);

  $results = '<ul class="ungerboeck_servsafe">';

  foreach ($json_events as $event) {
    $time = strtotime($event['StartDate']);
    $location = str_replace(', IA', '', $event['Description']);
    $location = str_replace('SERVSAFE (Spanish) - ', '', $location);
    $location = str_replace('SERVSAFE - ', '', $location);
    $webaddress = $event['WebAddress'];

    if ($event['WebAddress'] == '') {
      $event_details = json_decode(strip_tags(ungerboeck_helpers_get_event_details($config['orgcode'], $event['EventID'], $token)), TRUE);
      $webaddress = $event_details['EventUserFieldSets'][0]['UserText01'];
    }

    $results .= '<li>';
    $results .= '<a href="' . $webaddress . '">' . $location;
    if (strpos($event['Description'], 'Spanish') != FALSE) {
      $results .= ' (Spanish)';
    }

    $results .= '</a><br/>';
    $results .= date('l, F j, Y', $time) . '<br/>';

    $results .= '</li>';
  }

  $results .= '</ul>';

    return [
      '#markup' => $this->t($results),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
//    $form['ungerboeck_fieldset'] = array(
//      '#type' => 'fieldset',
//      '#collaspible' => TRUE,
//      '#collasped' => TRUE,
//    );

//    $form['ungerboeck_fieldset']['orgcode'] = array(
    $form['orgcode'] = array(
      '#type' => 'textfield',
      '#title' => t('OrgCode'),
      '#description' => t('For ISU, this is usually 10'),
      '#size' => 15,
      '#default_value' => $config['orgcode'],
    );

//    $form['ungerboeck_fieldset']['search_string'] = array(
    $form['search_string'] = array(
      '#type' => 'textfield',
      '#title' => t('Search String'),
      '#description' => t('This is the OData search string.<br/>Note: #TodayDate# will be replaced by the actual date'),
      '#size' => 175,
      '#maxlength' => 300,
      '#default_value' => $config['search_string'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    //$this->configuration['ungerboeck_servsafe_settings'] = $form_state->getValue('ungerboeck_servsafe_settings');
    $this->configuration['orgcode'] = $values['orgcode'];
    $this->configuration['search_string'] = $values['search_string'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'orgcode' => '10',
      'search_string' => 'Account eq \'00000150\' and Status eq \'30\' and StartDate ge DateTime\'#TodayDate#\'$orderby=StartDate,StartTime$page_size=4',
    );
  }
}
