<?php

namespace Drupal\ungerboeck_events\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ungerboeck_events.settings',
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
    $config = $this->config('ungerboeck_events.settings');
    $form['number_of_blocks'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of Blocks to Create'),
      '#description' => $this->t('Number of Blocks to Create, each block can be placed independantly of each other.'),
      '#maxlength' => 5,
      '#size' => 5,
      '#default_value' => $config->get('number_of_blocks'),
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

    $this->config('ungerboeck_events.settings')
      ->set('number_of_blocks', $form_state->getValue('number_of_blocks'))
      ->save();
  }

}
