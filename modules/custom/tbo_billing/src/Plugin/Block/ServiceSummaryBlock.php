<?php

namespace Drupal\tbo_billing\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_general\CardBlockBase;

/**
 * Provides a 'ServiceSummaryBlock' block.
 *
 * @Block(
 *  id = "service_summary_block",
 *  admin_label = @Translation("Resumen de servicios"),
 * )
 */
class ServiceSummaryBlock extends CardBlockBase {

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
          'service' => ['name_service' => 'default', 'icon' => '', 'url' => '/', 'weight' => 1, 'show' => 1],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $fields = [];

    $fields['services'] = [
      '#type' => 'table',
      '#header' => [t('Nombre del servicio'), t('icono'), t('url'), t('show'), t('weight')],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
                [
                  'action' => 'order',
                  'relationship' => 'sibling',
                  'group' => 'fields-order-weight',
                ],
      ],
    ];

    $fields['services']['service'] = [
      '#attributes' => [
        'class' => ['draggable'],
      ],
    ];

    $fields['services']['service']['name_service'] = [
      'class' => [
        '#type' => 'textfield',
        '#title' => t('Nombre del servicio'),
        '#title_display' => 'invisible',
        '#default_value' => $this->configuration['others']['config']['service']['name_service'],
        '#required' => TRUE,
        '#size' => 30,
      ],
    ];

    $fields['services']['service']['icon'] = [
      'class' => [
        '#type' => 'managed_file',
        '#title' => $this->t('Icono del servicio'),
        '#title_display' => 'invisible',
        '#required' => TRUE,
        '#default_value' => $this->configuration['others']['config']['service']['icon'],
        '#size' => 20,
        '#upload_location' => 'public://service_summary_img/',
      ],
    ];

    $fields['services']['service']['url'] = [
      'class' => [
        '#type' => 'textfield',
        '#title' => $this->t('Url redireccionamiento'),
        '#title_display' => 'invisible',
        '#default_value' => $this->configuration['others']['config']['service']['url'],
        '#required' => TRUE,
        '#size' => 35,
      ],
    ];

    $fields['services']['service']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['others']['config']['service']['show'],
    ];

    $fields['services']['service']['weight'] = [
      '#type' => 'weight',
      '#title' => t('Weight for service'),
      '#title_display' => 'invisible',
      '#default_value' => $this->configuration['others']['config']['service']['weight'],
    // Classify the weight element for #tabledrag.
      '#attributes' => ['class' => ['fields-order-weight']],
    ];

    $form = $this->cardBlockForm($fields);

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->cardBuildHeader(FALSE, FALSE);
    $this->config_name = 'serviceSummaryBlock';
    $this->directive = 'data-ng-service-summary';

    // Set session var.
    $this->cardBuildSession();

    $parameters = [
      'theme' => 'service_summary',
      'library' => 'tbo_billing/service-summary',
    ];

    $others = [];

    $this->cardBuildVarBuild($parameters, $others);

    return $this->build;
  }

}
