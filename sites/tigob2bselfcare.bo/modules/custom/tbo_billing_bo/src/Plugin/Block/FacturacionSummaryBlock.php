<?php

namespace Drupal\tbo_billing_bo\Plugin\Block;

use Drupal\tbo_billing\Plugin\Block\BillingSummaryBlock;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tbo_billing\Plugin\Config\Block\BillingSummaryBlockClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'FacturacionSummaryBlock' block..
 *
 * @Block(
 *  id = "facturacion_summary_block",
 *  admin_label = @Translation("Facturacion Summary block Bo"),
 * )
 */
class FacturacionSummaryBlock extends BillingSummaryBlock implements ContainerFactoryPluginInterface {
 
  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tbo_billing_bo.facturacion_summary_block')
    );
  }

}