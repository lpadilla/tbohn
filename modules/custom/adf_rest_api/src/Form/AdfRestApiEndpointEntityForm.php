<?php

namespace Drupal\adf_rest_api\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdfRestApiEndpointEntityForm.
 */
class AdfRestApiEndpointEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $adf_rest_api_endpoint_entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service name'),
      '#maxlength' => 250,
      '#default_value' => $adf_rest_api_endpoint_entity->label(),
      '#required' => TRUE,
    ];
    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint'),
      '#maxlength' => 250,
      '#default_value' => $adf_rest_api_endpoint_entity->get('endpoint'),
      '#required' => TRUE,
    ];
    $form['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Method'),
      '#options' => ['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE'],
      '#default_value' => $adf_rest_api_endpoint_entity->get('method'),
      '#required' => TRUE,
    ];
    $form['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout expiration'),
      '#default_value' => $adf_rest_api_endpoint_entity->get('timeout'),
      '#description' => $this->t('Maximum number of seconds to waiting a response.'),
      '#field_suffix' => $this->t('Seconds'),
      '#min' => 1,
      '#max' => 60,
      '#required' => TRUE,
    ];
    $form['cache_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Time in cache'),
      '#default_value' => $adf_rest_api_endpoint_entity->get('cache_time'),
      '#description' => $this->t('Time the answer is saved in cache. Input 0 to not save'),
      '#field_suffix' => $this->t('Minutes'),
      '#min' => 0,
      '#max' => 120,
      '#required' => TRUE,
    ];
    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Optional'),
      '#open' => FALSE,
    ];
    $form['options']['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client id'),
      '#maxlength' => 250,
      '#default_value' => $adf_rest_api_endpoint_entity->get('client_id'),
      '#description' => $this->t('Unique client id assigned to the registered application which is used by authorization server.'),
    ];
    $form['options']['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client secret'),
      '#maxlength' => 250,
      '#default_value' => $adf_rest_api_endpoint_entity->get('client_secret'),
      '#description' => $this->t('Client secret generated to the registered application which is used by authorization server.'),
    ];
    $form['options']['env_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Environment prefix'),
      '#default_value' => $adf_rest_api_endpoint_entity->get('env_prefix'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => $this->t('Variable that identifies deployment environment (e.g. prod or test)'),
    ];
    $form['options']['country_iso'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country ISO'),
      '#default_value' => $adf_rest_api_endpoint_entity->get('country_iso'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => $this->t('Country code (e.g. CO, PY, SV, BO, HN, GT...)'),
    ];
    $form['options']['prefix_country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prefix Country'),
      '#default_value' => $adf_rest_api_endpoint_entity->get('prefix_country'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => $this->t('Prefix for the number line (e.g. 503, 57,...)'),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $adf_rest_api_endpoint_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\adf_rest_api\Entity\AdfRestApiEndpointEntity::load',
      ],
      '#disabled' => !$adf_rest_api_endpoint_entity->isNew(),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $adf_rest_api_endpoint_entity = $this->entity;
    $status = $adf_rest_api_endpoint_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Adf rest api endpoint entity.', [
          '%label' => $adf_rest_api_endpoint_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Adf rest api endpoint entity.', [
          '%label' => $adf_rest_api_endpoint_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($adf_rest_api_endpoint_entity->toUrl('collection'));
  }

}
