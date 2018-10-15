<?php

namespace Drupal\tbo_billing_hn\Plugin\Block;

use  Drupal\tbo_billing\Plugin\Block\SetUpInvoiceDeliveryBlock;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tbo_billing\Plugin\Config\Block\SetUpInvoiceDeliveryBlockClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SetUpInvoiceDeliveryHnBlock' block.
 *
 * @Block(
 *  id = "set_up_invoice_delivery_hn_block",
 *  admin_label = @Translation("Configurar envio de factura HN"),
 * )
 */
class SetUpInvoiceDeliveryHnBlock extends SetUpInvoiceDeliveryBlock implements ContainerFactoryPluginInterface {



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
      $container->get('tbo_billing.set_up_invoice_delivery_block')
    );
  }

}  

  

 

  

 





 