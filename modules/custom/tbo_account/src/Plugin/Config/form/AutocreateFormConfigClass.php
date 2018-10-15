<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class PagerFormConfig.
 *
 * @package Drupal\tbo_account\Plugin\Config\form
 */
class AutocreateFormConfigClass
{

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'tbo_account.autocreateformconfig',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'autocreate_form_config';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = \Drupal::config('tbo_account.autocreateformconfig');
        $option = $config->get('option');
        if ($option == NULL){
           $option = 'normal';
        }
        $form['options'] = [
            "#type" => 'radios',
            '#title' => t('Proceso de auto creación de la empresa con servicios fijos:'),
            '#options' => [
                'normal' => t('Flujo normal'),
                'alternative' => t('Flujo alternativo'),
            ],
            '#default_value' => $option
        ];
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $config = \Drupal::configFactory()->getEditable('tbo_account.autocreateformconfig');
        $method = $form['options'][$form_state->getValue('options')]['#title'];
        $config->set('option', $form_state->getValue('options'))
          ->set('method',$method)
          ->save();
    }
}