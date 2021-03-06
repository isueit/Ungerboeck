<?php

namespace Drupal\ungerboeck_helpers\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\encrypt\Entity\EncryptionProfile;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ungerboeck_helpers.settings',
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
    $config = $this->config('ungerboeck_helpers.settings');
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
      '#size' => 64,
      '#default_value' => t(''),
    ];
    $form['server_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Server running the Ungerboeck API'),
      '#description' => $this->t('The URL of server running the Ungerboeck API, for ISU, the above URL shouldn&#039;t have to change.'),
      '#size' => 64,
      '#default_value' => $config->get('server_url'),
    ];
    $form['encryption_profile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Encryption Profile'),
      '#description' => $this->t('The Encryption Profile from <a href="admin/config/system/encryption/profiles">this config page</a>. Note: This should be a dropdown.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('encryption_profile'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // If a new password is given, then encrypt it and save it,
    // otherwise use the old password
    $config = $this->config('ungerboeck_helpers.settings');
    $saved_password = $config->get('password');
    $new_password = $form_state->getValue('password');
    $encryption_profile = EncryptionProfile::load($config->get('encryption_profile'));

    if (empty($new_password)) {
      $form_state->setValue('password', $saved_password);
    }
    else {
      if ($new_password != $saved_password) {
        $form_state->setValue('password', \Drupal::service('encryption')->encrypt($new_password, $encryption_profile));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ungerboeck_helpers.settings')
      ->set('username', $form_state->getValue('username'))
      ->set('password', $form_state->getValue('password'))
      ->set('server_url', $form_state->getValue('server_url'))
      ->set('encryption_profile', $form_state->getValue('encryption_profile'))
      ->save();
  }

}
