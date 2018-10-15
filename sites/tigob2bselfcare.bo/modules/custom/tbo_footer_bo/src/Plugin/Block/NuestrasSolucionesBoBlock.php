<?php

namespace Drupal\tbo_footer_bo\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
//use Drupal\tbo_general\CardBlockBase;
use Drupal\tbo_general\Plugin\Block\SocialNetworksBlock;

/**
 * Provides a 'NuestrasSolucionesBoBlock' block.
 *
 * @Block(
 *  id = "nuestras_soluciones_bo_block",
 *  admin_label = @Translation("Nuestras Soluciones BO"),
 * )
 */
class NuestrasSolucionesBoBlock extends SocialNetworksBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'filters_fields' => [],
      'table_fields' => [
        'voz' => [
          'label' => $this->t('Voz'),
          'image' => '',
          'url' => file_create_url('public://'), 
          'show' => 1,
          'weight' => 1,
          'service_field' => 'voz',
        ],
        'conectividad' => [
          'label' => $this->t('Conectividad'),
          'image' => '', 
          'url' => file_create_url('public://'),
           'show' => 1, 'weight' => 1, 'service_field' => 'conectividad',
        ],
        'nube' => [
          'label' => $this->t('En la Nube'),
          'image' => '', 
          'url' => file_create_url('public://'),
          'show' => 1, 'weight' => 1, 'service_field' => 'nube',
        ],
        'avanzadas' => [
          'label' => $this->t('Avanzadas'),
          'image' => '', 
          'url' => file_create_url('public://'),
          'show' => 1, 'weight' => 1, 'service_field' => 'avanzadas',
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
  public function build() {  //FormStateInterface $form_state
    //Set data uuid, filters_fields, table_fields
    $this->cardBuildHeader($filters = FALSE, $columns = FALSE);
    
    #Se construye la variable $build con los datos que se necesitan en el tema
    $parameters = [
      'theme' => 'footer_soluciones',
      'library' => 'tbo_footer_bo/nuestras-solucionnes',
    ];

    $src = '';

    # Verificación del campo imagen para cada caso de Redes Sociales definidas en la configuracion 
    $path= file_create_url('public://');

    $name=$name2=$name3=$name4=null;
    $src=$src2=$src3=$src4=null;
    $url=$url2=$url3=$url4=null;
    
    
    $soluciones =array();
    $count=0;
    foreach ($this->configuration['table_fields'] as $id => $entity) {
      
      if (!empty($this->configuration['table_fields'][$id]['image'][0])){
        $count=$count+1;
        $file = \Drupal\file\Entity\File::load($this->configuration['table_fields'][$id]['image'][0]);
        if ($file){
          $url="url".$count;$src="src".$count;$name="name".$count;
          $soluciones['url'.$count]=$this->configuration['table_fields'][$id]['url'];
          $soluciones['src'.$count]=$path.$file->getFilename();
          $soluciones['name'.$count]=$this->configuration['table_fields'][$id]['label'];
         
        }
      }
    }
    $others2 = [
        '#logo' => $soluciones,
        '#fields' => ['logo' => true],
      ];
    
    $this->cardBuildVarBuild($parameters, $others2);

    return $this->build;

    
  }

}

?>