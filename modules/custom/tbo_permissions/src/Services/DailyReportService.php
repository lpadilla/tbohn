<?php

namespace Drupal\tbo_permissions\Services;

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Class DailyReportService.
 *
 * @package Drupal\tbo_permissions\Services
 */
class DailyReportService {

  /**
   * Generate and send the daily report in Excel format.
   *
   * @return array
   *   Return data response.
   */
  public function generateAndSendDailyReport() {
    // Check if already executed this report.
    $lastReportDate = $this->getLastReportDateConfig();

    if ($lastReportDate != date('Y-m-d')) {
      // First we clean the old report files.
      $this->cleanReportFiles();

      $emailSender = \Drupal::service('tbo_mail.send');

      // We get the repository, to get the records to build the excel report.
      $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');
      $companiesWithModifiedCardsAccess = $permissionsRepository->getCompaniesWithCardsAccessChangedToday(['filter_today' => '']);

      if (count($companiesWithModifiedCardsAccess) > 0) {
        // Prepare file path.
        $dir = \Drupal::service('stream_wrapper_manager')
          ->getViaUri('public://')
          ->realpath();

        $doc_name = "reporte-empresas-acceso-cards-modificados-";
        $date = date('Y-m-d');
        $file_name = $doc_name . $date . '.xlsx';
        $path = $dir . '/' . $file_name;

        try {
          // Prepare the Excel file.
          $writer = WriterFactory::create(Type::XLSX);
          $writer->openToFile($path);

          $writer->getCurrentSheet()
            ->setName('Permisos de cards modificados');

          // Prepare the rows.
          $writer->addRow([
            'Nombre Empresa',
            'Tipo documento empresa',
            'Numero documento empresa',
            'Nombre de card cambiado',
            'Estado antiguo',
            'Estado nuevo',
            'Nombre de usuario que realiza el cambio',
            'Fecha y hora de ejecución de la acción en el sistema',
          ]);
          foreach ($companiesWithModifiedCardsAccess as $row) {
            $writer->addRow($row);
          }

          $writer->close();

          // We get the Super Admins emails for sending the daily report.
          $tokens['attachments'] = [
            'filepath' => $path,
            'filename' => $file_name,
            'filemime' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
          ];

          $superAdmins = $permissionsRepository->getAllSuperAdminsInfo();
          foreach ($superAdmins as $superAdmin) {
            $tokens['username'] = (isset($superAdmin->full_name) && !empty($superAdmin->full_name)) ? $superAdmin->full_name : $superAdmin->name;
            $tokens['mail_to_send'] = $superAdmin->mail;
            $emailSender->send_message($tokens, 'daily_cards_access_modifications_excel_report');
          }

          $this->setLastReportDateConfig();

          return [
            'result' => TRUE,
            'message' => 'Se envió correo electrónico con el reporte adjunto.',
          ];
        }
        catch (\Exception $e) {
          return [
            'result' => FALSE,
            'message' => 'Error: ' . $e->getMessage(),
          ];
        }
      }
      else {
        return [
          'result' => FALSE,
          'message' => 'Hoy no se han realizado modificaciones a los permisos de acceso a cards por empresa.',
        ];
      }
    }

    return [
      'result' => FALSE,
      'message' => 'Hoy ya se generó y envió el reporte de los permisos de acceso a cards por empresa modificados.',
    ];
  }

  /**
   * Remove old report files.
   */
  public function cleanReportFiles() {
    // First we get a list of report files.
    $publicDir = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();

    // Calculate the limit unixtime for deleting files.
    $limitTime = strtotime('-7 days', strtotime(date("Y-m-d") . " 00:00:00"));

    // Then we examine the name of each file looking for the last modified
    // date, so we can know if we have to delete the file.
    $lastModifiedUnixTime = 0;
    $entries = scandir($publicDir);
    foreach ($entries as $reportFile) {
      if (strpos($reportFile, "reporte-empresas-acceso-cards-modificados") === 0 ||
        strpos($reportFile, "reporte-empresas-cards-bloqueados") === 0) {
        $lastModifiedUnixTime = filemtime($publicDir . '/' . $reportFile);
        if ($lastModifiedUnixTime < $limitTime) {
          // Delete the file.
          unlink($publicDir . '/' . $reportFile);
        }
      }
    }
  }

  /**
   * Gets the Last Report Date configuration value.
   *
   * @return string
   *   Last daily report date.
   */
  public function getLastReportDateConfig() {
    $config = \Drupal::config('tbo_permissions.tbopermissionssettings');
    $lastReportDate = $config->get('last_report_date');
    if (empty($lastReportDate) || $lastReportDate == NULL) {
      $lastReportDate = FALSE;
    }

    return $lastReportDate;
  }

  /**
   * Set the Last Report Date configuration value.
   *
   * @return string
   *   Last daily report date.
   */
  public function setLastReportDateConfig() {
    // Save the new value.
    $lastReportDate = date('Y-m-d');
    $configStore = \Drupal::service('config.factory')
      ->getEditable('tbo_permissions.tbopermissionssettings');
    $configStore->set('last_report_date', $lastReportDate)
      ->save();

    return $lastReportDate;
  }

}
