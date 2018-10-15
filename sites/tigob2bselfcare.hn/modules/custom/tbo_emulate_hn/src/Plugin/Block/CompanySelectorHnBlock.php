<?php

namespace Drupal\tbo_emulate_hn\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;

/**
 * Provides a 'CompanySelectorBlock' block.
 *
 * @Block(
 *  id = "company_selector_block_hn",
 *  admin_label = @Translation("Company selector block Hn"),
 * )
 */
class CompanySelectorHnBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $uid = \Drupal::currentUser()->id();
    $query = \Drupal::database()->select('company_user_relations_field_data', 'userCompany');
    $query->join('company_entity_field_data', 'company', 'userCompany.company_id = company.id');
    $query->join('users_field_data', 'user', 'userCompany.users = user.uid');
    $query->addField('company', 'name');
    $query->addField('userCompany', 'company_id');
    $query->addField('user', 'mail');
    $query->condition('userCompany.users', $uid);
    if (isset($_SESSION['masquerading'])) {
      $account = User::load($_SESSION['old_user']);
      $roles = $account->getRoles();
      if (in_array('tigo_admin', $roles)) {
        $query->condition('userCompany.associated_id', $_SESSION['old_user']);
      }
    }

    $companies = $query->execute()->fetchAll();

    $config = \Drupal::config("tbo_general.companyselector");
    $build = [];

    $file = file_load($config->get('avatar')[0]);
    $build['#data']['avatar'] = [
      'avatar' => $config->get('show_avatar'),
      'src' => file_create_url($file->getFileUri()),
    ];

    $build['#data']['company'] = [
      'name' => $config->get('show_name'),
      'mail' => $config->get('show_mail'),
    ];

    $build['#data']['companies'] = $companies;

    $build['#data']['load_more'] = [
      'url' => $config->get('url'),
      'label' => $config->get('label'),
    ];

    $build['#theme'] = 'company_selector_hn';

    $build['#attached'] = array(
        'library' => array(
          'tbo_emulate_hn/company-selector'
        ),
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
