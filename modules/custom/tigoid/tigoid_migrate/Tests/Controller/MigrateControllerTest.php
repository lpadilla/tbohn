<?php

namespace Drupal\tigoid_migrate\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the tigoid_migrate module.
 */
class MigrateControllerTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "tigoid_migrate MigrateController's controller functionality",
      'description' => 'Test Unit for module tigoid_migrate and controller MigrateController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests tigoid_migrate functionality.
   */
  public function testMigrateController() {
    // Check that the basic functions of module tigoid_migrate.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
