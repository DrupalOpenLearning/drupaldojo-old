<?php

namespace Drupal\allinone_backup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Render\Renderer;

/**
 * An allinone_backup controller.
 */
class BackuplogsController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  protected $renderer;
  protected $file;

  /**
   * Implements __construct().
   */
  public function __construct(Renderer $renderer, FileSystem $fileStorage) {
    $this->renderer = $renderer;
    $this->file = $fileStorage;
  }

  /**
   * Implements create().
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('file_system')
    );
  }

  /**
   * Implements content().
   */
  public function content() {
    $content['logfieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('backup log'),
      '#open' => TRUE,
    ];
    $allinonefiles = 'private://';
    $filename = $this->file->realpath($allinonefiles . ".htaccess");
    $searchfor = 'allinonefiles';
    // Get the file contents, assuming the file to be readable (and exist).
    $contents = file_get_contents($filename);
    // Escape special characters in the query.
    $pattern = preg_quote($searchfor, '/');
    // Finalise the regular expression, matching the whole line.
    $pattern = "/^.*$pattern.*\$/m";
    // Search, and store all matching occurrences in $matches.
    if (!preg_match_all($pattern, $contents, $matches)) {
      chmod($filename, 0644);
      $f = fopen($filename, "a+") or drupal_set_message($this->t('private folder .htaccess file not found.'), 'error');
      fwrite($f, "
# allinonefiles zip download
<IfModule mod_authz_core.c>
 <FilesMatch \"\.(zip)$\">
    <RequireAll>
       Require all granted
    </RequireAll>
 </FilesMatch>
</IfModule>");
      fclose($f);
    }
    $content['logfieldset']['#markup'] = $this->renderer->render(views_embed_view('allinonebackup_block', 'block_1'));
    return $content;
  }

}
