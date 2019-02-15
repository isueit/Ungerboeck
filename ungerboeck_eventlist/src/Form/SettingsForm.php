<?php

namespace Drupal\ungerboeck_eventlist\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ungerboeck_eventlist\Controller\Helpers;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ungerboeck_eventlist.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ungerboeck_eventlist.settings');
    $form['number_of_blocks'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of Blocks to Create'),
      '#description' => $this->t('Number of Blocks to Create, each block can be placed independantly of each other.'),
      '#maxlength' => 5,
      '#size' => 5,
      '#default_value' => $config->get('number_of_blocks'),
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => t('URL of Events Feeds'),
      '#description' => t('Base URL of the feed from Ungerboeck'),
      '#size' => 175,
      '#maxlength' => 300,
      '#default_value' => $config->get('url'),
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ungerboeck_eventlist.settings')
      ->set('number_of_blocks', $form_state->getValue('number_of_blocks'))
      ->set('url', Helpers::trim_slash($form_state->getValue('url')))
      ->save();
  }

}
