<?php

/**
 * @file
 * Contains \Drupal\readme\Controller\HelpController.
 */

namespace Drupal\readme\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\readme\ReadmeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller routines for README routes.
 */
class ReadmeController extends ControllerBase {

  /**
   * The README manager.
   *
   * @var \Drupal\readme\ReadmeManager
   */
  protected $readmeManager;

  /**
   * Creates a new HelpController.
   *
   * @param \Drupal\readme\ReadmeManager $readme_manager
   *   The README manager.
   */
  public function __construct(ReadmeManager $readme_manager) {
    $this->readmeManager = $readme_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('readme.manager')
    );
  }

  /**
   * Prints a page listing all modules with README files.
   *
   * @return array
   *   A render array as expected by drupal_render().
   */
  public function readmeMain() {
    $content = [];

    $modules = system_rebuild_module_data();

    foreach ($modules as $name => $module) {
      if ($this->readmeManager->exists($name)) {
        $content[$name] = [
          'title' => $module->info['name'],
          'description' => $module->info['description'],
          'url' => Url::fromRoute('readme.page', ['name' => $name]),
        ];
      }
    }

    if ($content) {
      $build = [
        '#theme' => 'admin_block_content',
        '#content' => $content,
      ];
    }
    else {
      $build = [
        '#markup' => $this->t('None of the available module contain README files.'),
      ];
    }
    return $build;
  }

  /**
   * Prints a page display for the README of a module.
   *
   * @param string $name
   *   A module name to display a READ page for.
   *
   * @return array
   *   A render array as expected by drupal_render().
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws 'Page Not Found' if module name does not exist.
   */
  public function readmePage($name) {
    if (!$this->readmeManager->exists($name)) {
      throw new NotFoundHttpException();
    }

    $modules = system_rebuild_module_data();
    $module = $modules[$name];

    $build = [];
    $t_args = [
      '@name' => $module->info['name'],
      '@version' => $module->info['version'] ? $module->info['version'] : $this->t('DEV'),
      '@module' => $name,
    ];
    $build['#title'] = $this->t('@name (@module-@version)', $t_args);
    $build['html'] = [
      '#markup' => $this->readmeManager->getHtml($name),
      '#allowed_tags' => Xss::getAdminTagList(),
    ];
    if ($token = \Drupal::state()->get('readme.token')) {
      $token_url = Url::fromRoute('readme.html', ['name' => $name], ['absolute' => TRUE, 'query' => ['token' => $token]]);
      $build['token']['message'] = [
        '#markup' => $this->t('Use the below URL to allow external applications secure access to this README file.'),
        '#prefix' => '<br/><hr/>',
        '#suffix' => '<br/>',
      ];
      $build['token']['url'] = [
        '#type' => 'link',
        '#title' => $token_url->toString(),
        '#url' => $token_url,
      ];
    }
    return $build;
  }

  /**
   * Prints basic HTML for the README of a module.
   *
   * @param string $name
   *   A module name to display a READ page for.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response containing a module's README HTML.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws 'Page Not Found' if module name does not exist.
   */
  public function readmeHtml($name) {
    if (!$this->readmeManager->exists($name)) {
      throw new NotFoundHttpException();
    }

    return new Response($this->readmeManager->getHtml($name));
  }

}
