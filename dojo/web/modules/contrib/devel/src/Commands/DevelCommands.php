<?php
namespace Drupal\devel\Commands;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Component\Uuid\Php;
use Drupal\Core\Utility\Token;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * For commands that are parts of modules, Drush expects to find commandfiles in
 * __MODULE__/src/Commands, and the namespace is Drupal/__MODULE__/Commands.
 *
 * In addition to a commandfile like this one, you need to add a drush.services.yml
 * in root of your module like this module does.
 */
class DevelCommands extends DrushCommands {

  protected $token;

  protected $container;

  protected $eventDispatcher;

  public function __construct(Token $token, $container, $eventDispatcher) {
    parent::__construct();
    $this->token = $token;
    $this->container = $container;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @return mixed
   */
  public function getEventDispatcher() {
    return $this->eventDispatcher;
  }

  /**
   * @return mixed
   */
  public function getContainer() {
    return $this->container;
  }

  /**
   * @return Token
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * Uninstall, and Install a list of modules.

   * @command devel-reinstall
   * @param $modules A comma-separated list of module names.
   * @aliases dre
   * @allow-additional-options pm-uninstall,pm-enable
   */
  public function reinstall($projects) {
    $projects = _convert_csv_to_array($projects);

    // This is faster than 3 separate bootstraps.
    $args = array_merge(array('pm-uninstall'), $projects);
    // @todo. Use $application dispatch instead of drush_invoke().
    call_user_func_array('drush_invoke', $args);

    $args = array_merge(array('pm-enable'), $projects);
    call_user_func_array('drush_invoke', $args);
  }

  /**
   * List implementations of a given hook and optionally edit one.
   *
   * @command devel-hook
   * @param $hook The name of the hook to explore.
   * @usage devel-hook cron
   *   List implementations of hook_cron().
   * @aliases fnh,fn-hook,hook
   */
  function hook($hook) {
    // Get implementations in the .install files as well.
    include_once './core/includes/install.inc';
    drupal_load_updates();

    if ($hook_implementations = \Drupal::moduleHandler()->getImplementations($hook)) {
      if ($choice = drush_choice(array_combine($hook_implementations, $hook_implementations), 'Enter the number of the hook implementation you wish to view.')) {
        $info= $this->codeLocate($choice . "_$hook");
        $exec = drush_get_editor();
        drush_shell_exec_interactive($exec, $info['file']);
      }
    }
    else {
      $this->logger()->success(dt('No implementations.'));
    }
  }

  /**
   * List implementations of a given event and optionally edit one.
   *
   * @command devel-event
   * @param $event The name of the event to explore. If omitted, a list of events is shown.
   * @usage devel-event
   *   Pick a Kernel event, then pick an implementation, and then view its source code.
   * @usage devel-event kernel.terminate
   *   Pick a terminate subscribers and view its source code.
   * @aliases fne,fn-event,event
   */
  function event($event) {
    $dispatcher = $this->getEventDispatcher();
    if (empty($event)) {
      // @todo Expand this list and move to interact().
      $events = array('kernel.controller', 'kernel.exception', 'kernel.request', 'kernel.response', 'kernel.terminate', 'kernel.view');
      $events = array_combine($events, $events);
      if (!$event = drush_choice($events, 'Enter the event you wish to explore.')) {
        throw new UserAbortException();
      }
    }
    if ($implementations = $dispatcher->getListeners($event)) {
      foreach ($implementations as $implementation) {
        $callable = get_class($implementation[0]) . '::' . $implementation[1];
        $choices[$callable] = $callable;
      }
      if ($choice = drush_choice($choices, 'Enter the number of the implementation you wish to view.')) {
        $info= $this->codeLocate($choice);
        $exec = drush_get_editor();
        drush_shell_exec_interactive($exec, $info['file']);
      }
    }
    else {
      $this->logger()->success(dt('No implementations.'));
    }
  }

  /**
   * List available tokens.
   *
   * @command devel-token
   * @aliases token
   * @field-labels
   *   group: Group
   *   token: Token
   *   name: Name
   * @default-fields group,token,name
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function token($options = ['format' => 'table']) {
    $all = $this->getToken()->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }

  /**
   * Generate a UUID.
   *
   * @command devel-uuid
   * @aliases uuid
   * @usage drush devel-uuid
   *   Outputs a Universally Unique Identifier.
   *
   * @return string
   */
  public function uuid() {
    $uuid = new Php();
    return $uuid->generate();
  }


  /**
   * Get source code line for specified function or method.
   */
  function codeLocate($function_name) {
    // Get implementations in the .install files as well.
    include_once './core/includes/install.inc';
    drupal_load_updates();

    if (strpos($function_name, '::') === FALSE) {
      if (!function_exists($function_name)) {
        throw new \Exception(dt('Function not found'));
      }
      $reflect = new \ReflectionFunction($function_name);
    }
    else {
      list($class, $method) = explode('::', $function_name);
      if (!method_exists($class, $method)) {
        throw new \Exception(dt('Method not found'));
      }
      $reflect = new \ReflectionMethod($class, $method);
    }
    return array('file' => $reflect->getFileName(), 'startline' => $reflect->getStartLine(), 'endline' => $reflect->getEndLine());

  }

  /**
   * Get a list of available container services.
   *
   * @command devel-services
   * @param $prefix A prefix to filter the service list by.
   * @aliases devel-container-services,dcs
   * @usage drush devel-services
   *   Gets a list of all available container services
   * @usage drush dcs plugin.manager
   *   Get all services containing "plugin.manager"
   *
   * @return array
   */
  public function services($prefix = NULL, $options = ['format' => 'yaml']) {
    $container = $this->getContainer();

    // Get a list of all available service IDs.
    $services = $container->getServiceIds();

    // If there is a prefix, try to find matches.
    if (isset($prefix)) {
      $services = preg_grep("/$prefix/", $services);
    }

    if (empty($services)) {
      throw new \Exception(dt('No container services found.'));
    }

    sort($services);
    return $services;
  }
}