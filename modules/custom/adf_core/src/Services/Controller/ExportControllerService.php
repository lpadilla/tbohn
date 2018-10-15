<?php

namespace Drupal\adf_core\Services\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Masterminds\HTML5\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\UrlGeneratorTrait;

/**
 * Class ExportControllerService
 * @package Drupal\tbo_account\Services\Controller
 */
class ExportControllerService {
  use UrlGeneratorTrait;
  protected $user;

  public function __construct() {
    $this->user = \Drupal::currentUser();
  }

  public function exportData($type, $export, $reload, $uuid) {
    if (!$this->user->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $data_to_export = array();

    $data_to_export['with_filters']['filters'] = \Drupal::config('tbo_export.' . $export)->get('filters');
    $data_to_export['with_filters']['table_columns'] = \Drupal::config('tbo_export.' . $export)->get('table_columns');

    if($reload == 1) {
      unset($data_to_export['with_filters']['filters']);
      $data_to_export['with_filters']['filters'] = '';
    }

    if ($export == 'atp-associated-lines') {
      $data_to_export['parameters_associated_lines'] = [
        'p1' => $_GET['p1'],
        'p2' => $_GET['p2'],
        'p3' => $uuid,
      ];
    }

    //Set is reload
    $data_to_export['is_filter'] = $reload;
    $data_to_export['fields'] = \Drupal::config('tbo_export.' . $export)->get($uuid);

    $information = [
      'type' => $type,
      'export' => $export,
      'logs_data' => $data_to_export,
    ];

    //Set var $batch
    $batch = [
      'title' => t('Exportación de datos'),
      'operations' => [
        ['_tbo_get_data_export',[$information]],
        ['_tbo_export_data', [$information]],
      ],
      'progress_message' => t('Se ha completado un @percentage%'),
      'progressive' => TRUE,
      'init_message' => t('El proceso de exportación está iniciando'),
      'error_message' => t('Ha ocurrido un error al exportar el archivo.'),
      'finished' => '_tbo_general_export_batch_finished',
    ];

    if ($export == 'audit') {
      $batch['operations'] = [
        ['_tbo_export_audit_log',[$information]],
      ];
    }

    batch_set($batch);

    return batch_process('adf_core/download/export/'.$export);
  }

  /**
   * @param $export
   * @return BinaryFileResponse|RedirectResponse
   */
  public function downloadExportData ($export) {
    $tempstore = \Drupal::service('user.private_tempstore')->get('adf_core');
    $results = $tempstore->get('adf_core_download');
    if($results['empty'] !== 1) {
      // Set headers to download file
      try {
        $response = new BinaryFileResponse($results['path']);
        $response->headers->set('Content-Type', $results['header_doc']);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $results['file_name']);

        if ($export != 'atp-associated-lines') {
          drupal_set_message('Archivo exportado correctamente', 'status');
        }

        return $response;
      } catch (Exception $e) {
        drupal_set_message('Error en la exportación ' . $e->getMessage(), 'error');
        return new RedirectResponse('<front>');
      }
    } else {
      return $this->redirect('<front>');
    }
  }

  /**
   * @param $file_name
   * @param null $directory
   * @return BinaryFileResponse
   */
	public function downloadPublic($file_name, $directory = NULL) {
		$service = \Drupal::service('stream_wrapper_manager');
		$dir = $service->getViaUri('public://')->realpath();

		if($directory == 'NULL') {
			$directory = '';
		}

		if(!empty($directory)) {
			$routes = explode('&', $directory);
			$file = $dir;
			foreach ($routes as $route) {
				$file = $file . '/' . $route;
			}
			$file = $file . '/' . $file_name;
		} else {
			$file = $dir . '/' . $file_name;
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$header = finfo_file($finfo, $file_name);

		$response = new BinaryFileResponse($file);
		$response->headers->set('Content-Type', $header);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file_name);

		return $response;
	}

}
