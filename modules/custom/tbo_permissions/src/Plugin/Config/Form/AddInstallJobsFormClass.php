<?php

namespace Drupal\tbo_permissions\Plugin\Config\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_permissions\Form\AddInstallJobsForm;

/**
 * Manage config a 'AddInstallJobsFormClass' block.
 */
class AddInstallJobsFormClass {

  protected $instance;

  /**
   * AddInstallJobsFormClass constructor.
   */
  public function __construct() {
    $this->configStore = \Drupal::config('tbo_permissions.addinstalljobs');
  }

  /**
   * Create form instance.
   *
   * @param \Drupal\tbo_permissions\Form\AddInstallJobsForm $form
   *   Form instance.
   */
  public function createInstance(AddInstallJobsForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_install_jobs_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state) {
    $form = [];
    $jobsCount = 0;
    $companiesTotal = 0;
    $batchCount = 1;

    // We check if a "session" in this form it's already started.
    $sessionStarted = $this->configStore->get('session_started');
    if ($sessionStarted == 'true') {
      $jobsCount = $this->configStore->get('jobs_count');
      $companiesTotal = $this->configStore->get('companies_total');
      $batchCount = $this->configStore->get('batch_count');
    }
    else {
      // Session starts.
      // Get and set companies total.
      $database = \Drupal::database();
      $queryCompany = $database->select('company_entity_field_data', 'company')
        ->fields('company', ['id']);
      $companiesTotal = $queryCompany->countQuery()->execute()->fetchField();

      // Save the new values.
      $configStore = \Drupal::service('config.factory')
        ->getEditable('tbo_permissions.addinstalljobs');
      $configStore
        ->set('session_started', 'true')
        ->set('jobs_count', '0')
        ->set('batch_count', '1')
        ->set('companies_batch_size', '100')
        ->set('companies_total', $companiesTotal)
        ->save();
    }

    $form['#prefix'] = '<div class="formselect">';
    $form['#suffix'] = '</div>';
    $form['#tree'] = TRUE;

    $form['companies_total'] = [
      '#type' => 'textfield',
      '#title' => t('Total de Empresas:'),
      '#maxlength' => 10,
      '#size' => 13,
      '#disabled' => TRUE,
      '#default_value' => $companiesTotal,
    ];

    $form['jobs_count'] = [
      '#type' => 'textfield',
      '#title' => t('Tareas Programadas:'),
      '#maxlength' => 10,
      '#size' => 13,
      '#disabled' => TRUE,
      '#default_value' => $jobsCount,
    ];

    $form['batch_count'] = [
      '#type' => 'textfield',
      '#title' => t('Siguiente Lote de tareas No:'),
      '#maxlength' => 10,
      '#size' => 13,
      '#disabled' => TRUE,
      '#default_value' => $batchCount,
    ];

    $form['button-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-wrapper-button', 'col', 'input-field', 's12'],
      ],
    ];

    $form['button-wrapper']['submit'] = [
      '#type' => 'button',
      '#value' => t('Programar siguiente lote'),
      '#attributes' => [
        'class' => ['btn', 'btn-primary'],
      ],
      '#ajax' => [
        'callback' => [$this, 'addInstallJobsBatchCallback'],
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Creando tareas de instalación del módulo TBO Permissions.'),
        ],
      ],
    ];

    $form['button-wrapper']['reset'] = [
      '#type' => 'button',
      '#value' => t('Resetear sesion de creación de Jobs'),
      '#attributes' => [
        'class' => ['btn', 'btn-primary'],
      ],
      '#ajax' => [
        'callback' => [$this, 'resetJobsSessionCallback'],
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Reseteando los valores de sesión de creación de jobs y limpiando la cola de jobs.'),
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface &$form_state) {
  }

  /**
   * Add install jobs batch.
   *
   * @param array $form
   *   Form data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax Response.
   */
  public function addInstallJobsBatchCallback(array &$form, FormStateInterface $form_state) {
    $configStore = \Drupal::service('config.factory')
      ->getEditable('tbo_permissions.addinstalljobs');

    // Instantiate an AjaxResponse Object to return.
    $ajaxResponse = new AjaxResponse();

    $batchCount = $configStore->get('batch_count');
    $jobsCount = $configStore->get('jobs_count');
    $companiesBatchSize = $configStore->get('companies_batch_size');

    $startRange = ($batchCount - 1) * $companiesBatchSize;

    try {
      $queueFactory = \Drupal::service('queue');
      $companiesQueue = $queueFactory->get('company_permissions_queue_processor');

      $database = \Drupal::database();
      $queryCompany = $database->select('company_entity_field_data', 'company')
        ->fields('company', ['id'])
        ->orderBy('company.id', 'ASC')
        ->range($startRange, $companiesBatchSize);

      $companiesResult = $queryCompany->execute();
      $jobsCounter = 0;
      foreach ($companiesResult as $company) {
        // Add job to the module installation Queue.
        $companiesQueue->createItem($company->id);
        $jobsCounter++;
      }

      // Now we save the new state of config values.
      $jobsCount += $jobsCounter;
      if ($jobsCounter > 0) {
        $batchCount++;
      }
      $configStore
        ->set('jobs_count', $jobsCount)
        ->set('batch_count', $batchCount)
        ->save();

      // Set the new values in the inputs.
      $ajaxResponse->addCommand(new InvokeCommand('#edit-jobs-count', 'val', [
        $jobsCount,
      ]));
      $ajaxResponse->addCommand(new InvokeCommand('#edit-batch-count', 'val', [
        $batchCount,
      ]));
    }
    catch (\Exception $e) {
    }

    // Return the AjaxResponse Object.
    return $ajaxResponse;
  }


  /**
   * Reset the configuration values.
   *
   * @param array $form
   *   Form data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax Response.
   */
  public function resetJobsSessionCallback(array &$form, FormStateInterface $form_state) {
    // Instantiate an AjaxResponse Object to return.
    $ajaxResponse = new AjaxResponse();

    $configStore = \Drupal::service('config.factory')
      ->getEditable('tbo_permissions.addinstalljobs');

    // Get and set companies total.
    $database = \Drupal::database();
    $queryCompany = $database->select('company_entity_field_data', 'company')
      ->fields('company', ['id']);
    $companiesTotal = $queryCompany->countQuery()->execute()->fetchField();

    $configStore
      ->set('companies_total', $companiesTotal)
      ->set('jobs_count', '0')
      ->set('batch_count', '1')
      ->save();

    // ValCommand does not exist, so we can use InvokeCommand.
    $ajaxResponse->addCommand(new InvokeCommand('#edit-jobs-count', 'val', [
      0,
    ]));
    $ajaxResponse->addCommand(new InvokeCommand('#edit-batch-count', 'val', [
      1,
    ]));
    $ajaxResponse->addCommand(new InvokeCommand('#edit-companies-total', 'val', [
      $companiesTotal,
    ]));

    try {
      // Clean the queue.
      $queueFactory = \Drupal::service('queue');
      $companiesQueue = $queueFactory->get('company_permissions_queue_processor');
      $companiesQueue->deleteQueue();
    }
    catch (\Exception $e) {
    }

    // Return the AjaxResponse Object.
    return $ajaxResponse;
  }

}
