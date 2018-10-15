<?php

namespace Drupal\tbo_atp\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_atp\Plugin\Block\CorporativeProfilesBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Manage config a 'CorporativeProfilesBlockClass' block.
 */
class CorporativeProfilesBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param CorporativeProfilesBlock $instance
   * @param $config
   */
  public function setConfig(CorporativeProfilesBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'table_options' => [
        'table_fields' => [
          'profile' => [
            'title' => t('Perfil'),
            'label' => 'Perfil',
            'show' => 1,
            'service_field' => 'profile',
            'weight' => 1,
          ],
          'profile_description' => [
            'title' => t('Descrición del perfil'),
            'label' => 'Descripción del perfil',
            'show' => 1,
            'service_field' => 'profile_description',
            'weight' => 2,
          ],
          'associated_lines' => [
            'title' => t('Líneas asociadas'),
            'label' => 'Líneas asociadas',
            'show' => 1,
            'service_field' => 'associated_lines',
            'weight' => 3,
          ],
          'package_value' => [
            'title' => t('Valor del paquete'),
            'label' => 'Valor del paquete',
            'show' => 1,
            'service_field' => 'package_value',
            'weight' => 4,
          ],
          'total_value' => [
            'title' => t('Valor total'),
            'label' => 'Valor total',
            'show' => 1,
            'service_field' => 'total_value',
            'weight' => 5,
          ],
          'lines' => [
            'title' => t('Líneas'),
            'label' => '',
            'show' => 1,
            'service_field' => 'lines',
            'weight' => 6,
          ],
        ],
        'link_label' => 'Líneas',
      ],
      'link_lines' => '/',
      'others_display' => [
        'table_fields' =>  [
          'profile' => [
            'title' => t('Perfil'),
            'label' => 'Perfil',
            'show' => 1,
            'service_field' => 'profile',
          ],
          'profile_description' => [
            'title' => t('Descrición del perfil'),
            'label' => 'Descripción del perfil',
            'show' => 1,
            'service_field' => 'profile_description',
          ],
          'associated_lines' => [
            'title' => t('Líneas asociadas'),
            'label' => 'Líneas asociadas',
            'show' => 1,
            'service_field' => 'associated_lines',
          ],
          'package_value' => [
            'title' => t('Valor del paquete'),
            'label' => 'Valor del paquete',
            'show' => 1,
            'service_field' => 'package_value',
          ],
          'total_value' => [
            'title' => t('Valor total'),
            'label' => 'Valor total',
            'show' => 1,
            'service_field' => 'total_value',
          ],
          'lines' => [
            'title' => t('Líneas'),
            'label' => 'Líneas',
            'show' => 1,
            'service_field' => 'lines',
          ],
        ],
      ],
      'not_show_class' => [
        'columns' => 1,
      ],
      'filters_options' => [
        'filters_fields' => [
          'name_filter' => [
            'title' => t('filtro por nombre'),
            'label' => 'Realice una busqueda',
            'show' => 1,
            'validate_length' => 300,
            'service_field' => 'name_filter',
          ],
        ],
      ],
      'others' => [
        'config' =>  [
          'show_margin' => [
            'show_margin_filter' => 0,
            'show_margin_card' => 1,
            'show_margin_table' => 1,
          ],
          'paginate' => [
            'number_rows_pages' => 5,
          ],
          'scroll'=> [
            'number_scroll_content' => 10,
          ],
        ],
      ],
      'error_message' => 'En el momento no podemos obtener información, por favor intente de nuevo más tarde',
      'error_message_404' => 'No hay información disponible de perfiles para mostrar',
    ];
  }

  /*
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form = $this->instance->cardBlockForm();
    $form['filters_options']['filters_fields']['#header'] = [t('Field'), t('Label'), t('Show'), ''];
		$form['others_display']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'),'', ''];
		$form['table_options']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'), t('Weight'), '', ''];

    unset($form['filters_options']['filters_fields']['name_filter']['weight'],
      $form['filters_options']['filters_fields']['name_filter']['class'],
      $form['filters_options']['filters_fields']['#tabledrag']);

    $form['others_display']['#title'] = t('Configuraciones de registros en móvil');

    $form['table_options']['link_label'] = [
      '#type' => 'textfield',
      '#title' => t('Texto del link Lineas'),
      '#default_value' => $this->configuration['table_options']['link_label'],
    ];

    // Configuration of url for label "Lineas".
    $form['link_lines'] = [
      '#type' => 'textfield',
      '#title' => t('Url de direccionamiento'),
      '#default_value' => $this->configuration['link_lines'],
    ];

    $form['others']['config']['scroll'] = [
      '#type' => 'details',
      '#title' => t('Configurar scroll'),
      '#open' => TRUE,
    ];

    // Number of elements per scroll charge.
    $form['others']['config']['scroll']['number_scroll_content'] = [
      '#type' => 'textfield',
      '#title' => t('Numero de elementos a mostrar por pagina'),
      '#default_value' => $this->configuration['others']['config']['scroll']['number_scroll_content'],
      '#description' => t('Se recomienda usar un valor mayor o igual a 10.'),
    ];

    // Set message error when web services failing.
    $form['error_message'] = [
      '#type' => 'textfield',
      '#title' => t('Mensaje de error'),
      '#default_value' => $this->configuration['error_message'],
    ];

    $form['others']['config']['show_margin']['show_margin_table'] = [
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['others']['config']['show_margin']['show_margin_table'],
      '#title' => t('Agregar margen a la tabla'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['link_lines'] = $form_state->getValue('link_lines');
    $this->configuration['error_message'] = $form_state->getValue('error_message');
  }

  /**
   * {@inheritdoc}
   */
  public function build(CorporativeProfilesBlock &$instance, $configuration) {
    //Set data uuid, generate filters_fields, generate table_fields
    $instance->cardBuildHeader(TRUE, TRUE);
    $instance->setValue('config_name', 'atpContractFilterBlock');
    $instance->setValue('directive', 'data-ng-corporative-profiles');
    $instance->setValue('class', 'block-corporative-profiles');

    $movil_fields = [];

    foreach($configuration['others_display']['table_fields'] as $key => $field) {
      if($field['show'] == TRUE) {
        $movil_fields[$key] = [
          'label' => $field['label'],
          'service_field' => $field['service_field'],
        ];
      }
    }

    $parameters = [
      'library' => 'tbo_atp/corporative_profiles',
      'theme' => 'corporative_profiles',
    ];

    $val_atp = \Drupal::service('tbo_atp.general_service')->validateAtpServices();

    $other_config = [
      '#margin' => $configuration['others']['config']['show_margin'],
      '#movil_fields' => $movil_fields,
      '#url' => $configuration['link_lines'],
      '#link_label' => $configuration['table_options']['link_label'],
      '#val_atp' => $val_atp,
    ];

    $instance->cardBuildVarBuild($parameters, $other_config);

    // Set javascript variables.
    $otherConfig = [
      'scroll_number' => $configuration['others']['config']['scroll']['number_scroll_content'],
      'error_message' => $configuration['error_message'],
    ];

    // Set on drupalSettings the rest url and javascript variables.
    $config = $instance->cardBuildConfigBlock('/tbo-atp/corporative-profiles?_format=json', $otherConfig);

    $instance->cardBuildAddConfigDirective($config, 'corporativeProfilesBlock');

    return $instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if ((in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles))/* && ($_SESSION['company']['environment'] == 'movil' || $_SESSION['company']['environment'] == 'both')*/) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();

  }
}
