<?php

namespace Drupal\tbo_billing_hn\Plugin\Block;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'RegisterMailInvoicesHnBlock' block.
 *
 * @Block( 
 *  id = "register_mail_invoices_hn_block",
 *  admin_label = @Translation("Registro de correos para factura electrónica1"),
 * )
 */
class RegisterMailInvoicesHnBlock extends CardBlockBase {  
  /**
  * {@inheritdoc}
  */
  public function build(){   
    return [
      '#theme' => 'register-mail-invoices-hn',
      '#test' => 'prueba de bloque',
      '#attached' => array(
        'library' => array(
          'tbo_billing_hn/register-mail-invoice-form-hn'
        ),
      ),
    ];
  }
}