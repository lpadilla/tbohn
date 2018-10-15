<?php

namespace Drupal\tbo_billing\Plugin\Block;

use Drupal\tbo_general\CardBlockBase;

/**
 * Provides a 'ResponsePaymentBlock' block.
 *
 * @Block(
 *  id = "response_payment_block",
 *  admin_label = @Translation("Bloque respuesta de pago"),
 * )
 */
class ResponsePaymentBlock extends CardBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];
    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'response_payment',
      'library' => 'tbo_account/companies-list',
    ];

    if (isset($_SESSION['block_info']['type'])) {
      switch ($_SESSION['block_info']['type']) {

        case 'DECLINED':
          $data = [
            'title' => $this->t('Su pago no se ha completado'),
            'body' => $this->t('Algo ha salido mal y no se ha podido completar su pago, por favor intente nuevamente más tarde'),
            'url' => $_SESSION['block_info']['url'],
            'action' => isset($_SESSION['block_info']['action']) ? $_SESSION['block_info']['action'] : 0,
            'class' => 'alert-danger ',
          ];
          break;

        case 'PENDING':
          $data = [
            'title' => $this->t('Su pago está pendiente'),
            'body' => $this->t('La factura que intenta pagar está en estado pendiente'),
            'class' => 'alert-pending',
          ];
          break;

        case 'APPROVED':
          $data = [
            'title' => $this->t('Factura pagada'),
            'body' => $this->t('Su factura ha sido pagada con éxito'),
            'class' => 'alert-success',
          ];
          break;

        case 'CANCEL':
          $data = [
            'title' => $this->t('Su pago ha sido cancelado'),
            'body' => $this->t('No se ha podido completar su pago, por favor intente nuevamente más tarde'),
            'class' => 'alert-danger',
          ];
          break;

        case 'ERROR-VALUE':
          $data = [
            'title' => $this->t('Error'),
            'body' => $this->t('Valor de la factura inválido.'),
            'class' => 'alert-danger',
          ];
          break;

        case 'ERROR-PROCESS':
          $data = [
            'title' => $this->t('Error'),
            'body' => $this->t('No pudimos iniciar el proceso de pago, por favor intenta nuevamente.'),
            'class' => 'alert-danger',
          ];
          break;
      }
    }

    // Parameter additional.
    $others = [
      '#data' => $data,
    ];

    $this->cardBuildVarBuild($parameters, $others);
    return $this->build;
  }

}
