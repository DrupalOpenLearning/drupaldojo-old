<?php

namespace Drupal\swiftmailer\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the Transport and Message Settings UI.
 *
 * @group swiftmailer
 */
class SwiftMailerSettingsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'swiftmailer',
    'mailsystem',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Tests the Transport Settings.
   */
  public function testTransportSettings() {
    // Unauthorized user should not have access.
    $this->drupalGet('admin/config/swiftmailer/transport');
    $this->assertResponse(403);

    // Login..
    $user = $this->createUser(['administer swiftmailer']);
    $this->drupalLogin($user);
    $this->drupalGet(t('admin/config/swiftmailer/transport'));
    $this->assertText(t('Transport types'));

    // Select Smtp tranport option.
    $this->drupalPostAjaxForm(NULL, ['transport[type]' => 'smtp'], ['transport[type]' => 'smtp']);
    $this->drupalPostForm(NULL, [
      'transport[type]' => 'smtp',
      'transport[configuration][smtp][username]' => 'example',
      'transport[configuration][smtp][password]' => 'pass',
    ], t('Save configuration'));
    $this->assertText('using the SMTP transport type.');

    // Loading configuration to check if is set up correctly.
    $config = $this->config('swiftmailer.transport');
    $transport = $config->get('transport');
    $user = $config->get('smtp_username');
    $password = $config->get('smtp_password');
    $this->assertEqual($transport, 'smtp');
    $this->assertEqual($user, 'example');
    $this->assertEqual($password, 'pass');

    // Select Sppol tranport option.
    $this->drupalPostAjaxForm(NULL, ['transport[type]' => 'spool'], ['transport[type]' => 'spool']);
    $this->drupalPostForm(NULL, [
      'transport[type]' => 'spool',
      'transport[configuration][spool][directory]' => 'aaaaa',
    ], t('Save configuration'));
    $this->assertText('using the Spool transport type.');

    // Loading configuration to check if is set up correctly.
    $config = $this->config('swiftmailer.transport');
    $transport = $config->get('transport');
    $directory = $config->get('spool_directory');
    $this->assertEqual($transport, 'spool');
    $this->assertEqual($directory, 'aaaaa');

    // Select Sendmail tranport option.
    $this->drupalPostAjaxForm(NULL, ['transport[type]' => 'sendmail'], ['transport[type]' => 'sendmail']);
    $this->drupalPostForm(NULL, [
      'transport[type]' => 'sendmail',
      'transport[configuration][sendmail][path]' => 'bbbbb',
    ], t('Save configuration'));
    $this->assertText('using the Sendmail transport type.');

    // Loading configuration to check if is set up correctly.
    $config = $this->config('swiftmailer.transport');
    $transport = $config->get('transport');
    $path = $config->get('sendmail_path');
    $this->assertEqual($transport, 'sendmail');
    $this->assertEqual($path, 'bbbbb');
  }

  /**
   * Tests the Message Settings.
   */
  public function testMessageSettings() {
    $this->drupalGet('admin/config/swiftmailer/transport');
    $this->assertResponse(403);

    // Login..
    $user = $this->createUser(['administer swiftmailer']);
    $this->drupalLogin($user);
    $this->drupalGet(t('admin/config/swiftmailer/transport'));
    $this->assertText(t('Transport types'));

    $this->clickLink('Messages');
    $this->assertText(t('Message format'));

    $this->drupalPostForm(NULL, [
      'format[type]' => 'text/html',
      'convert[mode]' => 'TRUE',
      'character_set[type]' => 'EUC-CN',
    ], t('Save configuration'));
    $this->assertText('The configuration options have been saved.');

    $config = $this->config('swiftmailer.message');
    $format = $config->get('format');
    $mode = $config->get('convert_mode');
    $character = $config->get('character_set');
    $this->assertEqual($format, 'text/html');
    $this->assertEqual($mode, 'TRUE');
    $this->assertEqual($character, 'EUC-CN');
  }

}
