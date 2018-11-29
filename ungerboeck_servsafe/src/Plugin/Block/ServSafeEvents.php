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
    return [
      '#markup' => $this->t('This is a simple block!'),
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
