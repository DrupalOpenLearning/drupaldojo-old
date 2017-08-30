<?php

/**
 * @file
 * Definition of Drupal\readme\Tests\ReadmeTest.
 */

namespace Drupal\readme\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests readme conventions.
 *
 * @group Naming
 */
class ReadmeTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'help', 'readme'];

  /**
   * Tests readme conventions.
   */
  public function testReadme() {
    $admin_user = $this->drupalCreateUser(['administer modules', 'access administration pages']);
    $this->drupalLogin($admin_user);

    // Check for Readme link on 'Extend' page.
    $this->drupalGet('admin/modules');
    $this->assertRaw('<a href="' . base_path() . 'admin/readme/_config_example" class="module-link module-link-help module-link-readme" title="Readme" data-drupal-selector="edit-modules-ap-config-example-links-help" id="edit-modules-ap-config-example-links-help">Readme</a>');

    // Check Readme main page.
    $this->drupalGet('admin/readme');
    $this->assertRaw('<title>Readme | Drupal</title>');
    $this->assertRaw('<dt class="list-group__link"><a href="' . base_path() . 'admin/readme/readme">Readme</a></dt>');
    $this->assertRaw('<dd class="list-group__description">Allows site builders and administrator to view a module\'s README file.</dd>');

    // Check Readme detail page for README.md.
    $this->drupalGet('admin/readme/readme');
    $this->assertRaw('<title>Readme');
    $this->assertRaw('Table of Contents');

    // Check Readme detail page for README.txt.
    $this->drupalGet('admin/readme/readme_test');
    $this->assertRaw('<title>Readme test');
    $this->assertRaw('<pre>One two one two, this is just a test -- Beastie Boys</pre>');

    $this->drupalLogout();

    // Check Readme HTML access denied.
    $this->drupalGet('admin/readme/readme_test/html');
    $this->assertResponse(403);

    // Check Readme HTML access allowed with valid token.
    $this->drupalGet('admin/readme/readme_test/html', ['query' => ['token' => \Drupal::state()->get('readme.token')]]);
    $this->assertResponse(200);
    $this->assertRaw('<pre>One two one two, this is just a test -- Beastie Boys</pre>');

    // Clear token value.
    $this->drupalLogin($admin_user);
    $this->drupalPostForm('admin/config/development/readme/settings', ['token' => ''], t('Save configuration'));
    $this->drupalLogout();

    // Check Readme HTML access denied w/ token.
    $this->drupalGet('admin/readme/readme_test/html', ['query' => ['token' => \Drupal::state()->get('readme.token')]]);
    $this->assertResponse(403);

    // Check Readme HTML access denied w/o token.
    $this->drupalGet('admin/readme/readme_test/html');
    $this->assertResponse(403);
  }

}
