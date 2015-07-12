<?php

/**
 * @file
 * Contains Drupal\opcache\Form\SettingsForm.
 */

namespace Drupal\opcache\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\opcache\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'opcache.settings'
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
    $config = $this->config('opcache.settings');
    $form['backends'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Backends'),
      '#description' => $this->t('Enter the IP addresses of your PHP backends. For direct connections to FastCGI, prefix with fcgi://.'),
      '#default_value' => $config->get('backends'),
    );

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

    $this->config('opcache.settings')
      ->set('backends', $form_state->getValue('backends'))
      ->save();
  }

}
