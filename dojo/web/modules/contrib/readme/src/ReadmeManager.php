<?php

/**
 * @file
 * Contains \Drupal\readme\ReadmeManager.
 */

namespace Drupal\readme;

use Michelf\Markdown;

// If Markdown is not autoloaded, attempt to include it in /libraries/markdown.
if (!class_exists('Michelf\Markdown')) {
  include_once \Drupal::root() . '/libraries/markdown/Michelf/Markdown.inc.php';
}

/**
 * Manages module README files.
 */
class ReadmeManager implements ReadmeManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function markdownInstalled() {
    return class_exists('Michelf\Markdown');
  }

  /**
   * {@inheritdoc}
   */
  public function exists($module_name, $path = NULL) {
    return ($this->getPath($module_name, $path)) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath($module_name, $path = NULL) {
    $module_path = drupal_get_path('module', $module_name);
    if ($path) {
      return (file_exists("$module_path/$path")) ? "$module_path/$path" : FALSE;
    }
    elseif (file_exists("$module_path/README.txt")) {
      return "$module_path/README.txt";
    }
    elseif (file_exists("$module_path/README.md")) {
      return "$module_path/README.md";
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isMarkDown($module_name, $path = NULL) {
    $readme_path = $this->getPath($module_name, $path);
    if (!$readme_path) {
      return FALSE;
    }
    return preg_match('/\.md$/', $readme_path) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getHtml($module_name, $path = NULL) {
    $readme_path = $this->getPath($module_name, $path);
    if (!$readme_path) {
      return FALSE;
    }

    $content = file_get_contents($readme_path);
    $content = trim($content);

    if ($this->isMarkDown($module_name, $path = NULL) && $this->markdownInstalled()) {
      $html = Markdown::defaultTransform($content);
      return $this->tidy($html);
    }
    else {
      $html = htmlentities($content);
      // Hyper link URLs using (hidden) _filter_url function.
      if (function_exists('_filter_url')) {
        $filter = (object) [
          'settings' => [
            'filter_url_length' => NULL,
          ],
        ];
        $html = _filter_url($html, $filter);
      }
      return '<pre>' . $html . '</pre>';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectPageHtml($module_name, $path = NULL) {
    $html = $this->getHtml($module_name, $path);

    // Convert escaped HTML tags to valid HTML for Drupal.org project page.
    $html = str_replace('&lt;', '<', $html);
    $html = str_replace('&gt;', '>', $html);

    return $html;
  }

  /**
   * Tidy an HTML string.
   *
   * @param string $html
   *   HTML string to be tidied.
   *
   * @return string
   *   A tidied HTML string.
   */
  protected function tidy($html) {
    if (!class_exists('\tidy')) {
      return $html;
    }

    // Configuration.
    // - http://us3.php.net/manual/en/book.tidy.php
    // - http://tidy.sourceforge.net/docs/quickref.html#wrap
    $config = ['show-body-only' => TRUE, 'wrap' => '0'];

    $tidy = new \tidy();
    $tidy->parseString($html, $config, 'utf8');
    $tidy->cleanRepair();
    $html = tidy_get_output($tidy);

    // Remove <code> tag nested within <pre> tag.
    $html = preg_replace('#<pre><code>\s*#', "<code>\n", $html);
    $html = preg_replace('#\s*</code></pre>#', "\n</code>", $html);

    // Remove space after <br> tags.
    $html = preg_replace('/(<br[^>]*>)\s+/', '\1', $html);

    return $html;
  }

}
