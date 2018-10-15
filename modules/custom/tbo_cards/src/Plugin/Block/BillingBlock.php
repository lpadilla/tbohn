<?php

namespace Drupal\tbo_cards\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'BillingBlock' block.
 *
 * @Block(
 *  id = "billing_block",
 *  admin_label = @Translation("Mi factura"),
 * )
 */
class BillingBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    /*
    return array(

    'url_payment' => '',
    'show_details_link' => 0,
    'path_details_link' => '',
    'show_pdf_button' => 1,
    'show_payment_button' => 0,
    'fields_table' =>  [
    'show_total' => ['title' => $this->t("Total a pagar"), 'service_field' => 'balance', 'show' => 1, 'layout' => 'destacado','padding' => '', 'weight' => 1, 'class' => ''],
    'show_period' => ['title' => $this->t("Periodo"), 'service_field' => 'period_string', 'show' => 1, 'layout' => '1-columns', 'padding' => '', 'weight' => 2, 'class' => ''],
    'show_last_update' => ['title' => $this->t("Actualizado"), 'service_field' => 'last_update', 'show' => 1, 'layout' => '1-columns', 'padding' => '', 'weight' => 3, 'class' => ''],
    'show_expiration' =>['title' => $this->t("Vencimiento"), 'service_field' => 'expiration', 'show' => 1, 'layout' => '2-columns', 'padding' => '', 'weight' => 4, 'class' => ''],
    'show_balance_due' => ['title' => $this->t("Valor última factura"), 'service_field' => 'lastAmount', 'layout' => '2-columns', 'show' => '', 'padding' => 1, 'weight' => 5, 'class' => ''],
    'show_print_date' => ['title' => $this->t("Fecha impresión de Factura"), 'service_field' => 'printDate', 'layout' => '2-columns', 'show' => '', 'padding' => 1, 'weight' => 6, 'class' => ''],
    'show_cycle' => ['title' => $this->t("Tu plan se acredita"), 'service_field' => 'cycle', 'show' => 1, 'layout' => '2-columns', 'padding' => '', 'weight' => 7, 'class' => ''],
    'show_last_invoice' => ['title' => $this->t("Número de Factura"), 'service_field' => 'invoiceNumber', 'layout' => '2-columns', 'show' => '', 'padding' => 1, 'weight' => 8, 'class' => ''],
    'show_unpaidInvoices_number' => ['title' => $this->t("Estado"), 'service_field' => 'unpaidInvoices', 'layout' => '2-columns', 'show' => '', 'padding' => 1, 'weight' => 9, 'class' => ''],
    'show_payment_reference' => ['title' => $this->t("Referencia de pago"), 'service_field' => 'contract', 'layout' => '2-columns', 'show' => '', 'padding' => 1, 'weight' => 10, 'class' => ''],
    ]
    );
     */
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    /*
    $form['options'] = array(
    '#type' => 'details',
    '#title' => $this->t('Options'),
    '#open' => TRUE,
    );
    $form['options']['fields'] = array(
    '#type' => 'table',
    '#header' => array( t('Field'), t('Show'), t('Estilo'), t('Espaciado'), t('Weight'), ''),
    '#empty' => t('There are no items yet. Add an item.'),
    '#tabledrag' => array(
    array(
    'action' => 'order',
    'relationship' => 'sibling',
    'group' => 'fields-order-weight',
    ),
    ),
    );

    $fields = $this->configuration['fields_table'];
    uasort($fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

    foreach ($fields as $id => $entity) {
    // TableDrag: Mark the table row as draggable.
    $form['options']['fields'][$id]['#attributes']['class'][] = 'draggable';
    // TableDrag: Sort the table row according to its existing/configured weight.
    $form['options']['fields']['#weight'] = $entity['weight'];

    // Some table columns containing raw markup.
    $form['options']['fields'][$id]['label'] = array(
    '#plain_text' => $entity['title'],
    );

    $form['options']['fields'][$id]['show'] = array(
    '#type' => 'checkbox',
    '#default_value' => $entity['show'],
    );

    $form['options']['fields'][$id]['layout'] = array(
    '#type' => 'select',
    '#options' => array(
    'destacado' => $this->t('Destacado'),
    '1-columns' => $this->t('Una columnas'),
    '2-columns' => $this->t('Dos columnas'),
    ),
    '#default_value' => $entity['layout'],
    );

    $form['options']['fields'][$id]['padding'] = array(
    '#type' => 'select',
    '#options' => array(
    '' => $this->t('Ninguno'),
    'top-padding' => $this->t('Superior'),
    'bottom-padding' => $this->t('Inferior'),
    ),
    '#default_value' => $entity['padding'],
    );

    // TableDrag: Weight column element.
    $form['options']['fields'][$id]['weight'] = array(
    '#type' => 'weight',
    '#title' => t('Weight for @title', array('@title' => $entity['title'] )),
    '#title_display' => 'invisible',
    '#default_value' => $entity['weight'],
    // Classify the weight element for #tabledrag.
    '#attributes' => array('class' => array('fields-order-weight')),
    );

    $form['options']['fields'][$id]['service_field'] = array(
    '#type' => 'hidden',
    '#value' => $entity['service_field'],
    //'#title' => $config_fields[$id]['service_field'],
    );

    }


    $form['actions'] = array(
    '#type' => 'details',
    '#title' => $this->t('Actions'),
    '#open' => TRUE,
    );
    $form['actions']['show_payment_button'] = array(
    '#type' => 'checkbox',
    '#title' => $this->t("Mostrar boton pagar"),
    '#default_value' => $this->configuration['show_payment_button'],
    );
    $form['actions']['url_payment'] = array(
    '#type' => 'textfield',
    '#title' => $this->t("Url de pagos"),
    '#default_value' => $this->configuration['url_payment'],
    '#states' => array(
    'visible' => array(
    ':input[name="settings[actions][show_payment_button]"]' => array('checked' => TRUE),
    ),
    ),
    );
    $form['actions']['show_pdf_button'] = array(
    '#type' => 'checkbox',
    '#title' => $this->t("Mostrar boton decargar PDF"),
    '#default_value' => $this->configuration['show_pdf_button'],
    );
    $form['actions']['show_details_link'] = array(
    '#type' => 'checkbox',
    '#title' => $this->t("Mostrar enlace Detalles"),
    '#default_value' => $this->configuration['show_details_link'],
    );
    $form['actions']['path_details_link'] = array(
    '#type' => 'textfield',
    '#title' => $this->t("Url de detalles"),
    '#default_value' => $this->configuration['path_details_link'],
    '#states' => array(
    'visible' => array(
    ':input[name="settings[actions][show_details_link]"]' => array('checked' => TRUE),
    ),
    ),
    );

    return $form;
     */
    // TODO: Change the autogenerated stub.
    return parent::blockForm($form, $form_state);
  }

  /**
   * Function access(AccountInterface $account, $return_as_object = FALSE)
   * {
   * return $account->isAuthenticated();
   * }.
   **/

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    /*
    $this->configuration['fields_table'] = $form_state->getValue(['options','fields']);

    $this->configuration['show_details_link'] = $form_state->getValue(['actions','show_details_link']);
    $this->configuration['path_details_link'] = $form_state->getValue(['actions','path_details_link']);
    $this->configuration['show_payment_button'] = $form_state->getValue(['actions','show_payment_button']);
    $this->configuration['show_pdf_button'] = $form_state->getValue(['actions','show_pdf_button']);
    $this->configuration['url_payment'] = $form_state->getValue(['actions','url_payment']);
     */
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $uuid = $this->configuration['uuid'];

    /*
    $config_block = array(
    'url' => '/api/account/'. $account->id .'/billing?_format=json',
    );
     */

    /*
    $fields = $this->configuration['fields_table'];

    uasort($fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

    $data = array();
    foreach ($fields as $key_field => $field){
    if($field['show'] == 1){
    $data[$key_field]['label'] = $field['title'];

    $classes = [ "field-".$field['service_field'], $field['class'], $field['padding']];
    $data[$key_field]['class'] = implode(" ", $classes);
    $data[$key_field]['layout'] = $field['layout'];
    $data[$key_field]['service_field'] = $field['service_field'];
    unset($classes);
    } else {
    unset($fields[$key_field]);
    }
    }
     */

    $build = [
      '#theme' => 'card_billing',
      '#uuid' => $uuid,
      '#config' => $this->configuration,
      '#fields' => [],
      '#attached' => [
        'library' => [
          'tbo_cards/billing',
        ],
      ],
      '#plugin_id' => $this->getPluginId(),
    ];

    $build['#cache']['max-age'] = 0;

    // $build['#attached']['drupalSettings']['billingBlock'][$uuid] = $config_block;.
    return $build;
  }

}
