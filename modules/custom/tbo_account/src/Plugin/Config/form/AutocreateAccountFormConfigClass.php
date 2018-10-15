<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class AutocreateAccountFormConfigClass.
 *
 * @package Drupal\tbo_account\Plugin\Config\form
 */
class AutocreateAccountFormConfigClass {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_account.autocreateaccountformconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'autocreate_account_form_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('tbo_account.autocreateaccountformconfig');

    $form['#tree'] = TRUE;
    $form['tabs'] = [
      '#type' => 'vertical_tabs',
    ];

    // Form config.
    $group = 'form_config';

    $form[$group] = [
      '#type' => 'details',
      '#title' => t('Configuración del formulario'),
      '#group' => 'tabs',
    ];

    // Title.
    $field = 'title';
    $titleLabel = $config->get($group)[$field]['label'];

    if ($titleLabel == NULL) {
      $titleLabel = 'Cree su cuenta';
    }

    $form[$group][$field] = [
      '#type' => 'details',
      '#title' => t('Title'),
      '#description' => t('Título del formulario.'),
    ];
    $form[$group][$field]['label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => $titleLabel,
    ];

    // Text help.
    $field = 'help';
    $helpLabel = $config->get($group)[$field]['label'];
    $helpText = $config->get($group)[$field]['text'];

    if ($helpLabel == NULL) {
      $helpLabel = 'Recuerde';
    }
    if ($helpText == NULL) {
      $helpText = 'Deberá tener a la mano una factura de servicios de los últimos 3 meses con el número de contrato y referencia de pago.';
    }

    $form[$group][$field] = [
      '#type' => 'details',
      '#title' => t('Texto de ayuda'),
      '#description' => t('Texto de ayuda que se presentara en el encabezado del formulario.'),
    ];
    $form[$group][$field]['label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#maxlength' => 20,
      '#default_value' => $helpLabel,
      '#description' => t('Cantidad maxima 20 caracteres.'),
    ];
    $form[$group][$field]['text'] = [
      '#type' => 'textfield',
      '#title' => t('Texto'),
      '#maxlength' => 120,
      '#description' => t('Cantidad maxima 150 caracteres.'),
      '#default_value' => $helpText,
    ];

    // Document.
    $field = 'document';
    $documentPlaceholder = $config->get($group)[$field]['placeholder'];
    $documentPlaceholderNit = $config->get($group)[$field]['placeholderNit'];
    $documentDescription = $config->get($group)[$field]['description'];

    if ($documentPlaceholder == NULL) {
      $documentPlaceholder = 'Ingrese el número de documento.';
    }
    if ($documentPlaceholderNit == NULL) {
      $documentPlaceholderNit = 'Ingrese el número de documento sin el digito de verificación.';
    }
    if ($documentDescription == NULL) {
      $documentDescription = 'Ingresar sin signos de puntuación.';
    }

    $form[$group][$field] = [
      '#type' => 'details',
      '#title' => t('Tipo de documento'),
      '#description' => t('Configuración de ayudas en campos relacionados al tipo de documento.'),
    ];
    $form[$group][$field]['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#maxlength' => 30,
      '#default_value' => t($documentPlaceholder),
      '#description' => t('Cantidad maxima 30 caracteres.'),
    ];
    $form[$group][$field]['placeholderNit'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder NIT'),
      '#maxlength' => 40,
      '#default_value' => t($documentPlaceholderNit),
      '#description' => t('Cantidad maxima 40 caracteres.'),
    ];
    $form[$group][$field]['description'] = [
      '#type' => 'textfield',
      '#title' => t('Descripción'),
      '#default_value' => t($documentDescription),
    ];

    // Type of service.
    $field = 'service';
    $serviceHelp = $config->get($group)[$field]['help'];
    $serviceMobilePlaceholder = $config->get($group)[$field]['mobile']['placeholder'];
    $serviceMobileDescription = $config->get($group)[$field]['mobile']['description'];
    $serviceMobileLink = $config->get($group)[$field]['mobile']['link'];
    $serviceMobileNode = $config->get($group)[$field]['mobile']['node'];
    $serviceMobileModal = $config->get($group)[$field]['mobile']['modal'];
    $serviceMobileTarget = $config->get($group)[$field]['mobile']['target'];
    $serviceFixedPlaceholder = $config->get($group)[$field]['fixed']['placeholder'];
    $serviceFixedDescription = $config->get($group)[$field]['fixed']['description'];
    $serviceFixedLink = $config->get($group)[$field]['fixed']['link'];
    $serviceFixedNode = $config->get($group)[$field]['fixed']['node'];
    $serviceFixedModal = $config->get($group)[$field]['fixed']['modal'];
    $serviceFixedTarget = $config->get($group)[$field]['fixed']['target'];

    if ($serviceHelp == NULL) {
      $serviceHelp = 'Seleccione el tipo de servicio con el que desea registrarse.';
    }
    if ($serviceMobilePlaceholder == NULL) {
      $serviceMobilePlaceholder = 'Ingrese el referente de pago.';
    }
    if ($serviceMobileDescription == NULL) {
      $serviceMobileDescription = '¿Dónde encontrar el referente de pago?';
    }
    if ($serviceMobileModal == NULL) {
      $serviceMobileModal = 0;
    }
    if ($serviceMobileTarget == NULL) {
      $serviceMobileTarget = '_blank';
    }
    if ($serviceFixedPlaceholder == NULL) {
      $serviceFixedPlaceholder = 'Ingrese número de factura.';
    }
    if ($serviceFixedDescription == NULL) {
      $serviceFixedDescription = '¿Dónde encontrar el número de factura?';
    }
    if ($serviceFixedModal == NULL) {
      $serviceFixedModal = 0;
    }
    if ($serviceFixedTarget == NULL) {
      $serviceFixedTarget = '_blank';
    }

    $form[$group][$field] = [
      '#type' => 'details',
      '#title' => t('Tipo de servicio'),
      '#description' => t('Configuración de ayudas en campos relacionados al tipo de servicio.'),
    ];
    $form[$group][$field]['help'] = [
      '#type' => 'textfield',
      '#title' => t('Ayuda'),
      '#maxlength' => 60,
      '#default_value' => t($serviceHelp),
      '#description' => t('Cantidad maxima 60 caracteres.'),
    ];
    $form[$group][$field]['mobile'] = [
      '#type' => 'details',
      '#title' => t('Servicio móvil'),
      '#description' => t('Configuración de ayudas en campos relacionados al tipo de servicio móvil.'),
    ];
    $form[$group][$field]['mobile']['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#maxlength' => 30,
      '#default_value' => t($serviceMobilePlaceholder),
      '#description' => t('Cantidad maxima 30 caracteres.'),
    ];
    $form[$group][$field]['mobile']['description'] = [
      '#type' => 'textfield',
      '#title' => t('Descripción'),
      '#default_value' => t($serviceMobileDescription),
    ];
    $form[$group][$field]['mobile']['link'] = [
      '#type' => 'textfield',
      '#title' => t('Enlace'),
      '#description' => t('Link a información de ayuda.'),
      '#default_value' => $serviceMobileLink,
    ];
    $form[$group][$field]['mobile']['node'] = [
      '#type' => 'number',
      '#title' => t('Nodo'),
      '#description' => t('Nodo con información de ayuda.'),
      '#default_value' => $serviceMobileNode,
    ];
    $form[$group][$field]['mobile']['modal'] = [
      '#type' => 'checkbox',
      '#title' => t('Abrir ayuda modal'),
      '#default_value' => $serviceMobileModal,
    ];
    $form[$group][$field]['mobile']['target'] = [
      '#type' => 'select',
      '#options' => [
        '_blank' => t('Nueva'),
        '_parent' => t('Actual'),
      ],
      '#default_value' => $serviceMobileTarget,
    ];
    $form[$group][$field]['fixed'] = [
      '#type' => 'details',
      '#title' => t('Servicio fijo'),
      '#description' => t('Configuración de ayudas en campos relacionados al tipo de servicio fijo.'),
    ];
    $form[$group][$field]['fixed']['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#maxlength' => 30,
      '#default_value' => t($serviceFixedPlaceholder),
      '#description' => t('Cantidad maxima 30 caracteres.'),
    ];
    $form[$group][$field]['fixed']['description'] = [
      '#type' => 'textfield',
      '#title' => t('Descripción'),
      '#default_value' => t($serviceFixedDescription),
    ];
    $form[$group][$field]['fixed']['link'] = [
      '#type' => 'textfield',
      '#title' => t('Enlace'),
      '#description' => t('Link a información de ayuda.'),
      '#default_value' => $serviceFixedLink,
    ];
    $form[$group][$field]['fixed']['node'] = [
      '#type' => 'number',
      '#title' => t('Nodo'),
      '#description' => t('Nodo con información de ayuda.'),
      '#default_value' => $serviceFixedNode,
    ];
    $form[$group][$field]['fixed']['modal'] = [
      '#type' => 'checkbox',
      '#title' => t('Abrir ayuda modal'),
      '#default_value' => $serviceFixedModal,
    ];
    $form[$group][$field]['fixed']['target'] = [
      '#type' => 'select',
      '#options' => [
        '_blank' => t('Nueva'),
        '_parent' => t('Actual'),
      ],
      '#default_value' => $serviceFixedTarget,
    ];

    // Terms.
    $field = 'terms';
    $termsText = $config->get($group)[$field]['text'];
    $termsTextLink = $config->get($group)[$field]['textLink'];
    $termsLink = $config->get($group)[$field]['link'];
    $termsNode = $config->get($group)[$field]['node'];
    $termsModal = $config->get($group)[$field]['modal'];
    $termsTarget = $config->get($group)[$field]['target'];

    if ($termsText == NULL) {
      $termsText = 'Al presionar CONTINUAR está aceptando los';
    }
    if ($termsTextLink == NULL) {
      $termsTextLink = 'Términos y condiciones';
    }
    if ($termsModal == NULL) {
      $termsModal = 0;
    }
    if ($termsTarget == NULL) {
      $termsTarget = '_blank';
    }

    $form[$group][$field] = [
      '#type' => 'details',
      '#title' => t('Términos y condiciones'),
      '#description' => t('Configuración de ayudas en campos relacionados con términos y condiciones.'),
    ];
    $form[$group][$field]['text'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => t($termsText),
    ];
    $form[$group][$field]['textLink'] = [
      '#type' => 'textfield',
      '#title' => t('Texto enlace'),
      '#default_value' => t($termsTextLink),
    ];
    $form[$group][$field]['link'] = [
      '#type' => 'textfield',
      '#title' => t('Enlace'),
      '#description' => t('Link a información de términos y condiciones.'),
      '#default_value' => $termsLink,
    ];
    $form[$group][$field]['node'] = [
      '#type' => 'number',
      '#title' => t('Nodo'),
      '#description' => t('Nodo con información de términos y condiciones.'),
      '#default_value' => $termsNode,
    ];
    $form[$group][$field]['modal'] = [
      '#type' => 'checkbox',
      '#title' => t('Abrir términos y condiciones en modal'),
      '#default_value' => $termsModal,
    ];
    $form[$group][$field]['target'] = [
      '#type' => 'select',
      '#options' => [
        '_blank' => t('Nueva'),
        '_parent' => t('Actual'),
      ],
      '#default_value' => $termsTarget,
    ];

    // Button.
    $field = 'button';
    $buttonLabel = $config->get($group)[$field]['Label'];

    if ($buttonLabel == NULL) {
      $buttonLabel = 'Continuar';
    }

    $form[$group][$field] = [
      '#type' => 'details',
      '#title' => t('Botón continuar'),
      '#description' => t('Configuración del botón continuar.'),
    ];
    $form[$group][$field]['Label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => t($buttonLabel),
    ];

    // Tutorial.
    $field = 'tutorial';
    $tutorialText = $config->get($group)[$field]['text'];
    $tutorialLink = $config->get($group)[$field]['link'];
    $tutorialNode = $config->get($group)[$field]['node'];
    $tutorialModal = $config->get($group)[$field]['modal'];
    $tutorialTarget = $config->get($group)[$field]['target'];

    if ($tutorialText == NULL) {
      $tutorialText = 'Aprenda aquí como crear su cuenta.';
    }
    if ($tutorialModal == NULL) {
      $tutorialModal = 0;
    }
    if ($tutorialTarget == NULL) {
      $tutorialTarget = '_blank';
    }

    $form[$group][$field] = [
      '#type' => 'details',
      '#title' => t('Tutorial de creación'),
      '#description' => t('Configuración de ayudas en campos relacionados con tutorial de creación.'),
    ];
    $form[$group][$field]['text'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => t($tutorialText),
    ];
    $form[$group][$field]['link'] = [
      '#type' => 'textfield',
      '#title' => t('Enlace'),
      '#description' => t('Link a información de tutorial de creación.'),
      '#default_value' => $tutorialLink,
    ];
    $form[$group][$field]['node'] = [
      '#type' => 'number',
      '#title' => t('Nodo'),
      '#description' => t('Nodo con información de tutorial de creación.'),
      '#default_value' => $tutorialNode,
    ];
    $form[$group][$field]['modal'] = [
      '#type' => 'checkbox',
      '#title' => t('Abrir tutorial en modal'),
      '#default_value' => $tutorialModal,
    ];
    $form[$group][$field]['target'] = [
      '#type' => 'select',
      '#options' => [
        '_blank' => t('Nueva'),
        '_parent' => t('Actual'),
      ],
      '#default_value' => $tutorialTarget,
    ];

    // Limit failed attempts.
    $group = 'limit_failed_attempts';
    $form[$group] = [
      '#type' => 'details',
      '#title' => t('Limite de intento fallidos'),
      '#group' => 'tabs',
    ];
    $form[$group]['number'] = [
      '#type' => 'number',
      '#title' => t("Cantidad de intentos permitidos"),
      '#description' => t("Esta es la cantidad de intentos permitidos antes de bloquear el email del usuario por 24 horas para auto crear cuenta"),
      '#default_value' => isset($config->get($group)['number']) ? $config->get($group)['number'] : 0,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('tbo_account.autocreateaccountformconfig');
    $config
      ->set('form_config', $form_state->getValue('form_config'))
      ->set('limit_failed_attempts', $form_state->getValue('limit_failed_attempts'))
      ->save();
  }

}
