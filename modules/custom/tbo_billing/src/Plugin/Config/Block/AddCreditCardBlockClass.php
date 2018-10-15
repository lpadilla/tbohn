<?php

namespace Drupal\tbo_billing\Plugin\Config\Block;

use Drupal\tbo_billing\Plugin\Block\AddCreditCardBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Manage config a 'AddCreditCardBlockClass' block.
 */
class AddCreditCardBlockClass {
  protected $configuration;
  protected $instance;

  /**
   * @param \Drupal\tbo_billing\Plugin\Block\AddCreditCardBlock $instance
   * @param $config
   */
  public function setConfig(AddCreditCardBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [],
      ],
      'table_options' => [
        'table_fields' => [],
      ],
      'others' => [
        'config' => [
          'image' => '',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm(&$form, &$form_state) {
    $form['others']['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Imagen'),
      '#default_value' => $this->configuration['others']['config']['image'],
      '#description' => t('Ventana de ayuda CVV, por favor ingrese una imagen de formato PNG, JPEG y medidas minimas aa px X bb px'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg'],
      ],
    ];

    $form = $this->instance->cardBlockForm($form['others']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface &$form_state, &$config) {
    $fid = $form_state->getValue('others')['image'];
    // Save file permanently.
    if ($fid) {
      $this->setFileAsPermanent($fid);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(AddCreditCardBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    \Drupal::service('page_cache_kill_switch')->trigger();

    $node = Node::load(1);
    $render = [];

    if (isset($node)) {
      $render = \Drupal::entityTypeManager()
        ->getViewBuilder('node')
        ->view($node);
    }

    $referer = '';

    if (isset($_SESSION['detail_invoice_url'])) {
      $referer = $_SESSION['detail_invoice_url'];
    }
    else {
      $referer = '/detalle-factura';
    }

    $build = [];

    $uid = \Drupal::currentUser();
    $mail = $uid->getEmail();
    $src = '';
    $file = File::load(reset($this->configuration['others']['config']['image']));
    if ($file) {
      $src = file_create_url($file->getFileUri());

    }
    $build['#data'] = [
      'session' => $_SESSION['sendDetail'],
      'title' => t('Agregar Tarjeta de Crédito'),
      'block' => $render,
      'referer' => $referer,
      'mail' => $mail,
      'imageSrc' => $src,
      'contract' => t('Contrato'),
      'service' => t('Servicio'),
      'codeLabel' => t('CVV'),
      'mailLabel' => t('Correo electrónico'),
      'phoneLabel' => t('Número celular'),
      'address' => t('Dirección'),
      'cardLabel' => t('Número de tarjeta de crédito'),
      'dateLabel' => t('Fecha de expiración'),
      'mailError' => t('Dirección de email inválida.'),
      'phoneError' => t('Número de celular inválido.'),
      'cardError' => t('Tarjeta de crédito inválida.'),
      'codeError' => t('CVV invalido'),
      'environment' => $_SESSION['environment'],
    ];

    $build['#theme'] = 'add_credit_card';
    $build['#attached'] = [
      'library' => ['tbo_billing/add-credit-card'],
    ];

    return $build;
  }

  /**
   * Method to save file permanenty in the database.
   *
   * @param string $fid
   *   File id.
   */
  public function setFileAsPermanent($fid) {
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    $file = File::load($fid);

    // If file doesn't exist return.
    if (!is_object($file)) {
      return;
    }

    // Set as permanent.
    $file->setPermanent();

    // Save file.
    $file->save();

    // Add usage file.
    \Drupal::service('file.usage')->add($file, 'tbo_billing', 'tbo_billing', 1);
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
