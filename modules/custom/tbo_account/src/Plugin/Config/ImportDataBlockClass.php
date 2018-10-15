<?php

namespace Drupal\tbo_account\Plugin\Config;

use Drupal\tbo_account\Plugin\Block\ImportDataBlock;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 *
 */
class ImportDataBlockClass {

  protected $instance;
  protected $fileUsage;
  protected $configuration;

  /**
   * ImportDataBlockClass constructor.
   *
   * @param \Drupal\file\FileUsage\DatabaseFileUsageBackend $fileUsageBackend
   */
  public function __construct(DatabaseFileUsageBackend $fileUsageBackend) {
    $this->fileUsage = $fileUsageBackend;
  }

  /**
   * @param \Drupal\tbo_account\Plugin\Block\ImportDataBlock $instance
   * @param $config
   */
  public function setConfig(ImportDataBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'others_display' => [
        'table_fields' => [
          'select' => [
            'title' => t('Opciones de descarga'),
            'options' => ['txt' => 'txt', 'csv' => 'csv', 'xls' => 'xls'],
            'not_update_label' => 1,
            'label' => '',
            'service_field' => 'select',
            'show' => 1,
          ],
          'title' => [
            'label' => t('Importación masiva de empresas'),
            'title' => t('Título del bloque'),
            'sevice_field' => 'title',
            'show' => 1,
          ],
          'link_file' => [
            'title' => t('Fichero de ejemplo'),
            'sevice_field' => 'link_file',
            'show' => 1,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'import_datail' => [
            // 'label' => t('Detalles de importación'),.
            'url' => '/',
            'title' => t('Detalles de importación'),
            // 'update_label' => 1,.
            'service_field' => 'import_detail',
            'show' => 1,
            'active' => 0,
          ],
          'massive_import' => [
            'label' => t('Importación masiva'),
            'title' => t('Importación masiva'),
            'update_label' => 1,
            'service_field' => 'massive_import',
            'show' => 1,
            'active' => 0,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();
    // Modify others_display table headers.
    $form['others_display']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'), ''];

    // Unset show table header.
    unset($form['others_display']['table_fields']['file_input']['show']);
    unset($form['others_display']['table_fields']['table_fields']);

    $form['buttons']['table_fields']['import_datail']['active']['#disabled'] = TRUE;
    $form['buttons']['table_fields']['massive_import']['active']['#disabled'] = TRUE;
    $form['others_display']['table_fields']['link_file']['label'] = [
      '#type' => 'managed_file',
      '#name' => 'my_file',
      '#title' => t('Selecione el fichero de ejemplo'),
      '#description' => t('Solo formato csv'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#upload_location' => 'public://create_massive/example_file',
      '#default_value' => [$this->configuration['link_file']],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['table_options'] = $form_state->getValue(['table_options']);
    $this->configuration['filters_options'] = $form_state->getValue(['filters_options']);
    $this->configuration['others_display'] = $form_state->getValue(['others_display']);
    $this->configuration['buttons'] = $form_state->getValue(['buttons']);
    $this->configuration['others'] = $form_state->getValue(['others']);

    $fid = $form_state->getValue(['others_display', 'table_fields', 'link_file', 'label'])[0];
    $this->configuration['link_file'] = $fid;
    $file = File::load($fid);
    $file_uri = $file->getFileUri();
    if (strpos($file_uri, '_0.csv') !== FALSE) {
      $file_uri = str_replace('_0.csv', '.csv', $file_uri);
      $query = \Drupal::database()->select('file_managed', 'f');
      $query->addField('f', 'fid', 'fid');
      $query->condition('uri', $file_uri, '=');
      $new = $query->execute()->fetchObject();
      if ($new) {
        $old_fid = $new->fid;
        file_delete($old_fid);
      }
    }
    $file->save();
    $this->clearDirectory($fid);
    $this->fileUsage->add($file, 'tbo_account', 'tbo_account', 1);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('directive', 'data-ng-import-data');
    $this->instance->setValue('config_name', 'importMassive');
    $this->instance->setValue('class', 'massive-import');

    // Get file name and set url to download example file for import.
    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account\Form\CreateMassiveEnterpriseForm');
    if (!empty($this->configuration['link_file'])) {
      $name = File::load($this->configuration['link_file'])->getFilename();
      $file = '/adf_core/download-example/' . $name . '/create_massive&example_file';
    }
    else {
      $file = '';
    }

    $parameters = [
      'library' => 'tbo_account/import-data',
    ];

    $others = [
      '#theme' => 'import_masive_enterprise',
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#file_input' => $this->configuration['others_display']['table_fields']['file_input'],
      '#select' => $this->configuration['others_display']['table_fields']['select'],
      '#title' => $this->configuration['others_display']['table_fields']['title'],
      '#form' => $form,
      '#directive' => $this->instance->getValue('directive'),
      '#file' => $file,
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    $other_config = [];
    $config_block = $this->instance->cardBuildConfigBlock('/adf/process_data?_format=json', $other_config);
    $this->instance->cardBuildAddConfigDirective($config_block);

    return $this->instance->getValue('build');
  }

  /**
   * @param $fid_exclude
   */
  private function clearDirectory($fid_exclude) {
    $uri = 'public://create_massive/example_file';
    $query = \Drupal::database()->select('file_managed', 'f');
    $query->addField('f', 'fid', 'fid');
    $query->condition('uri', "%$uri%", 'like');
    $query->condition('fid', $fid_exclude, '<>');
    $file = $query->execute()->fetchObject();
    if ($file) {
      file_delete($file->fid);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
