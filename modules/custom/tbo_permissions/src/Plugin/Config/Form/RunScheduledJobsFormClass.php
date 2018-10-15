<?php

namespace Drupal\tbo_permissions\Plugin\Config\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_permissions\Form\RunScheduledJobsForm;
use Drupal\Core\Queue\SuspendQueueException;

/**
 * Manage config a 'RunScheduledJobsFormClass' block.
 */
class RunScheduledJobsFormClass {

  protected $instance;

  /**
   * RunScheduledJobsFormClass constructor.
   */
  public function __construct() {
    $this->configStore = \Drupal::config('tbo_permissions.runscheduledjobs');
  }

  /**
   * Create form instance.
   *
   * @param \Drupal\tbo_permissions\Form\RunScheduledJobsForm $form
   *   Form instance.
   */
  public function createInstance(RunScheduledJobsForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'run_scheduled_jobs_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state) {
    $form = [];
    $remainingJobsCount = 0;
    $runJobsCount = '0';

    // Save the new values.
    $configStore = \Drupal::service('config.factory')
      ->getEditable('tbo_permissions.runscheduledjobs');
    $configStore
      ->set('run_jobs_count', $runJobsCount)
      ->save();

    $queueFactory = \Drupal::service('queue');
    $companiesQueue = $queueFactory->get('company_permissions_queue_processor');
    $remainingJobsCount = $companiesQueue->numberOfItems();

    $form['#prefix'] = '<div class="formselect">';
    $form['#suffix'] = '</div>';
    $form['#tree'] = TRUE;

    $form['remaining_jobs_count'] = [
      '#type' => 'textfield',
      '#title' => t('Total Tareas Programadas:'),
      '#maxlength' => 10,
      '#size' => 13,
      '#disabled' => TRUE,
      '#default_value' => $remainingJobsCount,
    ];

    $form['run_jobs_count'] = [
      '#type' => 'textfield',
      '#title' => t('Total Tareas Ejecutadas:'),
      '#maxlength' => 10,
      '#size' => 13,
      '#disabled' => TRUE,
      '#default_value' => $runJobsCount,
    ];

    $form['button-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-wrapper-button', 'col', 'input-field', 's12'],
      ],
    ];

    $form['button-wrapper']['submit'] = [
      '#type' => 'button',
      '#value' => t('Ejecutar siguiente lote'),
      '#attributes' => [
        'class' => ['btn', 'btn-primary'],
      ],
      '#ajax' => [
        'callback' => [$this, 'runScheduledJobsBatchCallback'],
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Ejecutando tareas de instalación del módulo TBO Permissions.'),
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
   * Run install jobs batch.
   *
   * @param array $form
   *   Form data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax Response.
   */
  public function runScheduledJobsBatchCallback(array &$form, FormStateInterface $form_state) {
    $queueFactory = \Drupal::service('queue');
    $queueManager = \Drupal::service('plugin.manager.queue_worker');

    $companiesQueue = $queueFactory->get('company_permissions_queue_processor');
    $queueWorker = $queueManager->createInstance('company_permissions_queue_processor');

    $configStore = \Drupal::service('config.factory')
      ->getEditable('tbo_permissions.runscheduledjobs');

    // Instantiate an AjaxResponse Object to return.
    $ajaxResponse = new AjaxResponse();

    $runJobsCount = $configStore->get('run_jobs_count');
    $jobsBatchSize = 100;

    $jobsCounter = 0;
    for ($i = 0; $i < $jobsBatchSize; $i++) {
      try {
        if ($item = $companiesQueue->claimItem()) {
          $queueWorker->processItem($item->data);
          $companiesQueue->deleteItem($item);
          $jobsCounter++;
        }
        else {
          break;
        }
      }
      catch (SuspendQueueException $e) {
        $companiesQueue->releaseItem($item);
        break;
      }
      catch (\Exception $e) {
        break;
      }
    }

    // Now we save the new state of config values.
    $runJobsCount += $jobsCounter;
    $configStore
      ->set('run_jobs_count', $runJobsCount)
      ->save();

    $remainingJobsCount = $companiesQueue->numberOfItems();

    // ValCommand does not exist, so we can use InvokeCommand.
    $ajaxResponse->addCommand(new InvokeCommand('#edit-run-jobs-count', 'val', [
      $runJobsCount,
    ]));
    $ajaxResponse->addCommand(new InvokeCommand('#edit-remaining-jobs-count', 'val', [
      $remainingJobsCount,
    ]));

    // Return the AjaxResponse Object.
    return $ajaxResponse;
  }

}
