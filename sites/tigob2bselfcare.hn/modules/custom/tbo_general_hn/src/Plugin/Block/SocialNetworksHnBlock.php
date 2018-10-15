<?php

namespace Drupal\tbo_general_hn\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;

/**
 * Provides a 'SocialNetworksHnBlock' block.
 *
 * @Block(
 *  id = "social_networks_hn_block",
 *  admin_label = @Translation("Redes sociales Hn"),
 * )
 */
class SocialNetworksHnBlock extends CardBlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'filters_options' => [
        'filters_fields' =>  [
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'twitter' => [
            'label' => $this->t('Twitter'),
            'image' => '',
            'url' => file_create_url('public://'), 
            'show' => 1,
            'weight' => 1,
            'service_field' => 'twitter',
          ],
          'facebook' => [
            'label' => $this->t('Facebook'),
            'image' => '', 
            'url' => file_create_url('public://'),
             'show' => 1, 'weight' => 1, 
             'service_field' => 'facebook',
          ],
          'google' => [
            'label' => $this->t('Google'),
            'image' => '', 
            'url' => file_create_url('public://'),
            'show' => 1, 'weight' => 1, 
            'service_field' => 'google',
          ],
        ],
      ],
      'others_display' => [],
      'buttons' => [],
      'others' => [],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    //$table_fields: variable que contiene la configuracion por defecto de las columnas de la tabla
    $table_fields = $this->configuration['table_options']['table_fields'];

    //table_options: fieldset que contiene todas las columnas de la tabla
    $form['table_options'] = array(
      '#type' => 'details',
      '#title' => $this->t('Configuraciones tabla2'),
      '#open' => TRUE,
    );
    $form['table_options']['table_fields'] = array(
      '#type' => 'table',
      '#header' => array(t('label'), t('image'), t('url'), t('Show'), t('Weight'), ''),
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ),
    );

    //Se ordenan los filtros segun lo establecido en la configuración
    uasort($table_fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

    //Se crean todas las columnas de la tabla que mostrara la información
    foreach ($table_fields as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['table_options']['table_fields']['#weight'] = $entity['weight'];

      $form['table_options']['table_fields'][$id]['label'] = array(
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      );

      $form['table_options']['table_fields'][$id]['image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Avatar'),
        '#default_value' => $entity['image'],
        '#description' => $this->t('Por favor ingrese una imagen de formato PNG, JPEG, SVG y medidas minimas 70px X 46px'),
        '#upload_location' => 'public://',
        '#upload_validators' => array(
          'file_validate_extensions' => array('png jpg svg'),
          'file_validate_image_resolution' => array($maximum_dimensions = 0, $minimum_dimensions = '68x40'),
        ),
      ];

      $form['table_options']['table_fields'][$id]['url'] = array(
        '#type' => 'textfield',
        '#title' => t('Url de redireccion del logo del sitio'),
        '#default_value' => $entity['url'],
      );

      $form['table_options']['table_fields'][$id]['show'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      );

      // TableDrag: Weight column element.
      $form['table_options']['table_fields'][$id]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight for @title', array('@title' => $entity['label'])),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => array('class' => array('fields-order-weight')),
      );

      $form['table_options']['table_fields'][$id]['service_field'] = array(
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    parent::blockValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['table_options']['table_fields'] = $form_state->getValue(['table_options', 'table_fields']);
    
    # recorro cada valor del formulario que extiende de tbo_general/Plugin/Block/Soialnetworksblock
    //foreach ($table_fields as $id => $entity) {
    foreach ($this->configuration['table_options']['table_fields'] as $id => $entity) {
      
      if ($this->configuration['table_options']['table_fields'][$id]['image']) {
        
        $this->setFileAsPermanent($this->configuration['table_options']['table_fields'][$id]['image']);
      }
    }

  }

  /**
   * Method to save file permanenty in the database
   * @param string $fid
   * File id
   */
  public function setFileAsPermanent($fid) {
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    $file = \Drupal\file\Entity\File::load($fid);

    //If file doesn't exist return
    if (!is_object($file)) {
      return;
    }

    //Set as permanent
    $file->setPermanent();

    // Save file
    $file->save();

    // Add usage file
    \Drupal::service('file.usage')->add($file, 'tbo_general', 'tbo_general', 1);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    //Set data uuid, filters_fields, table_fields
    $this->cardBuildHeader(FALSE,FALSE);
    
    #Se construye la variable $build con los datos que se necesitan en el tema
    $parameters = [
      'theme' => 'general_social',
      'library' => 'tbo_general_hn/general-social',
    ];

    $src = '';

    # Verificación del campo imagen para cada caso de Redes Sociales definidas en la configuracion 
    $path= file_create_url('public://');

    $name=$name2=$name3=null;
    $src=$src2=$src3=null;
    $url=$url2=$url3=null;
    $show=$show2=$show3=null; 
    
    if (!empty($this->configuration['table_options']['table_fields']['twitter']['image'][0])){
      $file = \Drupal\file\Entity\File::load($this->configuration['table_options']['table_fields']['twitter']['image'][0]);
      if ($file){
        $src = $path.$file->getFilename();
        $url = $this->configuration['table_options']['table_fields']['twitter']['url'];
        $name= $this->configuration['table_options']['table_fields']['twitter']['label'];
        if($this->configuration['table_options']['table_fields']['twitter']['show'] == 1){
          $show=1;
        }
      }
    }

    if (!empty($this->configuration['table_options']['table_fields']['facebook']['image'][0])){
      $file = \Drupal\file\Entity\File::load($this->configuration['table_options']['table_fields']['facebook']['image'][0]);
      if ($file){
        $src2 = $path.$file->getFilename();
        $url2=$this->configuration['table_options']['table_fields']['facebook']['url'];
        $name3= $this->configuration['table_options']['table_fields']['facebook']['label'];

        if($this->configuration['table_options']['table_fields']['facebook']['show'] == 1){
          $show2=1;
        }
      }
    }

    if (!empty($this->configuration['table_options']['table_fields']['google']['image'][0])){
      $file = \Drupal\file\Entity\File::load($this->configuration['table_options']['table_fields']['google']['image'][0]);
      if ($file){
        $src3 = $path.$file->getFilename();
        $url3=$this->configuration['table_options']['table_fields']['google']['url'];
        $name3= $this->configuration['table_options']['table_fields']['google']['label'];
        if($this->configuration['table_options']['table_fields']['google']['show'] == 1){
          $show3=1;
        }
      }
    }

    $logo2 = [
        'url'   => $url,
        'src'   => $src,
        'name'  => $name,
        'show'  => $show,
        'url2'  => $url2,
        'src2'  => $src2,
        'name2' => $name2,
        'show2' => $show2,
        'url3'  => $url3,
        'src3'  => $src3,
        'name3' => $name3,
        'show3' => $show3,
      ];

    $others2 = [
        '#logo' => $logo2,
        '#fields' => ['logo' => true],
      ];
    
    $this->cardBuildVarBuild($parameters, $others2);

    return $this->build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }


}
