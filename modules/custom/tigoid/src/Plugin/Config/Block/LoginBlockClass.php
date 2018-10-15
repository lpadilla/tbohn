<?php

namespace Drupal\tigoid\Plugin\Config\Block;

use Drupal\tigoid\Plugin\Block\LoginBlock;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a 'LoginBlock' block config.
 */
class LoginBlockClass {
  protected $instance;
  protected $configuration;
  
  /**
   * Set config.
   *
   * @param \Drupal\tigoid\Plugin\Block\LoginBlock $instance
   *   Instance.
   * @param array $config
   *   Config data.
   */
  public function setConfig(LoginBlock &$instance, array &$config) {
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
          'body' => [
            'value' => t('Inicie sesión o cree su cuenta para consultar su información.'),
            'format' => 'full_html',
          ],
          'labels' => [
            'welcome' => '¡Bienvenido!',
            'create' => 'CREAR UNA CUENTA',
            'login' => 'INICIAR SESIÓN',
          ],
          'buttons' => [
            'create' => 1,
            'login' => 1,
          ],
          'link_below' => 'https://ayuda.tigoune.co/hc/es/articles/360000138468--C%C3%B3mo-crear-Mi-Cuenta-Empresas-',
          'text_below' => t('Aprenda aquí como crear su cuenta'),
          'new_flow' => FALSE,
        ],
      ],
    ];
    
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state, $configuration) {
    $this->configuration = $configuration;
    
    $form['others']['config']['body'] = [
      '#type' => 'text_format',
      '#title' => t("Message"),
      // '#description' => $this->t(''),.
      '#format' => $this->configuration['others']['config']['body']['format'],
      '#default_value' => isset($this->configuration['others']['config']['body']['value']) ? $this->configuration['others']['config']['body']['value'] : "",
    ];
    
    $form['others']['config']['labels'] = [
      '#type' => 'details',
      '#title' => t("Labels"),
      '#description' => t("Labels del card"),
      '#open' => TRUE,
    ];
    
    $form['others']['config']['labels']['welcome'] = [
      '#type' => 'textfield',
      '#title' => t("Bienvenida"),
      '#default_value' => isset($this->configuration['others']['config']['labels']['welcome']) ? $this->configuration['others']['config']['labels']['welcome'] : "",
    ];
    
    $form['others']['config']['labels']['create'] = [
      '#type' => 'textfield',
      '#title' => t("Creación"),
      '#default_value' => isset($this->configuration['others']['config']['labels']['create']) ? $this->configuration['others']['config']['labels']['create'] : "",
    ];
    
    $form['others']['config']['labels']['login'] = [
      '#type' => 'textfield',
      '#title' => t("Login"),
      '#default_value' => isset($this->configuration['others']['config']['labels']['login']) ? $this->configuration['others']['config']['labels']['login'] : "",
    ];

    $form['others']['config']['link_below'] = [
      '#type' => 'url',
      '#title' => t('Enlace inferior'),
      '#format' => $this->configuration['others']['config']['body']['format'],
      '#default_value' => isset($this->configuration['others']['config']['link_below']) ? $this->configuration['others']['config']['link_below'] : "",
    ];
    $form['others']['config']['text_below'] = [
      '#type' => 'textfield',
      '#title' => t('Texto del enlace inferior'),
      '#format' => $this->configuration['others']['config']['body']['format'],
      '#maxlength' => 255,
      '#default_value' => isset($this->configuration['others']['config']['text_below']) ? $this->configuration['others']['config']['text_below'] : "",
      '#attributes' => [
        'pattern' => '[a-z üÜñÑáéíóúÁÉÍÓÚ.,¡!¿?:A-Z0-9]+',
        'title' => t('Por favor ingrese solo letras, numeros y puntos.'),
      ],
    ];
    
    $form['others']['config']['buttons'] = [
      '#type' => 'details',
      '#title' => t("Botones"),
      '#description' => t("Visualizar botones del card"),
      '#open' => TRUE,
    ];
    
    $form['others']['config']['buttons']['create'] = [
      '#type' => 'checkbox',
      '#title' => t("Crear cuenta"),
      '#default_value' => isset($this->configuration['others']['config']['buttons']['create']) ? $this->configuration['others']['config']['buttons']['create'] : "",
    ];
    
    $form['others']['config']['buttons']['login'] = [
      '#type' => 'checkbox',
      '#title' => t("Login"),
      '#default_value' => isset($this->configuration['others']['config']['buttons']['login']) ? $this->configuration['others']['config']['buttons']['login'] : "",
    ];

    $form['others']['config']['new_flow'] = [
      '#type' => 'checkbox',
      '#title' => t('Seguir nuevo flujo'),
      '#default_value' => isset($this->configuration['others']['config']['new_flow']) ? $this->configuration['others']['config']['new_flow'] : FALSE,
    ];
    
    $form = $this->instance->cardBlockForm($form['others']['config']);
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function build(LoginBlock &$instance, $configuration) {
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'loginBlock');
    $this->instance->setValue('directive', 'login-tigo-id');
    $this->instance->setValue('class', 'login-tigo-id');
    
    // Set session var.
    $instance->cardBuildSession();
    
    $parameters = [
      'theme' => 'login',
    ];
    
    $others = [
      '#message' => ['#markup' => $configuration['others']['config']['body']['value']],
      '#link_below' => $configuration['others']['config']['link_below'],
      '#text_below' => $configuration['others']['config']['text_below'],
      '#create_account' => Url::fromRoute('tigoid.login.create'),
      '#login' => Url::fromRoute('tigoid.login.handler'),
      '#uuid' => $this->instance->getValue('uuid'),
      '#class' => $this->instance->getValue('class'),
      '#labels' => $configuration['others']['config']['labels'],
      '#buttons_login' => $configuration['others']['config']['buttons'],
    ];
    
    $other_config = [];
    $config_block = $instance->cardBuildConfigBlock('', $other_config);
    $instance->cardBuildVarBuild($parameters, $others);
    $instance->cardBuildAddConfigDirective($config_block);
    
    return $instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $form_state_complete = $form_state;
    if (get_class($form_state) == 'Drupal\Core\Form\SubformState') {
      $form_state_complete = $form_state->getCompleteFormState();
    }
    $text_below = $form_state_complete->getValue(
      [
        'settings',
        'others',
        'config',
        'text_below',
      ]
    );

    if ($text_below !== '') {
      if (!preg_match("/^[a-z üÜñÑáéíóúÁÉÍÓÚ.,¡!¿?:A-Z0-9]+$/", $text_below)) {
        $form_state->setErrorByName('text_below', t('Por favor en el campo "Texto del enlace inferior" ingrese solo letras, numeros y puntos.'));
      }
    }
  }


}
