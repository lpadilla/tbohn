<?php
namespace Drupal\tbo_general\Plugin\Config\Block;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_general\Plugin\Block\CustomScriptsBlock;
use Drupal\Core\Url;
/**
 * Manage config a 'CustomScriptsBlockClass' block.
 */
class CustomScriptsBlockClass {
  protected $instance;
  protected $configuration;
  /**
   * {@inheritdoc}
   */
  public function setConfig(CustomScriptsBlock &$instance, &$config) {
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
          'links' => [],
          'vars' => [],
        ],
      ],
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function blockForm(array &$form, FormStateInterface &$form_state, $config) {
    $form['#tree'] = TRUE;
    $aux_config = $form_state->get('linksConfig');
    $script_aux = isset($aux_config) ? $form_state->get('linksConfig') : $config['others']['config']['links']['table_fields'];
    $form_state->set('linksConfig', $script_aux);
    $form['others']['config']['vars'] = [
      'link' => [
        '#type' => 'textfield',
        '#title' => t('Nombre de la variable'),
        '#size' => 100,
        '#default_value' => $config['others']['config']['vars']['link'],
      ],
      'valueVariable' => [
        '#type' => 'textfield',
        '#title' => t('Valor de la variable'),
        '#size' => 100,
        '#default_value' => $config['others']['config']['vars']['valueVariable'],
      ],
    ];
    $form['others']['config']['links'] = [
      '#type' => 'details',
      '#title' => t('CONFIGURACIÓN Scripts JS'),
      '#description' => t('Ingrese el link del Javascripts'),
      '#open' => TRUE,
      '#prefix' => '<div id="wallets-wrapper">',
      '#suffix' => '</div>',
    ];
    // ¡Se ordenan los links segun lo establecido en la configuración!
    uasort($form['links']['table_fields'], [
      'Drupal\Component\Utility\SortArray', 'sortByWeightElement',
    ]);
    $form['others']['config']['links']['table_fields'] = [
      '#type' => 'table',
      '#header' => [
        t('ENLACE'),
        t('ACTIVADO'),
        t('ELIMINADO'),
        t('Weight'),
        '',
      ],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
    ];
    foreach ($form_state->get('linksConfig') as $key => $vars) {
      $form['others']['config']['links']['table_fields'][$key] = [
        '#attributes' => [
          'class' => 'draggable',
        ],
        'link' => [
          '#type' => 'textfield',
          '#size' => 100,
          '#default_value' => $vars['link'],
        ],
        'activation' => [
          '#type' => 'checkbox',
          '#title' => t('activación'),
          '#default_value' => $vars['activation'],
        ],
        'actions' => [
          '#type' => 'submit',
          '#name' => 'delete-wallet-' . $key,
          '#value' => t('Eliminar'),
          '#submit' => [
            [$this, 'removeRowCallback'],
          ],
          '#ajax' => [
            'callback' => [$this, 'removeWalletFunction'],
            'wrapper' => 'wallets-wrapper',
            'progress' => [
              'type' => 'throbber',
              'message' => t('Verifying entry...'),
            ],
          ],
        ],
        'weight' => [
          '#type' => 'weight',
          '#default_value' => $vars['weight'],
          '#attributes' => [
            'class' => ['fields-order-weight'],
          ],
        ],
      ];
    }
    $form['others']['config']['links']['add_row'] = [
      '#type' => 'submit',
      '#value' => t('Agregar Links'),
      '#submit' => [[$this, 'addWalletCallback']],
      '#ajax' => [
        'callback' => [$this, 'addWalletFunction'],
        'event' => 'click',
        'wrapper' => 'wallets-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying entry...'),
        ],
      ],
    ];
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function build(CustomScriptsBlock &$instance, &$config) {
    $linksValue = [];
    $this->instance = &$instance;
    $this->configuration = &$config;
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'customScriptsBlock');
    $this->instance->setValue('directive', 'data-ng-custom-scripts');
    $instance->setValue('class', 'block-custom-scripts');
    $current_url = Url::fromRoute('<current>');
    $path = $current_url->toString();
    $varsName = $config['others']['config']['vars']['link'];
    $varsValue = $config['others']['config']['vars']['valueVariable'];
    $build['valueVars'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => '' . $varsName . ' = "' . $varsValue . '"',
    ];
    // Question route of restrictions.
    foreach ($config['others']['config']['links']['table_fields'] as $key) {
      if ($key['activation'] == 1) {
        $linksValue[] = $key['link'];
      }
    }
    // Others.
    foreach ($linksValue as $key) {
      $build[$key] = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#attributes' => [
          'type' => 'text/javascript',
          'src' => $key,
        ],
      ];
    }
    return $build;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $rows = $form_state->get('links')['table_fields'];
    foreach ($rows as $key => $var) {
      $errors = FALSE;
      if (empty($var['id'])) {
        $form_state->setErrorByName($form['links']['table_fields'][$key]['link'], t('id field is required'));
        $errors = TRUE;
      }
    };
  }
  /**
   * {@inheritdoc}
   */
  public function addWalletFunction(array &$form, FormStateInterface &$form_state) {
    return $form['settings']['others']['config']['links'];
  }
  /**
   * {@inheritdoc}
   */
  public function addWalletCallback(array &$form, FormStateInterface &$form_state) {
    $aux_wallets = $form_state->get('linksConfig');
    $aux_wallets[] = [
      'link' => '',
      'activation' => '',
      'action' => '',
      'weight' => '',
    ];
    $form_state->set('linksConfig', $aux_wallets);
    $form_state->setRebuild();
  }
  /**
   * {@inheritdoc}
   */
  public function removeRowCallback(array &$form, FormStateInterface &$form_state) {
    $element = $form_state->getTriggeringElement();
    $value = intval(substr($element['#name'], 14, strlen($element['#name'])));
    $aux_wallets = $form_state->get('linksConfig');
    unset($aux_wallets[$value]);
    $form_state->set('linksConfig', $aux_wallets);
    $form_state->setRebuild();
  }
  /**
   * {@inheritdoc}
   */
  public function removeWalletFunction(array &$form, FormStateInterface &$form_state) {
    return $form['settings']['others']['config']['links'];
  }
}
