<?php

namespace Drupal\tbo_billing_hn\Plugin\Block;

use  Drupal\tbo_billing\Plugin\Block\InvoiceHistoryBlock;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tbo_billing\Plugin\Config\Block\InvoiceHistoryBlockClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'InvoiceHistoryHnBlock' block..
 *
 * @Block(
 *  id = "invoice_history_hn_block",
 *  admin_label = @Translation("Invoice history block HN"),
 * )
 */
class InvoiceHistoryHnBlock extends InvoiceHistoryBlock implements ContainerFactoryPluginInterface {
 
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
      $container->get('tbo_billing_hn.invoice_history_hn_block')
    );
  }

}
