<?php

namespace Drupal\tbo_general_hn\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'CopyrightHnBlock' block.
 *
 * @Block(
 *  id = "copyright_block",
 *  admin_label = @Translation("Copyright"),
 * )
 */
class CopyrightHnBlock extends CardBlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'table_options' => [
        'table_fields' => [],
      ],
      'filters_options' => [
        'filters_fields' => [],
      ],
      'others_display' => [],
      'buttons' => [],
      'others' => [
        'config' => [
          'copyright' => [
            'value' => $this->t('Aqui los terminos y condiciones.'),
          ],
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
      '#default_value' => $this->configuration['others']['config']['copyright']['value'],
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
      '#copyright' => ['#markup' => $this->configuration['others']['config']['copyright']['value']],
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
    return parent::blockAccess($account);
  }

}
