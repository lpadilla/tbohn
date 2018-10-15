<?php

namespace Drupal\tbo_adminmenu_bo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Access\AccessResult;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;
use Drupal\tbo_general\CardBlockBase;

/**
 * Provides a 'MenuAdminEmpresaBoBlock' block.
 *
 * @Block(
 *  id = "menu_admin_empresa_bo_block",
 *  admin_label = @Translation("Menu Administrador Empresa BO"),
 * )
 */
class MenuAdminEmpresaBoBlock extends CardBlockBase {
	# configuracion por defecto del bloque para los campos
	public function defaultConfiguration() {
	    return array(
	      'filters_fields' => [],
	      'others_display' => [],
	      'buttons' => [],
	      'others' => [
		    'config' => [
			    'etiqueta' => [
		          'value' => $this->t('Agregar Cuenta'),
		        ],
		        'url' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'],
		    ],
	      ],
	    );
	}

	public function blockForm($form, FormStateInterface $form_state) {
		$field['etiqueta'] = array(
	      '#type' => 'textfield',
	      '#title' => t('Etiqueta de redireccion'),
	      '#default_value' => $this->configuration['others']['config']['etiqueta']['value'],
	      '#maxlength' => 128,
	    );

		$field['url'] = array(
	      '#type' => 'textfield',
	      '#title' => t('Url de redireccion del label'),
	      '#default_value' => $this->configuration['others']['config']['url']
	    );

	    $form = $this->cardBlockForm($field);

	    return $form;
	}

	
	public function blockSubmit($form, FormStateInterface $form_state) {	
		$values = $form_state->getValue(['others', 'config']);
		$this->configuration['others']['config']['url'] = $values['url'];
		$this->configuration['others']['config']['etiqueta']['value'] = $values['etiqueta'];

	}
	

  	public function build() {
  		
  		#Consulta en BD para obtener la relaion del usuario con la empresa que administra
    	$uid = \Drupal::currentUser()->id(); # obtiene el id del usuario loguedo
    	$account_fields = \Drupal::currentUser()->getAccount(); #obtiene valores del usurio logueado
    	$query = \Drupal::database()->select('company_user_relations_field_data', 'userCompany');
	    $query->join('users_field_data', 'user', 'userCompany.users = user.uid');
	    $query->addField('userCompany', 'name');
	    $query->addField('userCompany', 'company_id');
	    $query->condition('userCompany.users', $uid);
	    $query->condition('userCompany.company_id', $_SESSION['company']['id'],'<>');
	    $query->addField('userCompany', 'created');
	    $query->orderBy('userCompany.created', 'DESC');
	    
	    if (isset($_SESSION['masquerading'])){
	      $account = User::load($_SESSION['old_user']);
	      $roles = $account->getRoles();
	      if(in_array('tigo_admin', $roles)){
	        $query->condition('userCompany.associated_id', $_SESSION['old_user']);
	      }
	    }

	    $companies = $query->execute()->fetchAll();


	    # Otra consula para obtener los valores del usuario con una unica empresa y datos personales a mostrar
	    $query2 = \Drupal::database()->select('company_user_relations_field_data', 'userCompany');
	    $query2->join('users_field_data', 'user', 'userCompany.users = user.uid');
	    $query2->addField('userCompany', 'name');
	    $query2->addField('userCompany', 'company_id');
	    $query2->addField('userCompany', 'created');
	    $query2->addField('user', 'mail');
	    $query2->condition('userCompany.users', $uid);
	    $query2->condition('userCompany.company_id', $_SESSION['company']['id']);
	    $query2->orderBy('userCompany.created', 'DESC');
	    $query2->range(0,1);

	    $companie_user = $query2->execute()->fetchAll();

	    $config = \Drupal::config("tbo_general.companyselector");
	    $build = [];

	    if (!empty($account_fields->full_name)){
	    	$build['#data']['username']= $account_fields->full_name;
	    } else {
	    	$build['#data']['username']= $account_fields->name;
	    }
	    
	    $build['#data']['mail']= $account_fields->init;

	    $path= file_create_url('public://');
	    
	    

	    $user = \Drupal\user\Entity\User::load($uid);
	      $pic = $user->get('user_picture')->getValue();
	      if ($pic){
		    	  $picture= $user->get('user_picture')->entity->url();
	      }else{
	        $field = \Drupal\field\Entity\FieldConfig::loadByName('user', 'user', 'user_picture');
	        $default_image = $field->getSetting('default_image');
	        if (!empty($default_image['uuid'])){
	          $file = \Drupal::entityManager()->loadEntityByUuid('file', $default_image['uuid']);
	          $picture = file_create_url($file->getFileUri());
	        }else{
	          $picture = "http://i.stack.imgur.com/34AD2.jpg";
	        }

	      }


	    $build['#data']['avatar'] = [
	      'avatar' => $config->get('show_avatar'),
	      'src' => $picture,
	    ];

	    $build['#data']['company'] = [
	      'name' => $config->get('show_name'),
	      'mail' => $config->get('show_mail'),
	    ];

	    # Si existe compañias que administra el usuario distinta a la que ha escogiddo,para mostrar en menu
	    if(count($companies) > 0){
	    $build['#data']['companies'] = $companies; # resultado de la 1era consulta en un array para recorrerlo en el template
		}

	    $build['#data']['companie_user'] = $companie_user;# datos del usuario administrador con compañia seleccionada

	    $build['#data']['load_more'] = [
	      'url' => $config->get('url'),
	      'label' => $config->get('label'),
	    ];

	    # Asigna el valor del campo etiqueta al build para usarlo en la plantilla
	    $build['#data']['etiqueta']=$this->configuration['others']['config']['etiqueta']['value'];

	    $build['#theme'] = 'admin_empresa_menu';# se define la planntilla a usar

	    $build['#attached'] = array(
        'library' => array('tbo_adminmenu_bo/admin-empresa-menu',
	        )
	    );

	    return $build;
  
	}

	/**
	* {@inheritdoc}
	*/
	public function getCacheMaxAge() {
		return 0;
	}

}