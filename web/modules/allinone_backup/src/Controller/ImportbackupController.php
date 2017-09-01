<?php

namespace Drupal\allinone_backup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * An allinone_backup controller.
 */
class ImportbackupController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  protected $renderer;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * Implements __construct().
   */
  public function __construct(Renderer $renderer, RequestStack $request) {
    $this->renderer = $renderer;
    $this->request = $request;
  }

  /**
   * Implements create().
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('request_stack')
    );
  }

  /**
   * Implements content().
   */
  public function content() {
    $path = $this->request->getCurrentRequest()->get('file');
    if ($path != "") {
      $_SESSION['select_backup'] = $path;
      $batch = [
        'title' => $this->t('Importing'),
        'operations' => [
              ['allinone_backup', ['importbackup']],
        ],
        'finished' => 'allinone_backup_finished_callback',
        'file' => drupal_get_path('module', 'allinone_backup') . '/allinone_backup.import.inc',
      ];
      batch_set($batch);
      return batch_process('user');
    }
    else {
      $content['logfieldset'] = [
        '#type' => 'details',
        '#title' => $this->t('Database Import'),
        '#open' => TRUE,
      ];
      $content['logfieldset']['#markup'] = $this->renderer->render(views_embed_view('allinonebackup_block', 'block_2'));

      $path = $this->request->getCurrentRequest()->get('importbackup');
      return $content;
    }
  }

}
