<?php

namespace Drupal\tbo_general\Services;

use Drupal\file\Entity\File;

define('TOL_VAL_UNAVAILABLE_SERVICE', 0);
define('TOL_VAL_VALID', 1);
define('TOL_VAL_INVALID', 2);

/**
 * Class TboConfigService.
 *
 * @package Drupal\tbo_general\Services
 */
class TboConfigService implements TboConfigServiceInterface {

  protected $configuration;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->configuration = \Drupal::config('tbo_general.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig($group, $property, $default = FALSE) {
    $group = $this->getConfigGroup($group, $default);
    if ($group == $default || !isset($group[$property])) {
      return $default;
    }
    return $group[$property];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigGroup($group, $default = NULL) {
    $group = $this->configuration->get($group);
    if (is_null($group)) {
      return $default;
    }
    return $group;
  }

  /**
   * Function to format a given value with the global settings currency.
   *
   * @param $value
   *
   * @return string
   */
  public function formatCurrency($value) {
    $decimals = $this->getConfig('region', 'decimal_numbers', 2);
    $decimal_separator = $this->getConfig('region', 'decimal_separator', ',');
    $thousand_separator = $this->getConfig('region', 'thousand_separator', '.');
    $position = $this->getConfig('region', 'position_currency_sing', 0);
    $newFormatValue = NULL;

    if (!$decimals) {
      $value = round($value);
    }
    $value = floatval($value);
    $sign = $this->getConfig('region', 'currency_sign', 2);

    // Set decimal and thousands separator.
    if ($decimal_separator && $thousand_separator) {
      $newFormatValue = number_format($value, $decimals, $decimal_separator, $thousand_separator);
    }
    else {
      $newFormatValue = number_format($value, $decimals);
    }
    unset($decimal_separator);
    unset($thousand_separator);
    unset($decimals);
    unset($settings);
    if ($position == 1) {
      return $newFormatValue . '' . $sign;
    }
    else {
      return $sign . '' . $newFormatValue;
    }
  }

  /**
   * @param $amount
   * @return string
   */
  public function formatUnit($amount) {
    $config_units = $this->getConfig('region', 'format_units');
    $number = number_format($amount, intval($config_units['decimal_numbers']), $config_units['decimal_separator'], $config_units['thousand_separator']);
    return $number;
  }

  /**
   * Formatea una fecha de acuerdo al formato establecido en al configuracion de TOL.
   *
   * @param $value
   *
   * @return mixed
   */
  public function formatDate($value) {
    $date_formatter = \Drupal::service('date.formatter');
    $date_format = $this->getConfig('region', 'format_date');
    return $date_formatter->format($value, $date_format);
  }

  /**
   * Formatea una hora de acuerdo al formato establecido en al configuracion de TOL
   * @param $value
   * @return mixed
   */
  public function formatHour($value) {
    $hour_formatter = \Drupal::service('date.formatter');
    $hour_format = $this->getConfig('region', 'format_hour');
    return $hour_formatter->format($value, $hour_format);
  }

  /**
   * Formatea una fecha de acuerdo al formato establecido en al configuracion de TOL para la fecha de actualización.
   *
   * @param $value
   *
   * @return date //formated_date
   */
  public function formatDateUpdate($value) {
    $date_formatter = \Drupal::service('date.formatter');
    // TODO Se comenta para dejar fijo el formato de las fechas de actualizado
    // $date_format = $this->getConfig('region', 'format_update');.
    return $date_formatter->format($value, 'html_datetime');
  }

  /**
   * Return the event length in the format defined in global settings.
   *
   * @param $value
   *
   * @return mixed
   */
  public function formatDuration($value) {
    $lenght_format = $this->getConfig('events', 'format_length_field');
    switch ($lenght_format) {
      case 'human':
        $hour = (int) gmdate("H", $value);
        $min = (int) gmdate("i", $value);
        $seg = (int) gmdate("s", $value);
        $text = "";
        if ($hour > 0) {
          if ($hour == 1) {
            $text .= t("@unit hora", ['@unit' => $hour]);
          }
          else {
            $text .= t("@unit horas", ['@unit' => $hour]);
          }
        }
        if ($min > 0) {
          $text .= " ";
          if ($min == 1) {
            $text .= t("@unit minuto", ['@unit' => $min]);
          }
          else {
            $text .= t("@unit minutos", ['@unit' => $min]);
          }
        }
        if ($seg > 0) {
          $text .= " ";
          if ($seg == 1) {
            $text .= t("@unit segundo", ['@unit' => $seg]);
          }
          else {
            $text .= t("@unit segundos", ['@unit' => $seg]);
          }
        }
        $lenght = $text;
        break;

      case 'H:i:s':
        $lenght = gmdate("H:i:s", $value);
        break;

      case 'minute_rounded':
      case 'minute_rounded_with_unit':
        if (is_numeric($value)) {
          if ($value > 0) {
            $lenght = ceil($value / 60);
          }
          elseif (is_numeric($value) && $value == 0) {
            $lenght = '0';
          }
          if ($lenght_format == 'minute_rounded_with_unit') {
            if ($lenght == 1) {
              $lenght = t('@lenght Minuto', ['@lenght' => $lenght]);
            }
            else {
              $lenght = t('@lenght Minutos', ['@lenght' => $lenght]);
            }
          }
        }
        break;
    }
    return $lenght;
  }

  /**
   * Retorna la descripción de.
   *
   * @return string $output
   */
  public function descriptionResponsiveWeight() {
    $output = t('<strong>Peso Responsive</strong><br>');
    $output .= t('<span> <strong>Alto:</strong>La columna se mostrará en todos los dispositivos </span><br>');
    $output .= t('<span> <strong>Medio:</strong>La columna se mostrará en tablets y versión desktop </span><br>');
    $output .= t('<span> <strong>Bajo:</strong>La columna solo se mostrará versión desktop </span><br>');
    return $output;
  }

  /**
   *
   */
  public function removeAccents($string) {
    if (!preg_match('/[\x80-\xff]/', $string)) {
      return $string;
    }

    $chars = [
      // Decompositions for Latin-1 Supplement.
      chr(195) . chr(128) => 'A',
      chr(195) . chr(129) => 'A',
      chr(195) . chr(130) => 'A',
      chr(195) . chr(131) => 'A',
      chr(195) . chr(132) => 'A',
      chr(195) . chr(133) => 'A',
      chr(195) . chr(135) => 'C',
      chr(195) . chr(136) => 'E',
      chr(195) . chr(137) => 'E',
      chr(195) . chr(138) => 'E',
      chr(195) . chr(139) => 'E',
      chr(195) . chr(140) => 'I',
      chr(195) . chr(141) => 'I',
      chr(195) . chr(142) => 'I',
      chr(195) . chr(143) => 'I',
      chr(195) . chr(145) => 'N',
      chr(195) . chr(146) => 'O',
      chr(195) . chr(147) => 'O',
      chr(195) . chr(148) => 'O',
      chr(195) . chr(149) => 'O',
      chr(195) . chr(150) => 'O',
      chr(195) . chr(153) => 'U',
      chr(195) . chr(154) => 'U',
      chr(195) . chr(155) => 'U',
      chr(195) . chr(156) => 'U',
      chr(195) . chr(157) => 'Y',
      chr(195) . chr(159) => 's',
      chr(195) . chr(160) => 'a',
      chr(195) . chr(161) => 'a',
      chr(195) . chr(162) => 'a',
      chr(195) . chr(163) => 'a',
      chr(195) . chr(164) => 'a',
      chr(195) . chr(165) => 'a',
      chr(195) . chr(167) => 'c',
      chr(195) . chr(168) => 'e',
      chr(195) . chr(169) => 'e',
      chr(195) . chr(170) => 'e',
      chr(195) . chr(171) => 'e',
      chr(195) . chr(172) => 'i',
      chr(195) . chr(173) => 'i',
      chr(195) . chr(174) => 'i',
      chr(195) . chr(175) => 'i',
      chr(195) . chr(177) => 'n',
      chr(195) . chr(178) => 'o',
      chr(195) . chr(179) => 'o',
      chr(195) . chr(180) => 'o',
      chr(195) . chr(181) => 'o',
      chr(195) . chr(182) => 'o',
      chr(195) . chr(182) => 'o',
      chr(195) . chr(185) => 'u',
      chr(195) . chr(186) => 'u',
      chr(195) . chr(187) => 'u',
      chr(195) . chr(188) => 'u',
      chr(195) . chr(189) => 'y',
      chr(195) . chr(191) => 'y',
      // Decompositions for Latin Extended-A.
      chr(196) . chr(128) => 'A',
      chr(196) . chr(129) => 'a',
      chr(196) . chr(130) => 'A',
      chr(196) . chr(131) => 'a',
      chr(196) . chr(132) => 'A',
      chr(196) . chr(133) => 'a',
      chr(196) . chr(134) => 'C',
      chr(196) . chr(135) => 'c',
      chr(196) . chr(136) => 'C',
      chr(196) . chr(137) => 'c',
      chr(196) . chr(138) => 'C',
      chr(196) . chr(139) => 'c',
      chr(196) . chr(140) => 'C',
      chr(196) . chr(141) => 'c',
      chr(196) . chr(142) => 'D',
      chr(196) . chr(143) => 'd',
      chr(196) . chr(144) => 'D',
      chr(196) . chr(145) => 'd',
      chr(196) . chr(146) => 'E',
      chr(196) . chr(147) => 'e',
      chr(196) . chr(148) => 'E',
      chr(196) . chr(149) => 'e',
      chr(196) . chr(150) => 'E',
      chr(196) . chr(151) => 'e',
      chr(196) . chr(152) => 'E',
      chr(196) . chr(153) => 'e',
      chr(196) . chr(154) => 'E',
      chr(196) . chr(155) => 'e',
      chr(196) . chr(156) => 'G',
      chr(196) . chr(157) => 'g',
      chr(196) . chr(158) => 'G',
      chr(196) . chr(159) => 'g',
      chr(196) . chr(160) => 'G',
      chr(196) . chr(161) => 'g',
      chr(196) . chr(162) => 'G',
      chr(196) . chr(163) => 'g',
      chr(196) . chr(164) => 'H',
      chr(196) . chr(165) => 'h',
      chr(196) . chr(166) => 'H',
      chr(196) . chr(167) => 'h',
      chr(196) . chr(168) => 'I',
      chr(196) . chr(169) => 'i',
      chr(196) . chr(170) => 'I',
      chr(196) . chr(171) => 'i',
      chr(196) . chr(172) => 'I',
      chr(196) . chr(173) => 'i',
      chr(196) . chr(174) => 'I',
      chr(196) . chr(175) => 'i',
      chr(196) . chr(176) => 'I',
      chr(196) . chr(177) => 'i',
      chr(196) . chr(178) => 'IJ',
      chr(196) . chr(179) => 'ij',
      chr(196) . chr(180) => 'J',
      chr(196) . chr(181) => 'j',
      chr(196) . chr(182) => 'K',
      chr(196) . chr(183) => 'k',
      chr(196) . chr(184) => 'k',
      chr(196) . chr(185) => 'L',
      chr(196) . chr(186) => 'l',
      chr(196) . chr(187) => 'L',
      chr(196) . chr(188) => 'l',
      chr(196) . chr(189) => 'L',
      chr(196) . chr(190) => 'l',
      chr(196) . chr(191) => 'L',
      chr(197) . chr(128) => 'l',
      chr(197) . chr(129) => 'L',
      chr(197) . chr(130) => 'l',
      chr(197) . chr(131) => 'N',
      chr(197) . chr(132) => 'n',
      chr(197) . chr(133) => 'N',
      chr(197) . chr(134) => 'n',
      chr(197) . chr(135) => 'N',
      chr(197) . chr(136) => 'n',
      chr(197) . chr(137) => 'N',
      chr(197) . chr(138) => 'n',
      chr(197) . chr(139) => 'N',
      chr(197) . chr(140) => 'O',
      chr(197) . chr(141) => 'o',
      chr(197) . chr(142) => 'O',
      chr(197) . chr(143) => 'o',
      chr(197) . chr(144) => 'O',
      chr(197) . chr(145) => 'o',
      chr(197) . chr(146) => 'OE',
      chr(197) . chr(147) => 'oe',
      chr(197) . chr(148) => 'R',
      chr(197) . chr(149) => 'r',
      chr(197) . chr(150) => 'R',
      chr(197) . chr(151) => 'r',
      chr(197) . chr(152) => 'R',
      chr(197) . chr(153) => 'r',
      chr(197) . chr(154) => 'S',
      chr(197) . chr(155) => 's',
      chr(197) . chr(156) => 'S',
      chr(197) . chr(157) => 's',
      chr(197) . chr(158) => 'S',
      chr(197) . chr(159) => 's',
      chr(197) . chr(160) => 'S',
      chr(197) . chr(161) => 's',
      chr(197) . chr(162) => 'T',
      chr(197) . chr(163) => 't',
      chr(197) . chr(164) => 'T',
      chr(197) . chr(165) => 't',
      chr(197) . chr(166) => 'T',
      chr(197) . chr(167) => 't',
      chr(197) . chr(168) => 'U',
      chr(197) . chr(169) => 'u',
      chr(197) . chr(170) => 'U',
      chr(197) . chr(171) => 'u',
      chr(197) . chr(172) => 'U',
      chr(197) . chr(173) => 'u',
      chr(197) . chr(174) => 'U',
      chr(197) . chr(175) => 'u',
      chr(197) . chr(176) => 'U',
      chr(197) . chr(177) => 'u',
      chr(197) . chr(178) => 'U',
      chr(197) . chr(179) => 'u',
      chr(197) . chr(180) => 'W',
      chr(197) . chr(181) => 'w',
      chr(197) . chr(182) => 'Y',
      chr(197) . chr(183) => 'y',
      chr(197) . chr(184) => 'Y',
      chr(197) . chr(185) => 'Z',
      chr(197) . chr(186) => 'z',
      chr(197) . chr(187) => 'Z',
      chr(197) . chr(188) => 'z',
      chr(197) . chr(189) => 'Z',
      chr(197) . chr(190) => 'z',
      chr(197) . chr(191) => 's',
    ];

    $string = strtr($string, $chars);

    return $string;
  }

  /**
   * Method to save file permanenty in the database.
   *
   * @param string $fid
   *   File id.
   */
  public function setFileAsPermanent($fid) {
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    $file = File::load($fid);

    // If file doesn't exist return.
    if (!is_object($file)) {
      return;
    }

    // Set as permanent.
    $file->setPermanent();

    // Save file.
    $file->save();

    // Add usage file.
    \Drupal::service('file.usage')->add($file, 'tbo_general', 'tbo_general', 1);
  }

  /**
   *
   */
  public function getExceptionMessages() {
    return $this->configuration->get('exception_messages');
  }

  /**
   * Formatea una numero de linea segun el formato establecido.
   *
   * @param mixed $value
   *   The msisdn.
   *
   * @return string
   *   The formated msisdn.
   */
  public function formatLine($value) {
    $line_format = $this->getConfig('region', 'format_lines');

    // Line format.
    $format = (int) $line_format['line_format'];

    if (strlen($value) == 10) {
      $split_number = str_split($value);
      if ($format == 1) {
        // (301) 100 1010
        $value = "($split_number[0]$split_number[1]$split_number[2])";
        $value .= " $split_number[3]$split_number[4]$split_number[5]";
        $value .= " $split_number[6]$split_number[7]$split_number[8]$split_number[9]";
      }
      else if ($format == 2) {
        // 301 100 1010
        $value = "$split_number[0]$split_number[1]$split_number[2]";
        $value .= " $split_number[3]$split_number[4]$split_number[5]";
        $value .= " $split_number[6]$split_number[7]$split_number[8]$split_number[9]";
      }
      else if ($format == 3) {
        // 301-100-1010
        $value = "$split_number[0]$split_number[1]$split_number[2]";
        $value .= "-$split_number[3]$split_number[4]$split_number[5]";
        $value .= "-$split_number[6]$split_number[7]$split_number[8]$split_number[9]";
      }
      else if ($format == 4) {
        // (301) 100-1010
        $value = "($split_number[0]$split_number[1]$split_number[2])";
        $value .= " $split_number[3]$split_number[4]$split_number[5]";
        $value .= "-$split_number[6]$split_number[7]$split_number[8]$split_number[9]";
      }
    }

    return $value;

  }

  /**
   * Get Format Phone.
   *
   * @return int
   *   The id format.
   */
  public function getFormatPhone() {
    $line_format = $this->getConfig('region', 'format_lines');
    $format = (int) $line_format['line_format'];
    return $format;
  }

  /**
   * Get fixed phone with format.
   */
  public function formatFixedPhone($value) {
    if (strlen($value) == 10) {
      $split_number = str_split($value);
      // (57) (4) 333-3333.
      $value = "($split_number[0]$split_number[1])";
      $value .= " ($split_number[2])";
      $value .= " $split_number[3]$split_number[4]$split_number[5]";
      $value .= "-$split_number[6]$split_number[7]$split_number[8]$split_number[9]";
    }

    return $value;
  }

}
