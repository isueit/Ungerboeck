<?php

namespace Drupal\ungerboeck_helpers\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdminSettingsForm.
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ungerboeck_helpers.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ungerboeck_helpers.adminsettings');
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('This is the username of the account that will be reading from Ungerboeck&#039;s API'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('username'),
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#description' => $this->t('Password for the above account. Note: this is not a secure password storage facility, so you should be using an account that has basically no rights. This field is blank even if a password is already set, that&#039;s OK.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('password'),
    ];
    $form['server_url'] = [
      '#type' => 'testfield',
      '#title' => $this->t('Server running the Ungerboeck API'),
      '#description' => $this->t('The URL of server running the Ungerboeck API, for ISU, the above URL shouldn&#039;t have to change.'),
      '#default_value' => $config->get('server_url'),
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

    $this->config('ungerboeck_helpers.adminsettings')
      ->set('username', $form_state->getValue('username'))
      ->set('password', $form_state->getValue('password'))
      ->set('server_url', $form_state->getValue('server_url'))
      ->save();
  }

}
