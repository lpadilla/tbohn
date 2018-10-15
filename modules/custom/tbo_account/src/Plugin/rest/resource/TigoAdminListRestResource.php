<?php

namespace Drupal\tbo_account\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "tigo_admin_list_resource",
 *   label = @Translation("Tigo admin list resource"),
 *   uri_paths = {
 *     "canonical" = "/api/tigo-admin-list",
 *      "https://www.drupal.org/link-relations/create" = "/api/tigo-admin-list"
 *   }
 * )
 */
class TigoAdminListRestResource extends ResourceBase {

  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                                                            $plugin_id,
                                                            $plugin_definition,
                                                            array $serializer_formats,
                                                            LoggerInterface $logger,
                                                            AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
    $configuration,
    $plugin_id,
    $plugin_definition,
    $container->getParameter('serializer.formats'),
    $container->get('logger.factory')->get('tbo_account'),
    $container->get('current_user')
    );
  }

  /**
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_account.manage_users_tigo_admin_rest')->get($this->currentUser);

  }

  /**
   * @param $params
   * @return \Drupal\rest\ResourceResponse
   */
  public function post($params) {
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_account.manage_users_tigo_admin_rest')->post($this->currentUser, $params);
  }

}
