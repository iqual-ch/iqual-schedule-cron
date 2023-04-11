<?php

namespace Drupal\scheduler_cron\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for setting Scheduler Cron settings .
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scheduler_cron_settingsForm';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'scheduler_cron.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('scheduler_cron.settings');

    // Add a textfield for interval.
    $form['interval'] = ['#type' => 'textfield', '#title' => $this->t('Interval in minutes'), '#default_value' => $config->get('interval')];

    // Add a checkbox for log setting.
    $form['log'] = ['#type' => 'checkbox', '#title' => $this->t('Logging'), '#default_value' => $config->get('log')];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration and set new values.
    $this->configFactory->getEditable('scheduler_cron.settings')
      ->set('interval', $form_state->getValue('interval'))
      ->set('log', $form_state->getValue('log'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'scheduler_cron.settings' => [
        'interval' => 5,
        'log' => FALSE,
      ],
    ];
  }

}
