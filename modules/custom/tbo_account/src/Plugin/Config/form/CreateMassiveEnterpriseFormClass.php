<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drupal\file\Entity\File;

/**
 *
 */
class CreateMassiveEnterpriseFormClass {
  protected $fileUsage;
  protected $currentUser;

  /**
   *
   */
  public function __construct(AccountInterface $current_user, DatabaseFileUsageBackend $file_usage) {

    $this->currentUser = $current_user;
    $this->fileUsage = $file_usage;

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_massive_enterprise';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $formState) {

    $form = [];

    // $form['#tree'] = TRUE;.
    $form['field_load'] = [
      '#type' => 'managed_file',
      '#title' => t('Cargar archivo'),
      '#description' => t('Debe cargar un archivo con el formato csv'),
      '#upload_location' => 'public://create_massive',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['#prefix'] = '<div class="btn-massive"><div class="col s12 m6 l6">';
    $form['actions']['#suffix'] = '</div></div>';

    $form['actions']['submit_data'] = [
      '#type' => 'submit',
      '#value' => t('Creación masiva'),
      '#attributes' => [
        'class' => ["waves-effect waves-light btn btn-success btn-primary"],
        'ng-disabled' => '!isValidFileExt()',
      ],
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState) {

    $fid = $formState->getValue('field_load')[0];
    $file = File::load($fid);
    $this->fileUsage->add($file, 'tbo_account', 'tbo_account', $this->currentUser->id(), 0);

    $file_saved = 'public://create_massive/' . $file->getFilename();

    $data_value = [
      'file' => $file,
      'url' => $file_saved,
      'module' => 'tbo_account',
      'id' => NULL,
      'count' => 0,
      'fid' => $fid,
    ];

    $service = '\Drupal\tbo_account\Services\MassiveImportEnterprisesService';

    // Set operations.
    $pre_batch = [
      'operations' => [
                [[$service, 'import_init'], []],
                [[$service, 'validate_userEnterprise'], []],
                [[$service, 'validate_data'], []],
                [[$service, 'create_enterprise'], []],
                [[$service, 'create_user'], []],
                [[$service, 'create_relation'], []],
                [[$service, 'send_messages'], []],
                [[$service, 'createLogs'], []],
                [[$service, 'set_import_log'], []],
      ],
      'finished' => [$service, 'batch_finished'],
      'progressive' => TRUE,
    ];
    // Set paginate batch.
    $batch = \Drupal::service('adf_import.import_service')->setImportBatch($pre_batch, $data_value);

    if ($batch === FALSE) {
      drupal_set_message(t('El archivo no contiene datos'));
    }
    else {
      array_unshift($batch['operations'], [[$service, 'pre_operations'], []]);
      // \Drupal::logger('$batch')->notice(print_r($batch, TRUE));.
      batch_set($batch);
    }
  }

}
