<?php

namespace Drupal\tbo_billing_bo\Plugin\Config;

use Drupal\tbo_billing_bo\Plugin\Block\ServicePortfolioBoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'ServicePortfolioBoBlock' block.
 */
class ServicePortfolioBoBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_billing_bo\Plugin\Block\ServicePortfolioBoBlock $instance
   * @param $config
   */
  public function setConfig(ServicePortfolioBoBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [
          'logoFilters' => ['title' => t("Logo Filtros"),
                            'label' => t('Filtros'), 
                            'service_field' => 'logoFilters', 
                            'show' => 1, 
                            'weight' => 1, 
                            'class' => '6-columns'],
          'linea' => [
                      'title' => t("Línea"), 
                      'label' => t("Numero"), 
                      'service_field' => 'msisdn', 
                      'show' => 1,                        
                      'weight' => 2, 
                      'class' => '2-columns'],
         'contract' => [
                        'title' => t("Número de Contrato"),
                        'label' => t('Número '),
                        'service_field' => 'contract',
                        'show' => 1,
                        'weight' => 3,
                        'class' => '2-columns',
                        'max_length' => 200],
          'category' => ['title' => t("Categoria"),
                         'label' => t('Categoria') , 
                         'service_field' => 'category', 
                         'show' => 1, 
                         'weight' => 4, 
                         'class' => '3-columns'],
          'search' => ['title' => t("Busqueda"), 
                       'label' => t('Buscador'), 
                       'service_field' => 'search', 
                       'show' => 1,
                       'weight' => 5, 
                       'class' => '3-columns', 
                       'max_length' => 200],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'category_name' => [
            'title' => t('Nombre de la categoria'),
            'service_field' => 'category_name',
            'show' => 1,
            'weight' => 2,
          ],
          'service_status'  => [
            'title' => t('Estado del servicio'),
            'service_field' => 'service_status',
            'show' => 1,
            'weight' => 4,
          ],
          'service_plan'  => [
            'title' => t('Plan'),
            'label' => t('Plan'),
            'service_field' => 'service_plan',
            'show' => 1,
            'weight' => 3,
          ],
          'linea'  => [
            'title' => t('Num de Línea'),
            'label' => t('Num de Línea'),
            'service_field' => 'msisdn',
            'show' => 1,
            'weight' => 5,
          ],
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'image' => [
            'title' => t('Imagen resultados'),
            'service_field' => 'image',
            'show' => 1,
            'not_update_label' => 1,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'detail' => [
            'title' => t('botón detalle'),
            'label' => t('Detalle'),
            'url' => t('/detalle-servicios'),
            'url_description' => t('Ejemplo /detalle-servicios'),
            'service_field' => 'action_card',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
          ],
        ],
      ],
      'others' => [
        'config' => [
          'scroll' => '10',
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
        ],
      ],
      'not_show_class' => [
        'columns' => 1,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, &$form_state) {
    $field['scroll'] = [
      '#type' => 'textfield',
      '#title' => t('Configure la cantidad de elementos del scroll'),
      '#default_value' => $this->configuration['others']['config']['scroll'],
      '#required' => TRUE,
    ];

    $form = $this->instance->cardBlockForm($field);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ServicePortfolioBoBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(TRUE, TRUE);
    $this->instance->setValue('config_name', 'servicePortfolioBoBlock');
    $this->instance->setValue('directive', 'data-ng-service-portfolio');
    $this->instance->setValue('class', 'block-portfolio');

 
    $labelCategory = '';

        $filters = [];
        foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
            if ($filter['show'] == 1)
            {
                $filters[$key_filter]['identifier'] = $key_filter;
                $filters[$key_filter]['label'] = $filter['title'];
                $filters[$key_filter]['placeholder'] = isset($filter['label']) ? $filter['label']:'';
                $classes = ["field-" . $filter['service_field'], $filter['class']];
                $filters[$key_filter]['class'] = implode(" ", $classes);
                $filters[$key_filter]['service_field'] = $filter['service_field'];

                if (!empty($filter['validate_length']))
                {
                    $filters[$key_filter]['validate_length'] = $filter['validate_length'];
                }
               
                if ($key_filter == 'address')
                {
                   $labelCategory = $filters[$key_filter]['label'];
                   $filters[$key_filter]['select_multiple'] = TRUE;
                   $filter_mobile[$key_filter]['select_multiple'] = TRUE;
                }

                if ($key_filter == 'contract')
                {
                   $labelCategory = $filters[$key_filter]['label'];
                   $filters[$key_filter]['select_multiple'] = TRUE;
                }

                if ($key_filter == 'linea')
                {
                    $labelCategory = $filters[$key_filter]['label'];
                    $filters[$key_filter]['select_multiple'] = TRUE;
                }

            }
        }

        $this->instance->setValue('filters', $filters);

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'service_portfolio_bo',
      'library' => 'tbo_billing_bo/service-portfolio-bo',
    ];

    $others = [
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#title' => $this->configuration['label'],
      '#environment' => $_SESSION['environment'],
      '#others_display' => $this->configuration['others_display']['table_fields'],
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    $other_config = [
      'environment' => $_SESSION['environment'],
      'environment_enterprise' => $_SESSION['environment'],
      'scroll' => $this->configuration['others']['config']['scroll'],
      'labelCategory' => $labelCategory,
      'company' => [
        'number' => $_SESSION['company']['nit'],
        'document' => strtoupper($_SESSION['company']['docType']),
      ],
    ];

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tbo_billing_bo/rest/service-portfolio-bo?_format=json', $other_config);

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block);
			
			
		$service = \Drupal::service('tbo_billing.delivery');
    // Se guarda el log de auditoria $event_type, $description, $details = NULL.
    $this->instance->cardSaveAuditLog('Servicios', 'Usuario accede al portafolio de servicios', 'Usuario ' . $service->getName . ' accede al portafolio de servicios asociados a su empresa', $_SESSION['company']['name'], $_SESSION['company']['nit']);
    
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

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles) ) { 
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
