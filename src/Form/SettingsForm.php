<?php

namespace Drupal\commerce_variation_attribute_filter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_variation_attribute_filter_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('commerce_variation_attribute_filter.settings')
      ->set('filter_values', $form_state->getValue('filter_values'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['commerce_variation_attribute_filter.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_variation_attribute_filter.settings');

    $form['filter_values'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Filter values'),
      '#description' => $this->t('Values to filter out of product variation titles, one per line.'),
      '#default_value' => $config->get('filter_values'),
    ];

    return parent::buildForm($form, $form_state);
  }
}
