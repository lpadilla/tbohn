<?php

namespace Drupal\tbo_entities\Form;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Class CategoryServicesEntityForm.
 *
 * @package Drupal\tbo_entities\Form
 */
class CategoryServicesEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $category_services_entity = $this->entity;

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $category_services_entity->id(),
      '#disabled' => !$category_services_entity->isNew(),
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this item. It must only contain lowercase letters, numbers, and underscores.'),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del categoria de servicios'),
      '#maxlength' => 30,
      '#default_value' => $category_services_entity->label(),
      '#description' => $this->t("Nombre del categoria de servicios."),
      '#required' => TRUE,
      '#id' => 'label',
    ];

    $form['#attached']['library'][] = 'image/admin';

    $default_value = '';
    // Show the thumbnail preview.
    if ($category_services_entity->get('icon')) {
      $file = File::load($category_services_entity->get('icon'));
      if ($file) {
        $form['preview'] = [
          '#type' => 'item',
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#uri' => $file->getFileUri(),
        ];
      }
      $default_value = [$category_services_entity->get('icon')];
    }

    $form['icon'] = [
    // You can find a list of available types in the form api.
      '#type' => 'managed_file',
      '#title' => t('Icono de la categoria'),
      '#default_value' => $default_value,
      '#upload_location' => 'public://category_services',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => [$maximum_dimensions = '50x50', $minimum_dimensions = '20x20'],
      ],
      '#description' => $this->t('El icono debe medir entre 20x20 pixeles y 50x50 pixeles, de extension png jpg jpeg'),
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => 30,
      '#default_value' => $category_services_entity->get('url'),
      '#description' => $this->t("URL de re direccionamiento."),
      '#required' => TRUE,
    ];

    $form['type_category'] = [
      '#type' => 'radios',
      '#title' => $this->t('Tipo de categoria de servicios'),
      '#options' => ['fijo' => $this->t('Fija'), 'movil' => $this->t('Móvil')],
      '#default_value' => $category_services_entity->get('type_category'),
      '#required' => TRUE,
    ];

    $form['parameter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Parámetro'),
      '#maxlength' => 30,
      '#default_value' => $category_services_entity->get('parameter'),
      '#description' => $this->t("Parámetro contra el que se debe validar en el servicio web. En este caso se va a usar el parametro productId."),
      '#required' => TRUE,
    ];

    $service = \Drupal::service('tbo_entities.entities_service');
    $options = $service->getInvitationsPopup();

    // Let's create the link.
    $url = Url::fromRoute(
      'entity.invitation_access_entity.add_form'

    );

    $internal_link = \Drupal::l('aquí', $url);

    $link = [
      '#type' => 'markup',
      '#markup' => $internal_link,
    ];

    $form['invitation_popup'] = [
      '#type' => 'select',
      '#title' => $this->t('Invitación en popup'),
      '#options' => $options,
      '#default_value' => $category_services_entity->get('invitation_popup'),
      '#required' => TRUE,
    ];

    /*$form['text_url_tyc'] = [
    //'#weight' => 25,
    '#type' => 'html_tag',
    '#tag' => 'span',
    '#value' => $this->t("Primero debe crear una opcion de del popup, lo puede crear @link.", ['@link', ['@tac' => render($link)]]),
    '#attributes' => [
    'class' => 'texto',
    ],
    ];*/

    // '#description' => ,.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $category_services_entity = $this->entity;
    $category_services_entity->set('icon', reset($form_state->getValue('icon')));
    $status = $category_services_entity->save();

    $fid = $form_state->getValue('icon');

    // Save file permanently.
    if ($fid) {
      \Drupal::service('tbo_general.tbo_config')->setFileAsPermanent($fid);
    }

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label categoria de servicios.', [
          '%label' => $category_services_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label categoria de servicios.', [
          '%label' => $category_services_entity->label(),
        ]));
    }

    // Delete cache.
    BaseApiCache::delete('entity', 'getCategories', array_merge([], []));
    BaseApiCache::delete('data', 'getCategories', array_merge([], []));

    $form_state->setRedirectUrl($category_services_entity->toUrl('collection'));
  }

}
