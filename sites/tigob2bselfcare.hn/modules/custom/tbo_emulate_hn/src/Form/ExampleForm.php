<?php

namespace Drupal\tbo_emulate_hn\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ExampleForm class.
 */
class ExampleForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL)
  {
    $styles = [
      'width' => '800',
    ];

    $form['open_modal'] = [
      '#type' => 'link',
      '#title' => $this->t('New User'),
      '#url' => Url::fromRoute('tbo.open_modal_form', ['title' => 'New User', 'form' => 'Drupal\tbo_emulate_hn\Form\ModalForm'], $styles),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'button',
          'waves-effect',
          'waves-light',
          'btn',
        ],
      ],
    ];

    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

  /** * {@inheritdoc} */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
  }

  /** * {@inheritdoc} */
  public function getFormId()
  {
    return 'modal_element';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   * An array of configuration object names that are editable if called in
   * conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames()
  {
    return ['config.modal_element'];
  }
}