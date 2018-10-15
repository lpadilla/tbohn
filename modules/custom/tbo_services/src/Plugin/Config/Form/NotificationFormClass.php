<?php

namespace Drupal\tbo_services\Plugin\Config\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_services\Form\NotificationForm;
use Drupal\adf_core\Util\UtilFile;

/**
 * Manage config a 'NotificationFormClass' block.
 */
class NotificationFormClass {

  /**
   * The instance to Notification Modal Form.
   *
   * @var
   */
  protected $instance;

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   *
   */
  public function createInstance(NotificationForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_services.notification',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'notification_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state) {
    $config = \Drupal::config('tbo_services.notification');
    $config_modal_initial = $config->get('modal_for_the_initial_verification');
    $config_modal_last_send_email = $config->get('modal_for_the_last_verification');
    $form['#tree'] = TRUE;

    $form['modal_for_the_initial_verification'] = [
      '#type' => 'details',
      '#title' => t("Modal para el primer ingreso del usuario y que su cuenta no se ha verificado"),
    ];

    $form['modal_for_the_initial_verification']['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Imagen'),
      '#default_value' => $config_modal_initial['image'],
      '#description' => t('Imagen del modal, por favor ingrese una imagen de formato PNG, JPEG y preferiblemente minimo de 50x50px o maximo de 400x400px'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg'],
        'file_validate_image_resolution' => ['300x300', '50x50'],
      ],
      '#required' => TRUE,
    ];

    $form['modal_for_the_initial_verification']['informative_text'] = [
      '#type' => 'text_format',
      '#title' => t("Texto informativo"),
      '#format' => 'full_html',
      '#maxlength' => 240,
      '#default_value' => $config_modal_initial['informative_text']['value'],
      '#description' => t('Maximo de caracteres 240'),
    ];

    $form['modal_for_the_initial_verification']['buttons'] = [
      '#type' => 'table',
      '#header' => [
        t("Name"),
        t("Label"),
        t("Mostrar"),
        t("Active"),
        t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
    ];

    $config_buttons = $config_modal_initial['buttons'];
    if ($config_buttons === NULL) {
      $config_buttons = [
        'verify_account' => [],
        'another_moment' => [],
      ];
    }

    // Se ordenan los filtros segun lo establecido en la configuración.
    uasort($config_buttons, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    $name_buttons = [
      'verify_account' => 'Verifique su cuenta',
      'another_moment' => 'En otro momento',
    ];
    foreach ($config_buttons as $key => $data) {
      // TableDrag: Mark the table row as draggable.
      $form['modal_for_the_initial_verification']['buttons'][$key]['#attributes']['class'][] = 'draggable';

      // existing/configured weight.
      $form['modal_for_the_initial_verification']['buttons'][$key]['#weight'] = $config_buttons[$key]['weight'];

      $form['modal_for_the_initial_verification']['buttons'][$key]['name'] = [
        '#plain_text' => $name_buttons[$key],
      ];

      $form['modal_for_the_initial_verification']['buttons'][$key]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $config_buttons[$key]['label'],
      ];

      $form['modal_for_the_initial_verification']['buttons'][$key]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $config_buttons[$key]['show'],
      ];

      $form['modal_for_the_initial_verification']['buttons'][$key]['active'] = [
        '#type' => 'checkbox',
        '#default_value' => $config_buttons[$key]['active'],
      ];

      // TableDrag: Weight column element.
      $form['modal_for_the_initial_verification']['buttons'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $name_buttons[$key]]),
        '#title_display' => 'invisible',
        '#default_value' => $config_buttons[$key]['weight'],
        '#attributes' => ['class' => ['fields-order-weight']],
      ];
    }

    // Configure modal to confirm send mail to verification.
    $form['modal_for_the_last_verification'] = [
      '#type' => 'details',
      '#title' => t("Modal para la confirmacion del envio del correo de verificación"),
    ];

    $form['modal_for_the_last_verification']['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Imagen'),
      '#multiple' => FALSE,
      '#default_value' => $config_modal_last_send_email['image'],
      '#description' => t('Imagen del modal, por favor ingrese una imagen de formato PNG, JPEG y preferiblemente minimo de 50x200px o maximo de 400x400px'),
      '#upload_location' => 'public://',
      '#progress_indicator' => 'bar',
      '#progress_message'   => t('Cargando...'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg'],
        'file_validate_image_resolution' => ['300x300', '50x50'],
      ],
    ];

    $form['modal_for_the_last_verification']['informative_text'] = [
      '#type' => 'text_format',
      '#title' => t("Texto informativo"),
      '#format' => 'full_html',
      '#maxlength' => 305,
      '#default_value' => $config_modal_last_send_email['informative_text']['value'],
      '#description' => t("El token para el correo del usuario es @email_user. Longitud maxima permitida 305 caracteres."),
    ];

    $form['modal_for_the_last_verification']['buttons'] = [
      '#type' => 'table',
      '#header' => [
        t("Name"),
        t("Label"),
        t("Mostrar"),
        t("Active"),
        t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
    ];

    $config_buttons_last_send_email = $config_modal_last_send_email['buttons'];
    if ($config_buttons_last_send_email === NULL) {
      $config_buttons_last_send_email = [
        'accept' => [],
      ];
    }

    // Se ordenan los filtros segun lo establecido en la configuración.
    uasort($config_buttons_last_send_email, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    $name_buttons_last_send_email = [
      'accept' => 'Aceptar',
    ];
    foreach ($config_buttons_last_send_email as $key => $data) {
      // TableDrag: Mark the table row as draggable.
      $form['modal_for_the_last_verification']['buttons'][$key]['#attributes']['class'][] = 'draggable';

      // existing/configured weight.
      $form['modal_for_the_last_verification']['buttons']['#weight'] = $config_buttons_last_send_email['weight'];

      $form['modal_for_the_last_verification']['buttons'][$key]['name'] = [
        '#plain_text' => $name_buttons_last_send_email[$key],
      ];

      $form['modal_for_the_last_verification']['buttons'][$key]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $config_buttons_last_send_email[$key]['label'],
      ];

      $form['modal_for_the_last_verification']['buttons'][$key]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $config_buttons_last_send_email[$key]['show'],
      ];

      $form['modal_for_the_last_verification']['buttons'][$key]['active'] = [
        '#type' => 'checkbox',
        '#default_value' => $config_buttons_last_send_email[$key]['active'],
      ];

      // TableDrag: Weight column element.
      $form['modal_for_the_last_verification']['buttons'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $name_buttons_last_send_email[$key]]),
        '#title_display' => 'invisible',
        '#default_value' => $config_buttons_last_send_email[$key]['weight'],
        '#attributes' => ['class' => ['fields-order-weight']],
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Guardar'),
      '#attributes' => [
        'class' => ['btn-primary'],
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'tbo_general/tools.tbo';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface &$form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state) {
    // Get files.
    $fid_init_verification = $form_state->getValue('modal_for_the_initial_verification')['image'];

    // Save files.
    if ($fid_init_verification) {
      UtilFile::setPermanentFile($fid_init_verification, 'tbo_services');
    }

    $fid_last_verification = $form_state->getValue('modal_for_the_last_verification')['image'];
    if ($fid_last_verification) {
      UtilFile::setPermanentFile($fid_last_verification, 'tbo_services');
    }

    $config = \Drupal::configFactory()->getEditable('tbo_services.notification');
    $config->set('modal_for_the_initial_verification', $form_state->getValue('modal_for_the_initial_verification'));
    $config->set('modal_for_the_last_verification', $form_state->getValue('modal_for_the_last_verification'));
    $config->save();
  }

}
