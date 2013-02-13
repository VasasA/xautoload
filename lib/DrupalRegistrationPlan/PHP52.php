<?php

class xautoload_DrupalRegistrationPlan_PHP52 {

  protected $finder;

  function __construct($finder) {
    $this->finder = $finder;
  }

  /**
   * Register prefixes and namespaces for enabled Drupal extensions.
   * (for namespaces, look at xautoload_DrupalRegistrationPlan_PHP53)
   */
  function start() {
    // Doing this directly tends to be a hell lot faster than system_list().
    // TODO:
    //   Is it safe to do this with a simple query?
    //   Do we need to expect any reset events?
    $extension_filepaths = db_query("SELECT name, filename, type from {system} WHERE status = 1")->fetchAll();
    $this->registerExtensionFilepaths($extension_filepaths);
  }

  /**
   * This is called during hook_init() / hook_custom_theme().
   */
  function mainPhase() {
    // Let other modules register stuff to the finder via hook_xautoload().
    $api = new xautoload_InjectedAPI_hookXautoload($this->finder);
    foreach (module_implements('xautoload') as $module) {
      $api->setModule($module);
      $f = $module . '_xautoload';
      $f($api);
    }
  }

  /**
   * Add modules after they have been enabled or installed.
   *
   * @param array $modules
   *   Array of module names, with numeric keys.
   */
  function addModules(array $modules) {
    $q = db_select('system');
    $q->condition('name', $modules);
    $q->fields('system', array('name', 'filename'));
    $extension_filepaths = $q->execute()->fetchAllKeyed();
    $this->registerExtensionFilepaths($extension_filepaths);
  }

  /**
   * Register prefixes for enabled Drupal extensions (modules/themes).
   *
   * @param array $extension_filepaths
   *   Associative array, keys are extension names, values are file paths.
   */
  protected function registerExtensionFilepaths($extension_filepaths) {
    $prefix_maps = array();
    foreach ($extension_filepaths as $info) {
      $prefix_maps[$info->type][$info->name] = dirname($info->filename) . '/lib';
    }
    $this->registerPrefixMaps($prefix_maps);
  }

  protected function registerPrefixMaps($prefix_maps) {
    foreach ($prefix_maps as $type => $map) {
      $missing_dir_plugin = new xautoload_MissingDirPlugin_DrupalExtensionPrefix($type, TRUE);
      $this->finder->registerPrefixesDeep($map, $missing_dir_plugin);
    }
  }
}
