<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_services\Plugin\Block\ChangeSimCardBlock;

/**
 *
 */
class ChangeSimCardBlockClass {

  protected $instance;
  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function setConfig(ChangeSimCardBlock &$instance, &$config) {
    $this->instance = $instance;
    $this->configuration = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'others_display' => [
        'table_fields' => [
          'in_title' => [
            'title' => t('titulo'),
            'label' => 'Cambiar SIM Card',
            'service_field' => 'title',
            'show' => TRUE,
          ],
          'description' => [
            'title' => t('Descripción'),
            'label' => 'Realice el cambio de su SIM Card actual, complete los siguientes datos',
            'service_field' => 'description',
            'show' => TRUE,
          ],
          'change_field' => [
            'title' => t('campo cambiar SIM card'),
            'label' => 'Ingresa el número de tu SIM Card',
            'service_field' => 'change_field',
            'input_type' => 'text',
            'with_status' => [
              'id' => 'status_sim',
              'angular' => '{[{ help_text }]}',
            ],
            'identifier' => 'new_sim',
            'show' => TRUE,
            'attributes' => [
              'regex-number' => '',
              'ng-change' => 'validate_sim(new_sim)',
            ],
            'max_length' => 15,
          ],
          'confirm_change' => [
            'title' => t('campo confirmar contraseña'),
            'label' => 'Confirmar cambio de Sim Card',
            'service_field' => 'confirm_change',
            'show' => TRUE,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'change' => [
            'title' => t('botón Aceptar'),
            'service_field' => 'action_card_change',
            'label' => 'Aceptar',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => 0,
          ],
          'cancel' => [
            'title' => t('botón Cancelar'),
            'label' => 'Cancelar',
            'service_field' => 'action_card_cancel',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => TRUE,
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'enterprise' => [
            'title' => t('Empresa'),
            'label' => 'Empresa',
            'service_field' => 'enterprise',
            'weight' => 1,
            'show' => TRUE,
          ],
          'date_change' => [
            'title' => t('Fecha'),
            'label' => 'Fecha',
            'service_field' => 'date_change',
            'weight' => 2,
            'show' => TRUE,
          ],
          'hour' => [
            'title' => t('Hora'),
            'label' => 'Hora',
            'service_field' => 'hour',
            'weight' => 3,
            'show' => TRUE,
          ],
          'document' => [
            'title' => t('Documento'),
            'service_field' => 'document',
            'weight' => 4,
            'show' => TRUE,
          ],
          'user' => [
            'title' => t('Usuario'),
            'label' => 'Usuario',
            'service_field' => 'user',
            'weight' => 5,
            'show' => TRUE,
          ],
          'description' => [
            'title' => t('Descripción'),
            'label' => 'Descripción',
            'service_field' => 'description',
            'weight' => 6,
            'show' => TRUE,
          ],
          'line_number' => [
            'title' => t('Número de línea'),
            'label' => 'Número de línea',
            'service_field' => 'line_number',
            'weight' => 7,
            'show' => TRUE,
          ],
          'detail' => [
            'title' => t('Detalle'),
            'label' => 'Detalle',
            'service_field' => 'detail',
            'weight' => 8,
            'show' => TRUE,
          ],
        ],
      ],
      'label_display' => '0',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();

    $form['buttons']['table_fields']['change']['active']['#disabled'] = TRUE;

    // Rebuild table headers.
    $form['table_options']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'), t('Weight')];
    $form['others_display']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show')];
    $form['buttons']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'), t('Active')];

    $values = ['change', 'cancel'];

    foreach ($values as $value) {
      unset($form['buttons']['table_fields'][$value]['url']);
    }

    // Set container name.
    $form['table_options']['#title'] = t('Configuraciones de campos del Pop-up');
    $form['others_display']['#title'] = t('Configuraciones de campos del card');

    // Unset class select of all fields.
    $fields = ['title', 'description', 'change_field', 'confirm_change'];

    foreach ($fields as $key => $field) {
      unset($form['table_options']['table_fields'][$field]['class']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ChangeSimCardBlock &$instance, &$config) {

    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'changeSimCardBlock');
    $this->instance->setValue('directive', 'data-ng-change-sim-card');
    $this->instance->setValue('class', 'block-changeSimCardBlock');

    // Set theme $vars.
    $parameters = [
      'theme' => 'change_sim_card',
      'library' => 'tbo_services/change_sim_card',
    ];

    $others = [
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#title' => [
        'label' => $this->configuration['label'],
        'label_display' => $this->configuration['label_display'],
      ],
      '#fields' => $this->configuration['others_display']['table_fields'],
      '#pop_up_fields' => $this->configuration['table_options']['table_fields'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Set JavaScript data.
    $other_params = [
      'pop_fields' => $this->configuration['table_options']['table_fields'],
    ];

    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/change-sim-card?_format=json', $other_params);

    $this->instance->cardBuildAddConfigDirective($config_block);

    return $this->instance->getValue('build');
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
