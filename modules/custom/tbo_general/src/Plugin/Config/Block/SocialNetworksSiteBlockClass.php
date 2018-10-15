<?php

namespace Drupal\tbo_general\Plugin\Config\Block;

use Drupal\tbo_general\Plugin\Block\SocialNetworksSiteBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\user\PrivateTempStoreFactory;
/**
 * Manage config a 'SocialNetworksSiteBlockClass' block.
 */

class SocialNetworksSiteBlockClass {
  protected $configuration;
  protected $instance;

  /**
   * @param \Drupal\tbo_general\Plugin\Block\SocialNetworksSiteBlock $instance
   * @param $config
   */
  public function setConfig(SocialNetworksSiteBlock &$instance, &$config) {
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
      'social_network' =>[],

    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm( array &$form, FormStateInterface &$form_state) {
    $social_network = (isset($this->configuration['social_network']['table_fields']) && empty( $form_state->get('socialNetworkn')))  ? $form_state->set('socialNetworkn',$this->configuration['social_network']['table_fields']) : $form_state->get('socialNetworkn');

    $form['social_network'] = [
      '#type' => 'details',
      '#title' => t('CONFIGURACIÓN REDES SOCIALES'),
      '#description' => t('llene la tabla con los campos'),
      '#open' => TRUE,
    ];
    $form['social_network']['table_fields'] = array(
      '#type' => 'table',
      '#header' => array(
        t('Imagen'),
        t('Url'),
        t('Mostrar'),
        t('Eliminar'),
        t('Weight'),
      ),
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'snetwork',
          'group' => 'fields-order-weight',
        ),
      ),

      '#prefix' => '<div id="social_network-wrapper">',
      '#suffix' => '</div>',
    );
    uasort($form_state->get('socialNetworkn'), array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    foreach ($form_state->get('socialNetworkn') as $key => $vars) {
      $form['social_network']['table_fields'][$key] = [
        '#attributes' => [
          'class' => 'draggable'
        ],
        'image_social_network' => [
          '#type' => 'managed_file',
          '#default_value' => $vars['image_social_network'],
          '#description' => t('Por favor ingrese una imagen de formato SVG y medidas minimas 24 px X 24 px'), 
            '#upload_location' => 'public://',
            '#upload_validators' => [
              'file_validate_image_resolution' => array("24*24"),
              'file_validate_extensions' => ['png jpg svg'],
            ],
        ],
        'url_social_network' => [
          '#type' => 'url',
          '#default_value' => $vars['url_social_network'],
          '#description' => t('Ingrese una URL de redireccionamiento'),
        ],
        'show_item' => [
          '#type' => 'checkbox',
          '#default_value' => $vars['show_item'],
        ],
        'actions' => [
          '#type' => 'submit',
          '#name' => 'delete-social_network-' . $key,
          '#value' => t('Eliminar'),
          '#submit' => array(array($this, 'removeRowCallback')),
          '#ajax' => [
            'callback' => array($this, 'removeSocialNetworkFunction'),
            'wrapper' => 'social_network-wrapper',
            'progress' => [
              'type' => 'throbber',
              'message' => t('Verifying entry...'),
            ],
          ],
        ],
        'weight' => [
          '#type' => 'weight',
          '#default_value' => $vars['weight'],
          '#title_display' => 'invisible',
          '#attributes' => array('class' => array('fields-order-weight')),
        ],
      ];
    }

    $form['social_network']['add_row'] = [
      '#type' => 'submit',
      '#value' => t('Agregar Red Social'),
      '#submit' => array(array($this, 'addSocialNetworknCallback')),
      '#ajax' => [
        'callback' => [$this, 'addSocialNetworknFunction'],
        'event' => 'click',
        'wrapper' => 'social_network-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying entry...'),
        ],
      ],
    ];
    $form_state->setCached(FALSE);
    return $form;
  }

  public function addSocialNetworknFunction( array &$form, FormStateInterface &$form_state) {
    return $form['settings']['social_network']['table_fields'];
  }
  
  public function addSocialNetworknCallback(array &$form, FormStateInterface &$form_state) {
    $social_network = $form_state->get('socialNetworkn');
     $social_network[] = array(
      'image_social_network' => '',
      'url_social_network' => '',
      'show_item' => '',
      'actions' => '',
      'weight'=>''
    );
    $form_state->set('socialNetworkn',$social_network);
    $form_state->setRebuild();
  }

  public function removeRowCallback(array &$form, FormStateInterface &$form_state) {
    $element = $form_state->getTriggeringElement();
    $value = explode("-", $element['#name'])[2];
    $aux_wallets = $form_state->get('socialNetworkn');
    unset ($aux_wallets[$value]);
    $form_state->set('socialNetworkn',$aux_wallets);
    $form_state->setRebuild();
  }
  
  public function removeSocialNetworkFunction(array &$form, FormStateInterface &$form_state) {
    return $form['settings']['social_network']['table_fields'];
  }


  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface &$form_state, &$config) {
    $social_network = $form_state->getValue('social_network');
    $social_network_save = array();
    foreach ($social_network['table_fields'] as $key => $value) {
      $social_network[$key] = array(
        'image_social_network' => $value['image_social_network'],
        'url_social_network' => $value['url_social_network'],
        'show_item' => $value['show_item'],
        'actions' => $value['actions'],
        'weight'=> $value['weight'],
      );
      $this->configuration['social_network']['table_fields'][$key] = $social_network[$key]; 
    }
    $form_state->set('socialNetworkn',$social_network_save);
  }

  /**
   * {@inheritdoc}
   */
  public function build(SocialNetworksSiteBlock &$instance, &$config) {
 
    $this->instance = &$instance;
    $this->configuration = &$config;
    
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $build = [];

    $this->instance->setValue('config_name', 'SocialNetworksSiteBlock');
    $this->instance->setValue('directive', 'data-ng-fixed-social-networks-site');
    $this->instance->setValue('class', 'block--fixed-social-networks-site');
    $this->instance->ordering('table_options');

    //get table_fields
    $table_fields = $this->configuration["social_network"]['table_fields'];
    //array for send
    $social_network_data = array();
    uasort($table_fields, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    //read data from configuration
    foreach ($table_fields as $key => $data_field) {
      //create item into array
      $social_network_data[$key] = $data_field;
      //load Image
      $file = File::load(reset($data_field['image_social_network']));
      //create src file
      if ($file) {
        $src = file_create_url($file->getFileUri());
        $social_network_data[$key]['image_social_network'] = $src;
      }
    }
    $build = array(
      '#theme' => 'social_networks_site_block',
      '#uuid' => $this->instance->getValue('uuid'),
      '#config' => $social_network_data,
    );

    return $build;
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
