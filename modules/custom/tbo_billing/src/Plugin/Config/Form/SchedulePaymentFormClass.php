<?php

namespace Drupal\tbo_billing\Plugin\Config\form;

use Drupal\user\Entity\User;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_billing\Services\PaymentDomiciliationService;
use Drupal\tbo_core\Services\AuditLogService;
use Drupal\tbo_mail\SendMessage;

/**
 * Manage config a 'SchedulePaymentForm' form.
 */
class SchedulePaymentFormClass {

  protected $api;
  protected $service_message;
  protected $domiciliationService;
  protected $log;

  /**
   * SchedulePaymentFormClass constructor.
   *
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   * @param \Drupal\tbo_billing\Services\PaymentDomiciliationService $domiciliationService
   * @param \Drupal\tbo_core\Services\AuditLogService $log
   * @param \Drupal\tbo_mail\SendMessage $message
   */
  public function __construct(TboApiClientInterface $api, PaymentDomiciliationService $domiciliationService, AuditLogService $log, SendMessage $message) {
    $this->api = $api;
    $this->service_message = $message;
    $this->domiciliationService = $domiciliationService;
    $this->log = $log;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'schedule_payment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $data = []) {
    $form['#attached']['library'][] = 'core/drupal.ajax';

    $form['data'] = [
      '#type' => 'value',
      '#value' => $data,
    ];

    $options = $this->domiciliationService->getOptionsCards($data);

    if ($options) {
      $default_value = $this->domiciliationService->getDefaultOptionCards($options);
      $form['cards'] = [
        '#type' => 'radios',
        '#required' => TRUE,
        '#options' => $options,
        '#default_value' => $default_value,
      ];
    }
    else {
      $form['empty']['#markup'] = '<p class="no-tc">' . t('En este momento no cuenta con tarjetas de crédito configuradas.') . '</p>';
    }

    $form['button-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-wrapper-button', 'input-field', 'clearfix', 'col', 's12'],
      ],
    ];

    if ($options) {
      $form['button-wrapper']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Guardar'),
        '#attributes' => [
          'class' => ['btn', 'btn-primary', 'right'],
        ],
      ];
    }

    // CANCEL link.
    $form['button-wrapper']['closet'] = [
      '#markup' => '<a href="#" data-ng-click="schedulePaymentClear()" class="modal-action modal-close create-account waves-effect waves-light btn btn-second right">Cancelar</a>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save Audit log.
    $this->log->loadName();
    $name = $this->log->getName();

    $uid = \Drupal::currentUser()->id();

    $type_service = $_SESSION['environment'];
    // 600006858393.
    $contractId = $_SESSION['sendDetail']['contractId'];

    // Get values formstate.
    $mail = \Drupal::currentUser()->getEmail();
    $document_number = $_SESSION['sendDetail']['docNumber'];
    $document_type = $_SESSION['company']['docType'];
    $enterprise_name = $_SESSION['company']['name'];

    $values = $form_state->getValues();

    $cardToken = $values['cards'];
    $haveRecurringPayment = $_SESSION['recurring_info_payment'];

    $default_value = FALSE;
    if ($haveRecurringPayment) {
      $options = $form['cards']['#options'];
      $default_value = $this->domiciliationService->getDefaultOptionCards($options, FALSE, FALSE);
    }

    // Si el cuando se envia el card se descubre que el token conincide con el
    // card de pago recurrente no hacemos nada.
    if ($default_value && $default_value === $cardToken && $default_value != 'none') {
      drupal_set_message(t('No se han realizados cambios en sus pagos automáticos.'), 'error');
      return [];
    }

    $customerIpAddress = \Drupal::request()->getClientIp();
    $method_delete_recurring = 'deleteRecurringBillingInfo';
    $method_delete_recurring_cache = 'recurringInfoByContractId';
    $method_create_recurrent = 'createRecurringInfoByContractId';
    $method_delete_get_card = 'getCardToken';
    // Delete recurrent payment.
    if ($haveRecurringPayment && $values['cards'] == 'none') {
      try {
        $this->saveAuditLog('Usuario solicita desprogramar pago.', 'Usuario ' . $name . ' solicita desprogramar pago.');

        $transactionId = $this->domiciliationService->getTransactionId();

        // Delete program payment
        // Parameters for service.
        if ($_SESSION['environment'] == 'fijo') {
          $params['query'] = [
            'limit' => 4,
          ];

          $params['tokens'] = [
            'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
          ];

          $params_body = [
            'cardToken' => $default_value,
            'customerId' => isset($_SESSION['sendDetail']['docNumber']) ? $_SESSION['sendDetail']['docNumber'] : '',
            'documentType' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
            'transactionId' => $transactionId,
            'customerIpAddress' => $customerIpAddress,
          ];

          $params['body'] = json_encode($params_body);
          $params['no_exception'] = TRUE;
        }
        elseif ($_SESSION['environment'] == 'movil') {
          $params = [
            'headers' => [
              'Content-Type' => 'application/json',
              'transactionId' => substr(md5($this->domiciliationService->getTransactionId()), 0, 16),
              'platformId' => 12347,
            ],
            'tokens' => [
              'clientId' => isset($_SESSION['sendDetail']['docNumber']) ? $_SESSION['sendDetail']['docNumber'] : '',
              'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
            ],
          ];
          $method_delete_recurring = 'deleteBillingAccount';
          $method_delete_recurring_cache = 'getBillingAccount';
        }

        // Delete RecurringBillingInfo.
        try {
          $delete_response = $this->api->$method_delete_recurring($params);
        }
        catch (\Exception $e) {
          $mensaje = UtilMessage::getMessage($e);
          // Return message in rest.
          drupal_set_message($mensaje['message'], 'error');
          // $requestUrl = \Drupal::request()->server->get('HTTP_REFERER');
          // return new ResourceResponse($mensaje['message']);
          // return new RedirectResponse($requestUrl);
        }

        if ($delete_response) {
          // Delete cache service recurringInfoByContractId.
          $params = [
            'tokens' => [
              'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
            ],
            'query' => [
              'limit' => 4,
            ],
          ];

          if ($method_delete_recurring_cache == 'getBillingAccount') {
            $params = [
              'tokens' => [
                'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
              ],
              'query' => [],
            ];
          }

          BaseApiCache::delete('service', $method_delete_recurring_cache, array_merge($params['tokens'], $params['query']));

          if ($_SESSION['environment'] == 'fijo') {
            // Remove cache getCardToken
            // params $category, $key, $arguments
            // Parameters for service getCardToken.
            $params = [
              'tokens' => [
                'docType' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
                'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
              ],
              'query' => [],
            ];
            BaseApiCache::delete('service', 'getCardToken', array_merge($params['tokens'], $params['query']));
          }

          // $data_card = $this->domiciliationService->getDataCardToken($default_value);
          // $cardInfo = $data_card['cardInfo'];
          // $showCardInfo = $this->domiciliationService->getMaskedCardNumber($cardInfo);
          $cardBrand = substr($haveRecurringPayment->cardBrand, -4);
          $cardInfo = $haveRecurringPayment->cardInfo;
          $showCardInfo = '**** **** **** ' . $cardInfo;
          /**
           * definiendo textos para el log
           */
          $this->saveAuditLog('Usuario desprogramo pago automático para sus servicios  ' . $type_service . '.', 'Usuario ' . $name . ' desprogramo pago para las facturas del contrato ' . $contractId . ' con la tarjeta ' . $cardBrand . ' ' . $showCardInfo);

          /**
           * envio de correo de notificacion de desprogramacion de pago programado
           */
          $this->domiciliationService->sendEmail($this->service_message, 'remove_schedule_payment', $name, $mail, $uid, $enterprise_name, $document_number, $document_type, $contractId, $showCardInfo, $cardBrand);

          /**
           * mensaje que vera el usuario en pantalla
           */
          drupal_set_message(t('Proceso exitoso. <br /> Se ha desprogramado correctamente el pago automático de las facturas asociadas al número de contrato @contractId con la tarjeta de crédito @cardBrand @cardInfo.', ['@contractId' => $contractId, '@cardInfo' => $showCardInfo, '@cardBrand' => $cardBrand]));
        }
        else {
          drupal_set_message(t('Ha ocurrido un error en el proceso. Por favor intente más tarde.'), 'error');
          // Send segment track
          $this->sendSegmentTrack('Desprogramar', 'Fallido');
          return [];
        }
      }
      catch (\Exception $e) {
        $this->saveAuditLog('Usuario no desprogramo pago automático.', 'Usuario ' . $name . ' no pudo desprogramar pago automático. El error retornado por el servicio web a consumir fue ' . $e->getMessage());
        drupal_set_message(t('Ha ocurrido un error en el proceso. Por favor intente más tarde.'), 'error');

        // Send segment track
        $this->sendSegmentTrack('Desprogramar', 'Fallido');
        return [];
      }
      // Send segment track
      $this->sendSegmentTrack('Desprogramar', 'Exitoso');
      return [];
    }

    // Si el contrato ya cuenta con pago recurrente programado entonces primero
    // lo eliminamos antes de agregar el nuevo y como no coincide con la tarjeta
    // que ya esta configurada para recurremte, dejamos que el servicio por
    // defecto la elimine.
    if ($haveRecurringPayment && $default_value) {
      if ($_SESSION['environment'] == 'fijo') {
        $transactionId = $this->domiciliationService->getTransactionId();
        // Delete program payment
        // Parameters for service.
        $params['query'] = [
          'limit' => 4,
        ];

        $params['tokens'] = [
          'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
        ];

        $params_body = [
          'cardToken' => $default_value,
          'customerId' => isset($_SESSION['sendDetail']['docNumber']) ? $_SESSION['sendDetail']['docNumber'] : '',
          'documentType' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
          'transactionId' => $transactionId,
          'customerIpAddress' => $customerIpAddress,
        ];

        $params['body'] = json_encode($params_body);
        $params['no_exception'] = TRUE;
      }
      elseif ($_SESSION['environment'] == 'movil') {
        $params = [
          'headers' => [
            'Content-Type' => 'application/json',
            'transactionId' => substr(md5($this->domiciliationService->getTransactionId()), 0, 16),
            'platformId' => 12347,
          ],
          'tokens' => [
            'clientId' => isset($_SESSION['sendDetail']['docNumber']) ? $_SESSION['sendDetail']['docNumber'] : '',
            'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
          ],
        ];
        $method_delete_recurring = 'deleteBillingAccount';
      }

      // Delete RecurringBillingInfo.
      try {
        $response = $this->api->$method_delete_recurring($params);
      }
      catch (\Exception $e) {
        $mensaje = UtilMessage::getMessage($e);
        // Return message in rest.
        drupal_set_message($mensaje['message'], 'error');
        // Send segment track
        $this->sendSegmentTrack('Programar', 'Fallido');
        return [];
      }
    }

    // Agregamos el pago recurrente
    // UserInfo.
    try {
      // Values user.
      $user2 = User::load(\Drupal::currentUser()->id());
      $user = \Drupal::currentUser();
      $lastName = "";
      $user = $user->getEmail();
      $phoneNumber = $user2->phone_number->value;
      $billingId = $_SESSION['company']['id'];
      $billingName = $_SESSION['company']['name'];
    }
    catch (\Exception $e) {
      drupal_set_message(t('Ha ocurrido un error en el proceso. Por favor intente más tarde.'), 'error');
      // Send segment track
      $this->sendSegmentTrack('Programar', 'Fallido');
      return [];
    }

    // CardInfo
    // si el valor de cards es 0 y hemos creado el nuevo card
    // entonces completamos los datos de card info y card brand con los datos
    // enviados en el formulario
    // si no se consulta estos datos con el card token que se selecciono
    // esto se hace ya que al consultar el listado de card tokens no aparece el
    // token del card que se creo, aunque la creacion si fue exitosa.
    try {
      $data_card = $this->domiciliationService->getDataCardToken($cardToken);
      $cardInfo = $data_card['cardInfo'];
      $showCardInfo = $this->domiciliationService->getMaskedCardNumber($cardInfo);
      $cardBrand = $data_card['cardBrand'];
      $prefix = $this->domiciliationService->getPrefixCardBrand($cardBrand);
      $cardBrand = $prefix . '-' . $cardBrand;
      // 1-Ahorros, 2-Corriente, 3-TC.
      $paymentMethod = "3";
    }
    catch (\Exception $e) {
      drupal_set_message(t('Ha ocurrido un error en el proceso. Por favor intente más tarde.'), 'error');
      // Send segment track
      $this->sendSegmentTrack('Programar', 'Fallido');
      return [];
    }

    try {
      $this->saveAuditLog('Usuario solicita programar pago.', 'Usuario ' . $name . ' solicita programar pago.');

      $transactionId = $this->domiciliationService->getTransactionId();
      // 30401246.
      $customerId = isset($_SESSION['sendDetail']['docNumber']) ? $_SESSION['sendDetail']['docNumber'] : '';
      // 600006858393.
      $contractId = isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '';
      // CC.
      $documentType = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';

      if ($_SESSION['environment'] == 'fijo') {
        $jsonBody = [
          "recurringBillingInfo" => [
            "billingId" => (string) $billingId,
            "billingName" => $billingName,
            "cardInfo" => $cardInfo,
            "cardBrand" => $cardBrand,
            "paymentMethod" => $paymentMethod,
            "phoneNumber" => "",
            "customerIpAddress" => $customerIpAddress,
          ],
          "customerInfo" => [
            "customerId" => $customerId,
            "documentType" => $documentType,
            "name" => $name,
            "lastName" => $lastName,
            "email" => $user,
            "phoneNumber" => $phoneNumber,
          ],
          "transactionId" => $transactionId,
          "cardToken" => $cardToken,
        ];

        $jsonBody = json_encode($jsonBody);

        $params = [
          'query' => [
            'limit' => 4,
          ],
          'tokens' => [
            'contractId' => $contractId,
          ],
          'body' => $jsonBody,
        ];
      }
      elseif ($_SESSION['environment'] == 'movil') {
        $data_send = $_SESSION['sendDetail'];
        $params = [
          'buyer' => [
            'name' => $name,
            'email' => $user,
            'contactPhone' => $phoneNumber,
            'dni' => $customerId,
            'dniType' => strtoupper($documentType),
            'street1' => $data_send['address'],
            'street2' => $data_send['address'],
            'postalCode' => $data_send['zipcode'],
            'city' => $data_send['city'],
            'state' => $data_send['state'],
            'country' => $data_send['country'],
            'phone' => $phoneNumber,
          ],
          'correlationId' => $cardToken,
          'account' => $contractId,
          'msisdn' => '',
          'segment' => 1,
          'daysForCollectionInvoice' => 1,
        ];

        $jsonBody = json_encode($params);

        $params = [
          'tokens' => [
            'clientId' => $customerId,
          ],
          'headers' => [
            'Content-Type' => 'application/json',
            'transactionId' => substr(md5($this->domiciliationService->getTransactionId()), 0, 16),
            'platformId' => 12347,
          ],
          'body' => $jsonBody,
        ];

        $method_create_recurrent = 'addBillingsAccount';
        $method_delete_get_card = 'getCreditsCardByIdentification';
        $method_delete_recurring_cache = 'getBillingAccount';
      }

      // Save recurrent payment.
      $save_recurrent = $this->api->$method_create_recurrent($params);

      /**
       * definiendo textos para el log
       */
      $this->saveAuditLog('Usuario programo pago de servicios ' . $type_service . '.', 'Usuario ' . $name . ' programo pago para las facturas del contrato ' . $contractId . ' con la tarjeta de crédito ' . $data_card['cardBrand'] . ' ' . $showCardInfo);

      /**
       * envio de correo de notificacion de programacion de pago programado
       */
      $this->domiciliationService->sendEmail($this->service_message, 'schedule_payment', $name, $mail, $uid, $enterprise_name, $document_number, $document_type, $contractId, $showCardInfo, $data_card['cardBrand']);

      /**
       * mensaje que vera el usuario en pantalla
       */
      drupal_set_message(t('Proceso exitoso. <br /> Se ha programado correctamente el pago automático de las facturas asociadas al número de contrato  @contractId con la tarjeta de crédito @cardBrand @cardInfo',
        ['@contractId' => $contractId, '@cardInfo' => $showCardInfo, '@cardBrand' => $data_card['cardBrand']]));

      // Delete cache service recurringInfoByContractId.
      $params = [
        'tokens' => [
          'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
        ],
        'query' => [
          'limit' => 4,
        ],
      ];

      if ($method_delete_recurring_cache == 'getBillingAccount') {
        $params = [
          'tokens' => [
            'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
          ],
          'query' => [],
        ];
      }

      BaseApiCache::delete('service', $method_delete_recurring_cache, array_merge($params['tokens'], $params['query']));

      // Remove cache getCardToken
      // params $category, $key, $arguments
      // Parameters for service getCardToken.
      $params = [
        'tokens' => [
          'docType' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
          'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        ],
        'query' => [],
      ];

      if ($method_delete_get_card == 'getCreditsCardByIdentification') {
        $params['tokens'] = [
          'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        ];
      }

      BaseApiCache::delete('service', $method_delete_get_card, array_merge($params['tokens'], $params['query']));

      // Send segment track
      $this->sendSegmentTrack('Programar', 'Exitoso');
    }
    catch (\Exception $e) {
      $this->saveAuditLog('Usuario no programo pago automático.', 'Usuario ' . $name . ' no pudo programar pago automatico. El error retornado por el servicio web a consumir fue ' . $e->getCode() . ' y descripción ' . $e->getMessage());

      $mensaje = UtilMessage::getMessage($e);
      // Return message in rest.
      drupal_set_message($mensaje['message'], 'error');

      // Add segment
      $this->sendSegmentTrack('Programar', 'Fallido');
      return [];
    }

    return [];
  }

  /**
   * Guardado log auditoria.
   */
  public function saveAuditLog($description, $details) {
    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => t('Facturación'),
      'description' => $description,
      'details' => $details,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $this->log->insertGenericLog($data);
  }

  /**
   * @param $event
   * @param $status
   */
  public function sendSegmentTrack($event, $status) {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());
    try {
      if (isset($tigoId)) {
        $service_segment = \Drupal::service('adf_segment');
        $service_segment->segmentPhpInit();
        $segment = $service_segment->getSegmentPhp();
        $segment_track = [
          'event' => 'TBO - ' . $event . ' pago - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Pago automático',
            'label' => $_SESSION['environment'] . ' - ' . $status,
            'site' => 'NEW',
          ],
        ];

        $segment->track($segment_track);
      }
    }
    catch (\Exception $e) {
      // Add action.
    }

  }

}
