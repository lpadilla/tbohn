<?php

namespace Drupal\tbo_general\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'CopyrightBlock' block.
 *
 * @Block(
 *  id = "copyright_block",
 *  admin_label = @Translation("Copyright"),
 * )
 */
class CopyrightBlock extends CardBlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_fields' => [],
      'table_fields' => [],
      'others_display' => [],
      'buttons' => [],
      'others' => [
        'copyright' => [
          'value' => $this->t('Aqui los terminos y condiciones.'),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $field['copyright'] = [
      '#type' => 'text_format',
      '#title' => t('Copyright'),
      '#default_value' => $this->configuration['others']['copyright']['value'],
      '#format' => 'full_html',
    ];

    $form = $this->cardBlockForm($field);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Set data uuid, filters_fields, table_fields.
    $this->cardBuildHeader($filters = FALSE, $columns = FALSE);

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'general_copyright',
      'library' => '',
    ];

    $others = [
      '#copyright' => ['#markup' => $this->configuration['others']['copyright']['value']],
    ];

    $this->cardBuildVarBuild($parameters, $others);

    return $this->build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
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
      return parent::blockAccess($account);
    }

    return AccessResult::forbidden();
  }

}
