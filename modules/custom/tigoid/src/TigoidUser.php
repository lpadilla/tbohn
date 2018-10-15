<?php

namespace Drupal\tigoid;

use Drupal\Core\Database\Connection;
use Drupal\user\Entity\User;
use Drupal\user_lines\UserLines;

/**
 * Class TigoidUser.
 *
 * @package Drupal\tigoid
 */
class TigoidUser implements TigoidUserInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The user lines object.
   *
   * @var
   */
  protected $lines;

  protected $config;

  /**
   * Constructs a TigoId object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
    // $this->lines = $lines;.
    $configuration = \Drupal::config('openid_connect.settings.tigoid')
      ->get('settings');

    $this->config = $configuration;
  }

  /**
   * Retorna el objeto cuenta de la cuenta que tenga el msisdn asociado.
   *
   * @param $msisdn
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|null|static
   */
  public function getAccountByMsisdn($msisdn) {

    $msisdn = $this->cleanIndicative($msisdn);
    $user_id = UserLines::getUidByMsisdn($msisdn);
    if (is_array($user_id)) {
      $user_id = array_shift($user_id);
    }

    if ($user_id) {
      $account = User::load($user_id);
      if (is_object($account)) {
        return $account;
      }
    }
    return FALSE;
  }

  /**
   *
   */
  public function cleanIndicative($msisdn) {
    return preg_replace('/^' . $this->config['indicative'] . '/', '', $msisdn);
  }

  /**
   *
   */
  public function createDummyUser($msisdn, $client_name, $sub) {

    $msisdn = $this->cleanIndicative($msisdn);

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $name = "micuenta+" . $msisdn;
    $email = $name . "@tigo.com";

    // Ensure there are no duplicates.
    for ($original = $name, $i = 1; openid_connect_username_exists($name); $i++) {
      $name = $original . '_' . $i;
    }

    $user = User::create([
      'name' => $name,
      'pass' => user_password(),
      'mail' => $email,
      'init' => $email,
      'status' => 1,
      'openid_connect_client' => $client_name,
      'openid_connect_sub' => $sub,
    ]);
    $user->save();

    // Save LogCreateUser.
    $service = \Drupal::service('tbo_account.create_companies_service');
    $service->insertLogCreateUser('createDummyUser', $name, '', '');

    // Optional settings.
    $user->set("init", $email);
    $user->set("langcode", $language);
    $user->set("preferred_langcode", $language);
    $user->set("preferred_admin_langcode", $language);

    // Save user.
    $res = $user->save();
    if ($res) {
      // Agregar linea.
      $userLines = new UserLines($user->id());
      $line = $userLines->add($msisdn, "Mi linea Tigo", "PRE", "", "");
      return TRUE;
    }

    return FALSE;

  }

}
