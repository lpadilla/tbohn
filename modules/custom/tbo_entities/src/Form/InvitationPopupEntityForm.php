<?php

namespace Drupal\tbo_entities\Form;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class InvitationPopupEntityForm.
 *
 * @package Drupal\tbo_entities\Form
 */
class InvitationPopupEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $invitation_popup_entity = $this->entity;

    $form['#prefix'] = '<div id="container-fields-wrapper">';
    $form['#suffix'] = '</div>';

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $invitation_popup_entity->id(),
      '#disabled' => !$invitation_popup_entity->isNew(),
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this item. It must only contain lowercase letters, numbers, and underscores.'),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del invitación en popup'),
      '#maxlength' => 30,
      '#default_value' => $invitation_popup_entity->label(),
      '#description' => $this->t("Nombre del invitación en popup."),
      '#required' => TRUE,
      '#id' => 'label',
    ];

    $form['#attached']['library'][] = 'image/admin';

    $default_value = '';
    // Show the thumbnail preview.
    if ($invitation_popup_entity->get('icon')) {
      $file = File::load($invitation_popup_entity->get('icon'));
      if ($file) {
        $form['preview'] = [
          '#type' => 'item',
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#uri' => $file->getFileUri(),
        ];
      }
      $default_value = [$invitation_popup_entity->get('icon')];
    }

    $form['icon'] = [
    // You can find a list of available types in the form api.
      '#type' => 'managed_file',
      '#title' => t('Icono de la invitación'),
      '#default_value' => $default_value,
      '#upload_location' => 'public://invitation_popup',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => [$maximum_dimensions = '50x50', $minimum_dimensions = '20x20'],
      ],
      '#description' => $this->t('El icono debe medir entre 20x20 pixeles y 50x50 pixeles, de extension png jpg jpeg'),
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Descripción del popup'),
      '#default_value' => $invitation_popup_entity->get('description'),
      '#required' => TRUE,
    ];

    /**
     * form ajax de botones:
     */
    $form['#tree'] = TRUE;

    $form['container_actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Acciones'),
      '#open' => TRUE,
    ];

    $form['container_actions']['actions'] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Listado de acciones'),
    ];

    /**
     * form ajax de botones:
     */
    $form['#tree'] = TRUE;

    $form['container_actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Acciones'),
      '#open' => TRUE,
    ];

    $form['container_actions']['actions'] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Listado de acciones'),
    ];

    $num_fields = $form_state->get('num_fields');

    if (empty($num_fields)) {
      if (!is_null($invitation_popup_entity->get('actions_popup'))) {
        $actions = $invitation_popup_entity->get('actions_popup');
        $actions = (array) \GuzzleHttp\json_decode($actions);
        $num_fields = count($actions);
      }
      else {
        $num_fields = 1;
      }
      $form_state->set('num_fields', $num_fields);
    }

    for ($i = 0; $i < $num_fields; $i++) {
      $element = "action_" . $i;
      $action = isset($actions[$element]) ? (array) $actions[$element] : [];

      $form['container_actions']['actions'][$element] = [
        '#type' => 'details',
        '#title' => $this->t('@number action', ['@number' => $i + 1]),
        '#open' => TRUE,
        '#description' => 'To send the id ot the current element use the wildcard {id} within the url',
        '#prefix' => '<div id="inner-container-wrapper">',
        '#suffix' => '</div>',
      ];

      $form['container_actions']['actions'][$element]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => isset($action['label']) ? $action['label'] : '',
        '#description' => $this->t("Introduzca un cadena de máximo @n caracteres", ['@n' => 20]),
        '#maxlength' => 20,
        '#size' => 20,
        '#weight' => '0',
        '#states' => [
          'required' => [
            [':input[name="settings[actions][' . $element . '][url]"]' => ['filled' => TRUE]],
            [':input[name="settings[actions][' . $element . '][class]"]' => ['filled' => TRUE]],
          ],
        ],
      ];

      $form['container_actions']['actions'][$element]['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Url'),
        '#default_value' => isset($action['url']) ? $action['url'] : '',
        '#description' => $this->t("Introduzca un cadena de máximo @n caracteres", ['@n' => 128]),
        '#maxlength' => 128,
        '#size' => 128,
        '#weight' => '0',
      ];

      $form['container_actions']['actions'][$element]['target'] = [
        '#type' => 'select',
        '#title' => $this->t('Target'),
        '#default_value' => isset($action['target']) ? $action['target'] : '',
        '#options' => [
          '_blank' => $this->t('Seleccione'),
          '_popup' => $this->t('Cargar en un popup'),
          '_blank' => $this->t('Cargar en una nueva ventana'),
          '_self'  => $this->t('Carga en el mismo marco en el que se hizo clic'),
          // '_parent' => $this->t('Cargar en el conjunto de marcos principal'),
          // '_top' => $this->t('Carga en el cuerpo de la ventana')
        ],
        '#weight' => '0',
      ];

      $form['container_actions']['actions'][$element]['class'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Class'),
        '#default_value' => isset($action['class']) ? $action['class'] : '',
        '#description' => $this->t("Introduzca un cadena de máximo @n caracteres", ['@n' => 30]),
        '#maxlength' => 30,
        '#size' => 30,
        '#weight' => '0',
      ];
    }

    $form['add'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => [[$this, 'addContainerCallback']],
      '#ajax' => [
        'callback' => [$this, 'addFieldSubmit'],
        'wrapper' => 'container-fields-wrapper',
      ],
      '#attributes' => ['data-link-action' => ['Add service to portfolio']],
    ];

    if ($num_fields > 1) {
      $form['remove'] = [
        '#type' => 'submit',
        '#value' => t('Remove last one'),
        '#submit' => [[$this, 'removeContainerCallback']],
        '#ajax' => [
          'callback' => [$this, 'addFieldSubmit'],
          'wrapper' => 'container-fields-wrapper',
        ],
        '#attributes' => ['data-link-action' => ['Delete service to portfolio']],
      ];
    }

    return $form;
  }

  /**
   *
   */
  public function addFieldSubmit(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   *
   */
  public function addContainerCallback(array &$form, FormStateInterface $form_state) {
    $max = $form_state->get('num_fields') + 1;
    $form_state->set('num_fields', $max);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function removeContainerCallback(array &$form, FormStateInterface $form_state) {
    $num_fields = $form_state->get('num_fields');
    if ($num_fields > 1) {
      $max = $num_fields - 1;
      $form_state->set('num_fields', $max);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $invitation_popup_entity = $this->entity;
    $invitation_popup_entity->set('icon', reset($form_state->getValue('icon')));
    $description = $form_state->getValue('description');
    $invitation_popup_entity->set('description', $description['value']);
    $description = $form_state->getValue('container_actions');
    $invitation_popup_entity->set('actions_popup', \GuzzleHttp\json_encode($description['actions']));
    $status = $invitation_popup_entity->save();

    $fid = $form_state->getValue('icon');

    // Save file permanently.
    if ($fid) {
      \Drupal::service('tbo_general.tbo_config')->setFileAsPermanent($fid);
    }

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label invitación en popup.', [
          '%label' => $invitation_popup_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label invitación en popup.', [
          '%label' => $invitation_popup_entity->label(),
        ]));
    }

    // Delete cache.
    BaseApiCache::delete('entity', 'getInvitationsPopup', array_merge([], []));

    $form_state->setRedirectUrl($invitation_popup_entity->toUrl('collection'));
  }

}
