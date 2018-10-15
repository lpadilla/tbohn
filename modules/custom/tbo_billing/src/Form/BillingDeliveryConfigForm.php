<?php

namespace Drupal\tbo_billing\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class ChangeInvoiceDeliveryForm.
 *
 * @package Drupal\tbo_billing\Form
 */
class BillingDeliveryConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billing_delivery_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = [], $data = []) {

    $form['#theme'] = 'billing_delivery_config_form';

    $account = \Drupal::service('selfcare_core.session');

    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['data'] = [
      '#type' => 'value',
      '#value' => $data['data'],
    ];
    if (isset($params['path_redirect'])) {
      $form['path_redirect'] = [
        '#type' => 'value',
        '#value' => $params['path_redirect'],
      ];
    }
    else {
      $form['path_redirect'] = [
        '#type' => 'value',
        '#value' => '/',
      ];
    }

    $alternateEmail = isset($data['alternateEmail']) ? $data['alternateEmail'] : "";
    $phoneNumber = isset($data['phoneNumber']) ? $data['phoneNumber'] : "";

    $form['alternateEmail'] = [
      '#type' => 'value',
      '#value' => $alternateEmail,
    ];
    $form['phoneNumber'] = [
      '#type' => 'value',
      '#value' => $phoneNumber,
    ];

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['bottom-line-gray'],
      ],
    ];

    // Si por configuracion del bloque esta que los metodos son excluyentes
    // se muestra las opciones de seleccionar metodo como radios
    // si no se muestra las opciones de seleccionar metodo como checknox
    // METHOD EXCLUDED.
    if ($params['exclude_fields'] == 1) {

      $id_container_active = 'exclude';

      $fields = $params[$id_container_active]['fields'];
      $others = $params[$id_container_active]['others'];

      // Si el estado de factura esta activo electronico
      // valor por defecto es factura electronica activada.
      $active_method_default = 'active_printed';
      if ($data['data']['subcriptionStatusId']) {
        $active_method_default = 'active_electronic';
      }

      $form['container']['active_method'] = [
        '#type' => 'radios',
        '#default_value' => $active_method_default,
        '#options' => [
          'active_electronic' => $this->t('Electrónica'),
          'active_printed' => $this->t('Impresa'),
        ],
      ];

      // Si por configuracion del bloque esta deshabilido el formato printed.
      if ($fields['enable_invoice_printed_fields'] != 1) {
        $form['container']['active_method']['active_printed']['#attributes']['disabled'] = 'disabled';
      }

      // Si por configuracion del bloque esta deshabilido el formato electronic.
      if ($fields['enable_invoice_electronic_fields'] != 1) {
        $form['container']['active_method']['active_electronic']['#attributes']['disabled'] = 'disabled';
      }

      $condition_state_electronic_fields = [
        "input[name='active_method']" => ['value' => 'active_electronic'],
      ];
      $condition_state_printed_fields = [
        "input[name='active_method']" => ['value' => 'active_printed'],
      ];

    }
    // METHOD SIMULTANEOUS.
    else {

      $id_container_active = 'simultaneous';
      $fields = $params[$id_container_active]['fields'];

      // Si el estado de factura esta activo electronico
      // valor por defecto es factura electronica activada.
      $active_electronic_default = 0;
      if ($data['electronic_invoice_status'] == 'true') {
        $active_electronic_default = 1;
      }

      // Si el estado de factura esta activo impresa
      // valor por defecto es factura impresa activada.
      $active_printed_default = 0;
      if ($data['printed_invoice_status'] == 'true') {
        $active_printed_default = 1;
      }

      $form['container']['active_electronic'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Electrónica'),
        '#default_value' => $active_electronic_default,
        '#weight' => 1,
      ];
      // Si por configuracion del bloque esta deshabilido el formato
      // electronic.
      if ($fields['enable_invoice_electronic_fields'] != 1) {
        $form['container']['active_electronic']['#disabled'] = TRUE;
      }

      $form['container']['active_printed'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Impresa'),
        '#default_value' => $active_printed_default,
        '#weight' => 20,
      ];

      // Si por configuracion del bloque esta deshabilido el formato
      // printed.
      if ($fields['enable_invoice_printed_fields'] != 1) {
        $form['container']['active_printed']['#disabled'] = TRUE;
      }

      $condition_state_electronic_fields = [
        "input[name='active_electronic']" => ["checked" => TRUE],
      ];
      $condition_state_printed_fields = [
        "input[name='active_printed']" => ["checked" => TRUE],
      ];
    }

    // customerId = numero de identificacion del usuario
    // si no esta en los datos de el fotmato de factura
    // se utiliza el nuemro de identificacion guardado en el usuario actual
    // este dato tambien se puede actualizar automaticamente en el formulario de
    // pagar factura home.
    $form['container_user_data'] = [
      '#type' => 'container',
    ];

    $user_document_number = \Drupal::service('user.data')->get('home', $account->getCurrentUser()->id(), 'user_document_number');

    $customerId = isset($data['customerId']) ? $data['customerId'] : $user_document_number;

    $docType_ids = entity_load_multiple('document_type_entity');
    foreach ($docType_ids as $key => $entity) {
      $list[$key] = strtoupper($key);
    }

    $form['document_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Documento'),
      '#options' => $list,
      '#default_value' => \Drupal::service('user.data')->get('home', $account->getCurrentUser()->id(), 'user_document_type') ?: '',
      '#required' => TRUE,
      // '#weight' => 40,.
    ];
    $form['document_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Número'),
      '#default_value' => $customerId,
      '#required' => TRUE,
      // '#weight' => 40,.
    ];

    // kint($data);die;
    // Contenedor de los campos de factura electronica.
    $form['container']['active_electronic_fields'] = [
      '#type' => 'container',
      '#states' => [
        "visible" => $condition_state_electronic_fields,
      ],
      '#attributes' => [
        'class' => ['active-fields'],
      ],
      '#weight' => 2,
    ];

    // Por defecto se muestra el correo que esta guardado en la cuenta.
    $form['container']['active_electronic_fields']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo electrónico'),
      '#default_value' => isset($data['data']['email']) ? $data['data']['email'] : NULL,
      '#suffix' => isset($fields['others']['extra_electronic']['value'])
      ? '<p>' . $fields['others']['extra_electronic']['value'] . '</p>' : NULL,
      '#required' => TRUE,
    ];

    /*$form['container']['active_electronic_fields']['email'] = [
    '#theme' => 'field_value_with_label',
    '#label' => $this->t('Correo electrónico'),
    '#value' => $data->getEmail()
    ];*/

    // Si por configuracion del bloque esta que los metodos son excluyentes.
    if ($params['exclude_fields'] == 1) {
      $form['container']['active_electronic_extras'] = [
        '#type' => 'container',
        '#states' => [
          "visible" => $condition_state_electronic_fields,
        ],
        '#weight' => 49,
      ];

      $form['container']['active_electronic_extras']['description_electronic_text'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Al activar tu factura electrónica no volveras a recibirla impresa. Puedes cambiarte a formato impreso desde esta página en cualquier momento.'),
      ];
    }

    // Contenedor de los campos de factura impresa.
    $form['container']['active_printed_fields'] = [
      '#type' => 'container',
      '#states' => [
        "visible" => $condition_state_printed_fields,
      ],
      '#attributes' => [
        'class' => ['active-fields'],
      ],
      '#weight' => 30,
    ];

    // Por defecto se muestra la direccion que esta guardada en la cuenta.
    $form['container']['active_printed_fields']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dirección'),
      '#maxlength' => 120,
      '#size' => 60,
      '#default_value' => isset($data['data']['address']) ? $data['data']['address'] : NULL,
    ];

    $form['container_submit'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container_submit'],
      ],
    ];

    if (!is_null($others['extra_electronic']['url_tyc_electronic_text'])) {
      $tc_modal = \Drupal::service('tc_modal.modal');
      $link = $tc_modal->tc_modal_add($this->t('términos y condiciones'), $others['extra_electronic']['url_tyc_electronic_text']);

      $form['active_electronic_extras']['text_url_tyc'] = [
        // '#weight' => 25,.
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $this->t('Al presionar OK estas aceptado los @tac.', ['@tac' => render($link)]),
        '#attributes' => [
          'class' => 'texto',
        ],
      ];
    }

    if (isset($params['path_redirect'])) {
      $options = ['absolute' => TRUE, 'attributes' => ['data-link-action' => ['Cancel change invoice delivery home']]];
      $url = Url::fromUri('internal:' . $params['path_redirect'], $options);

      $form['container_submit']['cancel'] = [
        '#markup' => \Drupal::l($this->t('Cancel'), $url),
        '#weight' => 50,
        '#attributes' => ['data-link-action' => ['Cancel change invoice delivery']],
      ];
    }

    $form['container_submit']['submit'] = [
      '#type' => 'submit',
      '#value' => t('OK'),
      '#weight' => 50,
      '#suffix' => '</div>',
      '#attributes' => ['data-link-action' => ['Change invoice delivery']],
    ];

    $form['#attached']['library'] = 'home/invoiceChange';

    return $form;
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
    // Display result.
    $values = $form_state->getValues();
    $service = \Drupal::service('home.account.invoice');
    $subscriptionStatusId = $values['data']['subcriptionStatusId'];

    $clientIp = \Drupal::request()->getClientIp();

    $registeringAppName = "CanalesAlternos";
    $customerIpAddress = $clientIp;
    $customerId = $values['document_number'];
    $billingId = "1";
    $email = $values['email'];
    $alternateEmail = $values['alternateEmail'];
    $phoneNumber = $values['phoneNumber'];
    $unSubscribeReasonId = "2";

    // Save values of document number in the current user.
    $session = \Drupal::service('selfcare_core.session');
    \Drupal::service('user.data')->set('home', $session->getCurrentUser()->id(), 'user_document_number', $customerId);
    \Drupal::service('user.data')->set('home', $session->getCurrentUser()->id(), 'user_document_type', $values['document_type']);

    $account = \Drupal::service('home.account');

    // Save log accept t&c.
    $message = 'El usuario @user acepto los terminos y condiciones de cambiar formato de factura para la cuenta @account. <p>
        - ID del cliente : @currentId<br>
        - IP del cliente : @clientIp<br>
        - Fecha y hora de aceptacion : ' . date("Y-m-d H:i") . '<br>
        </p>';

    $binds = ['@user' => $session->getCurrentUser()->getAccountName(), '@account' => $account->id, '@currentId' => $customerId, '@clientIp' => $clientIp];

    \Drupal::logger('home_change_invoice_delivery_form')->error((string) $message, $binds);

    try {
      // Si el metodo es formatos simultaneos.
      if (isset($values['active_electronic'])) {
        // Si se selecciono electronica.
        if ($values['active_electronic'] == 1) {
          // Si ya esta subscrito en electronica se actualiza los datos de la subscripcion.
          if ($subscriptionStatusId == 1) {
            // kint("update subscription");.
            $response = $service->homeInvoiceStatusUpdatePaperless($registeringAppName, $customerIpAddress, $customerId, $billingId, $email, $alternateEmail, $phoneNumber);
          }
          // Si no esta subscrito en electronica se crea la subscripcion.
          elseif ($subscriptionStatusId == 0) {
            // kint("create subscription");.
            $response = $service->homeInvoiceStatusCreatePaperless($registeringAppName, $customerIpAddress, $customerId, $billingId, $email, $alternateEmail, $phoneNumber);
          }
        }
        // Si se selecciono impresa se elimina la subscripcion.
        elseif ($values['active_printed'] == 1) {
          // kint("delete subscription");.
          $response = $service->homeInvoiceStatusDeletePaperless($registeringAppName, $customerIpAddress, $customerId, $unSubscribeReasonId);
        }
      }
      // Si el metodo es formatos excluyentes y se selecciono electronica.
      elseif (isset($values['active_method']) && $values['active_method'] == 'active_electronic') {
        // Si ya esta subscrito en electronica se actualiza los datos de la subscripcion.
        if ($subscriptionStatusId == 1) {
          // kint("update subscription");.
          $response = $service->homeInvoiceStatusUpdatePaperless($registeringAppName, $customerIpAddress, $customerId, $billingId, $email, $alternateEmail, $phoneNumber);
        }
        // Si no esta subscrito en electronica se crea la subscripcion.
        elseif ($subscriptionStatusId == 0) {
          // kint("create subscription");.
          $response = $service->homeInvoiceStatusCreatePaperless($registeringAppName, $customerIpAddress, $customerId, $billingId, $email, $alternateEmail, $phoneNumber);
        }
      }
      // Si el metodo es excluyente y se selecciono impresa se elimina la subscripcion.
      else {
        // kint("delete subscription");.
        $response = $service->homeInvoiceStatusDeletePaperless($registeringAppName, $customerIpAddress, $customerId, $unSubscribeReasonId);
      }

    }
    catch (\Exception $e) {
      \Drupal::logger('Error realizando la transaccion' . $e);
      drupal_set_message($this->t('En este momento no podemos procesar tu transaccion, por favor intenta de nuevo.'), 'error');
      return [];
    }

    if (!empty($response)) {
      drupal_set_message($this->t('Hemos recibido tu solicitud, pronto recibira un mensaje de confirmación.'));

      $url = Url::fromUri('internal:' . $values['path_redirect']);
      $form_state->setRedirectUrl($url);

    }
  }

}
