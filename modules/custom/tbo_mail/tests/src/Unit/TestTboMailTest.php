<?php

namespace Drupal\Tests\tbo_mail\Unit;

use Drupal\simpletest\WebTestBase;
use Drupal\tbo_mail\SendMessage;

/**
 * Tbo_mail test.
 *
 * @group tbo_mail
 */
class TestTboMailTest extends WebTestBase {

  /**
   * @var \Drupal\tbo_mail\SendMessage
   */
  public $conversionService;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->conversionService = new SendMessage();
  }

  /**
   * A simple test that tests our celsiusToFahrenheit() function.
   */
  public function testTboMail() {
    $tokens['mail_to_send'] = 'carolina.poveda@bitsamericas.com';
    $tokens['user'] = 'william';
    $tokens['role'] = 'ROLE';
    $tokens['link'] = 'https://www.google.com.co';
    $tokens['enterprise'] = 'enterprise';
    $tokens['enterprise_num'] = 'enterprise_num';
    $tokens['enterprise_doc'] = 'document';
    $tokens['document'] = 'document';
    $tokens['admin_mail'] = 'asdf@correo.com';
    $tokens['admin_enterprise'] = 'test';
    $tokens['admin_phone'] = 111111;
    $tokens['bill_status'] = 'test';
    $tokens['bill_number'] = 111111;
    $tokens['bill_old'] = 'test';
    $tokens['bill_new'] = 'test';
    $tokens['complain_type'] = 'test';
    $tokens['complain_description'] = 'test';
    $tokens['attachments'] = 'test';
    $tokens['cun'] = 'test';
    $tokens['creator'] = 'test';
    $tokens['creator_mail'] = 'test';
    $tokens['invitation_code'] = 'test';
    $templates = ['new_user', 'new_enterprise', 'assing_enterprise', 'config_bill', 'register_complain', 'autocreate_account'];

    foreach ($templates as $template) {
      $this->assertTrue(TRUE, $this->conversionService->send_message($tokens, $template));
    }
  }

}
